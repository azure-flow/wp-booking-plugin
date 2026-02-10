<?php

if (! defined('ABSPATH')) exit;
?>
<div class="sb-settings-panel sba-border sba-border-gray-200 sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="blacklist" id="sb-panel-settings-blacklist">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('ブラックリスト', 'sinmido-booking'); ?></h2>
	<div class="tablenav top sba-mb-2">
		<div class="alignleft actions">
			<select id="sb-blacklist-bulk-action" class="sba-border sba-border-gray-300 sba-rounded sba-p-1">
				<option value=""><?php esc_html_e('一括操作', 'sinmido-booking'); ?></option>
				<option value="delete"><?php esc_html_e('削除', 'sinmido-booking'); ?></option>
			</select>
			<button type="button" class="button" id="sb-blacklist-apply"><?php esc_html_e('適用', 'sinmido-booking'); ?></button>
		</div>
		<button type="button" class="button button-primary" id="sb-blacklist-add"><?php esc_html_e('情報を追加', 'sinmido-booking'); ?></button>
	</div>
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<td class="check-column"><input type="checkbox" id="sb-blacklist-select-all" /></td>
				<th scope="col" class="column-email"><?php esc_html_e('メールアドレス', 'sinmido-booking'); ?></th>
				<th scope="col" class="column-phone"><?php esc_html_e('電話番号', 'sinmido-booking'); ?></th>
				<th scope="col" class="column-memo"><?php esc_html_e('メモ', 'sinmido-booking'); ?></th>
				<th scope="col" class="column-date"><?php esc_html_e('日付', 'sinmido-booking'); ?></th>
			</tr>
		</thead>
		<tbody id="sb-blacklist-tbody">
			<?php if (empty($blacklist)) : ?>
				<tr><td colspan="5" class="sba-text-gray-500"><?php esc_html_e('登録がありません。', 'sinmido-booking'); ?></td></tr>
			<?php else : ?>
				<?php foreach ($blacklist as $idx => $row) : ?>
					<tr class="sb-blacklist-row" data-index="<?php echo (int) $idx; ?>">
						<th scope="row" class="check-column"><input type="checkbox" class="sb-blacklist-cb" value="<?php echo (int) $idx; ?>" /></th>
						<td class="column-email">
							<strong><?php echo esc_html($row['email'] ?: '—'); ?></strong>
							<div class="row-actions">
								<span class="edit"><a href="#" class="sb-blacklist-edit"><?php esc_html_e('編集', 'sinmido-booking'); ?></a></span>
								<span class="separator">|</span>
								<span class="trash"><a href="#" class="sb-blacklist-delete submitdelete"><?php esc_html_e('削除', 'sinmido-booking'); ?></a></span>
							</div>
						</td>
						<td class="column-phone"><?php echo esc_html(! empty($row['phone']) ? $row['phone'] : '—'); ?></td>
						<td class="column-memo"><?php echo esc_html(! empty($row['memo']) ? $row['memo'] : '—'); ?></td>
						<td class="column-date">
							<span class="sb-date-label"><?php esc_html_e('最終更新日', 'sinmido-booking'); ?></span><br />
							<?php echo esc_html(! empty($row['updated']) ? date_i18n(__('Y年n月j日 g:i A', 'sinmido-booking'), strtotime($row['updated'])) : '—'); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<div id="sb-blacklist-modal" class="sba-fixed sba-inset-0 sba-bg-black sba-bg-opacity-50 sba-hidden sba-flex sba-items-center sba-justify-center" style="z-index:100000;">
		<div class="sba-bg-white sba-rounded-lg sba-shadow-xl sba-w-full sba-max-w-md sba-m-4 sba-p-6">
			<h3 class="sba-text-lg sba-font-medium sba-mb-4 sb-blacklist-modal-title"><?php esc_html_e('情報を追加', 'sinmido-booking'); ?></h3>
			<p class="sba-mb-2">
				<label for="sb-blacklist-modal-email" class="sba-block sba-font-medium"><?php esc_html_e('メールアドレス', 'sinmido-booking'); ?></label>
				<input type="email" id="sb-blacklist-modal-email" class="sba-w-full sba-border sba-border-gray-300 sba-rounded sba-p-2" />
			</p>
			<p class="sba-mb-2">
				<label for="sb-blacklist-modal-phone" class="sba-block sba-font-medium"><?php esc_html_e('電話番号', 'sinmido-booking'); ?></label>
				<input type="text" id="sb-blacklist-modal-phone" class="sba-w-full sba-border sba-border-gray-300 sba-rounded sba-p-2" />
			</p>
			<p class="sba-mb-4">
				<label for="sb-blacklist-modal-memo" class="sba-block sba-font-medium"><?php esc_html_e('メモ', 'sinmido-booking'); ?></label>
				<textarea id="sb-blacklist-modal-memo" class="sba-w-full sba-border sba-border-gray-300 sba-rounded sba-p-2" rows="3"></textarea>
			</p>
			<div class="sba-flex sba-gap-2 sba-justify-end">
				<button type="button" class="button sb-blacklist-modal-cancel"><?php esc_html_e('キャンセル', 'sinmido-booking'); ?></button>
				<button type="button" class="button button-primary sb-blacklist-modal-save"><?php esc_html_e('保存', 'sinmido-booking'); ?></button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">window.sbBlacklistInitial = <?php echo wp_json_encode($blacklist, JSON_UNESCAPED_UNICODE); ?>;</script>
