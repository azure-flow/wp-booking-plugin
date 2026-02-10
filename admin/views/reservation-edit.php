<?php

if (! defined('ABSPATH')) {
	exit;
}
$rid = (int) $reservation->ID;
$event_id = (int) get_post_meta($rid, '_sb_event_id', true);
$reception_date = date_i18n(__('Y年n月j日 g:i A', 'sinmido-booking'), strtotime($reservation->post_date));
$event_name = $event ? (get_post_meta($event_id, '_sb_event_name', true) ?: $event->post_title) : '—';
$first_pref_raw = get_post_meta($rid, '_sb_first_preference', true) ?: '';
$second_pref = get_post_meta($rid, '_sb_second_preference', true) ?: '';
$name    = get_post_meta($rid, '_sb_name', true) ?: '';
$email   = get_post_meta($rid, '_sb_email', true) ?: '';
$phone   = get_post_meta($rid, '_sb_phone', true) ?: '';
$remarks = get_post_meta($rid, '_sb_remarks', true) ?: '';
$status  = get_post_meta($rid, '_sb_status', true) ?: 'tentative';
$confirmed_date = get_post_meta($rid, '_sb_confirmed_date', true) ?: '';
$confirmed_time_start = get_post_meta($rid, '_sb_confirmed_time_start', true) ?: '15:00';
$confirmed_time_end   = get_post_meta($rid, '_sb_confirmed_time_end', true) ?: '17:00';
if ($first_pref_raw !== '') {
	$first_pref = $first_pref_raw;
} elseif ($confirmed_date !== '') {
	$first_pref = date_i18n(__('Y年n月j日', 'sinmido-booking'), strtotime($confirmed_date . ' 12:00:00')) . ' ' . $confirmed_time_start . '～' . $confirmed_time_end;
} else {
	$first_pref = '—';
}
$admin_memo = get_post_meta($rid, '_sb_admin_memo', true) ?: '';
$custom_fields_json = get_post_meta($rid, '_sb_custom_fields', true) ?: '{}';
$custom_values = is_string($custom_fields_json) ? json_decode($custom_fields_json, true) : array();
if (! is_array($custom_values)) {
	$custom_values = array();
}
$form_fields = array();
if ($event_id) {
	$raw = get_post_meta($event_id, '_sb_form_fields', true);
	$form_fields = is_string($raw) ? json_decode($raw, true) : array();
}
if (! is_array($form_fields)) {
	$form_fields = Sinmido_Booking_Event_CPT::default_form_fields();
}
$custom_form_fields = array();
foreach ($form_fields as $f) {
	$id = isset($f['id']) ? $f['id'] : '';
	if (in_array($id, array('name', 'email', 'phone', 'remarks'), true)) {
		continue;
	}
	$custom_form_fields[] = $f;
}
$form_url = admin_url('admin-post.php?action=sinmido_booking_save_reservation');
$list_url = admin_url('admin.php?page=' . Sinmido_Booking_Admin::PAGE_RESERVATIONS);
$status_options = Sinmido_Booking_Reservation_CPT::get_status_options();
?>
<div class="wrap sinmido-booking-wrap sb-reservation-detail-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('予約確認・編集', 'sinmido-booking'); ?></h1>
	<p><a href="<?php echo esc_url($list_url); ?>">&larr; <?php esc_html_e('予約状況へ戻る', 'sinmido-booking'); ?></a></p>

	<?php if (isset($_GET['saved']) && $_GET['saved'] === '1') : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('変更を保存しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url($form_url); ?>" id="sb-reservation-edit-form">
		<?php wp_nonce_field('sinmido_booking_save_reservation'); ?>
		<input type="hidden" name="reservation_id" value="<?php echo (int) $rid; ?>" />

		<div class="sb-reservation-two-col">
			<div class="sb-reservation-block">
				<h3><?php esc_html_e('予約内容', 'sinmido-booking'); ?></h3>
				<div class="sb-reservation-field">
					<label><?php esc_html_e('受付日', 'sinmido-booking'); ?></label>
					<div class="sb-field-static"><?php echo esc_html($reception_date); ?></div>
				</div>
				<div class="sb-reservation-field">
					<label><?php esc_html_e('イベント名', 'sinmido-booking'); ?></label>
					<div class="sb-field-static"><?php echo esc_html($event_name); ?></div>
				</div>
				<div class="sb-reservation-field">
					<label><?php esc_html_e('第一希望', 'sinmido-booking'); ?></label>
					<div class="sb-field-static"><?php echo esc_html($first_pref); ?></div>
				</div>
				<div class="sb-reservation-field">
					<label><?php esc_html_e('第二希望', 'sinmido-booking'); ?></label>
					<div class="sb-field-static"><?php echo esc_html($second_pref ?: '—'); ?></div>
				</div>
				<div class="sb-reservation-field">
					<label for="sb_reservation_name"><?php esc_html_e('お名前', 'sinmido-booking'); ?> <span class="required">*</span></label>
					<input type="text" name="sb_reservation_name" id="sb_reservation_name" value="<?php echo esc_attr($name); ?>" required />
				</div>
				<div class="sb-reservation-field">
					<label for="sb_reservation_email"><?php esc_html_e('メールアドレス', 'sinmido-booking'); ?> <span class="required">*</span></label>
					<input type="email" name="sb_reservation_email" id="sb_reservation_email" value="<?php echo esc_attr($email); ?>" required />
				</div>
				<div class="sb-reservation-field">
					<label for="sb_reservation_phone"><?php esc_html_e('電話番号', 'sinmido-booking'); ?> <span class="required">*</span></label>
					<input type="tel" name="sb_reservation_phone" id="sb_reservation_phone" value="<?php echo esc_attr($phone); ?>" required />
				</div>
				<?php foreach ($custom_form_fields as $cf) :
					$fid = isset($cf['id']) ? $cf['id'] : '';
					$flabel = isset($cf['label']) ? $cf['label'] : $fid;
					$fval = isset($custom_values[$fid]) ? $custom_values[$fid] : '';
				?>
					<div class="sb-reservation-field">
						<label for="sb_custom_<?php echo esc_attr($fid); ?>"><?php echo esc_html($flabel); ?></label>
						<input type="text" name="sb_custom[<?php echo esc_attr($fid); ?>]" id="sb_custom_<?php echo esc_attr($fid); ?>" value="<?php echo esc_attr($fval); ?>" />
					</div>
				<?php endforeach; ?>
				<?php if (empty($custom_form_fields)) : ?>
					<div class="sb-reservation-field">
						<label><?php esc_html_e('カスタム項目', 'sinmido-booking'); ?></label>
						<div>
							<input type="text" name="sb_custom[extra1]" value="" placeholder="<?php esc_attr_e('項目1', 'sinmido-booking'); ?>" class="sb-custom-input" />
							<input type="text" name="sb_custom[extra2]" value="" placeholder="<?php esc_attr_e('項目2', 'sinmido-booking'); ?>" class="sb-custom-input sba-mt-1" />
							<input type="text" name="sb_custom[extra3]" value="" placeholder="<?php esc_attr_e('項目3', 'sinmido-booking'); ?>" class="sb-custom-input sba-mt-1" />
						</div>
					</div>
				<?php endif; ?>
				<div class="sb-reservation-field">
					<label for="sb_reservation_remarks"><?php esc_html_e('備考', 'sinmido-booking'); ?></label>
					<div id="sb_reservation_remarks" class="sb-field-static sba-border sba-border-gray-200 sba-rounded sba-p-2" style="min-height:3em;background:#fafafa;"><?php echo esc_html($remarks); ?></div>
				</div>
			</div>

			<div class="sb-reservation-block">
				<h3><?php esc_html_e('管理', 'sinmido-booking'); ?></h3>
				<div class="sb-reservation-field">
					<label for="sb_reservation_status"><?php esc_html_e('ステータス', 'sinmido-booking'); ?> <span class="required">*</span></label>
					<select name="sb_reservation_status" id="sb_reservation_status" required>
						<?php foreach ($status_options as $val => $label) : ?>
							<option value="<?php echo esc_attr($val); ?>" <?php selected($status, $val); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="sb-reservation-field">
					<label><?php esc_html_e('確定日', 'sinmido-booking'); ?> <span class="required">*</span></label>
					<div class="sba-flex sba-gap-2 sba-flex-wrap sba-items-center">
						<input type="date" name="sb_confirmed_date" value="<?php echo esc_attr($confirmed_date); ?>" />
						<input type="time" name="sb_confirmed_time_start" value="<?php echo esc_attr($confirmed_time_start); ?>" />
						<span>~</span>
						<input type="time" name="sb_confirmed_time_end" value="<?php echo esc_attr($confirmed_time_end); ?>" />
					</div>
				</div>
				<div class="sb-reservation-field">
					<label for="sb_reservation_admin_memo"><?php esc_html_e('メモ', 'sinmido-booking'); ?></label>
					<textarea name="sb_reservation_admin_memo" id="sb_reservation_admin_memo" rows="15"><?php echo esc_textarea($admin_memo); ?></textarea>
				</div>
			</div>
		</div>

		<p class="submit" style="margin-top: 20px;">
			<button type="submit" class="button button-primary"><?php esc_html_e('変更を保存', 'sinmido-booking'); ?></button>
			<a href="<?php echo esc_url($list_url); ?>" class="button"><?php esc_html_e('キャンセル', 'sinmido-booking'); ?></a>
		</p>
	</form>
</div>