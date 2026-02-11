
(function ($) {
	'use strict';

	$(document).on('click', '.sb-copy-shortcode', function () {
		var shortcode = $(this).data('shortcode');
		if (!shortcode) return;
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(shortcode).then(function () {
				alert('ショートコードをコピーしました。');
			}).catch(function () {
				fallbackCopy(shortcode);
			});
		} else {
			fallbackCopy(shortcode);
		}
	});

	function fallbackCopy(text) {
		var $t = $('<textarea>').val(text).css({ position: 'fixed', left: '-9999px' }).appendTo('body');
		$t[0].select();
		try {
			document.execCommand('copy');
			alert('ショートコードをコピーしました。');
		} catch (e) { }
		$t.remove();
	}

	$(document).on('click', '.sb-delete-event', function (e) {
		if (!confirm('このイベントを削除してもよろしいですか？')) {
			e.preventDefault();
		}
	});

	$(document).on('change', '.sb-select-all', function () {
		$('.sb-event-cb').prop('checked', this.checked);
	});

	$(document).on('change', '.sb-event-cb', function () {
		var total = $('.sb-event-cb').length;
		var checked = $('.sb-event-cb:checked').length;
		$('.sb-select-all').prop('checked', total > 0 && total === checked);
	});

	$(document).on('submit', '#sb-bulk-form', function () {
		var action = $(this).find('#sb-bulk-action-select').val();
		var count = $(this).find('.sb-event-cb:checked').length;
		if (!action || count === 0) {
			alert('イベントを選択し、一括操作を選んでから「適用」をクリックしてください。');
			return false;
		}
		if (action === 'delete' && !confirm('選択した ' + count + ' 件のイベントを削除してもよろしいですか？')) {
			return false;
		}
		return true;
	});

	$(document).on('click', '.sb-delete-reservation', function (e) {
		if (!confirm('この予約を削除してもよろしいですか？')) {
			e.preventDefault();
		}
	});

	$(document).on('change', '.sb-reservations-select-all', function () {
		$('.sb-reservation-cb').prop('checked', this.checked);
	});
	$(document).on('change', '.sb-reservation-cb', function () {
		var total = $('.sb-reservation-cb').length;
		var checked = $('.sb-reservation-cb:checked').length;
		$('.sb-reservations-select-all').prop('checked', total > 0 && total === checked);
	});
	$(document).on('submit', '#sb-reservations-bulk-form', function () {
		var action = $(this).find('#sb-reservations-bulk-action-select').val();
		var count = $(this).find('.sb-reservation-cb:checked').length;
		if (!action || count === 0) {
			alert('予約を選択し、一括操作を選んでから「適用」をクリックしてください。');
			return false;
		}
		if (action === 'delete' && !confirm('選択した ' + count + ' 件の予約を削除してもよろしいですか？')) {
			return false;
		}
		return true;
	});

	$(document).on('change', '#sb_reservation_status', function () {
		var status = $(this).val();
		var $block = $('#sb-confirmed-date-block');
		var $date = $('#sb_confirmed_date');
		var $start = $('#sb_confirmed_time_start');
		var $end = $('#sb_confirmed_time_end');
		if (status === 'confirmed') {
			$block.show();
			$date.prop('disabled', false);
			$start.prop('disabled', false);
			$end.prop('disabled', false);
		} else {
			$block.hide();
			$date.prop('disabled', true);
			$start.prop('disabled', true);
			$end.prop('disabled', true);
		}
	});

	$(document).on('click', '.sb-tab-btn', function () {
		var tab = $(this).data('sb-tab');
		if (!tab) return;
		$('.sb-tab-panel').addClass('sba-hidden');
		$('[data-sb-panel="' + tab + '"]').removeClass('sba-hidden');
		$('.sb-tab-btn').removeClass('nav-tab-active sba-bg-gray-100 sba-font-medium').addClass('sba-border-transparent');
		$(this).addClass('nav-tab-active sba-bg-gray-100 sba-font-medium').removeClass('sba-border-transparent');
		if (tab === 'schedule') {
			window.sbSchedule && window.sbSchedule.render();
		}
	});

	$(function () {
		var $panel = $('#sb-panel-schedule');
		if (!$panel.length) return;

		var $form = $('#sb-event-edit-form');
		var $hidden = $form.find('#sb_schedule_slots');
		if (!$hidden.length) $hidden = $('#sb_schedule_slots');
		var $body = $panel.find('.sb-schedule-calendar-body');
		var $monthTitle = $panel.find('.sb-schedule-month-title');
		var $prevLabel = $panel.find('.sb-schedule-prev-label');
		var $nextLabel = $panel.find('.sb-schedule-next-label');

		function getSlots() {
			var raw = $hidden.val();
			if (raw) {
				try {
					var arr = JSON.parse(raw);
					if (Array.isArray(arr)) return arr;
				} catch (e) { }
			}

			if (typeof window.sbScheduleSlotsInitial !== 'undefined' && Array.isArray(window.sbScheduleSlotsInitial)) {
				return window.sbScheduleSlotsInitial;
			}
			return [];
		}

		function ensureInitialSlots() {
			if (typeof window.sbScheduleSlotsInitial !== 'undefined' && Array.isArray(window.sbScheduleSlotsInitial)) {
				$hidden.val(JSON.stringify(window.sbScheduleSlotsInitial));
			} else {
				$hidden.val('[]');
			}
		}

		function setSlots(arr) {
			$hidden.val(JSON.stringify(arr));
		}

		function getSelectionMode() {
			// Always allow multiple-date selection mode
			return true;
		}

		function getSelectedDates() {
			var sel = $panel.data('sb-selected-dates');
			return Array.isArray(sel) ? sel : [];
		}

		function setSelectedDates(arr) {
			$panel.data('sb-selected-dates', arr);
		}

		function getEditDate() {
			return $panel.data('sb-edit-date') || null;
		}

		function setEditDate(dateStr) {
			if (dateStr) $panel.data('sb-edit-date', dateStr);
			else $panel.removeData('sb-edit-date');
		}

		// --- Schedule modal (multiple time ranges) ---
		var $slotRows = $('#sb-schedule-slot-rows');
		var $rowTemplate = $slotRows.find('.sb-schedule-time-row').first().clone();

		function getModalRows() {
			return $slotRows.find('.sb-schedule-time-row');
		}

		function updateRowLabels() {
			getModalRows().each(function (i) {
				$(this).find('.sba-font-medium.sba-text-gray-700').first().text('開催時間 ' + (i + 1));
			});
		}

		function addTimeRow(slotData) {
			slotData = slotData || {};
			var $row = $rowTemplate.clone();
			$row.find('.sb-slot-time-start').val(slotData.time_start || '10:00');
			$row.find('.sb-slot-time-end').val(slotData.time_end || '12:00');
			$slotRows.append($row);
			updateRowLabels();
		}

		function fillModalFromSlots(slots) {
			$slotRows.empty();
			if (slots && slots.length) {
				slots.forEach(function (s) { addTimeRow(s); });
				$('#sb-slot-interval').val(slots[0].interval_minutes || 60);
				$('#sb-slot-groups').val(slots[0].max_concurrent || 1);
				$('#sb-slot-duration').val(slots[0].duration_minutes || 60);
			} else {
				addTimeRow({});
			}
		}

		function fillModalFromSlot(slot) {
			fillModalFromSlots(slot ? [slot] : []);
		}

		function yearMonth() {
			var y = parseInt($monthTitle.data('year'), 10) || new Date().getFullYear();
			var m = parseInt($monthTitle.data('month'), 10) || (new Date().getMonth() + 1);
			return { year: y, month: m };
		}

		function setYearMonth(y, m) {
			$monthTitle.data('year', y).data('month', m).text(y + '年' + m + '月');
			var months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
			$prevLabel.text((m === 1 ? 12 : m - 1) + '月');
			$nextLabel.text((m === 12 ? 1 : m + 1) + '月');
		}

		function dateKey(y, m, d) {
			return y + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0');
		}

		function slotsForDate(dateStr) {
			return getSlots().filter(function (s) { return s.date === dateStr; });
		}

		function slotLabel(s) {
			return (s.time_start || '') + '~' + (s.time_end || '');
		}

		function renderCalendar() {
			var ym = yearMonth();
			var y = ym.year, m = ym.month;
			var first = new Date(y, m - 1, 1);
			var last = new Date(y, m, 0);
			var startDay = first.getDay();
			var daysInMonth = last.getDate();
			var prevMonth = m === 1 ? 12 : m - 1;
			var prevYear = m === 1 ? y - 1 : y;
			var prevLast = new Date(prevYear, prevMonth, 0).getDate();
			var nextM = m === 12 ? 1 : m + 1;
			var nextY = m === 12 ? y + 1 : y;

			var rows = [];
			var cells = [];
			var i, d, dateStr, slots, label, isSelectMode, selected, isSelected, cls;

			isSelectMode = getSelectionMode();
			selected = getSelectedDates();

			for (i = 0; i < startDay; i++) {
				d = prevLast - startDay + i + 1;
				dateStr = dateKey(prevYear, prevMonth, d);
				slots = slotsForDate(dateStr);
				label = slots.length ? slots.map(slotLabel).join(', ') : '';
				isSelected = selected.indexOf(dateStr) !== -1;
				cls = 'sba-border sba-border-gray-300 sba-p-1 sba-align-top sba-text-gray-400 ';
				if (isSelectMode && isSelected) cls += 'sba-bg-blue-200 ';
				if (isSelectMode) cls += 'sb-schedule-day sb-schedule-day-other-month';
				else if (slots.length) cls += 'sb-schedule-day sb-schedule-day-has-slots sb-schedule-day-other-month';
				cells.push('<td class="' + cls + '" data-date="' + dateStr + '" data-other="1"><div class="sb-schedule-cell-inner">' + d + (label ? '<br><span class="sb-schedule-slot-label">' + label + '</span>' : '') + '</div></td>');
			}

			for (d = 1; d <= daysInMonth; d++) {
				if (cells.length === 7) {
					rows.push('<tr>' + cells.join('') + '</tr>');
					cells = [];
				}
				dateStr = dateKey(y, m, d);
				slots = slotsForDate(dateStr);
				label = slots.length ? slots.map(slotLabel).join(', ') : '';
				isSelected = selected.indexOf(dateStr) !== -1;
				cls = 'sba-border sba-border-gray-300 sba-p-1 sba-align-top ';
				if (isSelectMode && isSelected) cls += 'sba-bg-blue-200 ';
				if (isSelectMode) cls += 'sb-schedule-day';
				else if (slots.length) cls += 'sb-schedule-day sb-schedule-day-has-slots';
				cells.push('<td class="' + cls + '" data-date="' + dateStr + '"><div class="sb-schedule-cell-inner">' + d + (label ? '<br><span class="sb-schedule-slot-label">' + label + '</span>' : '') + '</div></td>');
			}

			d = 1;
			while (cells.length < 7) {
				dateStr = dateKey(nextY, nextM, d);
				slots = slotsForDate(dateStr);
				label = slots.length ? slots.map(slotLabel).join(', ') : '';
				isSelected = selected.indexOf(dateStr) !== -1;
				cls = 'sba-border sba-border-gray-300 sba-p-1 sba-align-top sba-text-gray-400 ';
				if (isSelectMode && isSelected) cls += 'sba-bg-blue-200 ';
				if (isSelectMode) cls += 'sb-schedule-day sb-schedule-day-other-month';
				else if (slots.length) cls += 'sb-schedule-day sb-schedule-day-has-slots sb-schedule-day-other-month';
				cells.push('<td class="' + cls + '" data-date="' + dateStr + '" data-other="1"><div class="sb-schedule-cell-inner">' + d + (label ? '<br><span class="sb-schedule-slot-label">' + label + '</span>' : '') + '</div></td>');
				d++;
			}
			if (cells.length) rows.push('<tr>' + cells.join('') + '</tr>');
			$body.html(rows.join(''));
		}

		$panel.on('click', '.sb-schedule-enter-select', function () {
			$panel.find('.sb-schedule-view-default').addClass('sba-hidden');
			$panel.find('.sb-schedule-view-select').removeClass('sba-hidden');
			setSelectedDates([]);
			renderCalendar();
		});

		$panel.on('click', '.sb-schedule-cancel-select', function () {
			$panel.find('.sb-schedule-view-select').addClass('sba-hidden');
			$panel.find('.sb-schedule-view-default').removeClass('sba-hidden');
			setSelectedDates([]);
			renderCalendar();
		});

		$panel.on('click', '.sb-schedule-delete-select', function () {
			var dates = getSelectedDates();
			if (!dates.length) {
				alert('削除する日付を選択してください。');
				return;
			}
			var slots = getSlots().filter(function (s) { return dates.indexOf(s.date) === -1; });
			setSlots(slots);
			setSelectedDates([]);
			renderCalendar();
		});

		$panel.on('click', '.sb-schedule-day', function () {
			var dateStr = $(this).data('date');
			if (!dateStr) return;
			if (getSelectionMode()) {
				var sel = getSelectedDates().slice();
				var idx = sel.indexOf(dateStr);
				if (idx === -1) sel.push(dateStr); else sel.splice(idx, 1);
				setSelectedDates(sel);
				renderCalendar();
			} else if ($(this).hasClass('sb-schedule-day-has-slots')) {
				var daySlots = slotsForDate(dateStr);
				if (daySlots.length) {
					setEditDate(dateStr);
					setSelectedDates([dateStr]);
					fillModalFromSlots(daySlots);
					$('#sb-schedule-slot-modal').removeClass('sba-hidden');
				}
			}
		});

		$panel.on('click', '.sb-schedule-add-slot', function () {
			if (getSelectedDates().length === 0) {
				alert('日付を選択してください。');
				return;
			}
			setEditDate(null);
			fillModalFromSlots([]);
			$('#sb-schedule-slot-modal').removeClass('sba-hidden');
		});

		$panel.on('click', '.sb-schedule-prev-month', function () {
			var ym = yearMonth();
			var m = ym.month === 1 ? 12 : ym.month - 1;
			var y = ym.month === 1 ? ym.year - 1 : ym.year;
			setYearMonth(y, m);
			renderCalendar();
		});

		$panel.on('click', '.sb-schedule-next-month', function () {
			var ym = yearMonth();
			var m = ym.month === 12 ? 1 : ym.month + 1;
			var y = ym.month === 12 ? ym.year + 1 : ym.year;
			setYearMonth(y, m);
			renderCalendar();
		});

		$panel.closest('.wrap').on('click', '#sb-schedule-slot-modal', function (e) {
			if (e.target === this) {
				$('#sb-schedule-slot-modal').addClass('sba-hidden');
			}
		});

		$('#sb-schedule-slot-modal').on('click', '.sba-bg-white.sba-rounded-lg', function (e) {
			e.stopPropagation();
		});

		$('#sb-schedule-slot-modal').on('click', '.sb-modal-close', function (e) {
			e.preventDefault();
			e.stopPropagation();
			$('#sb-schedule-slot-modal').addClass('sba-hidden');
		});

		$('#sb-schedule-slot-modal').on('click', '.sb-schedule-add-time-row', function (e) {
			e.preventDefault();
			addTimeRow({});
		});

		$('#sb-schedule-slot-modal').on('click', '.sb-schedule-row-remove', function (e) {
			e.preventDefault();
			var $row = $(this).closest('.sb-schedule-time-row');
			if (getModalRows().length <= 1) return;
			$row.remove();
			updateRowLabels();
		});

		$('#sb-schedule-slot-modal').find('.sba-bg-white.sba-rounded-lg').on('click', '.sb-schedule-slot-save', function (e) {
			e.preventDefault();
			e.stopPropagation();
			var interval = parseInt($('#sb-slot-interval').val(), 10) || 60;
			var groups = parseInt($('#sb-slot-groups').val(), 10) || 1;
			var duration = parseInt($('#sb-slot-duration').val(), 10) || 60;
			var rows = [];
			getModalRows().each(function () {
				var $r = $(this);
				var timeStart = $r.find('.sb-slot-time-start').val();
				var timeEnd = $r.find('.sb-slot-time-end').val();
				if (timeStart && timeEnd) {
					rows.push({
						time_start: timeStart,
						time_end: timeEnd,
						interval_minutes: interval,
						max_concurrent: groups,
						duration_minutes: duration
					});
				}
			});
			if (!rows.length) {
				alert('開催時間を選択してください。');
				return;
			}
			var editDate = getEditDate();
			var slots;
			if (editDate) {
				slots = getSlots().filter(function (s) { return s.date !== editDate; });
				rows.forEach(function (row) {
					slots.push({
						date: editDate,
						time_start: row.time_start,
						time_end: row.time_end,
						interval_minutes: row.interval_minutes,
						max_concurrent: row.max_concurrent,
						duration_minutes: row.duration_minutes
					});
				});
				setEditDate(null);
			} else {
				var dates = getSelectedDates();
				if (!dates.length) {
					alert('日付を選択してください。');
					return;
				}
				slots = getSlots().filter(function (s) { return dates.indexOf(s.date) === -1; });
				dates.forEach(function (dateStr) {
					rows.forEach(function (row) {
						slots.push({
							date: dateStr,
							time_start: row.time_start,
							time_end: row.time_end,
							interval_minutes: row.interval_minutes,
							max_concurrent: row.max_concurrent,
							duration_minutes: row.duration_minutes
						});
					});
				});
				setSelectedDates([]);
			}
			setSlots(slots);
			$('#sb-schedule-slot-modal').addClass('sba-hidden');
			renderCalendar();
		});

		window.sbSchedule = { render: renderCalendar };
		ensureInitialSlots();
		var ym = yearMonth();
		setYearMonth(ym.year, ym.month);
		renderCalendar();
	});

	$(function () {
		var $formPanel = $('#sb-panel-formsetting');
		if (!$formPanel.length) return;
		var $formForm = $('#sb-event-edit-form');
		var $formHidden = $formForm.find('#sb_form_fields');
		if (!$formHidden.length) $formHidden = $('#sb_form_fields');
		var $listView = $formPanel.find('.sb-form-setting-list-view');
		var $editView = $formPanel.find('.sb-form-setting-edit-view');
		var $listEl = $formPanel.find('.sb-form-fields-list');
		var editIndex = -1;

		function getFormFields() {
			var raw = $formHidden.val();
			if (raw) {
				try {
					var arr = JSON.parse(raw);
					if (Array.isArray(arr)) return arr;
				} catch (e) {}
			}
			if (typeof window.sbFormFieldsInitial !== 'undefined' && Array.isArray(window.sbFormFieldsInitial)) {
				return window.sbFormFieldsInitial;
			}
			return [];
		}

		function ensureInitialFormFields() {
			if (typeof window.sbFormFieldsInitial !== 'undefined' && Array.isArray(window.sbFormFieldsInitial)) {
				$formHidden.val(JSON.stringify(window.sbFormFieldsInitial));
			} else {
				$formHidden.val('[]');
			}
		}

		function setFormFields(arr) {
			$formHidden.val(JSON.stringify(arr));
		}

		function initSortable() {
			if (typeof $.fn.sortable !== 'function') return;
			if ($listEl.data('ui-sortable')) {
				$listEl.sortable('destroy');
			}
			$listEl.sortable({
				axis: 'y',
				items: '.sb-form-field-item',
				update: function () {
					var fields = getFormFields();
					var newOrder = [];
					$listEl.find('.sb-form-field-item').each(function () {
						var idx = parseInt($(this).attr('data-index'), 10);
						if (!isNaN(idx) && fields[idx]) {
							newOrder.push(fields[idx]);
						}
					});
					if (newOrder.length) {
						setFormFields(newOrder);
						renderList();
					}
				}
			});
		}

		function renderList() {
			var fields = getFormFields();
			var html = '';
			fields.forEach(function (f, idx) {
				var label = (f.label || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				html += '<li class="sb-form-field-item sba-border sba-border-gray-200 sba-rounded sba-p-3 sba-bg-gray-50" data-index="' + idx + '">';
				html += '<div class="sba-flex sba-items-center sba-justify-between">';
				html += '<span class="sba-font-medium">' + label + (f.required ? ' *' : '') + '</span>';
				html += '<span class="sba-text-sm sba-flex sba-items-center sba-gap-2">';
				html += '<button type="button" class="sb-form-field-move-up sba-text-gray-500 hover:sba-underline" data-index="' + idx + '">↑</button>';
				html += '<button type="button" class="sb-form-field-move-down sba-text-gray-500 hover:sba-underline" data-index="' + idx + '">↓</button>';
				html += '<span class="sba-mx-1">|</span>';
				html += '<button type="button" class="sb-form-field-edit sba-text-blue-600 hover:sba-underline" data-index="' + idx + '">編集</button>';
				html += '<span class="sba-mx-1">|</span>';
				html += '<button type="button" class="sb-form-field-delete sba-text-blue-600 hover:sba-underline" data-index="' + idx + '">削除</button>';
				html += '</span>';
				html += '</div></li>';
			});
			$listEl.html(html || '<li class="sba-text-gray-500 sba-py-2">項目がありません。</li>');
			if (fields.length) {
				initSortable();
			}
		}

		function showEditView(title) {
			$editView.find('.sb-form-edit-title').text(title || '新規項目を追加');
			$listView.addClass('sba-hidden');
			$editView.removeClass('sba-hidden');
		}

		function hideEditView() {
			$editView.addClass('sba-hidden');
			$listView.removeClass('sba-hidden');
		}

		function fillOptionsList(options) {
			var $wrap = $('#sb-form-field-options-wrap');
			var $list = $wrap.find('.sb-form-options-list');
			$list.empty();
			(options || ['']).forEach(function (val) {
				var $row = $('<div class="sba-flex sba-gap-2 sba-items-center"></div>');
				var $input = $('<input type="text" class="sb-form-option-input sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" />').val(val || '');
				$row.append($input);
				$row.append($('<button type="button" class="button button-small sb-form-option-remove">削除</button>'));
				$list.append($row);
			});
		}

		function getOptionsFromForm() {
			var opts = [];
			$('#sb-form-field-options-wrap .sb-form-option-input').each(function () {
				var v = $(this).val().trim();
				if (v) opts.push(v);
			});
			return opts;
		}

		function toggleOptionsWrap() {
			var t = $('#sb-form-field-type').val();
			if (t === 'select' || t === 'checkbox' || t === 'radio') {
				$('#sb-form-field-options-wrap').removeClass('sba-hidden');
			} else {
				$('#sb-form-field-options-wrap').addClass('sba-hidden');
			}
		}

		$formPanel.on('click', '.sb-form-field-add', function () {
			editIndex = -1;
			$('#sb-form-field-id').val('').prop('readonly', false);
			$('#sb-form-field-label').val('');
			$('#sb-form-field-placeholder').val('');
			$('input[name="sb_form_field_required"][value="1"]').prop('checked', true);
			$('#sb-form-field-type').val('text');
			$('#sb-form-field-custom').val('');
			fillOptionsList(['']);
			toggleOptionsWrap();
			showEditView('新規項目を追加');
		});

		$formPanel.on('click', '.sb-form-field-edit', function () {
			var idx = parseInt($(this).data('index'), 10);
			var fields = getFormFields();
			if (idx < 0 || idx >= fields.length) return;
			var f = fields[idx];
			editIndex = idx;
			$('#sb-form-field-id').val(f.id).prop('readonly', true);
			$('#sb-form-field-label').val(f.label || '');
			$('#sb-form-field-placeholder').val(f.placeholder || '');
			$('input[name="sb_form_field_required"]').prop('checked', false).filter('[value="' + (f.required ? '1' : '0') + '"]').prop('checked', true);
			$('#sb-form-field-type').val(f.type || 'text');
			$('#sb-form-field-custom').val(f.custom_attributes || '');
			fillOptionsList(f.options && f.options.length ? f.options : ['']);
			toggleOptionsWrap();
			showEditView('項目を編集');
		});

		$formPanel.on('click', '.sb-form-field-delete', function () {
			var idx = parseInt($(this).data('index'), 10);
			var fields = getFormFields();
			if (idx < 0 || idx >= fields.length) return;
			if (!confirm('この項目を削除してもよろしいですか？')) return;
			fields = fields.slice(0, idx).concat(fields.slice(idx + 1));
			setFormFields(fields);
			renderList();
		});

		$formPanel.on('click', '.sb-form-edit-cancel', function () {
			hideEditView();
		});

		$formPanel.on('change', '#sb-form-field-type', function () {
			toggleOptionsWrap();
		});

		$formPanel.on('click', '.sb-form-option-add', function () {
			$('#sb-form-field-options-wrap .sb-form-options-list').append(
				'<div class="sba-flex sba-gap-2 sba-items-center"><input type="text" class="sb-form-option-input sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" value="" /><button type="button" class="button button-small sb-form-option-remove">削除</button></div>'
			);
		});

		$formPanel.on('click', '.sb-form-option-remove', function () {
			$(this).closest('.sba-flex').remove();
		});

		$formPanel.on('click', '.sb-form-field-save', function () {
			var id = $('#sb-form-field-id').val().trim().replace(/\s+/g, '_');
			var label = $('#sb-form-field-label').val().trim();
			var type = $('#sb-form-field-type').val();
			if (!id) {
				alert('ユニークIDを入力してください。');
				return;
			}
			if (!/^[a-zA-Z0-9_]+$/.test(id)) {
				alert('ユニークIDは半角英数字・アンダースコアのみ使用できます。');
				return;
			}
			if (!label) {
				alert('項目名を入力してください。');
				return;
			}
			if (type === 'select' || type === 'checkbox' || type === 'radio') {
				var opts = getOptionsFromForm();
				if (!opts.length) {
					alert('選択肢を1つ以上入力してください。');
					return;
				}
			}
			var fields = getFormFields().slice();
			var newField = {
				id: id,
				label: label,
				placeholder: $('#sb-form-field-placeholder').val().trim(),
				required: $('input[name="sb_form_field_required"]:checked').val() === '1',
				type: type,
				options: (type === 'select' || type === 'checkbox' || type === 'radio') ? getOptionsFromForm() : [],
				custom_attributes: $('#sb-form-field-custom').val().trim()
			};
			if (editIndex >= 0) {
				fields[editIndex] = newField;
			} else {
				var exists = fields.some(function (f) { return f.id === id; });
				if (exists) {
					alert('このユニークIDは既に使用されています。');
					return;
				}
				fields.push(newField);
			}
			setFormFields(fields);
			renderList();
			hideEditView();
		});

		ensureInitialFormFields();
		renderList();

		// Fallback reordering for touch devices: move up/down buttons
		$formPanel.on('click', '.sb-form-field-move-up', function () {
			var idx = parseInt($(this).data('index'), 10);
			var fields = getFormFields();
			if (isNaN(idx) || idx <= 0 || idx >= fields.length) return;
			var tmp = fields[idx - 1];
			fields[idx - 1] = fields[idx];
			fields[idx] = tmp;
			setFormFields(fields);
			renderList();
		});

		$formPanel.on('click', '.sb-form-field-move-down', function () {
			var idx = parseInt($(this).data('index'), 10);
			var fields = getFormFields();
			if (isNaN(idx) || idx < 0 || idx >= fields.length - 1) return;
			var tmp = fields[idx + 1];
			fields[idx + 1] = fields[idx];
			fields[idx] = tmp;
			setFormFields(fields);
			renderList();
		});
	});

	$(document).on('click', '.sb-settings-tab-btn', function () {
		var tab = $(this).data('sb-tab');
		if (!tab) return;
		$('.sb-settings-panel').addClass('sba-hidden');
		$('[data-sb-panel="' + tab + '"]').removeClass('sba-hidden');
		$('.sb-settings-tab-btn')
			.removeClass('sba-bg-gray-100 sba-font-medium')
			.addClass('sba-border-b-2 sba-border-transparent');
		$(this)
			.addClass('sba-bg-gray-100 sba-font-medium')
			.removeClass('sba-border-transparent');
	});

	$(function () {
		var $form = $('#sb-settings-form');
		var $hidden = $form.find('#sb_blacklist_json');
		var $tbody = $('#sb-blacklist-tbody');
		var $panel = $('#sb-panel-settings-blacklist');
		if (!$form.length || !$hidden.length || !$tbody.length || !$panel.length) return;

		var sbBlacklistData = (typeof window.sbBlacklistInitial !== 'undefined' && Array.isArray(window.sbBlacklistInitial))
			? window.sbBlacklistInitial.map(function (r) {
				return { email: r.email || '', phone: r.phone || '', memo: r.memo || '', updated: r.updated || '' };
			})
			: [];
		var blacklistEditIndex = -1;
		var $modal = $('#sb-blacklist-modal');
		var dateFormatLabel = $tbody.closest('.sb-settings-panel').find('.sb-date-label').first().text() || '最終更新日';

		function formatUpdated(updated) {
			if (!updated) return '—';
			var d = new Date(updated);
			if (isNaN(d.getTime())) return updated;
			var y = d.getFullYear();
			var m = d.getMonth() + 1;
			var day = d.getDate();
			var h = d.getHours();
			var min = d.getMinutes();
			var am = h < 12;
			var h12 = h % 12 || 12;
			var ampm = am ? 'AM' : 'PM';
			return y + '年' + m + '月' + day + '日 ' + h12 + ':' + (min < 10 ? '0' : '') + min + ' ' + ampm;
		}

		function syncHidden() {
			$hidden.val(JSON.stringify(sbBlacklistData));
		}

		function renderBlacklist() {
			var html = '';
			if (sbBlacklistData.length === 0) {
				html = '<tr><td colspan="5" class="sba-text-gray-500">登録がありません。</td></tr>';
			} else {
				sbBlacklistData.forEach(function (row, idx) {
					html += '<tr class="sb-blacklist-row" data-index="' + idx + '">' +
						'<th scope="row" class="check-column"><input type="checkbox" class="sb-blacklist-cb" value="' + idx + '" /></th>' +
						'<td class="column-email"><div class="sba-inline-block"><strong>' + (row.email ? escapeHtml(row.email) : '—') + '</strong>' +
						'<div class="row-actions"><span class="edit"><a href="#" class="sb-blacklist-edit">編集</a></span>' +
						'<span class="separator">|</span><span class="trash"><a href="#" class="sb-blacklist-delete submitdelete">削除</a></span></div></td>' +
						'<td class="column-phone">' + (row.phone ? escapeHtml(row.phone) : '—') + '</td>' +
						'<td class="column-memo">' + (row.memo ? escapeHtml(row.memo) : '—') + '</td>' +
						'<td class="column-date"><span class="sb-date-label">' + escapeHtml(dateFormatLabel) + '</span><br />' + formatUpdated(row.updated) + '</td></tr>';
				});
			}
			$tbody.html(html);
			syncHidden();
		}

		function escapeHtml(s) {
			var div = document.createElement('div');
			div.textContent = s;
			return div.innerHTML;
		}

		function openModal(isEdit, index) {
			blacklistEditIndex = isEdit ? index : -1;
			$modal.find('.sb-blacklist-modal-title').text(blacklistEditIndex >= 0 ? '編集' : '情報を追加');
			if (blacklistEditIndex >= 0 && sbBlacklistData[blacklistEditIndex]) {
				var r = sbBlacklistData[blacklistEditIndex];
				$modal.find('#sb-blacklist-modal-email').val(r.email || '');
				$modal.find('#sb-blacklist-modal-phone').val(r.phone || '');
				$modal.find('#sb-blacklist-modal-memo').val(r.memo || '');
			} else {
				$modal.find('#sb-blacklist-modal-email').val('');
				$modal.find('#sb-blacklist-modal-phone').val('');
				$modal.find('#sb-blacklist-modal-memo').val('');
			}
			$modal.removeClass('sba-hidden');
		}

		function closeModal() {
			$modal.addClass('sba-hidden');
			blacklistEditIndex = -1;
		}

		$panel.on('click', '#sb-blacklist-add', function () {
			openModal(false);
		});
		$panel.on('click', '.sb-blacklist-edit', function (e) {
			e.preventDefault();
			var idx = parseInt($(this).closest('tr').data('index'), 10);
			if (!isNaN(idx)) openModal(true, idx);
		});
		$panel.on('click', '.sb-blacklist-delete', function (e) {
			e.preventDefault();
			if (!confirm('この項目を削除してもよろしいですか？')) return;
			var idx = parseInt($(this).closest('tr').data('index'), 10);
			if (!isNaN(idx) && sbBlacklistData[idx]) {
				sbBlacklistData.splice(idx, 1);
				renderBlacklist();
			}
		});
		$modal.find('.sb-blacklist-modal-cancel').on('click', closeModal);
		$modal.find('.sb-blacklist-modal-save').on('click', function () {
			var email = $modal.find('#sb-blacklist-modal-email').val().trim();
			var phone = $modal.find('#sb-blacklist-modal-phone').val().trim();
			var memo = $modal.find('#sb-blacklist-modal-memo').val().trim();
			if (!email && !phone) {
				alert('メールアドレスまたは電話番号のいずれかを入力してください。');
				return;
			}
			var now = new Date().toISOString().replace('T', ' ').substring(0, 19);
			if (blacklistEditIndex >= 0) {
				sbBlacklistData[blacklistEditIndex] = { email: email, phone: phone, memo: memo, updated: now };
			} else {
				sbBlacklistData.push({ email: email, phone: phone, memo: memo, updated: now });
			}
			renderBlacklist();
			closeModal();
		});
		$modal.on('click', function (e) {
			if (e.target === this) closeModal();
		});

		$panel.on('change', '#sb-blacklist-select-all', function () {
			$tbody.find('.sb-blacklist-cb').prop('checked', this.checked);
		});
		$panel.on('click', '#sb-blacklist-apply', function () {
			var action = $('#sb-blacklist-bulk-action').val();
			if (action !== 'delete') return;
			var indices = [];
			$tbody.find('.sb-blacklist-cb:checked').each(function () {
				indices.push(parseInt($(this).val(), 10));
			});
			if (indices.length === 0) {
				alert('削除する項目を選択してください。');
				return;
			}
			if (!confirm('選択した ' + indices.length + ' 件を削除してもよろしいですか？')) return;
			indices.sort(function (a, b) { return b - a; });
			indices.forEach(function (i) { sbBlacklistData.splice(i, 1); });
			renderBlacklist();
			$('#sb-blacklist-select-all').prop('checked', false);
		});

		renderBlacklist();

		$form.on('submit', function () {
			syncHidden();
		});
	});
})(jQuery);
