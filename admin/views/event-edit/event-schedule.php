<?php

if (! defined('ABSPATH')) {
	exit;
}
?>
<div class="sb-tab-panel sba-bg-white sba-border sba-border-gray-200 sba-rounded-lg sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="schedule" id="sb-panel-schedule">
	<div class="sb-schedule-view-default sba-mb-4">
		<button type="button" class="button button-primary sb-schedule-enter-select"><?php esc_html_e('複数の日を選択', 'sinmido-booking'); ?></button>
	</div>
	<div class="sb-schedule-view-select sba-mb-4 sba-hidden">
		<button type="button" class="button button-primary sb-schedule-add-slot"><?php esc_html_e('時間枠を追加', 'sinmido-booking'); ?></button>
		<button type="button" class="button sb-schedule-cancel-select sba-ml-2 bg-danger sba-text-whtie"><?php esc_html_e('キャンセル', 'sinmido-booking'); ?></button>
		<button type="button" class="button sb-schedule-delete-select sba-ml-2"><?php esc_html_e('削除', 'sinmido-booking'); ?></button>
		<p class="description sba-mt-2 sba-mb-0"><?php esc_html_e('予約時間枠の追加、削除したい日を複数選んでください。', 'sinmido-booking'); ?></p>
	</div>
	<div class="sb-schedule-calendar sba-mt-4">
		<div class="sb-schedule-calendar-header sba-flex sba-items-center sba-justify-between sba-mb-3">
			<button type="button" class="button sb-schedule-prev-month" aria-label="<?php esc_attr_e('前月', 'sinmido-booking'); ?>">&lt; <span class="sb-schedule-prev-label"><?php esc_html_e('12月', 'sinmido-booking'); ?></span></button>
			<span class="sb-schedule-month-title sba-font-medium" data-year="<?php echo (int) $current_year; ?>" data-month="<?php echo (int) gmdate('n'); ?>"><?php echo (int) $current_year; ?>年<?php echo (int) gmdate('n'); ?>月</span>
			<button type="button" class="button sb-schedule-next-month" aria-label="<?php esc_attr_e('次月', 'sinmido-booking'); ?>"><span class="sb-schedule-next-label"><?php esc_html_e('2月', 'sinmido-booking'); ?></span> &gt;</button>
		</div>
		<div class="sb-schedule-calendar-grid-wrapper">
			<table class="sb-schedule-calendar-grid sba-border-collapse sba-text-sm">
				<thead>
					<tr>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('日', 'sinmido-booking'); ?></th>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('月', 'sinmido-booking'); ?></th>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('火', 'sinmido-booking'); ?></th>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('水', 'sinmido-booking'); ?></th>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('木', 'sinmido-booking'); ?></th>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('金', 'sinmido-booking'); ?></th>
						<th style="color: #50575E;" class="sba-border sba-border-gray-300 sba-p-2 sba-text-left"><?php esc_html_e('土', 'sinmido-booking'); ?></th>
					</tr>
				</thead>
				<tbody class="sb-schedule-calendar-body">
				</tbody>
			</table>
		</div>
	</div>
</div>

<div id="sb-schedule-slot-modal" class="sb-modal sba-fixed sba-inset-0 sba-bg-black sba-bg-opacity-50 sba-hidden sba-flex sba-items-center sba-justify-center sba-z-50" role="dialog" aria-labelledby="sb-schedule-slot-modal-title">
	<div class="sba-bg-white sba-rounded-lg sba-shadow-xl sba-max-w-md sba-w-full sba-mx-4 sba-p-6">
		<div class="sba-flex sba-justify-between sba-items-center sba-mb-4">
			<h2 id="sb-schedule-slot-modal-title" class="sba-text-lg sba-font-semibold sba-m-0"><?php esc_html_e('時間枠を設定', 'sinmido-booking'); ?></h2>
			<button type="button" class="sb-modal-close sba-text-gray-500 hover:sba-text-gray-700 sba-text-2xl sba-leading-none" aria-label="<?php esc_attr_e('閉じる', 'sinmido-booking'); ?>">&times;</button>
		</div>
		<div class="sba-space-y-4">
			<div>
				<label class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('開催時間', 'sinmido-booking'); ?><span class="required sba-pl-2">*</span></label>
				<div class="sba-flex sba-items-center sba-gap-2">
					<select id="sb-slot-time-start" class="sba-border sba-border-gray-300 sba-rounded sba-p-2 sba-flex-1">
						<?php for ($h = 0; $h < 24; $h++) : for ($m = 0; $m < 60; $m += 30) : $t = sprintf('%02d:%02d', $h, $m); ?>
								<option value="<?php echo esc_attr($t); ?>" <?php selected($t, '10:00'); ?>><?php echo esc_html($t); ?></option>
						<?php endfor;
						endfor; ?>
					</select>
					<span>~</span>
					<select id="sb-slot-time-end" class="sba-border sba-border-gray-300 sba-rounded sba-p-2 sba-flex-1">
						<?php for ($h = 0; $h < 24; $h++) : for ($m = 0; $m < 60; $m += 30) : $t = sprintf('%02d:%02d', $h, $m); ?>
								<option value="<?php echo esc_attr($t); ?>" <?php selected($t, '12:00'); ?>><?php echo esc_html($t); ?></option>
						<?php endfor;
						endfor; ?>
					</select>
				</div>
			</div>
			<div>
				<label class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('予約の受付間隔・同時受付可能組数', 'sinmido-booking'); ?> *</label>
				<div class="sba-flex sba-flex-wrap sba-items-center sba-gap-1">
					<select id="sb-slot-interval" class="sba-border sba-border-gray-300 sba-rounded sba-p-2">
						<option value="15">15<?php esc_html_e('分', 'sinmido-booking'); ?></option>
						<option value="30">30<?php esc_html_e('分', 'sinmido-booking'); ?></option>
						<option value="60" selected>1<?php esc_html_e('時間', 'sinmido-booking'); ?></option>
						<option value="90">1.5<?php esc_html_e('時間', 'sinmido-booking'); ?></option>
						<option value="120">2<?php esc_html_e('時間', 'sinmido-booking'); ?></option>
					</select>
					<span>ごとに</span>
					<select id="sb-slot-groups" class="sba-border sba-border-gray-300 sba-rounded sba-p-2">
						<?php for ($g = 1; $g <= 10; $g++) : ?>
							<option value="<?php echo $g; ?>" <?php selected($g, 1); ?>><?php echo $g; ?>組</option>
						<?php endfor; ?>
					</select>
					<span>まで受付可能</span>
				</div>
			</div>
			<div>
				<label class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('所領時間', 'sinmido-booking'); ?> *</label>
				<div class="sba-flex sba-items-center sba-gap-1">
					<input type="number" id="sb-slot-duration" class="sba-border sba-border-gray-300 sba-rounded sba-p-2 sba-w-20" value="60" min="1" max="480" />
					<span>分かかる</span>
				</div>
			</div>
		</div>
		<div class="sba-mt-6 sba-flex sba-justify-end">
			<button type="button" class="button button-primary sb-schedule-slot-save"><?php esc_html_e('保存', 'sinmido-booking'); ?></button>
		</div>
	</div>
</div>