<?php

if (! defined('ABSPATH')) exit;
?>
<div class="sb-settings-panel sba-border sba-border-gray-200 sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="css" id="sb-panel-settings-css">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('CSS', 'sinmido-booking'); ?></h2>
	<p class="description sba-mb-4"><?php esc_html_e('カスタムCSSを定義する事で訪問者向けの予約カレンダー、入力フォームなどのデザインを変更できます。', 'sinmido-booking'); ?></p>
	<textarea name="sb_custom_css" id="sb_custom_css" rows="30" class="large-text sba-w-full sba-font-mono sba-text-sm sba-border sba-border-gray-300 sba-rounded sba-p-3" spellcheck="false"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
</div>
