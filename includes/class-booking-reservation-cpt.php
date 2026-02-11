<?php


if (! defined('ABSPATH')) {
	exit;
}


class Sinmido_Booking_Reservation_CPT {

	
	public static function register_cpt() {
		$labels = array(
			'name'               => _x('予約', 'post type general name', 'sinmido-booking'),
			'singular_name'      => _x('予約', 'post type singular name', 'sinmido-booking'),
			'menu_name'          => _x('予約', 'admin menu', 'sinmido-booking'),
			'add_new'            => _x('新規追加', 'reservation', 'sinmido-booking'),
			'add_new_item'       => __('新規予約を追加', 'sinmido-booking'),
			'edit_item'          => __('予約を編集', 'sinmido-booking'),
			'new_item'           => __('新規予約', 'sinmido-booking'),
			'view_item'          => __('予約を表示', 'sinmido-booking'),
			'search_items'       => __('予約を検索', 'sinmido-booking'),
			'not_found'          => __('予約が見つかりません', 'sinmido-booking'),
			'not_found_in_trash' => __('ゴミ箱に予約はありません', 'sinmido-booking'),
		);

		$args = array(
			'labels'              => $labels,
			'public'               => false,
			'publicly_queryable'   => false,
			'show_ui'              => false,
			'show_in_menu'         => false,
			'query_var'            => false,
			'rewrite'              => false,
			'capability_type'      => 'post',
			'has_archive'          => false,
			'hierarchical'         => false,
			'menu_position'        => null,
			'supports'             => array('title'),
		);

		register_post_type(SINMIDO_BOOKING_CPT_RESERVATION, $args);
	}

	
	public function register_meta() {
		$meta_keys = array(
			'_sb_event_id'             => 'integer',
			'_sb_status'                => 'string',
			'_sb_name'                  => 'string',
			'_sb_email'                 => 'string',
			'_sb_phone'                 => 'string',
			'_sb_first_preference'      => 'string',
			'_sb_second_preference'     => 'string',
			'_sb_custom_fields'         => 'string',
			'_sb_remarks'               => 'string',
			'_sb_confirmed_date'        => 'string',
			'_sb_confirmed_time_start'  => 'string',
			'_sb_confirmed_time_end'    => 'string',
			'_sb_admin_memo'            => 'string',
			'_sb_blacklist_memo'        => 'string',
		);

		foreach ($meta_keys as $key => $type) {
			register_post_meta(
				SINMIDO_BOOKING_CPT_RESERVATION,
				$key,
				array(
					'type'              => $type,
					'single'            => true,
					'sanitize_callback' => array($this, 'sanitize_meta'),
					'auth_callback'     => function () {
						return current_user_can('edit_posts');
					},
				)
			);
		}
	}

	
	public function sanitize_meta($value, $meta_key) {
		if ($meta_key === '_sb_event_id') {
			return max(0, (int) $value);
		}
		if ($meta_key === '_sb_custom_fields' && is_string($value)) {
			return $value;
		}
		if (in_array($meta_key, array('_sb_remarks', '_sb_admin_memo', '_sb_blacklist_memo'), true)) {
			return sanitize_textarea_field($value);
		}
		if ($meta_key === '_sb_email') {
			return sanitize_email($value);
		}
		return sanitize_text_field($value);
	}

	
	public static function get_status_options() {
		return array(
			'confirmed' => __('確定', 'sinmido-booking'),
			'tentative' => __('仮予約', 'sinmido-booking'),
			'cancelled' => __('キャンセル', 'sinmido-booking'),
			'warning'   => __('要注意', 'sinmido-booking'),
		);
	}
}
