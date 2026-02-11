<?php



if (! defined('ABSPATH')) {
	exit;
}
$list_url = admin_url('admin.php?page=' . Sinmido_Booking_Admin::PAGE_RESERVATIONS);
?>
<div class="wrap sinmido-booking-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('予約状況', 'sinmido-booking'); ?></h1>
	<?php
	$new_reservation_url = wp_nonce_url(
		admin_url('admin-post.php?action=sinmido_booking_new_reservation'),
		'sinmido_booking_new_reservation'
	);
	?>
	<a href="<?php echo esc_url($new_reservation_url); ?>" class="page-title-action"><?php esc_html_e('新規予約', 'sinmido-booking'); ?></a>
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
	<?php if (isset($_GET['bulk_deleted']) && is_numeric($_GET['bulk_deleted']) && (int) $_GET['bulk_deleted'] > 0) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html(sprintf(__('%d 件の予約を削除しました。', 'sinmido-booking'), (int) $_GET['bulk_deleted'])); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['error']) && $_GET['error'] === 'bulk_none') : ?>
		<div class="notice notice-error">
			<p><?php esc_html_e('一括操作：予約を選択するか、操作を指定してください。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>
	<?php if (isset($_GET['error']) && $_GET['error'] === 'create') : ?>
		<div class="notice notice-error">
			<p><?php esc_html_e('予約の作成に失敗しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>

	<?php if (! empty($reservations)) : ?>
		<form method="post" action="<?php echo esc_url(admin_url('admin-post.php?action=sinmido_booking_bulk_action_reservations')); ?>" id="sb-reservations-bulk-form">
			<?php wp_nonce_field('sinmido_booking_bulk_action_reservations'); ?>
			<div class="tablenav top">
				<div class="alignleft actions bulkactions">
					<label for="sb-reservations-bulk-action-select" class="screen-reader-text"><?php esc_html_e('一括操作', 'sinmido-booking'); ?></label>
					<select name="bulk_action" id="sb-reservations-bulk-action-select">
						<option value=""><?php esc_html_e('一括操作', 'sinmido-booking'); ?></option>
						<option value="delete"><?php esc_html_e('削除', 'sinmido-booking'); ?></option>
					</select>
					<button type="submit" class="button action" id="sb-reservations-bulk-apply"><?php esc_html_e('適用', 'sinmido-booking'); ?></button>
				</div>
			</div>
		<?php endif; ?>

		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column">
						<?php if (! empty($reservations)) : ?>
							<label class="screen-reader-text" for="sb-reservations-select-all"><?php esc_html_e('すべて選択', 'sinmido-booking'); ?></label>
							<input type="checkbox" id="sb-reservations-select-all" class="sb-reservations-select-all" />
						<?php endif; ?>
					</th>
					<th scope="col" class="column-status"><?php esc_html_e('状況', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-customer"><?php esc_html_e('お客様名', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-event"><?php esc_html_e('イベント名', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-confirmed"><?php esc_html_e('確定日', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-memo"><?php esc_html_e('メモ', 'sinmido-booking'); ?></th>
					<th scope="col" class="column-date"><?php esc_html_e('日付', 'sinmido-booking'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($reservations)) : ?>
					<tr>
						<td colspan="7"><?php esc_html_e('予約はありません。', 'sinmido-booking'); ?></td>
					</tr>
				<?php else : ?>
					<?php foreach ($reservations as $reservation) : ?>
						<?php
						$rid       = (int) $reservation->ID;
						$status    = get_post_meta($rid, '_sb_status', true) ?: 'tentative';
						$name      = get_post_meta($rid, '_sb_name', true) ?: '—';
						$event_id  = (int) get_post_meta($rid, '_sb_event_id', true);
						$event_name = Sinmido_Booking_Admin::get_event_display_name($event_id);
						$confirmed_date = get_post_meta($rid, '_sb_confirmed_date', true);
						$confirmed_time_start = get_post_meta($rid, '_sb_confirmed_time_start', true);
						$confirmed_time_end   = get_post_meta($rid, '_sb_confirmed_time_end', true);
						$admin_memo = get_post_meta($rid, '_sb_admin_memo', true) ?: '';
						$remarks     = get_post_meta($rid, '_sb_remarks', true) ?: '';
						$black_memo  = get_post_meta($rid, '_sb_blacklist_memo', true) ?: '';
						$memo_text   = $black_memo !== '' ? $black_memo : ($admin_memo !== '' ? $admin_memo : $remarks);
						$detail_url = admin_url('admin.php?page=' . Sinmido_Booking_Admin::PAGE_RESERVATION_EDIT . '&reservation_id=' . $rid);
						$delete_url = wp_nonce_url(
							admin_url('admin-post.php?action=sinmido_booking_delete_reservation&reservation_id=' . $rid),
							'sinmido_booking_delete_reservation'
						);
						$status_label = Sinmido_Booking_Admin::get_reservation_status_label($status);
						$confirmed_display = '—';
						if ($status === 'confirmed') {
							if ($confirmed_date && $confirmed_time_start && $confirmed_time_end) {
								$day_ja = array(
									'Sun' => '日',
									'Mon' => '月',
									'Tue' => '火',
									'Wed' => '水',
									'Thu' => '木',
									'Fri' => '金',
									'Sat' => '土',
								);
								$ts = strtotime($confirmed_date . ' ' . $confirmed_time_start);
								$format = 'n月j日(D)';
								$confirmed_str = date_i18n($format, $ts);
								$confirmed_str = preg_replace_callback('/\(([A-Za-z]{3})\)/', function ($m) use ($day_ja) {
									return isset($day_ja[$m[1]]) ? '(' . $day_ja[$m[1]] . ')' : $m[0];
								}, $confirmed_str);
								$confirmed_display = $confirmed_str . ' ' . $confirmed_time_start . ' ~ ' . $confirmed_time_end;
							} elseif ($confirmed_date) {
								$day_ja = array(
									'Sun' => '日',
									'Mon' => '月',
									'Tue' => '火',
									'Wed' => '水',
									'Thu' => '木',
									'Fri' => '金',
									'Sat' => '土',
								);
								$ts = strtotime($confirmed_date);
								$format = 'n月j日(D)';
								$confirmed_str = date_i18n($format, $ts);
								$confirmed_str = preg_replace_callback('/\(([A-Za-z]{3})\)/', function ($m) use ($day_ja) {
									return isset($day_ja[$m[1]]) ? '(' . $day_ja[$m[1]] . ')' : $m[0];
								}, $confirmed_str);
								$confirmed_display = $confirmed_str;
							}
						}
						$is_provisional = ($status === 'tentative');
						if ($is_provisional) {
							$memo_preview = '—';
							$memo_title   = '';
						} else {
							$memo_preview = $memo_text !== '' ? mb_strimwidth($memo_text, 0, 75, '...') : '—';
							$memo_title   = $memo_text !== '' ? esc_attr($memo_text) : '';
						}
						?>
						<tr>
							<th scope="row" class="check-column">
								<input type="checkbox" name="reservation_ids[]" value="<?php echo (int) $rid; ?>" class="sb-reservation-cb" />
							</th>
							<td class="column-status">
								<span class="sp-table-th">状況</span>
								<span class="sb-reservation-status sb-status-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_label); ?></span>
							</td>
							<td class="column-customer">
								<span class="sp-table-th">お客様名</span>
								<div class="sba-inline-block">
									<strong><a href="<?php echo esc_url($detail_url); ?>"><?php echo esc_html($name); ?> <?php esc_html_e('様', 'sinmido-booking'); ?></a></strong>
									<div class="row-actions">
										<span class="view"><a href="<?php echo esc_url($detail_url); ?>"><?php esc_html_e('確認', 'sinmido-booking'); ?></a></span>
										<span class="separator">|</span>
										<span class="edit"><a href="<?php echo esc_url($detail_url); ?>"><?php esc_html_e('編集', 'sinmido-booking'); ?></a></span>
										<span class="separator">|</span>
										<span class="trash"><a href="<?php echo esc_url($delete_url); ?>" class="submitdelete sb-delete-reservation"><?php esc_html_e('削除', 'sinmido-booking'); ?></a></span>
									</div>
								</div>
							</td>
							<td class="column-event sba-text-nowrap sba-whitespace-nowrap sba-overflow-hidden sba-mr-4"><span class="sp-table-th">イベント名</span><?php echo esc_html($event_name); ?></td>
							<td class="column-confirmed"><span class="sp-table-th">確定日</span><?php echo $confirmed_display; ?></td>
							<td class="column-memo" <?php echo $memo_title ? ' title="' . $memo_title . '"' : ''; ?>><span class="sp-table-th">メモ</span><?php echo esc_html($memo_preview); ?></td>
							<td class="column-date">
								<span class="sp-table-th">日付</span>
								<span class="sb-date-label"><?php esc_html_e('受付日', 'sinmido-booking'); ?></span><br />
								<?php echo esc_html(date_i18n(__('Y年n月j日 g:i A', 'sinmido-booking'), strtotime($reservation->post_date))); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if (! empty($reservations)) : ?>
		</form>
	<?php endif; ?>
</div>
<style>
	.wp-list-table .column-memo {
		min-width: 10rem;
		max-width: 20rem;
		white-space: normal;
	}
</style>