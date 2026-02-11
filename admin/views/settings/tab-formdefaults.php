<?php

if (! defined('ABSPATH')) exit;
?>
<div class="sb-settings-panel sba-border sba-bg-white sba-border-gray-200 sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="formdefaults" id="sb-panel-settings-formdefaults">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('フォーム初期項目', 'sinmido-booking'); ?></h2>
	<p class="description sba-mb-3">
		<?php esc_html_e('新しいカレンダーを作成したときに最初から入っているフォーム項目をここで設定できます。', 'sinmido-booking'); ?>
	</p>
	<p class="description sba-mb-3">
		<?php esc_html_e('イベント編集画面の「フォーム設定」と同じように、ここで項目を追加・編集・削除できます。', 'sinmido-booking'); ?>
	</p>

	<div class="sb-defaultform-list-view">
		<p class="sba-mb-4">
			<button type="button" class="button button-primary sb-defaultform-field-add"><?php esc_html_e('新規項目を追加', 'sinmido-booking'); ?></button>
		</p>
		<p class="sba-font-medium sba-mb-2"><?php esc_html_e('項目', 'sinmido-booking'); ?></p>
		<ul class="sb-defaultform-fields-list sba-space-y-2 sba-list-none sba-p-0 sba-m-0">
			<!-- JSでレンダリング -->
		</ul>
	</div>

	<div class="sb-defaultform-edit-view sba-hidden sba-mt-6 sba-pt-6 sba-border-t sba-border-gray-200">
		<h3 class="sba-text-lg sba-font-medium sba-mb-4 sb-defaultform-edit-title"><?php esc_html_e('新規項目を追加', 'sinmido-booking'); ?></h3>
		<div class="sba-flex sba-flex-col sba-gap-3 sba-space-y-4 sba-max-w-xl">
			<div>
				<div class="sba-flex sba-items-center sba-justify-between">
					<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('ユニークID', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
					<input type="text" id="sb-defaultform-field-id" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="例: name" />
				</div>
				<p class="sba-text-sm sba-text-gray-500 sba-mt-3"><?php esc_html_e('半角英数字・アンダースコアのみ。編集時は変更できません。', 'sinmido-booking'); ?></p>
			</div>
			<div class="sba-flex sba-items-center sba-justify-between">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('項目名', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
				<input type="text" id="sb-defaultform-field-label" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="例: お名前" />
			</div>
			<div class="sba-flex sba-items-center sba-justify-between">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('プレースホルダー', 'sinmido-booking'); ?></label>
				<input type="text" id="sb-defaultform-field-placeholder" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="例) 田中太郎" />
			</div>
			<div class="sba-flex sba-items-center">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('必須', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
				<label class="sba-inline-flex sba-items-center sba-mr-4">
					<input type="radio" name="sb_defaultform_field_required" value="0" class="sba-mr-1" /> <?php esc_html_e('無効', 'sinmido-booking'); ?>
				</label>
				<label class="sba-inline-flex sba-items-center">
					<input type="radio" name="sb_defaultform_field_required" value="1" class="sba-mr-1" checked /> <?php esc_html_e('有効', 'sinmido-booking'); ?>
				</label>
			</div>
			<div class="sba-flex sba-items-center">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('タイプ', 'sinmido-booking'); ?> <span class="sba-text-red-600">*</span></label>
				<select id="sb-defaultform-field-type" class="sba-flex-1  sba-border sba-border-gray-300 sba-rounded sba-p-2">
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
			<div id="sb-defaultform-field-options-wrap" class="sba-flex sba-items-start sba-justify-between sba-hidden">
				<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('値（選択肢）', 'sinmido-booking'); ?></label>
				<div class="sba-flex sba-flex-col sba-gap-2 sba-flex-1">
					<div class="sb-defaultform-options-list sba-space-y-2"></div>
					<button type="button" class="button button-small sb-defaultform-option-add sba-mt-2">+ <?php esc_html_e('追加する', 'sinmido-booking'); ?></button>
				</div>
			</div>
			<div>
				<div class="sba-flex sba-items-center">
					<label style="width: 130px;" class="sba-block sba-font-medium sba-mb-1"><?php esc_html_e('カスタム属性', 'sinmido-booking'); ?></label>
					<input type="text" id="sb-defaultform-field-custom" class="sba-flex-1 sba-border sba-border-gray-300 sba-rounded sba-p-2" placeholder="" />
				</div>
				<p class="sba-text-sm sba-text-gray-500 sba-mt-3"><?php esc_html_e('計測ツールで入力が必要な場合、こちらに入力してください。', 'sinmido-booking'); ?></p>
			</div>
			<div class="sba-pt-2">
				<button type="button" class="button button-primary sb-defaultform-field-save"><?php esc_html_e('変更を保存', 'sinmido-booking'); ?></button>
				<button type="button" class="button sb-defaultform-edit-cancel sba-ml-2"><?php esc_html_e('キャンセル', 'sinmido-booking'); ?></button>
			</div>
		</div>
	</div>

	<input type="hidden" name="sb_default_form_fields" id="sb_default_form_fields" value="" />
	<script type="text/javascript">
		window.sbDefaultFormFieldsInitial = <?php echo wp_json_encode($default_form_fields, JSON_UNESCAPED_UNICODE); ?>;
	</script>
</div>

