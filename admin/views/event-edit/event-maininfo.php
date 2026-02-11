<?php



if (! defined('ABSPATH')) {
	exit;
}
?>
<div class="sb-tab-panel sba-border sba-bg-white sba-border-gray-200 sba-rounded-sm sba-p-6 sba-mb-6" data-sb-panel="maininfo" id="sb-panel-maininfo">
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="sb_event_name"><?php esc_html_e('イベント名', 'sinmido-booking'); ?> <span class="required">*</span></label></th>
			<td><input type="text" name="sb_event_name" id="sb_event_name" value="<?php echo esc_attr($name); ?>" class="regular-text" required /></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('ステータス', 'sinmido-booking'); ?> <span class="required">*</span></th>
			<td>
				<label><input type="radio" name="sb_status_enabled" value="0" <?php checked(empty($settings['status_enabled'])); ?> /> <?php esc_html_e('無効', 'sinmido-booking'); ?></label>
				<label class="sba-ml-4"><input type="radio" name="sb_status_enabled" value="1" <?php checked(! empty($settings['status_enabled'])); ?> /> <?php esc_html_e('有効', 'sinmido-booking'); ?></label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('カレンダーの固定', 'sinmido-booking'); ?></th>
			<td>
				<select name="sb_fix_calendar_year" id="sb_fix_calendar_year" class="sba-mr-2">
					<?php foreach ($years as $y) : ?>
						<option value="<?php echo (int) $y; ?>" <?php selected(isset($settings['fix_calendar_year']) ? (int) $settings['fix_calendar_year'] : $current_year, $y); ?>><?php echo (int) $y; ?>年</option>
					<?php endforeach; ?>
				</select>
				<select name="sb_fix_calendar_month_num" id="sb_fix_calendar_month_num" class="sba-mr-2">
					<?php for ($m = 1; $m <= 12; $m++) : ?>
						<option value="<?php echo $m; ?>" <?php selected(isset($settings['fix_calendar_month_num']) ? (int) $settings['fix_calendar_month_num'] : (int) gmdate('n'), $m); ?>><?php echo $m; ?>月</option>
					<?php endfor; ?>
				</select>
				<label><input type="checkbox" name="sb_fix_calendar_month" value="1" <?php checked(! empty($settings['fix_calendar_month'])); ?> /> <?php esc_html_e('有効化', 'sinmido-booking'); ?></label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('リダイレクト', 'sinmido-booking'); ?></th>
			<td>
				<label><input type="radio" name="sb_redirect_enabled" value="0" <?php checked(empty($settings['redirect_enabled'])); ?> /> <?php esc_html_e('無効', 'sinmido-booking'); ?></label>
				<label class="sba-ml-4"><input type="radio" name="sb_redirect_enabled" value="1" <?php checked(! empty($settings['redirect_enabled'])); ?> /> <?php esc_html_e('有効', 'sinmido-booking'); ?></label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sb_redirect_after_booking">サンクスページ</label></th>
			<td>
				<input type="url" name="sb_redirect_after_booking" id="sb_redirect_after_booking" value="<?php echo esc_attr($settings['redirect_after_booking']); ?>" class="large-text" placeholder="https://" />
				<p class="description"><?php esc_html_e('予約完了後に指定した任意のページにリダイレクトすることができます。', 'sinmido-booking'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('予約時間枠内に残りの枠数を表示', 'sinmido-booking'); ?></th>
			<td>
				<label><input type="checkbox" name="sb_show_remaining_slots" value="1" <?php checked(! empty($settings['show_remaining_slots'])); ?> /> ON</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('予約残数表示', 'sinmido-booking'); ?></th>
			<td>
				<select name="sb_remaining_display_threshold" id="sb_remaining_display_threshold">
					<?php for ($n = 0; $n <= 10; $n++) : ?>
						<option value="<?php echo $n; ?>" <?php selected(isset($settings['remaining_display_threshold']) ? (int) $settings['remaining_display_threshold'] : 3, $n); ?>><?php echo $n; ?></option>
					<?php endfor; ?>
				</select>
				<p class="description"><?php esc_html_e('予約可能数の残りが少ない場合にカレンダーに残数を表示します。表示し始める予約残数を0~10で設定できます。', 'sinmido-booking'); ?></p>
				<p class="description"><?php esc_html_e('例「3」に設定した場合 予約残数が4以上は、予約カレンダーに「◎」が表示されます。予約残数が3以下の場合「△」と表示されます。', 'sinmido-booking'); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('凡例', 'sinmido-booking'); ?></th>
			<td class="sba-text-sm sba-text-gray-600">
				◎ <?php esc_html_e('予約可', 'sinmido-booking'); ?> <br>
				△ <?php esc_html_e('予約可（残りわずか）', 'sinmido-booking'); ?> <br>
				ー <?php esc_html_e('予約不可', 'sinmido-booking'); ?> <br>
				× <?php esc_html_e('予約一杯', 'sinmido-booking'); ?> <br>
				<?php esc_html_e('電話:要問い合わせ', 'sinmido-booking'); ?> (TEL.<?php echo esc_html($contact_phone); ?>)
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sb_contact_phone"><?php esc_html_e('電話：要問い合わせの番号', 'sinmido-booking'); ?></label></th>
			<td><input type="text" name="sb_contact_phone" id="sb_contact_phone" value="<?php echo esc_attr($settings['contact_phone']); ?>" class="regular-text" placeholder="090-1234-5678" /></td>
		</tr>
	</table>
	<input type="hidden" name="sb_event_date" value="<?php echo esc_attr($date); ?>" />
	<input type="hidden" name="sb_event_time_start" value="<?php echo esc_attr($time_start); ?>" />
	<input type="hidden" name="sb_event_time_end" value="<?php echo esc_attr($time_end); ?>" />
	<input type="hidden" name="sb_slots_per_slot" value="<?php echo (int) $slots; ?>" />
	<input type="hidden" name="sb_bookable_days_ahead" value="<?php echo (int) $settings['bookable_days_ahead']; ?>" />
	<input type="hidden" name="sb_not_bookable_days" value="<?php echo (int) $settings['not_bookable_days']; ?>" />
</div>