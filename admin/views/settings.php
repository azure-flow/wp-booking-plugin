<?php



if (! defined('ABSPATH')) {
	exit;
}
?>
<div class="wrap sinmido-booking-wrap">
	<h1><?php esc_html_e('システム設定', 'sinmido-booking'); ?></h1>
	<p><a href="<?php echo esc_url($back_url); ?>">&larr; <?php esc_html_e('カレンダー一覧へ戻る', 'sinmido-booking'); ?></a></p>

	<?php if (isset($_GET['saved']) && $_GET['saved'] === '1') : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e('設定を保存しました。', 'sinmido-booking'); ?></p>
		</div>
	<?php endif; ?>

	<nav class="nav-tab-wrapper sb-settings-tabs sba-border-b sba-border-gray-200 sba-mb-2" aria-label="<?php esc_attr_e('システム設定', 'sinmido-booking'); ?>">
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-bg-gray-100 sba-font-medium" data-sb-tab="general"><?php esc_html_e('一般設定', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="email"><?php esc_html_e('自動返信メール設定', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="blacklist"><?php esc_html_e('ブラックリスト', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="formdefaults"><?php esc_html_e('フォーム初期項目', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="external"><?php esc_html_e('外部サービス連携', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="css"><?php esc_html_e('CSS', 'sinmido-booking'); ?></button>
		<button type="button" class="nav-tab sb-settings-tab-btn sba-px-4 sba-py-2 sba-border-b-2 sba-border-transparent hover:sba-border-gray-300" data-sb-tab="js"><?php esc_html_e('JavaScript', 'sinmido-booking'); ?></button>
	</nav>

	<form method="post" action="<?php echo esc_url($form_url); ?>" class="sb-settings-form" id="sb-settings-form">
		<?php wp_nonce_field('sinmido_booking_save_settings'); ?>
		<input type="hidden" name="sb_blacklist_json" id="sb_blacklist_json" value="" />

		<?php include $settings_dir . '/tab-general.php'; ?>
		<?php include $settings_dir . '/tab-email.php'; ?>
		<?php include $settings_dir . '/tab-blacklist.php'; ?>
		<?php include $settings_dir . '/tab-formdefaults.php'; ?>
		<?php include $settings_dir . '/tab-external.php'; ?>
		<?php include $settings_dir . '/tab-css.php'; ?>
		<?php include $settings_dir . '/tab-js.php'; ?>

		<p class="submit sba-mt-6">
			<button type="submit" class="button button-primary"><?php esc_html_e('変更を保存', 'sinmido-booking'); ?></button>
		</p>
	</form>
</div>