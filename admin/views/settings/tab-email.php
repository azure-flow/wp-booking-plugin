<?php

if (! defined('ABSPATH')) exit;
?>
<div class="sb-settings-panel sba-border sba-bg-white sba-border-gray-200 sba-p-6 sba-mb-6 sba-hidden" data-sb-panel="email" id="sb-panel-settings-email">
	<h2 class="sba-text-lg sba-font-medium sba-mb-4"><?php esc_html_e('自動返信メール設定', 'sinmido-booking'); ?></h2>
	<p class="description sba-mb-4"><?php esc_html_e('ここでは自動返信メールのテンプレートを編集できます。以下のメールタグが利用できます: [DateAndTime] [Date] [Time] [name] [phone] [email] など。', 'sinmido-booking'); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e('予約時にメール送信', 'sinmido-booking'); ?></th>
				<td>
					<label class="sba-inline-flex sba-items-center sba-py-1 sba-mr-6">
						<input type="checkbox" name="sb_notify_on_booking" value="1" <?php checked( ! empty( $settings['notify_on_booking'] ) ); ?> />
						<span class="sba-ml-1"><?php esc_html_e('管理者に通知する', 'sinmido-booking'); ?></span>
					</label>
					<label class="sba-inline-flex sba-items-center sba-py-1">
						<input type="checkbox" name="sb_send_confirm_email" value="1" <?php checked( ! empty( $settings['send_confirm_email'] ) ); ?> />
						<span class="sba-ml-1"><?php esc_html_e('顧客に自動返信する', 'sinmido-booking'); ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('配信形式', 'sinmido-booking'); ?></th>
				<td>
					<label class="sba-mr-4"><input type="radio" name="sb_mail_format" value="html" <?php checked($settings['mail_format'], 'html'); ?> /> HTML</label>
					<label><input type="radio" name="sb_mail_format" value="text" <?php checked($settings['mail_format'], 'text'); ?> /> <?php esc_html_e('テキスト', 'sinmido-booking'); ?></label>
				</td>
			</tr>
		</tbody>
	</table>
	<h3 class="sba-mt-6 sba-mb-2"><?php esc_html_e('SMTP（送信できない場合）', 'sinmido-booking'); ?></h3>
	<p class="description sba-mb-2"><?php esc_html_e('XAMPPやWindowsで「Could not instantiate mail function」が出る場合は、ここでSMTP（Gmail等）を設定してください。', 'sinmido-booking'); ?></p>
	<p class="description sba-mb-2 sba-text-amber-700"><?php esc_html_e('「Could not connect to SMTP host」の場合は、ポート465・暗号化SSLを試すか、ファイアウォール/アンチウイルスでポート587/465が許可されているか確認してください。', 'sinmido-booking'); ?></p>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row" class="sba-inline-block"><?php esc_html_e('SMTPを使う', 'sinmido-booking'); ?></th>
				<td>
					<label class="sba-inline-flex sba-items-center">
						<input type="checkbox" name="sb_use_smtp" value="1" <?php checked( ! empty( $settings['use_smtp'] ) ); ?> />
						<span class="sba-ml-1"><?php esc_html_e('有効', 'sinmido-booking'); ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_smtp_host"><?php esc_html_e('SMTPホスト', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_smtp_host" id="sb_smtp_host" value="<?php echo esc_attr( $settings['smtp_host'] ); ?>" class="large-text" placeholder="smtp.gmail.com" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_smtp_port"><?php esc_html_e('ポート', 'sinmido-booking'); ?></label></th>
				<td><input type="number" name="sb_smtp_port" id="sb_smtp_port" value="<?php echo esc_attr( (int) $settings['smtp_port'] ); ?>" min="1" max="65535" class="small-text" /> <span class="description"><?php esc_html_e('Gmail: 587 (TLS), 465 (SSL)', 'sinmido-booking'); ?></span></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_smtp_encryption"><?php esc_html_e('暗号化', 'sinmido-booking'); ?></label></th>
				<td>
					<select name="sb_smtp_encryption" id="sb_smtp_encryption">
						<option value="" <?php selected( $settings['smtp_encryption'], '' ); ?>><?php esc_html_e('なし', 'sinmido-booking'); ?></option>
						<option value="tls" <?php selected( $settings['smtp_encryption'], 'tls' ); ?>>TLS</option>
						<option value="ssl" <?php selected( $settings['smtp_encryption'], 'ssl' ); ?>>SSL</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_smtp_timeout"><?php esc_html_e('接続タイムアウト（秒）', 'sinmido-booking'); ?></label></th>
				<td><input type="number" name="sb_smtp_timeout" id="sb_smtp_timeout" value="<?php echo esc_attr( (int) ( isset( $settings['smtp_timeout'] ) ? $settings['smtp_timeout'] : 30 ) ); ?>" min="10" max="120" class="small-text" /></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e('SSL検証', 'sinmido-booking'); ?></th>
				<td>
					<label class="sba-inline-flex sba-items-center">
						<input type="checkbox" name="sb_smtp_skip_verify" value="1" <?php checked( ! empty( $settings['smtp_skip_verify'] ) ); ?> />
						<span class="sba-ml-1"><?php esc_html_e('SSL証明書の検証をスキップする（接続できない場合のデバッグ用・本番では無効推奨）', 'sinmido-booking'); ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_smtp_user"><?php esc_html_e('ユーザー名（メールアドレス）', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_smtp_user" id="sb_smtp_user" value="<?php echo esc_attr( $settings['smtp_user'] ); ?>" class="large-text" placeholder="your@gmail.com" autocomplete="off" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_smtp_pass"><?php esc_html_e('パスワード', 'sinmido-booking'); ?></label></th>
				<td><input type="password" name="sb_smtp_pass" id="sb_smtp_pass" value="<?php echo esc_attr( isset( $settings['smtp_pass'] ) ? $settings['smtp_pass'] : '' ); ?>" class="large-text" placeholder="<?php echo esc_attr( __( '未設定の場合は入力', 'sinmido-booking' ) ); ?>" autocomplete="new-password" /> <span class="description"><?php esc_html_e('Gmailの場合は「アプリパスワード」を発行して入力してください。保存後も再読み込みで表示されます。', 'sinmido-booking'); ?></span></td>
			</tr>
		</tbody>
	</table>
	<h3 class="sba-mt-6 sba-mb-2"><?php esc_html_e('管理者向け', 'sinmido-booking'); ?></h3>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><label for="sb_admin_recipient"><?php esc_html_e('送信先', 'sinmido-booking'); ?> *</label></th>
				<td>
					<input type="text" name="sb_admin_recipient" id="sb_admin_recipient" value="<?php echo esc_attr($settings['admin_recipient']); ?>" class="large-text" placeholder="a@gmail.com,b@gmail.com" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_admin_sender_name"><?php esc_html_e('送信元表示名', 'sinmido-booking'); ?></label></th>
				<td>
					<input type="text" name="sb_admin_sender_name" id="sb_admin_sender_name" value="<?php echo esc_attr( isset( $settings['admin_sender_name'] ) ? $settings['admin_sender_name'] : '' ); ?>" class="large-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'メールの「差出人」に表示する名前。空欄の場合はサイト名を使用。', 'sinmido-booking' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_admin_sender"><?php esc_html_e('送信元（上書き）', 'sinmido-booking'); ?></label></th>
				<td>
					<input type="text" name="sb_admin_sender" id="sb_admin_sender" value="<?php echo esc_attr($settings['admin_sender']); ?>" class="large-text" placeholder="例: 表示名 &lt;mail@example.com&gt;" />
					<p class="description"><?php esc_html_e( '「表示名 &lt;メールアドレス&gt;」の形式で指定すると、表示名とアドレスをまとめて上書きします。空欄の場合は上記表示名と管理者メールアドレスを使用。', 'sinmido-booking' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_admin_subject"><?php esc_html_e('題名', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_admin_subject" id="sb_admin_subject" value="<?php echo esc_attr($settings['admin_subject']); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_admin_body"><?php esc_html_e('本文', 'sinmido-booking'); ?></label></th>
				<td><textarea name="sb_admin_body" id="sb_admin_body" rows="8" class="large-text sba-w-full"><?php echo esc_textarea($settings['admin_body']); ?></textarea></td>
			</tr>
		</tbody>
	</table>
	<h3 class="sba-mt-6 sba-mb-2"><?php esc_html_e('顧客向け', 'sinmido-booking'); ?></h3>
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row"><label for="sb_customer_recipient"><?php esc_html_e('送信先', 'sinmido-booking'); ?> *</label></th>
				<td><input type="text" name="sb_customer_recipient" id="sb_customer_recipient" value="<?php echo esc_attr($settings['customer_recipient']); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_customer_sender_name"><?php esc_html_e('送信元表示名', 'sinmido-booking'); ?></label></th>
				<td>
					<input type="text" name="sb_customer_sender_name" id="sb_customer_sender_name" value="<?php echo esc_attr( isset( $settings['customer_sender_name'] ) ? $settings['customer_sender_name'] : '' ); ?>" class="large-text" placeholder="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					<p class="description"><?php esc_html_e( '顧客向けメールの「差出人」に表示する名前。空欄の場合はサイト名を使用。', 'sinmido-booking' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_customer_sender"><?php esc_html_e('送信元（上書き）', 'sinmido-booking'); ?></label></th>
				<td>
					<input type="text" name="sb_customer_sender" id="sb_customer_sender" value="<?php echo esc_attr($settings['customer_sender']); ?>" class="large-text" placeholder="例: 表示名 &lt;mail@example.com&gt;" />
					<p class="description"><?php esc_html_e( '「表示名 &lt;メールアドレス&gt;」の形式で指定すると上書きします。空欄の場合は上記表示名と管理者メールアドレスを使用。', 'sinmido-booking' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_customer_subject"><?php esc_html_e('題名', 'sinmido-booking'); ?></label></th>
				<td><input type="text" name="sb_customer_subject" id="sb_customer_subject" value="<?php echo esc_attr($settings['customer_subject']); ?>" class="large-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="sb_customer_body"><?php esc_html_e('本文', 'sinmido-booking'); ?></label></th>
				<td><textarea name="sb_customer_body" id="sb_customer_body" rows="8" class="large-text sba-w-full"><?php echo esc_textarea($settings['customer_body']); ?></textarea></td>
			</tr>
		</tbody>
	</table>
	<p class="description sba-mt-2"><?php esc_html_e('管理者向け・顧客向けの送信先・送信元は、上記「一般」の通知用メールアドレス・送信元表示名を参照する場合もあります。', 'sinmido-booking'); ?></p>
</div>
