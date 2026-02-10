<?php

if (! defined('ABSPATH')) {
	exit;
}
?>
<div class="wrap sinmido-booking-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('カレンダー一覧', 'sinmido-booking'); ?></h1>
	<a href="<?php echo esc_url(admin_url('admin.php?page=' . Sinmido_Booking_Admin::PAGE_EDIT)); ?>" class="page-title-action"><?php esc_html_e('新規カレンダー作成', 'sinmido-booking'); ?></a>
	<hr class="wp-header-end" />

	<?php if (isset($_GET['saved']) && $_GET['saved'] === '1') : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('保存しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1') : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('削除しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['duplicated']) && $_GET['duplicated'] === '1') : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('複製しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['bulk_deleted']) && is_numeric($_GET['bulk_deleted']) && (int) $_GET['bulk_deleted'] > 0) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html(sprintf(__('%d 件のイベントを削除しました。', 'sinmido-booking'), (int) $_GET['bulk_deleted'])); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['bulk_duplicated']) && is_numeric($_GET['bulk_duplicated']) && (int) $_GET['bulk_duplicated'] > 0) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html(sprintf(__('%d 件のイベントを複製しました。', 'sinmido-booking'), (int) $_GET['bulk_duplicated'])); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['error'])) : ?>
		<div class="notice notice-error">
			<p>
				<?php
				if ($_GET['error'] === 'bulk_none') {
					esc_html_e('一括操作：イベントを選択するか、操作を指定してください。', 'sinmido-booking');
				} else {
					esc_html_e('エラーが発生しました。', 'sinmido-booking');
				}
				?>
			</p>
		</div>
	<?php endif; ?>

	<?php if (! empty($events)) : ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=sinmido_booking_bulk_action')); ?>" id="sb-bulk-form">
			<?php wp_nonce_field('sinmido_booking_bulk_action'); ?>
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<label for="sb-bulk-action-select" class="screen-reader-text"><?php esc_html_e('一括操作', 'sinmido-booking'); ?></label>
					<select name="bulk_action" id="sb-bulk-action-select">
						<option value=""><?php esc_html_e('一括操作', 'sinmido-booking'); ?></option>
						<option value="duplicate"><?php esc_html_e('複製', 'sinmido-booking'); ?></option>
						<option value="delete"><?php esc_html_e('削除', 'sinmido-booking'); ?></option>
					</select>
					<button type="submit" class="button action" id="sb-bulk-apply"><?php esc_html_e('適用', 'sinmido-booking'); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column">
						<?php if (! empty($events)) : ?>
							<label class="screen-reader-text" for="sb-select-all"><?php esc_html_e('すべて選択', 'sinmido-booking'); ?></label>
							<input type="checkbox" id="sb-select-all" class="sb-select-all" />
						<?php endif; ?>
					</th>
					<th scope="col" class="column-name"><?php esc_html_e('イベント名', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-shortcode"><?php esc_html_e('ショートコード', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-date"><?php esc_html_e('日付', 'sinmido-booking'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($events)) : ?>
					<tr>
						<td colspan="4"><?php esc_html_e('登録済みのイベントはありません。', 'sinmido-booking'); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ($events as $event) : ?>
						<?php
						$shortcode = Sinmido_Booking_Admin::get_shortcode($event->ID);
						$name      = get_post_meta($event->ID, '_sb_event_name', true) ?: $event->post_title;
						$edit_url  = admin_url('admin.php?page=' . Sinmido_Booking_Admin::PAGE_EDIT . '&event_id=' . $event->ID);
						$delete_url = wp_nonce_url(
							admin_url('admin-post.php?action=sinmido_booking_delete_event&event_id=' . $event->ID),
							'sinmido_booking_delete_event'
						);
						$duplicate_url = wp_nonce_url(
							admin_url('admin-post.php?action=sinmido_booking_duplicate_event&event_id=' . $event->ID),
							'sinmido_booking_duplicate_event'
						);
						?>
						<tr>
							<th scope="row" class="check-column">
								<input type="checkbox" name="event_ids[]" value="<?php echo (int) $event->ID; ?>" class="sb-event-cb" />
							</th>
							<td class="column-name">
								<strong><?php echo esc_html($name); ?></strong>
								<div class="row-actions">
									<span class="edit"><a href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('編集', 'sinmido-booking'); ?></a></span>
									<span class="separator">|</span>
									<span class="duplicate"><a href="<?php echo esc_url($duplicate_url); ?>"><?php esc_html_e('複製', 'sinmido-booking'); ?></a></span>
									<span class="separator">|</span>
									<span class="trash"><a href="<?php echo esc_url($delete_url); ?>" class="submitdelete sb-delete-event"><?php esc_html_e('削除', 'sinmido-booking'); ?></a></span>
								</div>
							</td>
							<td class="column-shortcode">
								<code class="sb-shortcode-code"><?php echo esc_html($shortcode); ?></code>
								<button type="button" class="button button-small sb-copy-shortcode" data-shortcode="<?php echo esc_attr($shortcode); ?>"><?php esc_html_e('コピー', 'sinmido-booking'); ?></button>
							</td>
							<td class="column-date">
								<span class="sb-date-label"><?php esc_html_e('最終更新日', 'sinmido-booking'); ?></span><br />
								<?php echo esc_html(date_i18n(__('Y年n月j日 g:i A', 'sinmido-booking'), strtotime($event->post_modified))); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if (! empty($events)) : ?>
		</form>
	<?php endif; ?>
</div>