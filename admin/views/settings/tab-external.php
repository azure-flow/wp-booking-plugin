<?php

if (! defined('ABSPATH')) exit;
?>
<div class="sb-settings-panel sba-border sba-border-gray-200 sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="external" id="sb-panel-settings-external">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('外部サービス連携', 'sinmido-booking'); ?></h2>

	<div class="sba-mb-8">
		<h3 class="sba-font-medium sba-mb-2">reCAPTCHA (v3)</h3>
		<p class="description sba-mb-2"><?php esc_html_e('Google reCAPTCHA (v3)は、ユーザー操作を求めずにバックグラウンドで不正送信やスパム対策を行います。', 'sinmido-booking'); ?> <a href="https://www.google.com/recaptcha" target="_blank" rel="noopener">google.com/recaptcha</a></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sb_recaptcha_site_key"><?php esc_html_e('サイトキー', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_recaptcha_site_key" id="sb_recaptcha_site_key" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_recaptcha_secret_key"><?php esc_html_e('シークレットキー', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_recaptcha_secret_key" id="sb_recaptcha_secret_key" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" class="regular-text" /></td>
			</tr>
		</table>
	</div>

	<div>
		<h3 class="sba-font-medium sba-mb-2">Turnstile</h3>
		<p class="description sba-mb-2"><?php esc_html_e('Cloudflare Turnstile (Invisible) は、ユーザー操作を必要とせずにスパムや不正送信を防止するセキュリティ機能です。', 'sinmido-booking'); ?> <a href="https://www.cloudflare.com/products/turnstile/" target="_blank" rel="noopener">cloudflare.com</a></p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sb_turnstile_site_key"><?php esc_html_e('サイトキー', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_turnstile_site_key" id="sb_turnstile_site_key" value="<?php echo esc_attr($settings['turnstile_site_key']); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_turnstile_secret_key"><?php esc_html_e('シークレットキー', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_turnstile_secret_key" id="sb_turnstile_secret_key" value="<?php echo esc_attr($settings['turnstile_secret_key']); ?>" class="regular-text" /></td>
			</tr>
		</table>
	</div>
</div>
