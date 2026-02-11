<?php



if (! defined('ABSPATH')) {
	exit;
}
?>
<div class="sb-tab-panel sba-bg-white sba-border sba-border-gray-200 sba-rounded-sm sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="formsetting" id="sb-panel-formsetting">
	<div class="sb-form-setting-list-view">
		<p class="sba-mb-4">
			<button type="button" class="button button-primary sb-form-field-add"><?php esc_html_e('新規項目を追加', 'sinmido-booking'); ?></button>
		</p>
		<p class="sba-font-medium sba-mb-2"><?php esc_html_e('項目', 'sinmido-booking'); ?></p>
		<ul class="sb-form-fields-list sba-space-y-2 sba-list-none sba-p-0 sba-m-0">
			<?php foreach ($form_fields as $idx => $field) : ?>
				<li class="sb-form-field-item sba-border sba-border-gray-200 sba-rounded sba-p-3 sba-bg-gray-50" data-index="<?php echo (int) $idx; ?>">
					<div class="sba-flex sba-items-center sba-justify-between">
						<span class="sba-font-medium"><?php echo esc_html($field['label']); ?><?php echo ! empty($field['required']) ? ' *' : ''; ?></span>
						<span class="sba-text-sm">
							<button type="button" class="sb-form-field-edit sba-text-blue-600 hover:sba-underline" data-index="<?php echo (int) $idx; ?>"><?php esc_html_e('編集', 'sinmido-booking'); ?></button>
							<span class="sba-mx-1">|</span>
							<button type="button" class="sb-form-field-delete sba-text-blue-600 hover:sba-underline" data-index="<?php echo (int) $idx; ?>"><?php esc_html_e('削除', 'sinmido-booking'); ?></button>
						</span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="sb-form-setting-edit-view sba-hidden sba-mt-6 sba-pt-6 sba-border-t sba-border-gray-200">
		<h3 class="sba-text-lg sba-font-medium sba-mb-4 sb-form-edit-title"><?php esc_html_e('新規項目を追加', 'sinmido-booking'); ?></h3>
		<div class="sba-flex sba-flex-col sba-gap-3 sba-space-y-4 sba-max-w-xl">
			<div>
				<div class="sba-flex sba-items-center sba-justify-between">
					<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('ユニークID', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
					<input type="text" id="sb-form-field-id" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="例: my_field" />
				</div>
				<p class="sba-text-sm sba-text-gray-500 sba-mt-3"><?php esc_html_e('半角英数字・アンダースコアのみ。編集時は変更できません。', 'sinmido-booking'); ?></p>
			</div>
			<div class="sba-flex sba-items-center sba-justify-between">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('項目名', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
				<input type="text" id="sb-form-field-label" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="例: 名前" />
			</div>
			<div class="sba-flex sba-items-center sba-justify-between">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('プレースホルダー', 'sinmido-booking'); ?></label>
				<input type="text" id="sb-form-field-placeholder" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="例) 田中太郎" />
			</div>
			<div class="sba-flex sba-items-center">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('必須', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
				<label class="sba-inline-flex sba-items-center sba-mr-4">
					<input type="radio" name="sb_form_field_required" value="0" class="sba-mr-1" /> <?php esc_html_e('無効', 'sinmido-booking'); ?>
				</label>
				<label class="sba-inline-flex sba-items-center">
					<input type="radio" name="sb_form_field_required" value="1" class="sba-mr-1" checked /> <?php esc_html_e('有効', 'sinmido-booking'); ?>
				</label>
			</div>
			<div class="sba-flex sba-items-center">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('タイプ', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
				<select id="sb-form-field-type" class="sba-flex-1  sba-border sba-border-gray-300 sba-rounded sba-p-2">
					<option value="text">text</option>
					<option value="email">email</option>
					<option value="tel">tel</option>
					<option value="number">number</option>
					<option value="textarea">textarea</option>
					<option value="select">select</option>
					<option value="checkbox">checkbox</option>
					<option value="radio">radio</option>
				</select>
			</div>
			<div id="sb-form-field-options-wrap" class="sba-flex sba-items-start sba-justify-between sba-hidden">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('値（選択肢）', 'sinmido-booking'); ?></label>
				<div class="sba-flex sba-flex-col sba-gap-2 sba-flex-1">
					<div class="sb-form-options-list sba-space-y-2"></div>
					<button type="button" class="button button-small sb-form-option-add sba-mt-2">+ <?php esc_html_e('追加する', 'sinmido-booking'); ?></button>
				</div>
			</div>
			<div>
				<div class="sba-flex sba-items-center">
					<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('カスタム属性', 'sinmido-booking'); ?></label>
					<input type="text" id="sb-form-field-custom" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="" />
				</div>
				<p class="sba-text-sm sba-text-gray-500 sba-mt-3"><?php esc_html_e('計測ツールで入力が必要な場合、こちらに入力してください。', 'sinmido-booking'); ?></p>
			</div>
			<div class="sba-pt-2">
				<button type="button" class="button button-primary sb-form-field-save"><?php esc_html_e('変更を保存', 'sinmido-booking'); ?></button>
				<button type="button" class="button sb-form-edit-cancel sba-ml-2"><?php esc_html_e('キャンセル', 'sinmido-booking'); ?></button>
			</div>
		</div>
	</div>
</div>