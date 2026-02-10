<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sinmido_Booking {

	
	private $cpt;

	
	private $admin;

	
	public function run() {
		$this->cpt   = new Sinmido_Booking_Event_CPT();
		$this->admin = new Sinmido_Booking_Admin();

		add_action( 'init', array( 'Sinmido_Booking_Event_CPT', 'register_cpt' ), 0 );
		add_action( 'init', array( 'Sinmido_Booking_Reservation_CPT', 'register_cpt' ), 0 );
		add_action( 'init', array( $this->cpt, 'register_meta' ), 5 );
		$reservation_cpt = new Sinmido_Booking_Reservation_CPT();
		add_action( 'init', array( $reservation_cpt, 'register_meta' ), 5 );

		if ( is_admin() ) {
			$this->admin->register_hooks();
		}

		add_shortcode( 'sinmido_booking', array( $this, 'shortcode_callback' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'register_front_assets' ), 5 );

		$rest = new Sinmido_Booking_REST();
		add_action( 'rest_api_init', array( $rest, 'register_routes' ) );

		add_action( 'wp_mail_failed', array( $this, 'log_wp_mail_failed' ), 10, 1 );

		add_action( 'phpmailer_init', array( $this, 'configure_phpmailer_smtp' ), 10, 1 );
	}

	
	public function configure_phpmailer_smtp( $phpmailer ) {
		$options = get_option( Sinmido_Booking_Admin::OPTION_SYSTEM_SETTINGS, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		$defaults = Sinmido_Booking_Admin::default_system_settings();
		$settings = array_merge( $defaults, $options );
		if ( empty( $settings['use_smtp'] ) || empty( $settings['smtp_host'] ) ) {
			return;
		}
		$phpmailer->isSMTP();
		$phpmailer->Host       = $settings['smtp_host'];
		$phpmailer->Port       = ! empty( $settings['smtp_port'] ) ? (int) $settings['smtp_port'] : 587;
		$phpmailer->SMTPSecure = isset( $settings['smtp_encryption'] ) ? $settings['smtp_encryption'] : 'tls';
		$phpmailer->SMTPAuth   = ! empty( $settings['smtp_user'] );
		$phpmailer->Timeout    = ! empty( $settings['smtp_timeout'] ) ? (int) $settings['smtp_timeout'] : 30;
		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = $settings['smtp_user'];
			$phpmailer->Password = $settings['smtp_pass'];
		}
		if ( ! empty( $settings['smtp_skip_verify'] ) ) {
			$phpmailer->SMTPOptions = array(
				'ssl' => array(
					'verify_peer'       => false,
					'verify_peer_name'  => false,
					'allow_self_signed' => true,
				),
			);
		}
	}

	
	public function log_wp_mail_failed( $error ) {
		if ( ! $error instanceof WP_Error ) {
			return;
		}
		$log_dir  = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : dirname( dirname( dirname( __DIR__ ) ) );
		$log_file = $log_dir . '/sinmido-booking-mail.log';
		$message  = $error->get_error_message();
		$code     = $error->get_error_code();
		$data     = array( 'code' => $code, 'message' => $message );
		$err_data = $error->get_error_data();
		if ( ! empty( $err_data ) && is_array( $err_data ) ) {
			$data['data'] = $err_data;
		}
		$line = '[Sinmido Booking Mail] ' . gmdate( 'Y-m-d H:i:s' ) . ' wp_mail_failed ' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) . "\n";
		if ( is_writable( $log_dir ) || ( file_exists( $log_file ) && is_writable( $log_file ) ) ) {
			@file_put_contents( $log_file, $line, FILE_APPEND | LOCK_EX );
		}
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( trim( $line ) );
		}
	}

	
	public function register_front_assets() {
		$url = SINMIDO_BOOKING_PLUGIN_URL;
		$dir = SINMIDO_BOOKING_PLUGIN_DIR;
		wp_register_script(
			'sinmido-booking-front',
			$url . 'front/js/booking-front.js',
			array(),
			SINMIDO_BOOKING_VERSION,
			true
		);
		wp_register_style(
			'sinmido-booking-front',
			$url . 'front/css/booking-front.css',
			array(),
			SINMIDO_BOOKING_VERSION
		);
	}

	
	public function shortcode_callback( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'        => '',
				'event_id'  => '',
			),
			$atts,
			'sinmido_booking'
		);
		$id = ! empty( $atts['event_id'] ) ? $atts['event_id'] : $atts['id'];
		if ( ! $id || ! is_numeric( $id ) ) {
			return '<!-- sinmido_booking: invalid or missing event id -->';
		}
		$post = get_post( (int) $id );
		if ( ! $post || $post->post_type !== SINMIDO_BOOKING_CPT_EVENT || $post->post_status !== 'publish' ) {
			return '<!-- sinmido_booking: event not found -->';
		}
		$event_name = get_post_meta( $post->ID, '_sb_event_name', true ) ?: $post->post_title;
		$event_id   = (int) $post->ID;

		wp_enqueue_style( 'sinmido-booking-front' );
		wp_enqueue_script( 'sinmido-booking-front' );
		wp_localize_script( 'sinmido-booking-front', 'sinmidoBooking', array(
			'restUrl' => rest_url(),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'eventId' => $event_id,
		) );

		$system = get_option( Sinmido_Booking_Admin::OPTION_SYSTEM_SETTINGS, array() );
		if ( ! is_array( $system ) ) {
			$system = array();
		}
		$defaults = Sinmido_Booking_Admin::default_system_settings();
		$custom_css = isset( $system['custom_css'] ) ? (string) $system['custom_css'] : ( isset( $defaults['custom_css'] ) ? $defaults['custom_css'] : '' );
		$custom_js  = isset( $system['custom_js'] ) ? (string) $system['custom_js'] : ( isset( $defaults['custom_js'] ) ? $defaults['custom_js'] : '' );
		if ( $custom_css !== '' ) {
			wp_add_inline_style( 'sinmido-booking-front', $custom_css );

			$custom_css_escaped = wp_strip_all_tags( $custom_css );
			$scoped = preg_replace_callback( '/(^|\})\s*([^{]+)(\{)/s', function ( $m ) {
				$sel = trim( $m[2] );
				if ( $sel === '' || strpos( $sel, '@' ) === 0 ) {
					return $m[0];
				}
				return $m[1] . "\n" . '.sinmido-booking-root ' . trim( $m[2] ) . ' ' . $m[3];
			}, $custom_css_escaped );
			$html_custom_css   = '<style id="sinmido-booking-custom-css" type="text/css">' . "\n" . $scoped . "\n" . '</style>';
		} else {
			$html_custom_css = '';
		}
		$html_custom_js = '';
		if ( $custom_js !== '' ) {

			$custom_js_escaped = str_replace( array( '</script>', '</SCRIPT>' ), array( '<\/script>', '<\/script>' ), $custom_js );
			wp_add_inline_script( 'sinmido-booking-front', $custom_js_escaped, 'after' );

			$html_custom_js = '<script id="sinmido-booking-custom-js" type="text/javascript">' . "\n" . wp_strip_all_tags( $custom_js_escaped ) . "\n" . '</script>';
		}

		$rest_url = rest_url();
		$nonce    = wp_create_nonce( 'wp_rest' );
		$html  = '<div class="sinmido-booking-root" data-event-id="' . esc_attr( $event_id ) . '" data-event-name="' . esc_attr( $event_name ) . '" data-rest-url="' . esc_attr( $rest_url ) . '" data-nonce="' . esc_attr( $nonce ) . '">';
		$html .= '<p class="sinmido-booking-placeholder">予約カレンダー（' . esc_html( $event_name ) . '）を読み込み中...</p>';
		$html .= '</div>';
		if ( ! empty( $html_custom_css ) ) {
			$html .= $html_custom_css;
		}
		if ( ! empty( $html_custom_js ) ) {
			$html .= $html_custom_js;
		}
		$html .= '<script>window.__sinmido_booking_event_id__ = ' . (int) $event_id . ';</script>';
		return $html;
	}
}
