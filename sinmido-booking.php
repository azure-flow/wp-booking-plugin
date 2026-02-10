<?php
/**
 * Plugin Name:       Sinmido Booking
 * Plugin URI:        https://www.jp
 * Description:       イベントカレンダーと予約管理。管理者：イベント、ショートコード。ユーザー：カレンダー＋ショートコード経由の予約フォーム。
 * Version:           1.0.0
 * Author:            BZ
 * License:           GPL-2.0+
 * Text Domain:       sinmido-booking
 * Domain Path:       /languages
 * www:       bz3323766@gmail.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SINMIDO_BOOKING_VERSION', '1.0.0' );
define( 'SINMIDO_BOOKING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SINMIDO_BOOKING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SINMIDO_BOOKING_CPT_EVENT', 'sb_event' );
define( 'SINMIDO_BOOKING_CPT_RESERVATION', 'sb_reservation' );

require_once SINMIDO_BOOKING_PLUGIN_DIR . 'includes/class-sinmido-booking.php';
require_once SINMIDO_BOOKING_PLUGIN_DIR . 'includes/class-booking-event-cpt.php';
require_once SINMIDO_BOOKING_PLUGIN_DIR . 'includes/class-booking-reservation-cpt.php';
require_once SINMIDO_BOOKING_PLUGIN_DIR . 'includes/class-booking-rest.php';
require_once SINMIDO_BOOKING_PLUGIN_DIR . 'admin/class-booking-admin.php';

function sinmido_booking_run() {
	$loader = new Sinmido_Booking();
	$loader->run();
}
add_action( 'plugins_loaded', 'sinmido_booking_run' );

function sinmido_booking_activate() {
	Sinmido_Booking_Event_CPT::register_cpt();
	Sinmido_Booking_Reservation_CPT::register_cpt();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'sinmido_booking_activate' );

function sinmido_booking_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'sinmido_booking_deactivate' );
