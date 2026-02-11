<?php

if (! defined('ABSPATH')) {
	exit;
}

class Sinmido_Booking_Admin
{

	const PAGE_SLUG     = 'sinmido-booking';
	const PAGE_LIST     = 'sinmido-booking-events';
	const PAGE_EDIT     = 'sinmido-booking-edit';
	const PAGE_SETTINGS = 'sinmido-booking-settings';
	const PAGE_RESERVATIONS     = 'sinmido-booking-reservations';
	const PAGE_RESERVATION_EDIT = 'sinmido-booking-reservation-edit';
	const OPTION_SYSTEM_SETTINGS = 'sinmido_booking_system_settings';
	const OPTION_BLACKLIST       = 'sinmido_booking_blacklist';


	public function register_hooks()
	{
		add_action('admin_menu', array($this, 'add_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
		add_action('admin_post_sinmido_booking_save_event', array($this, 'handle_save_event'));
		add_action('admin_post_sinmido_booking_save_settings', array($this, 'handle_save_settings'));
		add_action('admin_post_sinmido_booking_delete_event', array($this, 'handle_delete_event'));
		add_action('admin_post_sinmido_booking_duplicate_event', array($this, 'handle_duplicate_event'));
		add_action('admin_post_sinmido_booking_bulk_action', array($this, 'handle_bulk_action'));
		add_action('admin_post_sinmido_booking_save_reservation', array($this, 'handle_save_reservation'));
		add_action('admin_post_sinmido_booking_delete_reservation', array($this, 'handle_delete_reservation'));
		add_action('admin_post_sinmido_booking_bulk_action_reservations', array($this, 'handle_bulk_action_reservations'));
		add_action('admin_post_sinmido_booking_new_reservation', array($this, 'handle_new_reservation'));
		add_filter('submenu_file', array($this, 'filter_submenu_file'), 10, 2);
	}


	public function add_menu()
	{
		add_menu_page(
			__('予約管理', 'sinmido-booking'),
			__('予約管理', 'sinmido-booking'),
			'edit_posts',
			self::PAGE_RESERVATIONS,
			array($this, 'render_reservations_page'),
			'dashicons-calendar-alt',
			26
		);
		add_submenu_page(
			self::PAGE_RESERVATIONS,
			__('予約状況', 'sinmido-booking'),
			__('予約状況', 'sinmido-booking'),
			self::PAGE_RESERVATIONS,
			'edit_posts',
			array($this, 'render_reservations_page')
		);
		add_submenu_page(
			self::PAGE_RESERVATIONS,
			__('カレンダー一覧', 'sinmido-booking'),
			__('カレンダー一覧', 'sinmido-booking'),
			'edit_posts',
			self::PAGE_LIST,
			array($this, 'render_list_page')
		);
		add_submenu_page(
			self::PAGE_RESERVATIONS,
			__('新規カレンダー作成', 'sinmido-booking'),
			__('新規カレンダー作成', 'sinmido-booking'),
			'edit_posts',
			self::PAGE_EDIT,
			array($this, 'render_edit_page')
		);
		add_submenu_page(
			self::PAGE_RESERVATIONS,
			__('システム設定', 'sinmido-booking'),
			__('システム設定', 'sinmido-booking'),
			'manage_options',
			self::PAGE_SETTINGS,
			array($this, 'render_settings_page')
		);

		add_submenu_page(
			self::PAGE_RESERVATIONS,
			__('予約詳細', 'sinmido-booking'),
			'',
			'edit_posts',
			self::PAGE_RESERVATION_EDIT,
			array($this, 'render_reservation_edit_page')
		);
	}


	public function enqueue_assets($hook_suffix)
	{
		$pages = array('toplevel_page_' . self::PAGE_SLUG, '予約管理_page_' . self::PAGE_EDIT);
		$load   = false;
		if (strpos($hook_suffix, self::PAGE_SLUG) !== false) {
			$load = true;
		}
		if (! $load) {
			return;
		}

		wp_enqueue_style(
			'sinmido-booking-admin',
			SINMIDO_BOOKING_PLUGIN_URL . 'admin/css/admin.css',
			array(),
			SINMIDO_BOOKING_VERSION
		);

		$tailwind_css = SINMIDO_BOOKING_PLUGIN_DIR . 'admin/css/admin-tailwind.css';
		if (file_exists($tailwind_css)) {
			wp_enqueue_style(
				'sinmido-booking-admin-tailwind',
				SINMIDO_BOOKING_PLUGIN_URL . 'admin/css/admin-tailwind.css',
				array('sinmido-booking-admin'),
				SINMIDO_BOOKING_VERSION
			);
		}
		wp_enqueue_script(
			'sinmido-booking-admin',
			SINMIDO_BOOKING_PLUGIN_URL . 'admin/js/admin.js',
			array('jquery', 'jquery-ui-sortable'),
			SINMIDO_BOOKING_VERSION,
			true
		);
	}


	public function render_list_page()
	{
		$events = $this->get_events();
		include SINMIDO_BOOKING_PLUGIN_DIR . 'admin/views/event-list.php';
	}


	public function filter_submenu_file($submenu_file, $parent_file)
	{
		if ($parent_file === self::PAGE_RESERVATIONS && isset($_GET['page']) && $_GET['page'] === self::PAGE_RESERVATION_EDIT) {
			return self::PAGE_RESERVATIONS;
		}
		return $submenu_file;
	}


	public function render_edit_page()
	{
		$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
		$event    = null;
		$is_edit  = $event_id > 0;
		if ($is_edit) {
			$event = get_post($event_id);
			if (! $event || $event->post_type !== SINMIDO_BOOKING_CPT_EVENT) {
				wp_die(esc_html__('イベントが見つかりません。', 'sinmido-booking'));
			}
		}
		include SINMIDO_BOOKING_PLUGIN_DIR . 'admin/views/event-edit.php';
	}


	public static function get_system_settings()
	{
		$options = get_option(self::OPTION_SYSTEM_SETTINGS, array());
		if (! is_array($options)) {
			$options = array();
		}
		return array_merge(self::default_system_settings(), $options);
	}


	public static function default_system_settings()
	{
		return array(

			'regular_holidays'     => array(),
			'long_holiday_start'   => '',
			'long_holiday_end'     => '',
			'deadline_type'        => 'days_before',
			'deadline_days_before' => 2,
			'deadline_time'        => '18:00',
			'deadline_hours_before' => 6,
			'inquiry_mode'         => '',
			'inquiry_phone'        => '',
			'description'          => '',

			'admin_email'          => get_option('admin_email'),
			'from_name'            => get_bloginfo('name'),
			'date_format'           => 'Y年n月j日',
			'time_format'           => 'H:i',
			'notify_on_booking'    => true,
			'send_confirm_email'   => true,
			'mail_format'           => 'html',
			'admin_recipient'       => '',
			'admin_sender'          => '',
			'admin_sender_name'     => '',
			'admin_subject'        => '',
			'admin_body'            => '',
			'customer_recipient'   => '',
			'customer_sender'      => '',
			'customer_sender_name' => '',
			'customer_subject'     => '',
			'customer_body'        => '',

			'use_smtp'             => false,
			'smtp_host'            => '',
			'smtp_port'            => 587,
			'smtp_user'            => '',
			'smtp_pass'            => '',
			'smtp_encryption'      => 'tls',
			'smtp_skip_verify'     => false,
			'smtp_timeout'         => 30,

			'recaptcha_site_key'    => '',
			'recaptcha_secret_key'  => '',
			'turnstile_site_key'    => '',
			'turnstile_secret_key'  => '',

			'custom_css'            => '',
			'custom_js'             => '',
		);
	}


	public function render_settings_page()
	{
		$options = get_option(self::OPTION_SYSTEM_SETTINGS, array());
		if (! is_array($options)) {
			$options = array();
		}
		$settings  = array_merge(self::default_system_settings(), $options);
		$blacklist = self::get_blacklist();
		$form_url  = admin_url('admin-post.php?action=sinmido_booking_save_settings');
		$back_url  = admin_url('admin.php?page=' . self::PAGE_SLUG);
		$settings_dir = SINMIDO_BOOKING_PLUGIN_DIR . 'admin/views/settings';
		include SINMIDO_BOOKING_PLUGIN_DIR . 'admin/views/settings.php';
	}


	public function handle_save_settings()
	{
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_save_settings');
		$defaults = self::default_system_settings();
		$settings = array_merge($defaults, array(
			'regular_holidays'      => isset($_POST['sb_regular_holidays']) && is_array($_POST['sb_regular_holidays']) ? array_map('sanitize_text_field', wp_unslash($_POST['sb_regular_holidays'])) : array(),
			'long_holiday_start'    => isset($_POST['sb_long_holiday_start']) ? sanitize_text_field(wp_unslash($_POST['sb_long_holiday_start'])) : '',
			'long_holiday_end'      => isset($_POST['sb_long_holiday_end']) ? sanitize_text_field(wp_unslash($_POST['sb_long_holiday_end'])) : '',
			'deadline_type'         => isset($_POST['sb_deadline_type']) && in_array($_POST['sb_deadline_type'], array('days_before', 'hours_before'), true) ? sanitize_text_field(wp_unslash($_POST['sb_deadline_type'])) : 'days_before',
			'deadline_days_before'  => isset($_POST['sb_deadline_days_before']) ? max(0, min(30, (int) $_POST['sb_deadline_days_before'])) : 2,
			'deadline_time'         => isset($_POST['sb_deadline_time']) ? sanitize_text_field(wp_unslash($_POST['sb_deadline_time'])) : '18:00',
			'deadline_hours_before' => isset($_POST['sb_deadline_hours_before']) ? max(0, min(168, (int) $_POST['sb_deadline_hours_before'])) : 6,
			'inquiry_mode'          => isset($_POST['sb_inquiry_mode']) && in_array($_POST['sb_inquiry_mode'], array('', 'disabled', 'enabled'), true) ? sanitize_text_field(wp_unslash($_POST['sb_inquiry_mode'])) : '',
			'inquiry_phone'         => isset($_POST['sb_inquiry_phone']) ? sanitize_text_field(wp_unslash($_POST['sb_inquiry_phone'])) : '',
			'description'           => isset($_POST['sb_description']) ? sanitize_textarea_field(wp_unslash($_POST['sb_description'])) : '',
			'admin_email'           => isset($_POST['sb_admin_email']) ? sanitize_email(wp_unslash($_POST['sb_admin_email'])) : $defaults['admin_email'],
			'from_name'             => isset($_POST['sb_from_name']) ? sanitize_text_field(wp_unslash($_POST['sb_from_name'])) : $defaults['from_name'],
			'date_format'           => isset($_POST['sb_date_format']) ? sanitize_text_field(wp_unslash($_POST['sb_date_format'])) : $defaults['date_format'],
			'time_format'           => isset($_POST['sb_time_format']) ? sanitize_text_field(wp_unslash($_POST['sb_time_format'])) : $defaults['time_format'],
			'notify_on_booking'     => ! empty($_POST['sb_notify_on_booking']),
			'send_confirm_email'    => ! empty($_POST['sb_send_confirm_email']),
			'mail_format'           => isset($_POST['sb_mail_format']) && $_POST['sb_mail_format'] === 'text' ? 'text' : 'html',
			'admin_recipient'       => isset($_POST['sb_admin_recipient']) ? sanitize_text_field(wp_unslash($_POST['sb_admin_recipient'])) : '',
			'admin_sender'          => isset($_POST['sb_admin_sender']) ? sanitize_text_field(wp_unslash($_POST['sb_admin_sender'])) : '',
			'admin_sender_name'     => isset($_POST['sb_admin_sender_name']) ? sanitize_text_field(wp_unslash($_POST['sb_admin_sender_name'])) : '',
			'admin_subject'         => isset($_POST['sb_admin_subject']) ? sanitize_text_field(wp_unslash($_POST['sb_admin_subject'])) : '',
			'admin_body'            => isset($_POST['sb_admin_body']) ? wp_kses_post(wp_unslash($_POST['sb_admin_body'])) : '',
			'customer_recipient'    => isset($_POST['sb_customer_recipient']) ? sanitize_text_field(wp_unslash($_POST['sb_customer_recipient'])) : '',
			'customer_sender'      => isset($_POST['sb_customer_sender']) ? sanitize_text_field(wp_unslash($_POST['sb_customer_sender'])) : '',
			'customer_sender_name' => isset($_POST['sb_customer_sender_name']) ? sanitize_text_field(wp_unslash($_POST['sb_customer_sender_name'])) : '',
			'customer_subject'      => isset($_POST['sb_customer_subject']) ? sanitize_text_field(wp_unslash($_POST['sb_customer_subject'])) : '',
			'customer_body'         => isset($_POST['sb_customer_body']) ? wp_kses_post(wp_unslash($_POST['sb_customer_body'])) : '',
			'use_smtp'              => ! empty($_POST['sb_use_smtp']),
			'smtp_host'             => isset($_POST['sb_smtp_host']) ? sanitize_text_field(wp_unslash($_POST['sb_smtp_host'])) : '',
			'smtp_port'             => isset($_POST['sb_smtp_port']) ? max(1, min(65535, (int) $_POST['sb_smtp_port'])) : 587,
			'smtp_user'             => isset($_POST['sb_smtp_user']) ? sanitize_text_field(wp_unslash($_POST['sb_smtp_user'])) : '',
			'smtp_encryption'       => isset($_POST['sb_smtp_encryption']) && in_array($_POST['sb_smtp_encryption'], array('', 'tls', 'ssl'), true) ? sanitize_text_field(wp_unslash($_POST['sb_smtp_encryption'])) : 'tls',
			'smtp_skip_verify'      => ! empty($_POST['sb_smtp_skip_verify']),
			'smtp_timeout'          => isset($_POST['sb_smtp_timeout']) ? max(10, min(120, (int) $_POST['sb_smtp_timeout'])) : 30,
			'recaptcha_site_key'    => isset($_POST['sb_recaptcha_site_key']) ? sanitize_text_field(wp_unslash($_POST['sb_recaptcha_site_key'])) : '',
			'recaptcha_secret_key'  => isset($_POST['sb_recaptcha_secret_key']) ? sanitize_text_field(wp_unslash($_POST['sb_recaptcha_secret_key'])) : '',
			'turnstile_site_key'    => isset($_POST['sb_turnstile_site_key']) ? sanitize_text_field(wp_unslash($_POST['sb_turnstile_site_key'])) : '',
			'turnstile_secret_key'  => isset($_POST['sb_turnstile_secret_key']) ? sanitize_text_field(wp_unslash($_POST['sb_turnstile_secret_key'])) : '',
			'custom_css'            => isset($_POST['sb_custom_css']) ? wp_strip_all_tags(wp_unslash($_POST['sb_custom_css'])) : '',
			'custom_js'             => isset($_POST['sb_custom_js']) ? wp_strip_all_tags(wp_unslash($_POST['sb_custom_js'])) : '',
		));
		if (empty($settings['admin_email'])) {
			$settings['admin_email'] = get_option('admin_email');
		}

		$smtp_pass_new = isset($_POST['sb_smtp_pass']) ? trim((string) wp_unslash($_POST['sb_smtp_pass'])) : '';
		$existing     = get_option(self::OPTION_SYSTEM_SETTINGS, array());
		$settings['smtp_pass'] = ($smtp_pass_new !== '') ? $smtp_pass_new : (isset($existing['smtp_pass']) ? $existing['smtp_pass'] : '');
		update_option(self::OPTION_SYSTEM_SETTINGS, $settings);

		if (isset($_POST['sb_blacklist_json']) && is_string($_POST['sb_blacklist_json'])) {
			$decoded = json_decode(wp_unslash($_POST['sb_blacklist_json']), true);
			if (is_array($decoded)) {
				$blacklist = array();
				foreach ($decoded as $row) {
					if (! empty($row['email']) || ! empty($row['phone'])) {
						$blacklist[] = array(
							'email'   => isset($row['email']) ? sanitize_email($row['email']) : '',
							'phone'   => isset($row['phone']) ? sanitize_text_field($row['phone']) : '',
							'memo'    => isset($row['memo']) ? sanitize_text_field($row['memo']) : '',
							'updated' => isset($row['updated']) ? sanitize_text_field($row['updated']) : gmdate('Y-m-d H:i:s'),
						);
					}
				}
				update_option(self::OPTION_BLACKLIST, $blacklist);
			}
		}
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_SETTINGS, 'saved' => '1'), admin_url('admin.php')));
		exit;
	}


	public static function get_blacklist()
	{
		$list = get_option(self::OPTION_BLACKLIST, array());
		return is_array($list) ? $list : array();
	}


	private function get_reservations()
	{
		return get_posts(
			array(
				'post_type'      => SINMIDO_BOOKING_CPT_RESERVATION,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}


	public static function get_event_display_name($event_id)
	{
		if (!$event_id) {
			return '—';
		}
		$post = get_post($event_id);
		if (!$post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT) {
			return '—';
		}
		$name = get_post_meta($event_id, '_sb_event_name', true);
		return $name ? $name : $post->post_title;
	}


	public static function get_reservation_status_label($status)
	{
		$options = Sinmido_Booking_Reservation_CPT::get_status_options();
		return isset($options[$status]) ? $options[$status] : $status;
	}


	public function render_reservations_page()
	{
		$reservations = $this->get_reservations();
		include SINMIDO_BOOKING_PLUGIN_DIR . 'admin/views/reservation-list.php';
	}


	public function render_reservation_edit_page()
	{
		$reservation_id = isset($_GET['reservation_id']) ? (int) $_GET['reservation_id'] : 0;
		$reservation    = null;
		$event          = null;
		if ($reservation_id <= 0) {
			wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_RESERVATIONS));
			exit;
		}
		$reservation = get_post($reservation_id);
		if (!$reservation || $reservation->post_type !== SINMIDO_BOOKING_CPT_RESERVATION) {
			wp_die(esc_html__('予約が見つかりません。', 'sinmido-booking'));
		}
		$event_id = (int) get_post_meta($reservation_id, '_sb_event_id', true);
		if ($event_id) {
			$event = get_post($event_id);
		}
		$events = $this->get_events();
		include SINMIDO_BOOKING_PLUGIN_DIR . 'admin/views/reservation-edit.php';
	}


	private function get_events()
	{
		return get_posts(
			array(
				'post_type'      => SINMIDO_BOOKING_CPT_EVENT,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}


	public static function get_shortcode($event_id)
	{
		return '[sinmido_booking id="' . (int) $event_id . '"]';
	}


	public function handle_save_event()
	{
		if (! current_user_can('edit_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_save_event');

		$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
		$name     = isset($_POST['sb_event_name']) ? sanitize_text_field(wp_unslash($_POST['sb_event_name'])) : '';
		if (empty($name)) {
			wp_safe_redirect(add_query_arg(array('page' => self::PAGE_EDIT, 'error' => 'name'), admin_url('admin.php')));
			exit;
		}

		$date_start = isset($_POST['sb_event_date']) ? sanitize_text_field(wp_unslash($_POST['sb_event_date'])) : '';
		$time_start = isset($_POST['sb_event_time_start']) ? sanitize_text_field(wp_unslash($_POST['sb_event_time_start'])) : '';
		$time_end   = isset($_POST['sb_event_time_end']) ? sanitize_text_field(wp_unslash($_POST['sb_event_time_end'])) : '';
		$slots      = isset($_POST['sb_slots_per_slot']) ? max(1, (int) $_POST['sb_slots_per_slot']) : 1;

		$settings = Sinmido_Booking_Event_CPT::default_settings();
		$settings['bookable_days_ahead']    = isset($_POST['sb_bookable_days_ahead']) ? max(1, (int) $_POST['sb_bookable_days_ahead']) : 30;
		$settings['not_bookable_days']      = isset($_POST['sb_not_bookable_days']) ? max(0, (int) $_POST['sb_not_bookable_days']) : 0;
		$settings['show_remaining_slots']   = ! empty($_POST['sb_show_remaining_slots']) && $_POST['sb_show_remaining_slots'] !== '0';
		$settings['fix_calendar_month']     = ! empty($_POST['sb_fix_calendar_month']);
		$settings['fix_calendar_year']      = isset($_POST['sb_fix_calendar_year']) ? (int) $_POST['sb_fix_calendar_year'] : (int) gmdate('Y');
		$settings['fix_calendar_month_num'] = isset($_POST['sb_fix_calendar_month_num']) ? max(1, min(12, (int) $_POST['sb_fix_calendar_month_num'])) : (int) gmdate('n');
		$settings['redirect_enabled']       = ! empty($_POST['sb_redirect_enabled']);
		$settings['redirect_after_booking'] = isset($_POST['sb_redirect_after_booking']) ? esc_url_raw(wp_unslash($_POST['sb_redirect_after_booking'])) : '';
		$settings['contact_phone']          = isset($_POST['sb_contact_phone']) ? sanitize_text_field(wp_unslash($_POST['sb_contact_phone'])) : '';
		$settings['remaining_display_threshold'] = isset($_POST['sb_remaining_display_threshold']) ? max(0, min(10, (int) $_POST['sb_remaining_display_threshold'])) : 3;
		$settings['status_enabled']         = ! empty($_POST['sb_status_enabled']);

		if ($event_id > 0) {
			$post = get_post($event_id);
			if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT) {
				wp_safe_redirect(add_query_arg(array('page' => self::PAGE_SLUG, 'error' => 'invalid'), admin_url('admin.php')));
				exit;
			}
			wp_update_post(
				array(
					'ID'         => $event_id,
					'post_title' => $name,
				)
			);
		} else {
			$event_id = wp_insert_post(
				array(
					'post_type'   => SINMIDO_BOOKING_CPT_EVENT,
					'post_title'  => $name,
					'post_status' => 'publish',
				)
			);
			if (is_wp_error($event_id)) {
				wp_safe_redirect(add_query_arg(array('page' => self::PAGE_EDIT, 'error' => 'create'), admin_url('admin.php')));
				exit;
			}
		}

		$schedule_slots_raw = isset($_POST['sb_schedule_slots']) ? wp_unslash($_POST['sb_schedule_slots']) : '';
		$schedule_slots     = array();
		if ($schedule_slots_raw !== '') {
			$decoded = json_decode($schedule_slots_raw, true);
			if (is_array($decoded)) {
				foreach ($decoded as $item) {
					if (! empty($item['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $item['date'])) {
						$schedule_slots[] = array(
							'date'              => $item['date'],
							'time_start'        => isset($item['time_start']) ? sanitize_text_field($item['time_start']) : '09:00',
							'time_end'          => isset($item['time_end']) ? sanitize_text_field($item['time_end']) : '17:00',
							'interval_minutes'  => isset($item['interval_minutes']) ? max(1, min(480, (int) $item['interval_minutes'])) : 60,
							'max_concurrent'   => isset($item['max_concurrent']) ? max(1, min(100, (int) $item['max_concurrent'])) : 1,
							'duration_minutes' => isset($item['duration_minutes']) ? max(1, min(480, (int) $item['duration_minutes'])) : 60,
						);
					}
				}
			}
		}
		$form_fields_raw = isset($_POST['sb_form_fields']) ? wp_unslash($_POST['sb_form_fields']) : '';
		$form_fields_saved = Sinmido_Booking_Event_CPT::default_form_fields();
		if ($form_fields_raw !== '') {
			$decoded = json_decode($form_fields_raw, true);
			if (is_array($decoded) && ! empty($decoded)) {
				$form_fields_saved = array();
				foreach ($decoded as $item) {
					if (! is_array($item) || empty($item['id'])) {
						continue;
					}
					$options = array();
					if (! empty($item['options']) && is_array($item['options'])) {
						foreach ($item['options'] as $opt) {
							$options[] = sanitize_text_field(is_string($opt) ? $opt : '');
						}
					}
					$form_fields_saved[] = array(
						'id'                => sanitize_key($item['id']),
						'label'             => isset($item['label']) ? sanitize_text_field($item['label']) : '',
						'required'          => ! empty($item['required']),
						'type'              => isset($item['type']) ? sanitize_key($item['type']) : 'text',
						'placeholder'       => isset($item['placeholder']) ? sanitize_text_field($item['placeholder']) : '',
						'options'           => $options,
						'custom_attributes' => isset($item['custom_attributes']) ? sanitize_text_field($item['custom_attributes']) : '',
					);
				}
				if (empty($form_fields_saved)) {
					$form_fields_saved = Sinmido_Booking_Event_CPT::default_form_fields();
				}
			}
		}

		update_post_meta($event_id, '_sb_event_name', $name);
		update_post_meta($event_id, '_sb_event_date', $date_start);
		update_post_meta($event_id, '_sb_event_time_start', $time_start);
		update_post_meta($event_id, '_sb_event_time_end', $time_end);
		update_post_meta($event_id, '_sb_slots_per_slot', $slots);
		update_post_meta($event_id, '_sb_settings', wp_json_encode($settings));
		update_post_meta($event_id, '_sb_schedule_slots', wp_json_encode($schedule_slots));
		update_post_meta($event_id, '_sb_form_fields', wp_json_encode($form_fields_saved, JSON_UNESCAPED_UNICODE));

		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_EDIT, 'event_id' => $event_id, 'saved' => '1'), admin_url('admin.php')));
		exit;
	}


	public function handle_delete_event()
	{
		if (! current_user_can('delete_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_delete_event');
		$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
		if ($event_id > 0) {
			$post = get_post($event_id);
			if ($post && $post->post_type === SINMIDO_BOOKING_CPT_EVENT) {
				wp_trash_post($event_id);
			}
		}
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_LIST, 'deleted' => '1'), admin_url('admin.php')));
		exit;
	}


	public function handle_duplicate_event()
	{
		if (! current_user_can('edit_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_duplicate_event');
		$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
		if ($event_id <= 0) {
			wp_safe_redirect(add_query_arg(array('page' => self::PAGE_LIST), admin_url('admin.php')));
			exit;
		}
		$new_id = $this->duplicate_single_event($event_id);
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_LIST, 'duplicated' => $new_id ? '1' : '0'), admin_url('admin.php')));
		exit;
	}


	public function handle_bulk_action()
	{
		if (! current_user_can('edit_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_bulk_action');
		$action = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
		$ids    = isset($_POST['event_ids']) && is_array($_POST['event_ids']) ? array_map('intval', $_POST['event_ids']) : array();
		$ids    = array_filter($ids);

		if (empty($ids) || ($action !== 'delete' && $action !== 'duplicate')) {
			wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATIONS, 'error' => 'bulk_none'), admin_url('admin.php')));
			exit;
		}

		$count = 0;
		foreach ($ids as $event_id) {
			$post = get_post($event_id);
			if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT) {
				continue;
			}
			if ($action === 'delete' && current_user_can('delete_posts')) {
				wp_trash_post($event_id);
				$count++;
			} elseif ($action === 'duplicate') {
				$new_id = $this->duplicate_single_event($event_id);
				if ($new_id) {
					$count++;
				}
			}
		}

		$arg = $action === 'delete' ? 'bulk_deleted' : 'bulk_duplicated';
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATIONS, $arg => $count), admin_url('admin.php')));
		exit;
	}


	private function duplicate_single_event($event_id)
	{
		$post = get_post($event_id);
		if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT) {
			return 0;
		}
		$new_id = wp_insert_post(
			array(
				'post_type'   => SINMIDO_BOOKING_CPT_EVENT,
				'post_title'  => $post->post_title . ' (コピー)',
				'post_status' => 'publish',
			)
		);
		if (is_wp_error($new_id)) {
			return 0;
		}
		$meta_keys = array('_sb_event_name', '_sb_event_date', '_sb_event_time_start', '_sb_event_time_end', '_sb_slots_per_slot', '_sb_settings', '_sb_schedule_slots', '_sb_form_fields');
		foreach ($meta_keys as $key) {
			$val = get_post_meta($event_id, $key, true);
			if ($key === '_sb_event_name' && $val) {
				$val .= ' (コピー)';
			}
			update_post_meta($new_id, $key, $val);
		}
		return $new_id;
	}


	public function handle_save_reservation()
	{
		if (! current_user_can('edit_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_save_reservation');
		$reservation_id = isset($_POST['reservation_id']) ? (int) $_POST['reservation_id'] : 0;
		if ($reservation_id <= 0) {
			wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_RESERVATIONS));
			exit;
		}
		$post = get_post($reservation_id);
		if (! $post || $post->post_type !== SINMIDO_BOOKING_CPT_RESERVATION) {
			wp_safe_redirect(admin_url('admin.php?page=' . self::PAGE_RESERVATIONS));
			exit;
		}
		$name   = isset($_POST['sb_reservation_name']) ? sanitize_text_field(wp_unslash($_POST['sb_reservation_name'])) : '';
		$email  = isset($_POST['sb_reservation_email']) ? sanitize_email(wp_unslash($_POST['sb_reservation_email'])) : '';
		$phone  = isset($_POST['sb_reservation_phone']) ? sanitize_text_field(wp_unslash($_POST['sb_reservation_phone'])) : '';
		$status = isset($_POST['sb_reservation_status']) && in_array($_POST['sb_reservation_status'], array('confirmed', 'tentative', 'cancelled', 'warning'), true)
			? sanitize_text_field(wp_unslash($_POST['sb_reservation_status']))
			: 'tentative';
		$confirmed_date = isset($_POST['sb_confirmed_date']) ? sanitize_text_field(wp_unslash($_POST['sb_confirmed_date'])) : '';
		$confirmed_time_start = isset($_POST['sb_confirmed_time_start']) ? sanitize_text_field(wp_unslash($_POST['sb_confirmed_time_start'])) : '';
		$confirmed_time_end   = isset($_POST['sb_confirmed_time_end']) ? sanitize_text_field(wp_unslash($_POST['sb_confirmed_time_end'])) : '';
		$remarks    = isset($_POST['sb_reservation_remarks']) ? sanitize_textarea_field(wp_unslash($_POST['sb_reservation_remarks'])) : '';
		$admin_memo = isset($_POST['sb_reservation_admin_memo']) ? sanitize_textarea_field(wp_unslash($_POST['sb_reservation_admin_memo'])) : '';
		$custom_fields = array();
		if (isset($_POST['sb_custom']) && is_array($_POST['sb_custom'])) {
			foreach ($_POST['sb_custom'] as $k => $v) {
				$custom_fields[sanitize_key($k)] = sanitize_text_field(wp_unslash($v));
			}
		}
		update_post_meta($reservation_id, '_sb_name', $name);
		update_post_meta($reservation_id, '_sb_email', $email);
		update_post_meta($reservation_id, '_sb_phone', $phone);
		update_post_meta($reservation_id, '_sb_status', $status);
		update_post_meta($reservation_id, '_sb_confirmed_date', $confirmed_date);
		update_post_meta($reservation_id, '_sb_confirmed_time_start', $confirmed_time_start);
		update_post_meta($reservation_id, '_sb_confirmed_time_end', $confirmed_time_end);
		update_post_meta($reservation_id, '_sb_remarks', $remarks);
		update_post_meta($reservation_id, '_sb_admin_memo', $admin_memo);
		update_post_meta($reservation_id, '_sb_custom_fields', wp_json_encode($custom_fields, JSON_UNESCAPED_UNICODE));
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATION_EDIT, 'reservation_id' => $reservation_id, 'saved' => '1'), admin_url('admin.php')));
		exit;
	}


	public function handle_delete_reservation()
	{
		if (! current_user_can('delete_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_delete_reservation');
		$reservation_id = isset($_GET['reservation_id']) ? (int) $_GET['reservation_id'] : 0;
		if ($reservation_id > 0) {
			$post = get_post($reservation_id);
			if ($post && $post->post_type === SINMIDO_BOOKING_CPT_RESERVATION) {
				wp_delete_post($reservation_id, true);
			}
		}
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATIONS, 'deleted' => '1'), admin_url('admin.php')));
		exit;
	}


	public function handle_bulk_action_reservations()
	{
		if (! current_user_can('edit_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_bulk_action_reservations');
		$action = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
		$ids    = isset($_POST['reservation_ids']) && is_array($_POST['reservation_ids']) ? array_map('intval', $_POST['reservation_ids']) : array();
		$ids    = array_filter($ids);
		if (empty($ids) || $action !== 'delete') {
			wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATIONS, 'error' => 'bulk_none'), admin_url('admin.php')));
			exit;
		}
		$count = 0;
		foreach ($ids as $rid) {
			$post = get_post($rid);
			if ($post && $post->post_type === SINMIDO_BOOKING_CPT_RESERVATION && current_user_can('delete_posts')) {
				wp_delete_post($rid, true);
				$count++;
			}
		}
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATIONS, 'bulk_deleted' => $count), admin_url('admin.php')));
		exit;
	}


	public function handle_new_reservation()
	{
		if (! current_user_can('edit_posts')) {
			wp_die(esc_html__('権限がありません。', 'sinmido-booking'));
		}
		check_admin_referer('sinmido_booking_new_reservation');
		$event_id = isset($_POST['event_id']) ? (int) $_POST['event_id'] : 0;
		$events = $this->get_events();
		$first_event_id = ! empty($events) ? (int) $events[0]->ID : 0;
		$event_id = $event_id > 0 ? $event_id : $first_event_id;
		$post_id = wp_insert_post(
			array(
				'post_type'   => SINMIDO_BOOKING_CPT_RESERVATION,
				'post_title'  => __('新規予約', 'sinmido-booking') . ' ' . gmdate('Y-m-d H:i'),
				'post_status' => 'publish',
			)
		);
		if (is_wp_error($post_id) || ! $post_id) {
			wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATIONS, 'error' => 'create'), admin_url('admin.php')));
			exit;
		}
		update_post_meta($post_id, '_sb_event_id', $event_id);
		update_post_meta($post_id, '_sb_status', 'tentative');
		update_post_meta($post_id, '_sb_name', '');
		update_post_meta($post_id, '_sb_email', '');
		update_post_meta($post_id, '_sb_phone', '');
		update_post_meta($post_id, '_sb_first_preference', '');
		update_post_meta($post_id, '_sb_second_preference', '');
		update_post_meta($post_id, '_sb_confirmed_date', '');
		update_post_meta($post_id, '_sb_confirmed_time_start', '15:00');
		update_post_meta($post_id, '_sb_confirmed_time_end', '17:00');
		wp_safe_redirect(add_query_arg(array('page' => self::PAGE_RESERVATION_EDIT, 'reservation_id' => $post_id), admin_url('admin.php')));
		exit;
	}
}
