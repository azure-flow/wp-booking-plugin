<?php

if (! defined('ABSPATH')) {
	exit;
}

class Sinmido_Booking_REST
{

	const NAMESPACE = 'sinmido-booking/v1';


	public function register_routes()
	{
		register_rest_route(
			self::NAMESPACE,
			'/events/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_event'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ($param) {
							return is_numeric($param);
						},
					),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/events/(?P<id>\d+)/availability',
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_availability'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id'    => array(
						'validate_callback' => function ($param) {
							return is_numeric($param);
						},
					),
					'year'  => array(
						'required'          => true,
						'validate_callback' => function ($param) {
							return is_numeric($param) && (int) $param >= 1970 && (int) $param <= 2100;
						},
					),
					'month' => array(
						'required'          => true,
						'validate_callback' => function ($param) {
							return is_numeric($param) && (int) $param >= 1 && (int) $param <= 12;
						},
					),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/events/(?P<id>\d+)/slots',
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_slots_for_date'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id'   => array(
						'validate_callback' => function ($param) {
							return is_numeric($param);
						},
					),
					'date' => array(
						'required'          => true,
						'validate_callback' => function ($param) {
							return is_string($param) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
						},
					),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/events/(?P<id>\d+)/reservations',
			array(
				'methods'             => 'POST',
				'callback'            => array($this, 'create_reservation'),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'validate_callback' => function ($param) {
							return is_numeric($param);
						},
					),
				),
			)
		);
		register_rest_route(
			self::NAMESPACE,
			'/system-settings',
			array(
				'methods'             => 'GET',
				'callback'            => array($this, 'get_system_settings'),
				'permission_callback' => '__return_true',
			)
		);
	}


	private function get_system_settings_array()
	{
		$options = get_option(Sinmido_Booking_Admin::OPTION_SYSTEM_SETTINGS, array());
		if (! is_array($options)) {
			$options = array();
		}
		$defaults = Sinmido_Booking_Admin::default_system_settings();
		$merged   = array_merge($defaults, $options);
		$start    = isset($merged['long_holiday_start']) ? $merged['long_holiday_start'] : '';
		$end      = isset($merged['long_holiday_end']) ? $merged['long_holiday_end'] : '';
		if ($start !== '') {
			$start = date('Y-m-d', strtotime(str_replace('/', '-', $start)));
		}
		if ($end !== '') {
			$end = date('Y-m-d', strtotime(str_replace('/', '-', $end)));
		}
		return array(
			'description'         => isset($merged['description']) ? (string) $merged['description'] : '',
			'inquiry_mode'        => isset($merged['inquiry_mode']) ? (string) $merged['inquiry_mode'] : '',
			'inquiry_phone'       => isset($merged['inquiry_phone']) ? (string) $merged['inquiry_phone'] : '',
			'regular_holidays'    => isset($merged['regular_holidays']) && is_array($merged['regular_holidays']) ? array_map('strval', $merged['regular_holidays']) : array(),
			'long_holiday_start'  => $start,
			'long_holiday_end'    => $end,
			'recaptcha_site_key'  => isset($merged['recaptcha_site_key']) ? (string) $merged['recaptcha_site_key'] : '',
			'turnstile_site_key'  => isset($merged['turnstile_site_key']) ? (string) $merged['turnstile_site_key'] : '',
		);
	}


	private function get_full_system_settings()
	{
		$options = get_option(Sinmido_Booking_Admin::OPTION_SYSTEM_SETTINGS, array());
		if (! is_array($options)) {
			$options = array();
		}
		return array_merge(Sinmido_Booking_Admin::default_system_settings(), $options);
	}


	private function get_holiday_period_text()
	{
		$sys   = $this->get_system_settings_array();
		$parts = array();
		$days  = array(
			'0' => __('日曜日', 'sinmido-booking'),
			'1' => __('月曜日', 'sinmido-booking'),
			'2' => __('火曜日', 'sinmido-booking'),
			'3' => __('水曜日', 'sinmido-booking'),
			'4' => __('木曜日', 'sinmido-booking'),
			'5' => __('金曜日', 'sinmido-booking'),
			'6' => __('土曜日', 'sinmido-booking'),
		);
		if (! empty($sys['regular_holidays']) && is_array($sys['regular_holidays'])) {
			$names = array();
			foreach ($sys['regular_holidays'] as $dw) {
				$names[] = isset($days[(string) $dw]) ? $days[(string) $dw] : $dw;
			}
			$parts[] = sprintf(__('毎週%sが休業日です。', 'sinmido-booking'), implode('・', $names));
		}
		if (! empty($sys['long_holiday_start']) && ! empty($sys['long_holiday_end'])) {
			$start_fmt = date_i18n(__('Y年n月j日', 'sinmido-booking'), strtotime($sys['long_holiday_start']));
			$end_fmt   = date_i18n(__('Y年n月j日', 'sinmido-booking'), strtotime($sys['long_holiday_end']));
			$parts[]   = sprintf(__('%1$s～%2$sまで長期休業しています。', 'sinmido-booking'), $start_fmt, $end_fmt);
		}
		return implode(' ', $parts);
	}


	private function is_system_closed_date($date)
	{
		$sys = $this->get_system_settings_array();
		$dw  = (int) date('w', strtotime($date));
		if (! empty($sys['regular_holidays']) && in_array((string) $dw, $sys['regular_holidays'], true)) {
			return true;
		}
		if ($sys['long_holiday_start'] !== '' && $sys['long_holiday_end'] !== '') {
			$ts = strtotime($date);
			if ($ts >= strtotime($sys['long_holiday_start']) && $ts <= strtotime($sys['long_holiday_end'])) {
				return true;
			}
		}
		return false;
	}


	public function get_system_settings(WP_REST_Request $request)
	{
		return rest_ensure_response($this->get_system_settings_array());
	}


	private function verify_recaptcha($token, $secret)
	{
		if ($token === '' || $secret === '') {
			return false;
		}
		$res = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
			'body' => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
			),
			'timeout' => 10,
		));
		if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
			return false;
		}
		$body = json_decode(wp_remote_retrieve_body($res), true);
		return ! empty($body['success']) && (! isset($body['score']) || (float) $body['score'] >= 0.5);
	}


	private function verify_turnstile($token, $secret)
	{
		if ($token === '' || $secret === '') {
			return false;
		}
		$res = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
			'body' => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
			),
			'timeout' => 10,
		));
		if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
			return false;
		}
		$body = json_decode(wp_remote_retrieve_body($res), true);
		return ! empty($body['success']);
	}


	public function get_event(WP_REST_Request $request)
	{
		$id   = (int) $request['id'];
		$post = get_post($id);
		if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT || $post->post_status !== 'publish') {
			return new WP_Error('not_found', __('Event not found.', 'sinmido-booking'), array('status' => 404));
		}

		$settings = get_post_meta($post->ID, '_sb_settings', true);
		$settings = is_string($settings) ? json_decode($settings, true) : array();
		if (! is_array($settings)) {
			$settings = Sinmido_Booking_Event_CPT::default_settings();
		}

		$schedule_slots_raw = get_post_meta($post->ID, '_sb_schedule_slots', true);
		$schedule_slots     = is_string($schedule_slots_raw) ? json_decode($schedule_slots_raw, true) : array();
		if (! is_array($schedule_slots)) {
			$schedule_slots = array();
		}

		$form_fields_raw = get_post_meta($post->ID, '_sb_form_fields', true);
		$form_fields     = is_string($form_fields_raw) ? json_decode($form_fields_raw, true) : null;
		if (! is_array($form_fields)) {
			$form_fields = Sinmido_Booking_Event_CPT::default_form_fields();
		}

		$data = array(
			'id'               => $post->ID,
			'name'             => get_post_meta($post->ID, '_sb_event_name', true) ?: $post->post_title,
			'date'             => get_post_meta($post->ID, '_sb_event_date', true),
			'time_start'       => get_post_meta($post->ID, '_sb_event_time_start', true),
			'time_end'         => get_post_meta($post->ID, '_sb_event_time_end', true),
			'slots_per_slot'   => (int) get_post_meta($post->ID, '_sb_slots_per_slot', true) ?: 1,
			'settings'         => $settings,
			'schedule_slots'   => $schedule_slots,
			'form_fields'      => $form_fields,
		);

		return rest_ensure_response($data);
	}


	private function count_reservations($event_id, $date, $time_start = null)
	{
		$args = array(
			'post_type'      => SINMIDO_BOOKING_CPT_RESERVATION,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array('key' => '_sb_event_id', 'value' => (int) $event_id, 'compare' => '='),
				array('key' => '_sb_confirmed_date', 'value' => $date, 'compare' => '='),
				array('key' => '_sb_status', 'value' => 'cancelled', 'compare' => '!='),
			),
		);
		if ($time_start !== null && $time_start !== '') {
			$args['meta_query'][] = array('key' => '_sb_confirmed_time_start', 'value' => $time_start, 'compare' => '=');
		}
		$query = new WP_Query($args);
		return $query->found_posts;
	}


	public function get_availability(WP_REST_Request $request)
	{
		$id    = (int) $request['id'];
		$year  = (int) $request['year'];
		$month = (int) $request['month'];

		$post = get_post($id);
		if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT || $post->post_status !== 'publish') {
			return new WP_Error('not_found', __('Event not found.', 'sinmido-booking'), array('status' => 404));
		}

		$schedule_slots_raw = get_post_meta($post->ID, '_sb_schedule_slots', true);
		$schedule_slots     = is_string($schedule_slots_raw) ? json_decode($schedule_slots_raw, true) : array();
		if (! is_array($schedule_slots)) {
			$schedule_slots = array();
		}

		$settings = get_post_meta($post->ID, '_sb_settings', true);
		$settings = is_string($settings) ? json_decode($settings, true) : array();
		if (! is_array($settings)) {
			$settings = Sinmido_Booking_Event_CPT::default_settings();
		}
		$threshold = isset($settings['remaining_display_threshold']) ? (int) $settings['remaining_display_threshold'] : 3;

		$sys              = $this->get_full_system_settings();
		$deadline_type    = isset($sys['deadline_type']) ? (string) $sys['deadline_type'] : 'days_before';
		$deadline_days    = isset($sys['deadline_days_before']) ? (int) $sys['deadline_days_before'] : 0;
		$deadline_time    = isset($sys['deadline_time']) ? (string) $sys['deadline_time'] : '18:00';
		$deadline_hours   = isset($sys['deadline_hours_before']) ? (int) $sys['deadline_hours_before'] : 0;
		$inquiry_enabled  = isset($sys['inquiry_mode']) && $sys['inquiry_mode'] === 'enabled';
		$now_ts           = current_time('timestamp');

		$days_in_month = (int) gmdate('t', strtotime($year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '-01'));
		$today         = gmdate('Y-m-d');
		$result        = array();

		$dates_with_slots = array();
		foreach ($schedule_slots as $slot) {
			if (empty($slot['date']) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $slot['date'])) {
				continue;
			}
			$slot_year  = (int) substr($slot['date'], 0, 4);
			$slot_month = (int) substr($slot['date'], 5, 2);
			if ($slot_year === $year && $slot_month === $month) {
				$dates_with_slots[$slot['date']] = true;
			}
		}

		foreach ($dates_with_slots as $date => $dummy) {
			if ($date < $today) {
				continue;
			}
			$day_slots = array_values(array_filter($schedule_slots, function ($s) use ($date) {
				return isset($s['date']) && $s['date'] === $date;
			}));
			$total_capacity = 0;
			$total_remaining = 0;
			foreach ($day_slots as $s) {
				$interval = isset($s['interval_minutes']) ? max(1, (int) $s['interval_minutes']) : 60;
				$max_con  = isset($s['max_concurrent']) ? max(1, (int) $s['max_concurrent']) : 1;
				$start    = isset($s['time_start']) ? $s['time_start'] : '09:00';
				$end      = isset($s['time_end']) ? $s['time_end'] : '17:00';
				$t1       = strtotime('1970-01-01 ' . $start);
				$t2       = strtotime('1970-01-01 ' . $end);
				if ($t2 <= $t1) {
					continue;
				}
				for ($t = $t1; $t < $t2; $t += $interval * 60) {
					$slot_time = gmdate('H:i', $t);
					$cap       = $max_con;
					$booked    = $this->count_reservations($id, $date, $slot_time);
					$remaining_slot = max(0, $cap - $booked);
					$total_capacity += $cap;
					$total_remaining += $remaining_slot;
				}
			}
			if ($total_capacity <= 0) {
				$status = 'none';
			} elseif ($total_remaining <= 0) {
				$status = 'full';
			} elseif ($total_remaining <= $threshold) {
				$status = 'few';
			} else {
				$status = 'available';
			}

			$is_system_closed = $this->is_system_closed_date($date);
			if ($is_system_closed) {
				$status = 'closed';
			} elseif ($inquiry_enabled && $total_capacity > 0) {
				$deadline_ts = null;
				if ($deadline_type === 'days_before') {
					$time_str = preg_match('/^\d{2}:\d{2}$/', $deadline_time) ? $deadline_time : '00:00';
					$deadline_ts = strtotime($date . ' ' . $time_str) - ($deadline_days * DAY_IN_SECONDS);
				} elseif ($deadline_type === 'hours_before') {
					$deadline_ts = strtotime($date . ' 00:00:00') - ($deadline_hours * HOUR_IN_SECONDS);
				}
				if ($deadline_ts && $now_ts >= $deadline_ts) {
					$status = 'inquiry';
				}
			}
			$result[$date] = array(
				'status'    => $status,
				'total'     => $total_capacity,
				'remaining' => $total_remaining,
			);
		}

		return rest_ensure_response($result);
	}


	public function get_slots_for_date(WP_REST_Request $request)
	{
		$id   = (int) $request['id'];
		$date = $request['date'];

		$today = gmdate('Y-m-d');
		if ($date < $today) {
			return new WP_Error('past_date', __('過去の日付は選択できません。', 'sinmido-booking'), array('status' => 403));
		}
		if ($this->is_system_closed_date($date)) {
			$holiday_text = $this->get_holiday_period_text();
			$closed_msg   = __('本日は当サイトの休業日です。', 'sinmido-booking');
			if ($holiday_text !== '') {
				$closed_msg .= ' ' . $holiday_text;
			} else {
				$closed_msg .= ' ' . __('この日は予約を受け付けておりません。', 'sinmido-booking');
			}
			return new WP_Error('closed_date', $closed_msg, array('status' => 403));
		}

		$post = get_post($id);
		if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT || $post->post_status !== 'publish') {
			return new WP_Error('not_found', __('Event not found.', 'sinmido-booking'), array('status' => 404));
		}

		$schedule_slots_raw = get_post_meta($post->ID, '_sb_schedule_slots', true);
		$schedule_slots     = is_string($schedule_slots_raw) ? json_decode($schedule_slots_raw, true) : array();
		if (! is_array($schedule_slots)) {
			$schedule_slots = array();
		}

		$day_slots = array_filter($schedule_slots, function ($s) use ($date) {
			return isset($s['date']) && $s['date'] === $date;
		});

		$settings = get_post_meta($post->ID, '_sb_settings', true);
		$settings = is_string($settings) ? json_decode($settings, true) : array();
		$threshold = isset($settings['remaining_display_threshold']) ? (int) $settings['remaining_display_threshold'] : 3;

		$result = array();
		foreach ($day_slots as $s) {
			$interval = isset($s['interval_minutes']) ? max(1, (int) $s['interval_minutes']) : 60;
			$max_con  = isset($s['max_concurrent']) ? max(1, (int) $s['max_concurrent']) : 1;
			$start    = isset($s['time_start']) ? $s['time_start'] : '09:00';
			$end      = isset($s['time_end']) ? $s['time_end'] : '17:00';
			$duration = isset($s['duration_minutes']) ? max(1, (int) $s['duration_minutes']) : 60;
			$t1       = strtotime('1970-01-01 ' . $start);
			$t2       = strtotime('1970-01-01 ' . $end);
			if ($t2 <= $t1) {
				continue;
			}
			for ($t = $t1; $t < $t2; $t += $interval * 60) {
				$slot_time_start = gmdate('H:i', $t);
				$slot_time_end   = gmdate('H:i', $t + $duration * 60);
				$booked          = $this->count_reservations($id, $date, $slot_time_start);
				$remaining       = max(0, $max_con - $booked);
				$result[] = array(
					'time_start'       => $slot_time_start,
					'time_end'         => $slot_time_end,
					'max_concurrent'   => $max_con,
					'remaining'        => $remaining,
					'status'           => $remaining <= 0 ? 'full' : ($remaining <= $threshold ? 'few' : 'available'),
				);
			}
		}

		usort($result, function ($a, $b) {
			return strcmp($a['time_start'], $b['time_start']);
		});

		return rest_ensure_response($result);
	}


	public function create_reservation(WP_REST_Request $request)
	{
		$id = (int) $request['id'];
		$post = get_post($id);
		if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT || $post->post_status !== 'publish') {
			return new WP_Error('not_found', __('Event not found.', 'sinmido-booking'), array('status' => 404));
		}

		$sys = $this->get_system_settings_array();
		$params = $request->get_json_params();
		if (! is_array($params)) {
			$params = $request->get_body_params();
		}
		if (! is_array($params)) {
			$params = array();
		}

		$confirmed_date = isset($params['confirmed_date']) ? sanitize_text_field($params['confirmed_date']) : '';
		$confirmed_time_start = isset($params['confirmed_time_start']) ? sanitize_text_field($params['confirmed_time_start']) : '';
		$confirmed_time_end   = isset($params['confirmed_time_end']) ? sanitize_text_field($params['confirmed_time_end']) : '';
		if ($confirmed_date === '' || $confirmed_time_start === '') {
			return new WP_Error('missing_datetime', __('Date and time are required.', 'sinmido-booking'), array('status' => 400));
		}
		if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $confirmed_date)) {
			return new WP_Error('invalid_date', __('Invalid date format.', 'sinmido-booking'), array('status' => 400));
		}
		$today = gmdate('Y-m-d');
		if ($confirmed_date < $today) {
			return new WP_Error('past_date', __('過去の日付は予約できません。', 'sinmido-booking'), array('status' => 403));
		}
		if ($this->is_system_closed_date($confirmed_date)) {
			$holiday_text = $this->get_holiday_period_text();
			$closed_msg   = __('本日は当サイトの休業日です。', 'sinmido-booking');
			if ($holiday_text !== '') {
				$closed_msg .= ' ' . $holiday_text;
			} else {
				$closed_msg .= ' ' . __('この日は予約を受け付けておりません。', 'sinmido-booking');
			}
			return new WP_Error('closed_date', $closed_msg, array('status' => 403));
		}

		$options = get_option(Sinmido_Booking_Admin::OPTION_SYSTEM_SETTINGS, array());
		$recaptcha_secret = isset($options['recaptcha_secret_key']) ? $options['recaptcha_secret_key'] : '';
		$turnstile_secret = isset($options['turnstile_secret_key']) ? $options['turnstile_secret_key'] : '';
		$captcha_ok = false;
		if ($recaptcha_secret !== '') {
			$token = isset($params['recaptcha_token']) ? sanitize_text_field($params['recaptcha_token']) : '';
			$captcha_ok = ($token !== '' && $this->verify_recaptcha($token, $recaptcha_secret));
		}
		if (! $captcha_ok && $turnstile_secret !== '') {
			$token = isset($params['turnstile_token']) ? sanitize_text_field($params['turnstile_token']) : '';
			$captcha_ok = ($token !== '' && $this->verify_turnstile($token, $turnstile_secret));
		}
		if ($recaptcha_secret !== '' || $turnstile_secret !== '') {
			if (! $captcha_ok) {
				return new WP_Error('captcha_failed', __('認証に失敗しました。再度お試しください。', 'sinmido-booking'), array('status' => 400));
			}
		}

		$form_fields_raw = get_post_meta($post->ID, '_sb_form_fields', true);
		$form_fields     = is_string($form_fields_raw) ? json_decode($form_fields_raw, true) : null;
		if (! is_array($form_fields)) {
			$form_fields = Sinmido_Booking_Event_CPT::default_form_fields();
		}

		$custom = array();
		$name   = '';
		$email  = '';
		$phone  = '';
		foreach ($form_fields as $field) {
			$fid = isset($field['id']) ? $field['id'] : '';
			if ($fid === '') {
				continue;
			}
			$val = isset($params[$fid]) ? $params[$fid] : '';
			if (is_array($val)) {
				$val = implode(', ', $val);
			}
			$val = sanitize_text_field((string) $val);
			if ($fid === 'name' || $fid === 'お名前') {
				$name = $val;
			} elseif ($fid === 'email' || $fid === 'メールアドレス') {
				$email = sanitize_email($val);
			} elseif ($fid === 'phone' || $fid === '電話番号') {
				$phone = $val;
			}
			$custom[$fid] = $val;
		}
		if ($name === '') {
			$name = isset($params['name']) ? sanitize_text_field($params['name']) : (isset($params['お名前']) ? sanitize_text_field($params['お名前']) : __('Reservation', 'sinmido-booking'));
		}

		$blacklist = Sinmido_Booking_Admin::get_blacklist();
		$email_n   = strtolower(trim($email));
		$phone_n   = preg_replace('/[^\d+]/', '', $phone);
		$is_warning = false;
		$blacklist_memo = '';
		foreach ($blacklist as $row) {
			$bl_email = isset($row['email']) ? strtolower(trim((string) $row['email'])) : '';
			$bl_phone = isset($row['phone']) ? preg_replace('/[^\d+]/', '', (string) $row['phone']) : '';
			$bl_memo  = isset($row['memo']) ? (string) $row['memo'] : '';
			if ($bl_email !== '' && $email_n !== '' && $email_n === $bl_email) {
				$is_warning    = true;
				$blacklist_memo = $bl_memo;
				break;
			}
			if ($bl_phone !== '' && $phone_n !== '' && $phone_n === $bl_phone) {
				$is_warning    = true;
				$blacklist_memo = $bl_memo;
				break;
			}
		}

		$post_data = array(
			'post_type'   => SINMIDO_BOOKING_CPT_RESERVATION,
			'post_status' => 'publish',
			'post_title'  => $name . ' - ' . $confirmed_date . ' ' . $confirmed_time_start,
		);
		$reservation_id = wp_insert_post($post_data);
		if (is_wp_error($reservation_id) || $reservation_id === 0) {
			return new WP_Error('create_failed', __('Could not create reservation.', 'sinmido-booking'), array('status' => 500));
		}

		update_post_meta($reservation_id, '_sb_event_id', $id);
		update_post_meta($reservation_id, '_sb_status', $is_warning ? 'warning' : 'tentative');
		update_post_meta($reservation_id, '_sb_name', $name);
		update_post_meta($reservation_id, '_sb_email', $email);
		update_post_meta($reservation_id, '_sb_phone', $phone);
		update_post_meta($reservation_id, '_sb_confirmed_date', $confirmed_date);
		update_post_meta($reservation_id, '_sb_confirmed_time_start', $confirmed_time_start);
		update_post_meta($reservation_id, '_sb_confirmed_time_end', $confirmed_time_end ? $confirmed_time_end : $confirmed_time_start);
		update_post_meta($reservation_id, '_sb_custom_fields', wp_json_encode($custom, JSON_UNESCAPED_UNICODE));
		update_post_meta($reservation_id, '_sb_remarks', isset($params['remarks']) ? sanitize_textarea_field($params['remarks']) : '');
		if ($is_warning && $blacklist_memo !== '') {
			update_post_meta($reservation_id, '_sb_blacklist_memo', sanitize_textarea_field($blacklist_memo));
		}
		update_post_meta($reservation_id, '_sb_second_preference', isset($params['second_preference']) ? sanitize_text_field($params['second_preference']) : '');

		$this->send_booking_emails($reservation_id, $post, $name, $email, $phone, $custom, $confirmed_date, $confirmed_time_start, $confirmed_time_end);

		$settings = get_post_meta($post->ID, '_sb_settings', true);
		$settings = is_string($settings) ? json_decode($settings, true) : array();
		$redirect_url = isset($settings['redirect_after_booking']) ? $settings['redirect_after_booking'] : '';
		$redirect_enabled = ! empty($settings['redirect_enabled']);

		return rest_ensure_response(array(
			'success'         => true,
			'reservation_id'   => $reservation_id,
			'redirect_url'    => ($redirect_enabled && $redirect_url !== '') ? $redirect_url : '',
		));
	}


	private function log_mail($message, $context = array())
	{
		$prefix = '[Sinmido Booking Mail] ';
		$line   = $prefix . gmdate('Y-m-d H:i:s') . ' ' . $message;
		if (! empty($context)) {
			$line .= ' ' . wp_json_encode($context, JSON_UNESCAPED_UNICODE);
		}
		$line .= "\n";
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log(trim($line));
		}
		$log_dir  = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : dirname(dirname(dirname(__DIR__)));
		$log_file = $log_dir . '/sinmido-booking-mail.log';
		if (is_writable($log_dir) || (file_exists($log_file) && is_writable($log_file))) {
			@file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
		}
	}


	private function replace_mail_placeholders($text, $replace)
	{
		if (! is_string($text)) {
			return '';
		}
		foreach ($replace as $key => $value) {
			$text = str_replace('[' . $key . ']', (string) $value, $text);
		}
		return $text;
	}


	private function send_booking_emails($reservation_id, $event_post, $name, $email, $phone, $custom, $confirmed_date, $confirmed_time_start, $confirmed_time_end)
	{
		$this->log_mail('send_booking_emails called', array(
			'reservation_id' => $reservation_id,
			'guest_email'    => $email ? (substr($email, 0, 2) . '***') : '(empty)',
		));
		$sys = $this->get_full_system_settings();
		$this->log_mail('email settings', array(
			'notify_on_booking'   => ! empty($sys['notify_on_booking']),
			'send_confirm_email' => ! empty($sys['send_confirm_email']),
			'admin_recipient'    => ! empty($sys['admin_recipient']) ? '(set)' : '(empty)',
			'customer_recipient' => isset($sys['customer_recipient']) ? $sys['customer_recipient'] : '(not set)',
			'mail_format'        => isset($sys['mail_format']) ? $sys['mail_format'] : 'html',
		));
		$date_format = ! empty($sys['date_format']) ? $sys['date_format'] : 'Y年n月j日';
		$time_format = ! empty($sys['time_format']) ? $sys['time_format'] : 'H:i';
		$date_formatted = date_i18n($date_format, strtotime($confirmed_date . ' 12:00:00'));
		$time_range    = $confirmed_time_start . '~' . $confirmed_time_end;
		$date_time_str = $date_formatted . ' ' . $time_range;

		$replace = array_merge(
			array(
				'DateAndTime' => $date_time_str,
				'Date'        => $date_formatted,
				'Time'        => $time_range,
				'name'        => $name,
				'phone'       => $phone,
				'email'       => $email,
				'remarks'     => isset($custom['remarks']) ? $custom['remarks'] : '',
				'second_preference' => isset($custom['second_preference']) ? $custom['second_preference'] : '',
			),
			$custom
		);

		$is_html = (isset($sys['mail_format']) && $sys['mail_format'] === 'text') ? false : true;
		$admin_email  = isset($sys['admin_email']) ? $sys['admin_email'] : get_option('admin_email');
		$from_name    = isset($sys['from_name']) ? $sys['from_name'] : get_bloginfo('name');
		$default_from = $from_name . ' <' . $admin_email . '>';

		if (! empty($sys['notify_on_booking']) && ! empty($sys['admin_recipient'])) {
			$to      = array_map('trim', explode(',', $sys['admin_recipient']));
			$to      = array_filter($to);
			$subject = isset($sys['admin_subject']) && $sys['admin_subject'] !== '' ? $sys['admin_subject'] : __('【予約】新しい予約がありました', 'sinmido-booking');
			$body    = isset($sys['admin_body']) && $sys['admin_body'] !== '' ? $sys['admin_body'] : sprintf(

				__('%1$s 様から予約がありました。日時: %2$s', 'sinmido-booking'),
				$name,
				$date_time_str
			);
			$subject = $this->replace_mail_placeholders($subject, $replace);
			$body    = $this->replace_mail_placeholders($body, $replace);
			if (! empty($sys['admin_sender'])) {
				$sender = $sys['admin_sender'];
			} elseif (! empty($sys['admin_sender_name'])) {
				$sender = $sys['admin_sender_name'] . ' <' . $admin_email . '>';
			} else {
				$sender = $default_from;
			}
			$headers = array(
				'Content-Type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8',
				'From: ' . $sender,
			);
			if ($is_html && strip_tags($body) === $body) {
				$body = '<p>' . nl2br(esc_html($body)) . '</p>';
			} elseif ($is_html) {
				$body = wp_kses_post($body);
			}
			$this->log_mail('sending admin email', array('to' => $to, 'subject' => $subject));
			$result = wp_mail($to, $subject, $body, $headers);
			$this->log_mail('admin wp_mail result', array('success' => $result));
		} else {
			$this->log_mail('admin email skipped', array(
				'reason' => ! empty($sys['admin_recipient']) ? 'notify_on_booking off' : 'admin_recipient empty',
			));
		}

		if (! empty($sys['send_confirm_email']) && $email !== '') {
			$customer_recipient_raw = isset($sys['customer_recipient']) ? trim($sys['customer_recipient']) : '[email]';
			$to = $this->replace_mail_placeholders($customer_recipient_raw, $replace);
			$to = array_map('trim', explode(',', $to));
			$to = array_filter($to);
			if (empty($to)) {
				$to = array($email);
			}
			$subject = isset($sys['customer_subject']) && $sys['customer_subject'] !== '' ? $sys['customer_subject'] : __('【予約受付】ご予約を承りました', 'sinmido-booking');
			$body    = isset($sys['customer_body']) && $sys['customer_body'] !== '' ? $sys['customer_body'] : sprintf(

				__('%1$s 様\n\nご予約を承りました。\n日時: %2$s', 'sinmido-booking'),
				$name,
				$date_time_str
			);
			$subject = $this->replace_mail_placeholders($subject, $replace);
			$body    = $this->replace_mail_placeholders($body, $replace);
			if (! empty($sys['customer_sender'])) {
				$sender = $sys['customer_sender'];
			} elseif (! empty($sys['customer_sender_name'])) {
				$sender = $sys['customer_sender_name'] . ' <' . $admin_email . '>';
			} else {
				$sender = $default_from;
			}
			$headers = array(
				'Content-Type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8',
				'From: ' . $sender,
			);
			if ($is_html && strip_tags($body) === $body) {
				$body = '<p>' . nl2br(esc_html($body)) . '</p>';
			} elseif ($is_html) {
				$body = wp_kses_post($body);
			}
			$this->log_mail('sending customer email', array('to' => $to, 'subject' => $subject));
			$result = wp_mail($to, $subject, $body, $headers);
			$this->log_mail('customer wp_mail result', array('success' => $result));
		} else {
			$this->log_mail('customer email skipped', array(
				'reason' => $email === '' ? 'guest email empty' : 'send_confirm_email off',
			));
		}
	}
}
