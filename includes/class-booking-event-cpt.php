<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Sinmido_Booking_Event_CPT {

	
	public static function register_cpt() {
		$labels = array(
			'name'               => _x( '予約イベント', 'post type general name', 'sinmido-booking' ),
			'singular_name'      => _x( '予約イベント', 'post type singular name', 'sinmido-booking' ),
			'menu_name'          => _x( '予約イベント', 'admin menu', 'sinmido-booking' ),
			'add_new'            => _x( '新規追加', 'event', 'sinmido-booking' ),
			'add_new_item'       => __( '新規イベントを追加', 'sinmido-booking' ),
			'edit_item'          => __( 'イベントを編集', 'sinmido-booking' ),
			'new_item'           => __( '新規イベント', 'sinmido-booking' ),
			'view_item'          => __( 'イベントを表示', 'sinmido-booking' ),
			'search_items'       => __( 'イベントを検索', 'sinmido-booking' ),
			'not_found'          => __( 'イベントが見つかりません', 'sinmido-booking' ),
			'not_found_in_trash' => __( 'ゴミ箱にイベントはありません', 'sinmido-booking' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
		);

		register_post_type( SINMIDO_BOOKING_CPT_EVENT, $args );
	}

	
	public function register_meta() {
		$meta_keys = array(
			'_sb_event_name'           => 'string',
			'_sb_event_date'           => 'string',
			'_sb_event_time_start'     => 'string',
			'_sb_event_time_end'       => 'string',
			'_sb_slots_per_slot'       => 'integer',
			'_sb_settings'             => 'string',
			'_sb_form_fields'          => 'string',
			'_sb_schedule_slots'       => 'string',
		);

		foreach ( $meta_keys as $key => $type ) {
			register_post_meta(
				SINMIDO_BOOKING_CPT_EVENT,
				$key,
				array(
					'type'              => $type,
					'single'            => true,
					'sanitize_callback' => array( $this, 'sanitize_meta' ),
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	
	public function sanitize_meta( $value, $meta_key ) {
		if ( $meta_key === '_sb_slots_per_slot' ) {
			return max( 1, (int) $value );
		}
		if ( $meta_key === '_sb_settings' && is_string( $value ) ) {
			return $value;
		}
		return sanitize_text_field( $value );
	}

	
	public static function default_settings() {
		$current_year = (int) gmdate( 'Y' );
		$current_month = (int) gmdate( 'n' );
		return array(
			'bookable_days_ahead'       => 30,
			'not_bookable_days'         => 0,
			'show_remaining_slots'      => true,
			'fix_calendar_month'        => false,
			'fix_calendar_year'         => $current_year,
			'fix_calendar_month_num'    => $current_month,
			'redirect_after_booking'     => '',
			'redirect_enabled'          => false,
			'contact_phone'             => '',
			'remaining_display_threshold' => 3,
			'status_enabled'            => true,
			'regular_holidays'          => array(),
			'public_holidays'           => array(),
		);
	}

	
	public static function default_form_fields() {
		// ハードコードされたデフォルト
		$default = array(
			array( 'id' => 'name',    'label' => 'お名前',       'required' => true,  'type' => 'text',     'placeholder' => '例：田中太郎' ),
			array( 'id' => 'email',   'label' => 'メールアドレス', 'required' => true,  'type' => 'email',    'placeholder' => '' ),
			array( 'id' => 'phone',   'label' => '電話番号',     'required' => true,  'type' => 'tel',      'placeholder' => '' ),
			array( 'id' => 'remarks', 'label' => '備考',         'required' => false, 'type' => 'textarea', 'placeholder' => '' ),
		);

		// システム設定から上書き（フォーム初期項目タブ）
		$sys = get_option( 'sinmido_booking_system_settings', array() );
		if ( is_array( $sys ) && ! empty( $sys['default_form_fields'] ) && is_array( $sys['default_form_fields'] ) ) {
			return $sys['default_form_fields'];
		}

		return $default;
	}
}
