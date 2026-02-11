<?php

if (! defined('ABSPATH')) {
	exit;
}

$event_id = isset($event_id) ? (int) $event_id : 0;
$is_edit  = $event_id > 0 && isset($event) && $event instanceof WP_Post;
$name     = $is_edit ? (get_post_meta($event->ID, '_sb_event_name', true) ?: $event->post_title) : '';
$date       = $is_edit ? get_post_meta($event->ID, '_sb_event_date', true) : '';
$time_start = $is_edit ? get_post_meta($event->ID, '_sb_event_time_start', true) : '09:00';
$time_end   = $is_edit ? get_post_meta($event->ID, '_sb_event_time_end', true) : '17:00';
$slots      = $is_edit ? (int) get_post_meta($event->ID, '_sb_slots_per_slot', true) : 1;
if ($slots < 1) {
	$slots = 1;
}

$settings = $is_edit ? json_decode(get_post_meta($event->ID, '_sb_settings', true), true) : array();
if (! is_array($settings)) {
	$settings = Sinmido_Booking_Event_CPT::default_settings();
} else {
	$settings = array_merge(Sinmido_Booking_Event_CPT::default_settings(), $settings);
}

$form_fields_json = $is_edit ? get_post_meta($event->ID, '_sb_form_fields', true) : '';
$form_fields      = $form_fields_json ? json_decode($form_fields_json, true) : null;
if (! is_array($form_fields)) {
	$form_fields = Sinmido_Booking_Event_CPT::default_form_fields();
}

$schedule_slots_json = $is_edit ? get_post_meta($event->ID, '_sb_schedule_slots', true) : '';
$schedule_slots     = $schedule_slots_json ? json_decode($schedule_slots_json, true) : array();
if (! is_array($schedule_slots)) {
	$schedule_slots = array();
}

$page_title = $is_edit ? __('イベントを編集', 'sinmido-booking') : __('新規カレンダー作成', 'sinmido-booking');
$back_url   = admin_url('admin.php?page=' . Sinmido_Booking_Admin::PAGE_RESERVATIONS);
$form_url   = admin_url('admin-post.php?action=sinmido_booking_save_event');
$nonce      = wp_nonce_field('sinmido_booking_save_event', '_wpnonce', true, false);

$current_year  = (int) gmdate('Y');
$years         = range($current_year - 2, $current_year + 2);
$contact_phone = isset($settings['contact_phone']) ? $settings['contact_phone'] : '';
if (empty($contact_phone)) {
	$contact_phone = '090-1234-5678';
}

$event_edit_dir = __DIR__ . '/event-edit';
?>
<div class="wrap sinmido-booking-wrap">
	<h1><?php echo esc_html($page_title); ?></h1>
	<p><a href="<?php echo esc_url($back_url); ?>">&larr; <?php esc_html_e('カレンダー一覧へ戻る', 'sinmido-booking'); ?></a></p>

	<?php if (isset($_GET['saved']) && $_GET['saved'] === '1') : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('保存しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['error'])) : ?>
		<div class="notice notice-error">
			<p>
				<?php
				switch ($_GET['error']) {
					case 'name':
						esc_html_e('イベント名を入力してください。', 'sinmido-booking');
						break;
					case 'create':
						esc_html_e('イベントの作成に失敗しました。', 'sinmido-booking');
						break;
					default:
						esc_html_e('エラーが発生しました。', 'sinmido-booking');
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<nav class="nav-tab-wrapper sb-event-tabs sba-border-b sba-border-gray-300 sba-mb-2" aria-label="<?php esc_attr_e('カレンダー登録', 'sinmido-booking'); ?>">
		<button type="button" class="nav-tab sb-tab-btn sba-px-4 sba-py-2 sba-border-b-2 nav-tab-active sba-bg-gray-100 sba-font-medium" data-sb-tab="maininfo"><?php esc_html_e('基本情報', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="schedule"><?php esc_html_e('スケジュール', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="formsetting"><?php esc_html_e('フォーム設定', 'sinmido-booking'); ?></button>
	</nav>

	<form method="post" action="<?php echo esc_url($form_url); ?>" class="sb-edit-form" id="sb-event-edit-form">
		<?php echo $nonce; ?>
		<?php if ($event_id > 0) : ?>
			<input type="hidden" name="event_id" value="<?php echo (int) $event_id; ?>" />
		<?php endif; ?>
		<script type="text/javascript">
			window.sbScheduleSlotsInitial = <?php echo wp_json_encode($schedule_slots); ?>;
		</script>
		<input type="hidden" name="sb_schedule_slots" id="sb_schedule_slots" value="" />
		<script type="text/javascript">
			window.sbFormFieldsInitial = <?php echo wp_json_encode($form_fields, JSON_UNESCAPED_UNICODE); ?>;
		</script>
		<input type="hidden" name="sb_form_fields" id="sb_form_fields" value="" />

		<?php include $event_edit_dir . '/event-maininfo.php'; ?>
		<?php include $event_edit_dir . '/event-schedule.php'; ?>
		<?php include $event_edit_dir . '/event-formsetting.php'; ?>

		<p class="submit">
			<button type="submit" class="button button-primary"><?php esc_html_e('保存', 'sinmido-booking'); ?></button>
			<a href="<?php echo esc_url($back_url); ?>" class="button"><?php esc_html_e('キャンセル', 'sinmido-booking'); ?></a>
		</p>
		<p class="description"><?php esc_html_e('上記「保存」で基本情報・スケジュール・フォーム設定をすべてデータベースに保存します。', 'sinmido-booking'); ?></p>
	</form>

	<?php if ($is_edit) : ?>
		<hr />
		<p><strong><?php esc_html_e('ショートコード', 'sinmido-booking'); ?>:</strong> <code><?php echo esc_html(Sinmido_Booking_Admin::get_shortcode($event->ID)); ?></code> <button type="button" class="button button-small sb-copy-shortcode" data-shortcode="<?php echo esc_attr(Sinmido_Booking_Admin::get_shortcode($event->ID)); ?>"><?php esc_html_e('コピー', 'sinmido-booking'); ?></button></p>
		<p class="description"><?php esc_html_e('固定ページまたはPHPに上記ショートコードを入力すると、class-sinmido-bookingが表示されます。', 'sinmido-booking'); ?></p>
	<?php endif; ?>
</div>