<?php

if (! defined('ABSPATH')) exit;
?>
<div class="sb-settings-panel sba-border sba-bg-white sba-border-gray-200 sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="js" id="sb-panel-settings-js">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('JavaScript', 'sinmido-booking'); ?></h2>
	<p class="description sba-mb-4"><?php esc_html_e('カスタムJavaScriptを定義する事ができます。', 'sinmido-booking'); ?></p>
	<textarea name="sb_custom_js" id="sb_custom_js" rows="30" class="large-text sba-w-full sba-font-mono sba-text-sm sba-border sba-border-gray-300 sba-rounded sba-p-3" spellcheck="false"><?php echo esc_textarea($settings['custom_js']); ?></textarea>
</div>
