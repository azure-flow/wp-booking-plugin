
(function () {
	'use strict';

	var REST_NS = 'sinmido-booking/v1';
	var SYMBOL_AVAILABLE = '◎';
	var SYMBOL_FEW = '△';
	var SYMBOL_NONE = '-';
	var SYMBOL_FULL = '×';
	var SYMBOL_INQUIRY = '📞';

	function getConfig(root) {
		var c = { restUrl: '', nonce: '' };
		if (root) {
			if (root.getAttribute('data-rest-url')) c.restUrl = root.getAttribute('data-rest-url');
			if (root.getAttribute('data-nonce')) c.nonce = root.getAttribute('data-nonce');
		}
		if (!c.restUrl && window.sinmidoBooking && window.sinmidoBooking.restUrl) c.restUrl = window.sinmidoBooking.restUrl;
		if (!c.nonce && window.sinmidoBooking && window.sinmidoBooking.nonce) c.nonce = window.sinmidoBooking.nonce;
		if (!c.restUrl && window.wpApiSettings && window.wpApiSettings.root) c.restUrl = window.wpApiSettings.root;
		return c;
	}

	function apiFetch(path, options, root) {
		options = options || {};
		var config = getConfig(root || document.querySelector('.sinmido-booking-root'));
		var base = config.restUrl ? config.restUrl.replace(/\/$/, '') : '';
		var url = base ? base + path : '';
		var headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
		if (config.nonce) headers['X-WP-Nonce'] = config.nonce;
		if (!url) {
			return Promise.reject(new Error('REST API URL が設定されていません。'));
		}
		return fetch(url, {
			method: options.method || 'GET',
			headers: headers,
			body: options.body,
			credentials: 'same-origin'
		}).then(function (res) {
			if (!res.ok) {
				return res.json().then(function (j) {
					var msg = (j && j.message) ? j.message : res.statusText;
					var err = new Error(msg);
					err.code = j && j.code ? j.code : '';
					throw err;
				}).catch(function (e) {
					if (e && e.message) throw e;
					throw new Error(res.statusText || '送信に失敗しました。');
				});
			}
			return res.json();
		});
	}

	function getEventId() {
		var root = document.querySelector('.sinmido-booking-root');
		if (!root) return null;
		var id = root.getAttribute('data-event-id');
		return id ? parseInt(id, 10) : (window.__sinmido_booking_event_id__ || null);
	}

	function loadRecaptchaScript(siteKey) {
		if (window.grecaptcha && window.grecaptcha.execute) {
			return Promise.resolve();
		}
		return new Promise(function (resolve, reject) {
			var s = document.createElement('script');
			s.src = 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent(siteKey);
			s.async = true;
			s.onload = function () {
				if (window.grecaptcha) resolve();
				else reject(new Error('reCAPTCHA failed to load'));
			};
			s.onerror = function () { reject(new Error('reCAPTCHA failed to load')); };
			document.head.appendChild(s);
		});
	}

	function getRecaptchaToken(siteKey) {
		return loadRecaptchaScript(siteKey).then(function () {
			return window.grecaptcha.execute(siteKey, { action: 'booking' });
		});
	}

	function loadTurnstileScript(siteKey) {
		if (window.turnstile && window.turnstile.render) {
			return Promise.resolve();
		}
		return new Promise(function (resolve, reject) {
			var s = document.createElement('script');
			s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
			s.async = true;
			s.onload = function () {
				if (window.turnstile) resolve();
				else reject(new Error('Turnstile failed to load'));
			};
			s.onerror = function () { reject(new Error('Turnstile failed to load')); };
			document.head.appendChild(s);
		});
	}

	function getTurnstileToken(siteKey) {
		return loadTurnstileScript(siteKey).then(function () {
			return new Promise(function (resolve, reject) {
				var container = document.createElement('div');
				container.className = 'sb-turnstile-widget';
				container.style.cssText = 'position:absolute;left:-9999px;';
				document.body.appendChild(container);
				var widgetId = window.turnstile.render(container, {
					sitekey: siteKey,
					size: 'invisible',
					appearance: 'interaction-only',
					callback: function (token) {
						try { if (container.parentNode) document.body.removeChild(container); } catch (e) {}
						resolve(token);
					},
					'error-callback': function () {
						try { if (container.parentNode) document.body.removeChild(container); } catch (e) {}
						reject(new Error('Turnstile verification failed'));
					},
					'expired-callback': function () {
						try { if (container.parentNode) document.body.removeChild(container); } catch (e) {}
						reject(new Error('Turnstile expired'));
					}
				});
				if (window.turnstile.execute) {
					window.turnstile.execute(widgetId);
				}
			});
		});
	}

	function getReasonByCode(code) {
		var reasons = {
			blocked: 'このメールアドレスまたは電話番号では、当サイトから予約・メール送信を行うことができません。',
			closed_date: '本日は当サイトの休業日です。',
			inquiry_only: '現在、予約を受け付けておりません。お問い合わせください。',
			past_date: '過去の日付は予約できません。',
			captcha_failed: '認証に失敗しました。再度お試しください。',
			recaptcha_failed: '認証に失敗しました。再度お試しください。',
			missing_datetime: '日時が指定されていません。',
			invalid_date: '日付の形式が正しくありません。'
		};
		return code ? reasons[code] || '' : '';
	}

	function pad2(n) {
		return (n < 10 ? '0' : '') + n;
	}

	function dateKey(y, m, d) {
		return y + '-' + pad2(m) + '-' + pad2(d);
	}

	function getStatusSymbol(status) {
		switch (status) {
			case 'available': return SYMBOL_AVAILABLE;
			case 'few': return SYMBOL_FEW;
			case 'full': return SYMBOL_FULL;
			case 'inquiry': return SYMBOL_INQUIRY;
			case 'closed': return SYMBOL_NONE;
			case 'none':
			default: return SYMBOL_NONE;
		}
	}

	function renderCalendar(root, eventId, eventName, settings, year, month, availability, systemSettings) {
		systemSettings = systemSettings || root._sbSystemSettings || {};
		var wrap = root.querySelector('.sb-booking-calendar-wrap');
		var selectedDate = wrap && wrap.getAttribute ? wrap.getAttribute('data-selected-date') : null;

		var first = new Date(year, month - 1, 1);
		var last = new Date(year, month, 0);
		var startDow = first.getDay();
		var daysInMonth = last.getDate();
		var prevMonth = month === 1 ? 12 : month - 1;
		var prevYear = month === 1 ? year - 1 : year;
		var prevLast = new Date(prevYear, prevMonth, 0).getDate();
		var contactPhone = (settings && settings.contact_phone) ? settings.contact_phone : '';
		var inquiryPhone = (systemSettings && systemSettings.inquiry_phone) ? systemSettings.inquiry_phone : contactPhone;

		var now = new Date();
		var nowYear = now.getFullYear();
		var nowMonth = now.getMonth() + 1;
		var isPrevDisabled = (year < nowYear) || (year === nowYear && month <= nowMonth);
		var minYear = root._sbCalendarMinYear;
		var minMonth = root._sbCalendarMinMonth;
		if (minYear != null && minMonth != null) {
			if (year < minYear || (year === minYear && month <= minMonth)) {
				isPrevDisabled = true;
			}
		}
		var html = '<div class="sb-booking-calendar">';
		html += '<div class="sb-calendar-header">';
		html += '<button type="button" class="sb-btn sb-btn-prev" aria-label="前月"' + (isPrevDisabled ? ' disabled' : '') + '>' +
			'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
			'<path d="M15 6L9 12L15 18" stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>' +
			'</svg></button>';
		html += '<span class="sb-calendar-title">' + year + '年' + month + '月</span>';
		html += '<button type="button" class="sb-btn sb-btn-next" aria-label="次月">' +
			'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
			'<path d="M9 6L15 12L9 18" stroke="white" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>' +
			'</svg></button>';
		html += '</div>';
		html += '<table class="sb-calendar-grid"><thead><tr>';
		['日', '月', '火', '水', '木', '金', '土'].forEach(function (d) {
			html += '<th>' + d + '</th>';
		});
		html += '</tr></thead><tbody>';

		var cells = [];
		for (var i = 0; i < startDow; i++) {
			var d = prevLast - startDow + i + 1;
			cells.push({ day: d, dateKey: dateKey(prevYear, prevMonth, d), currentMonth: false, status: 'none' });
		}
		var today = new Date();
		var todayKey = dateKey(today.getFullYear(), today.getMonth() + 1, today.getDate());
		for (var d = 1; d <= daysInMonth; d++) {
			var dk = dateKey(year, month, d);
			if (dk < todayKey) {
				cells.push({ day: d, dateKey: dk, currentMonth: true, status: 'none', remaining: null });
				continue;
			}
			var info = availability && availability[dk];
			var status = info ? info.status : 'none';
			var remaining = (info && typeof info.remaining === 'number') ? info.remaining : null;
			cells.push({ day: d, dateKey: dk, currentMonth: true, status: status, remaining: remaining });
		}
		var remaining = 7 - (cells.length % 7);
		if (remaining < 7) {
			for (var n = 1; n <= remaining; n++) {
				cells.push({ day: n, dateKey: dateKey(month === 12 ? year + 1 : year, month === 12 ? 1 : month + 1, n), currentMonth: false, status: 'none' });
			}
		}

		var showRemaining = !!(settings && settings.show_remaining_slots);
		var row = [];
		for (var i = 0; i < cells.length; i++) {
			var c = cells[i];
			var isInquiry = c.currentMonth && c.status === 'inquiry';
			var clickable = c.currentMonth && (c.status === 'available' || c.status === 'few') && c.status !== 'closed';
			var hasCircle = c.currentMonth && (c.status === 'available' || c.status === 'few');
			var cls = 'sb-day';
			if (!c.currentMonth) cls += ' sb-day-other';
			if (clickable) cls += ' sb-day-clickable';
			if (c.status === 'closed') cls += ' sb-day-closed';
			if (c.status === 'available') cls += ' sb-day-available';
			if (c.status === 'few') cls += ' sb-day-few';
			if (isInquiry) cls += ' sb-day-inquiry';
			if (selectedDate === c.dateKey) cls += ' sb-day-selected';
			var symbol = getStatusSymbol(c.status);
			var displayValue = symbol;
			var showAmount = showRemaining && c.currentMonth && (c.status === 'available' || c.status === 'few' || c.status === 'full') && c.remaining !== null;
			if (showAmount) {
				displayValue = String(c.remaining);
			}
			var amountOrSymbolClass = showAmount ? 'sb-day-amount-reservation' : 'sb-day-symbol';
			var numHtml = hasCircle
				? '<span class="sb-day-circle"><span class="sb-day-num">' + c.day + '</span></span>'
				: '<span class="sb-day-num">' + c.day + '</span>';
			row.push('<td class="' + cls + '" data-date="' + c.dateKey + '" data-status="' + c.status + '">' +
				numHtml + '<span class="' + amountOrSymbolClass + '">' + displayValue + '</span></td>');
			if (row.length === 7) {
				html += '<tr>' + row.join('') + '</tr>';
				row = [];
			}
		}
		html += '</div>';

		if (row.length) html += '<tr>' + row.join('') + '</tr>';
		html += '</tbody></table>';
		html += '<div class="sb-legend">';
		html += '<span>' + SYMBOL_AVAILABLE + ': 即予約可</span>';
		html += '<span>' + SYMBOL_FEW + ': 即予約可（残りわずか）</span>';
		html += '<span>' + SYMBOL_NONE + ': 予約不可</span>';
		html += '<span>' + SYMBOL_FULL + ': 予約一杯</span>';
		if (inquiryPhone) {
			html += '<span>' + SYMBOL_INQUIRY + ': 要問い合わせ (TEL.' + inquiryPhone + ')</span>';
		}
		html += '</div>';

		if (!wrap) {
			wrap = document.createElement('div');
			wrap.className = 'sb-booking-calendar-wrap';
			root.insertBefore(wrap, root.firstChild);
		}
		wrap.innerHTML = html;
		if (systemSettings.description) {
			var descEl = document.createElement('div');
			descEl.className = 'sb-description';
			descEl.textContent = systemSettings.description;
			wrap.querySelector('.sb-booking-calendar').appendChild(descEl);
		}
		wrap.querySelector('.sb-calendar-title').setAttribute('data-year', year);
		wrap.querySelector('.sb-calendar-title').setAttribute('data-month', month);

		wrap.querySelector('.sb-btn-prev').addEventListener('click', function () {
			var m = month === 1 ? 12 : month - 1;
			var y = month === 1 ? year - 1 : year;
			loadAndRenderCalendar(root, eventId, eventName, settings, y, m);
		});
		wrap.querySelector('.sb-btn-next').addEventListener('click', function () {
			var m = month === 12 ? 1 : month + 1;
			var y = month === 12 ? year + 1 : year;
			loadAndRenderCalendar(root, eventId, eventName, settings, y, m);
		});
		wrap.querySelectorAll('.sb-day-clickable').forEach(function (td) {
			td.addEventListener('click', function () {
				var date = td.getAttribute('data-date');
				wrap.setAttribute('data-selected-date', date);
				loadAndShowSlots(root, eventId, eventName, settings, date);
				renderCalendar(root, eventId, eventName, settings, year, month, availability, systemSettings);
			});
		});
		if (inquiryPhone) {
			wrap.querySelectorAll('.sb-day-inquiry').forEach(function (td) {
				td.addEventListener('click', function () {
					var tel = inquiryPhone.replace(/[^0-9+]/g, '');
					if (tel) {
						window.location.href = 'tel:' + tel;
					}
				});
			});
		}
	}

	function loadAndRenderCalendar(root, eventId, eventName, settings, year, month) {
		apiFetch('/' + REST_NS + '/events/' + eventId + '/availability?year=' + year + '&month=' + month, null, root)
			.then(function (availability) {
				renderCalendar(root, eventId, eventName, settings, year, month, availability, root._sbSystemSettings);
			})
			.catch(function (err) {
				var wrap = root.querySelector('.sb-booking-calendar-wrap');
				if (wrap) wrap.innerHTML = '<p class="sb-error">' + (err.message || '読み込みに失敗しました') + '</p>';
			});
	}

	function renderSlotsSection(root, eventId, eventName, settings, date, slots, formFields) {
		var wrap = root.querySelector('.sb-slots-wrap');
		if (!wrap) {
			wrap = document.createElement('div');
			wrap.className = 'sb-slots-wrap';
			var calWrap = root.querySelector('.sb-booking-calendar-wrap');
			root.insertBefore(wrap, calWrap ? calWrap.nextSibling : root.firstChild);
		}
		var dateLabel = date.replace(/-/g, '/');
		var html = '<div class="sb-slots-section">';
		html += '<h3 class="sb-slots-title">' + dateLabel + ' の時間枠</h3>';
		html += '<table class="sb-slots-table"><thead><tr><th>開催時間</th><th>空席状況</th><th></th></tr></thead><tbody>';
		slots.forEach(function (slot) {
			var symbol = slot.status === 'full' ? SYMBOL_FULL : (slot.status === 'few' ? SYMBOL_FEW : SYMBOL_AVAILABLE);
			var disabled = slot.status === 'full';
			html += '<tr>';
			html += '<td>' + slot.time_start + '~' + slot.time_end + '</td>';
			html += '<td><span class="sb-slot-symbol">' + symbol + '</span></td>';
			html += '<td><button type="button" class="sb-btn sb-btn-book" data-date="' + date + '" data-time-start="' + slot.time_start + '" data-time-end="' + slot.time_end + '"' + (disabled ? ' disabled' : '') + '>この時間で予約する &gt;</button></td>';
			html += '</tr>';
		});
		html += '</tbody></table>';
		html += '<p class="sb-disclaimer">※ 通信状況により対応できない場合があります。その際は、こちらから代替方法についてご連絡させていただきますので予めご了承ください。</p>';
		html += '</div>';
		wrap.innerHTML = html;
		wrap.style.display = 'block';

		wrap.querySelectorAll('.sb-btn-book:not([disabled])').forEach(function (btn) {
			btn.addEventListener('click', function () {
				showForm(root, eventId, eventName, formFields, date, btn.getAttribute('data-time-start'), btn.getAttribute('data-time-end'));
			});
		});
		var section = wrap.querySelector('.sb-slots-section');
		if (section && section.scrollIntoView) {
			requestAnimationFrame(function () {
				section.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		}
	}

	function loadAndShowSlots(root, eventId, eventName, settings, date) {
		var wrap = root.querySelector('.sb-slots-wrap');
		if (wrap) wrap.innerHTML = '<p class="sb-loading">読み込み中...</p>';
		apiFetch('/' + REST_NS + '/events/' + eventId + '/slots?date=' + encodeURIComponent(date), null, root)
			.then(function (slots) {
				var formFields = root._sbFormFields || [];
				renderSlotsSection(root, eventId, eventName, settings, date, slots, formFields);
			})
			.catch(function (err) {
				var w = root.querySelector('.sb-slots-wrap');
				if (w) w.innerHTML = '<p class="sb-error">' + (err.message || '読み込みに失敗しました') + '</p>';
			});
	}

	function showForm(root, eventId, eventName, formFields, date, timeStart, timeEnd) {
		var formWrap = root.querySelector('.sb-form-wrap');
		if (!formWrap) {
			formWrap = document.createElement('div');
			formWrap.className = 'sb-form-wrap';
			var slotsWrap = root.querySelector('.sb-slots-wrap');
			root.insertBefore(formWrap, slotsWrap ? slotsWrap.nextSibling : root.firstChild);
		}
		var dateTimeLabel = date.replace(/-/g, '/') + ' ' + timeStart + '~' + timeEnd;
		var html = '<div class="sb-reservation-form">';
		html += '<h3 class="sb-form-title">Form</h3>';
		html += '<p class="sb-form-subtitle">予約フォーム</p>';
		html += '<form class="sb-form" data-event-id="' + eventId + '">';
		html += '<input type="hidden" name="confirmed_date" value="' + date + '">';
		html += '<input type="hidden" name="confirmed_time_start" value="' + timeStart + '">';
		html += '<input type="hidden" name="confirmed_time_end" value="' + timeEnd + '">';
		html += '<div class="sb-form-row auto-filled"><label>参加希望イベント名 <span class="sb-required">自動入力</span></label><input type="text" readonly value="' + (eventName || '').replace(/"/g, '&quot;') + '"></div>';
		html += '<div class="sb-form-row auto-filled"><label>参加希望日時 <span class="sb-required">自動入力</span></label><input type="text" readonly value="' + dateTimeLabel.replace(/"/g, '&quot;') + '" placeholder="カレンダーから参加希望日を選択してください。"></div>';
		html += '<div class="sb-form-row"><label>第二希望日時（任意）</label><input type="text" name="second_preference" placeholder="例：2026/03/15 10:00~11:00"></div>';
		(formFields || []).forEach(function (f) {
			var id = f.id || ('field_' + Math.random().toString(36).slice(2));
			var label = (f.label || id) + (f.required ? ' <span class="sb-required">必須</span>' : '');
			var placeholder = f.placeholder || '';
			var rawType = (f.type || 'text').toString().toLowerCase();
			var hasOptions = f.options && Array.isArray(f.options) && f.options.length > 0;
			var isSelect = rawType === 'select';
			var isCheckbox = rawType === 'checkbox';
			var isRadio = rawType === 'radio';
			var fieldType = isSelect ? 'select' : (isCheckbox ? 'checkbox' : (isRadio ? 'radio' : rawType));
			var required = f.required ? ' required' : '';
			// custom_attributes は属性断片としてそのまま差し込む（ユーザーが " や ` を自由に使えるようにする）
			var customAttr = f.custom_attributes ? ' ' + String(f.custom_attributes) : '';
			if (fieldType === 'textarea') {
				html += '<div class="sb-form-row"><label>' + label + '</label><textarea name="' + id + '" placeholder="' + placeholder.replace(/"/g, '&quot;') + '"' + required + customAttr + '></textarea></div>';
			} else if (fieldType === 'select') {
				var optsSelect = hasOptions ? f.options : [];
				html += '<div class="sb-form-row"><label>' + label + '</label><select name="' + id + '"' + required + customAttr + '>';
				html += '<option value="">選択してください</option>';
				optsSelect.forEach(function (opt) {
					var val = (opt && typeof opt === 'string') ? opt : (opt && opt.value !== undefined ? opt.value : '');
					var text = (opt && opt.label !== undefined) ? opt.label : val;
					html += '<option value="' + String(val).replace(/"/g, '&quot;') + '">' + String(text).replace(/</g, '&lt;') + '</option>';
				});
				html += '</select></div>';
			} else if (fieldType === 'checkbox') {
				var optsCheckbox = hasOptions ? f.options : ['1'];
				html += '<div class="sb-form-row"><label>' + label + '</label><div class="sb-form-choices">';
				optsCheckbox.forEach(function (opt, idx) {
					var valCb = (opt && typeof opt === 'string') ? opt : (opt && opt.value !== undefined ? opt.value : '');
					var textCb = (opt && opt.label !== undefined) ? opt.label : valCb;
					html += '<label class="sb-choice"><input type="checkbox" name="' + id + '" value="' + String(valCb).replace(/"/g, '&quot;') + '"' + customAttr + '> ' + String(textCb).replace(/</g, '&lt;') + '</label>';
				});
				html += '</div></div>';
			} else if (fieldType === 'radio') {
				var optsRadio = hasOptions ? f.options : [];
				if (!optsRadio.length) {
					optsRadio = ['1'];
				}
				html += '<div class="sb-form-row"><label>' + label + '</label><div class="sb-form-choices">';
				optsRadio.forEach(function (opt, idx) {
					var valR = (opt && typeof opt === 'string') ? opt : (opt && opt.value !== undefined ? opt.value : '');
					var textR = (opt && opt.label !== undefined) ? opt.label : valR;
					var reqAttr = (f.required && idx === 0) ? ' required' : '';
					html += '<label class="sb-choice"><input type="radio" name="' + id + '" value="' + String(valR).replace(/"/g, '&quot;') + '"' + reqAttr + customAttr + '> ' + String(textR).replace(/</g, '&lt;') + '</label>';
				});
				html += '</div></div>';
			} else {
				var inputType = fieldType === 'email' ? 'email' : (fieldType === 'tel' ? 'tel' : (fieldType === 'number' ? 'number' : 'text'));
				html += '<div class="sb-form-row"><label>' + label + '</label><input type="' + inputType + '" name="' + id + '" placeholder="' + placeholder.replace(/"/g, '&quot;') + '"' + required + customAttr + '></div>';
			}
		});
		html += '<div class="sb-form-actions"><button type="submit" class="sb-btn sb-btn-primary">予約する</button></div>';
		html += '</form></div>';
		formWrap.innerHTML = html;
		formWrap.style.display = 'block';
		if (formWrap.scrollIntoView) {
			requestAnimationFrame(function () {
				formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		}

		formWrap.querySelector('.sb-form').addEventListener('submit', function (e) {
			e.preventDefault();
			var form = e.target;
			var fd = new FormData(form);
			var body = {
				confirmed_date: fd.get('confirmed_date'),
				confirmed_time_start: fd.get('confirmed_time_start'),
				confirmed_time_end: fd.get('confirmed_time_end')
			};
			var checkboxValues = {};
			form.querySelectorAll('input, textarea, select').forEach(function (el) {
				if (!el.name || el.name === 'confirmed_date' || el.name === 'confirmed_time_start' || el.name === 'confirmed_time_end') {
					return;
				}
				if (el.type === 'checkbox') {
					if (!el.checked) return;
					if (!checkboxValues[el.name]) {
						checkboxValues[el.name] = [];
					}
					checkboxValues[el.name].push(el.value);
					return;
				}
				if (el.type === 'radio') {
					if (!el.checked) return;
					body[el.name] = el.value;
					return;
				}
				body[el.name] = el.value;
			});
			Object.keys(checkboxValues).forEach(function (name) {
				body[name] = checkboxValues[name].join(',');
			});
			if (fd.get('second_preference')) body.second_preference = fd.get('second_preference');
			var submitBtn = form.querySelector('button[type="submit"]');
			var bookingRoot = form.closest('.sinmido-booking-root');
			var siteKey = (bookingRoot._sbSystemSettings || {}).recaptcha_site_key;
			submitBtn.disabled = true;
			submitBtn.textContent = '送信中...';
			function doSubmit() {
				apiFetch('/' + REST_NS + '/events/' + eventId + '/reservations', {
					method: 'POST',
					body: JSON.stringify(body)
				}, bookingRoot).then(function (res) {
					if (res.redirect_url) {
						window.location.href = res.redirect_url;
						return;
					}
					formWrap.innerHTML = '<p class="sb-success">予約を受け付けました。ありがとうございます。</p>';
				}).catch(function (err) {
					submitBtn.disabled = false;
					submitBtn.textContent = '予約する';
					var reason = err.message || getReasonByCode(err.code) || '送信に失敗しました。';
					alert('送信に失敗しました。\n\n理由：' + reason);
				});
			}
			function getCaptchaToken() {
				var settings = bookingRoot._sbSystemSettings || {};
				var turnstileKey = settings.turnstile_site_key;
				var recaptchaKey = settings.recaptcha_site_key;
				if (turnstileKey) {
					return getTurnstileToken(turnstileKey).then(function (token) {
						body.turnstile_token = token;
					});
				}
				if (recaptchaKey) {
					return getRecaptchaToken(recaptchaKey).then(function (token) {
						body.recaptcha_token = token;
					});
				}
				return Promise.resolve();
			}
			getCaptchaToken().then(function () {
				doSubmit();
			}).catch(function (err) {
				submitBtn.disabled = false;
				submitBtn.textContent = '予約する';
				alert('送信に失敗しました。\n\n理由：' + (err.message || '認証の読み込みに失敗しました。'));
			});
		});
	}

	function init() {
		var eventId = getEventId();
		if (!eventId) return;
		var root = document.querySelector('.sinmido-booking-root');
		if (!root) return;

		var config = getConfig(root);
		if (!config.restUrl) {
			root.innerHTML = '<p class="sb-error">REST API URL が設定されていません。</p>';
			return;
		}

		root.innerHTML = '<p class="sb-loading">読み込み中...</p>';
		Promise.all([
			apiFetch('/' + REST_NS + '/system-settings', null, root),
			apiFetch('/' + REST_NS + '/events/' + eventId, null, root)
		]).then(function (results) {
			var systemSettings = results[0];
			var event = results[1];
			root._sbSystemSettings = systemSettings;
			root._sbFormFields = event.form_fields || [];
			root.innerHTML = '';
			var settings = event.settings || {};
			var year;
			var month;
			root._sbCalendarMinYear = null;
			root._sbCalendarMinMonth = null;
			if (settings.fix_calendar_month && settings.fix_calendar_year && settings.fix_calendar_month_num) {
				var fixYear = parseInt(settings.fix_calendar_year, 10) || (new Date()).getFullYear();
				var fixMonth = parseInt(settings.fix_calendar_month_num, 10) || ((new Date()).getMonth() + 1);
				if (fixMonth < 1 || fixMonth > 12) fixMonth = (new Date()).getMonth() + 1;
				year = fixYear;
				month = fixMonth;
				var eventDateStr = event.date;
				if (eventDateStr && typeof eventDateStr === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(eventDateStr)) {
					var eventYear = parseInt(eventDateStr.slice(0, 4), 10);
					var eventMonth = parseInt(eventDateStr.slice(5, 7), 10);
					if (eventMonth >= 1 && eventMonth <= 12) {
						if (eventYear > year || (eventYear === year && eventMonth > month)) {
							year = eventYear;
							month = eventMonth;
						}
					}
				}
				root._sbCalendarMinYear = year;
				root._sbCalendarMinMonth = month;
			} else {
				var now = new Date();
				year = now.getFullYear();
				month = now.getMonth() + 1;
			}
			var now = new Date();
			var nowYear = now.getFullYear();
			var nowMonth = now.getMonth() + 1;
			if (root._sbCalendarMinYear != null && root._sbCalendarMinMonth != null) {
				if (nowYear < root._sbCalendarMinYear || (nowYear === root._sbCalendarMinYear && nowMonth < root._sbCalendarMinMonth)) {
					root.innerHTML = '<p class="sb-calendar-not-yet">上記イベントの予約は' + root._sbCalendarMinYear + '年' + root._sbCalendarMinMonth + '月よりご利用いただけます。</p>';
					return;
				}
			}
			loadAndRenderCalendar(root, eventId, event.name, settings, year, month);
		}).catch(function (err) {
			root.innerHTML = '<p class="sb-error">' + (err.message || '読み込みに失敗しました') + '</p>';
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
