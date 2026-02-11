<?php

if (! defined('ABSPATH')) exit;
$holidays = isset($settings['regular_holidays']) && is_array($settings['regular_holidays']) ? $settings['regular_holidays'] : array();
$days = array(1 => __('月', 'sinmido-booking'), 2 => __('火', 'sinmido-booking'), 3 => __('水', 'sinmido-booking'), 4 => __('木', 'sinmido-booking'), 5 => __('金', 'sinmido-booking'), 6 => __('土', 'sinmido-booking'), 0 => __('日', 'sinmido-booking'));
?>
<div class="sb-settings-panel sba-border sba-bg-white sba-border-gray-200 sba-p-6 sba-mb-6" data-sb-panel="general" id="sb-panel-settings-general">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('一般設定', 'sinmido-booking'); ?></h2>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e('定休日', 'sinmido-booking'); ?></th>
				<td>
					<fieldset class="sba-flex sba-flex-wrap sba-gap-4">
						<?php foreach ($days as $num => $label) : ?>
							<label class="sba-inline-flex sba-items-center">
								<input type="checkbox" name="sb_regular_holidays[]" value="<?php echo (int) $num; ?>" <?php checked(in_array((string)$num, $holidays, true)); ?> />
								<span class="sba-ml-1"><?php echo esc_html($label); ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('長期休暇', 'sinmido-booking'); ?></th>
				<td>
					<input type="text" name="sb_long_holiday_start" value="<?php echo esc_attr($settings['long_holiday_start']); ?>" placeholder="2026/05/04" class="sba-border sba-border-gray-300 sba-rounded sba-p-2" />
					<span class="sba-mx-1">~</span>
					<input type="text" name="sb_long_holiday_end" value="<?php echo esc_attr($settings['long_holiday_end']); ?>" placeholder="2026/05/06" class="sba-border sba-border-gray-300 sba-rounded sba-p-2" />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('予約締め切り', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></th>
				<td>
					<fieldset>
						<label class="sba-block sba-mb-2">
							<input type="radio" name="sb_deadline_type" class="sba-my-1" value="days_before" <?php checked($settings['deadline_type'], 'days_before'); ?> />
							<select name="sb_deadline_days_before" class="sba-mx-1 sba-border sba-border-gray-300 sba-rounded sba-p-1 sba-pr-6">
								<?php for ($d = 0; $d <= 30; $d++) : ?>
									<option value="<?php echo $d; ?>" <?php selected((int)$settings['deadline_days_before'], $d); ?>><?php echo $d; ?>日前</option>
								<?php endfor; ?>
							</select>
							の
							<select name="sb_deadline_time" class="sba-mx-1 sba-border sba-border-gray-300 sba-rounded sba-p-1 sba-pr-6">
								<?php for ($h = 0; $h < 24; $h++) : for ($m = 0; $m < 60; $m += 30) : $t = sprintf('%02d:%02d', $h, $m); ?>
									<option value="<?php echo esc_attr($t); ?>" <?php selected($settings['deadline_time'], $t); ?>><?php echo esc_html($t); ?></option>
								<?php endfor; endfor; ?>
							</select>
							に締め切る
						</label>
						<label class="sba-block">
							<input type="radio" name="sb_deadline_type" class="sba-my-1" value="hours_before" <?php checked($settings['deadline_type'], 'hours_before'); ?> />
							<select name="sb_deadline_hours_before" class="sba-mx-1 sba-border sba-border-gray-300 sba-rounded sba-p-1 sba-pr-6">
								<?php for ($h = 0; $h <= 168; $h++) : ?>
									<option value="<?php echo $h; ?>" <?php selected((int)$settings['deadline_hours_before'], $h); ?>><?php echo $h; ?></option>
								<?php endfor; ?>
							</select>
							時間前に締め切る
						</label>
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('要問い合わせ', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></th>
				<td>
					<fieldset>
						<label class="sba-block"><input type="radio" name="sb_inquiry_mode" value="" <?php checked($settings['inquiry_mode'], ''); ?> /> <?php esc_html_e('指定なし', 'sinmido-booking'); ?></label>
						<label class="sba-block"><input type="radio" name="sb_inquiry_mode" value="disabled" <?php checked($settings['inquiry_mode'], 'disabled'); ?> /> <?php esc_html_e('無効', 'sinmido-booking'); ?></label>
						<label class="sba-block"><input type="radio" name="sb_inquiry_mode" value="enabled" <?php checked($settings['inquiry_mode'], 'enabled'); ?> /> <?php esc_html_e('有効', 'sinmido-booking'); ?></label>
					</fieldset>
					<p class="description sba-mt-2">
						<label for="sb_inquiry_phone"><?php esc_html_e('電話番号', 'sinmido-booking'); ?></label>
						<input type="text" name="sb_inquiry_phone" id="sb_inquiry_phone" value="<?php echo esc_attr($settings['inquiry_phone']); ?>" class="sba-ml-2 sba-border sba-border-gray-300 sba-rounded sba-p-2 regular-text" />
					</p>
					<p class="description sba-mt-1"><?php esc_html_e('予約が締め切られた場合に、電話でのお問い合わせを許可するかを設定できます。', 'sinmido-booking'); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_description"><?php esc_html_e('説明文', 'sinmido-booking'); ?></label></th>
				<td>
					<textarea name="sb_description" id="sb_description" rows="5" class="large-text sba-w-full sba-border sba-border-gray-300 sba-rounded sba-p-2"><?php echo esc_textarea($settings['description']); ?></textarea>
					<p class="description"><?php esc_html_e('予約カレンダーの下部に表示する説明文を設定できます。', 'sinmido-booking'); ?></p>
				</td>
			</tr>
		</tbody>
	</table>
</div>
