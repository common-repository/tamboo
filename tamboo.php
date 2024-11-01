<?php
/*
Plugin Name: Tamboo
Plugin URI:  https://wordpress.org/plugins/tamboo
Description: See everything your website visitors are really doing!  With Tamboo, you get visitor recordings, heat maps, funnels, and behavioral analytics for your WordPress site.  To get started: 1) Click the "Activate" link to the left of this description, 2) <a href="https://gettamboo.com" target="_blank">Create a Tamboo account</a> to get your account key, and 3) Go to your Tamboo configuration page, and save your account key.
Version:     1.2.0
Author:      Tamboo
Author URI:  https://gettamboo.com
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined('ABSPATH') or die('No script kiddies please!');

include_once('tamboo-config.php');

add_action('admin_menu', 'tamboo_menu');
add_action('wp_footer', 'tamboo_tracking_code');

function tamboo_menu() {
	add_menu_page('Tamboo', 'Tamboo', 'manage_options', 'tamboo', 'tamboo_options', plugins_url('/tamboo-wp-logo-menu.png', __FILE__));

	add_action('admin_init', 'register_tamboo_settings');
}

function register_tamboo_settings() {
	register_setting('tamboo-settings-group', 'tamboo_account_key');
	register_setting('tamboo-settings-group', 'tamboo_on_session_recording_callback');
}

function tamboo_options() {
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	$tamboo_account_key = get_option('tamboo_account_key');
	$tamboo_on_session_recording_callback = get_option('tamboo_on_session_recording_callback');

	?>
<style type="text/css">
.tamboo .logo {
	width: 100px;
	margin-left: -10px;
	margin-bottom: -15px;
}

.tamboo .form-table th {
	font-weight: normal;
}

.tamboo .form-table tr,
.tamboo .form-table th,
.tamboo .form-table td {
	vertical-align: top;
	margin: 0px;
	padding: 10px;
}

.tamboo .form-table td:first-of-type {
	width: auto;
	max-width: 250px;
}

.tamboo .form-table label {
	font-weight: 600;
}

.tamboo .form-table h2 {
	margin-top: 15px;
	margin-bottom: 0px;
	font-weight: 300;
}

.tamboo .form-table h3 {
	margin-top: 5px;
	margin-bottom: 5px;
	font-size: 14px;
}

.tamboo .form-table h4 {
	margin-top: 0px;
	margin-bottom: 5px;
}

.tamboo .form-table textarea {
	font-family: courier;
	font-size: 13px;
	width: 500px;
	height: 150px;
}

.tamboo .form-table code {
	font-size: 12px;
	white-space: pre-wrap;
	display: block;
	background-color: #fff;
	padding: 4px;
	margin-bottom: 10px;
}

.tamboo .form-table ul {
	margin-top: 10px;
}

.tamboo .form-table li {
	list-style-type: disc;
	margin-left: 20px;
}
</style>
<div class="wrap tamboo">
	<p><img src="<?php echo plugins_url('/tamboo-wp-logo-page.png', __FILE__); ?>" class="logo"></p>

	<h1>
		Tamboo lets you record and watch videos of your WordPress website visitors.
		<br>
		<small>Every scroll, mouse move, click, and keystroke - in a YouTube-like player.</small>
		<br><br>
	</h1>

	<?php if ($tamboo_account_key == null) { ?>
	<div class="error">
		<p><strong>You have not yet configured your Tamboo plugin!</strong></p>
		<p>Tamboo will not work on your WordPress site until you have configured your account key below.</p>
		<p>Please go to <a href="https://gettamboo.com" target="_blank">https://gettamboo.com</a> to sign up and obtain your account key.  Once you have your account key, enter it into the <strong>Account Key</strong> settings box below and then click the <strong>Save Changes</strong> button.</p>
	</div>
	<?php } else { ?>
	<div class="updated">
		<p><strong>Your Tamboo plugin is configured.</strong></p>
		<p>Tamboo is now actively recording visitors to your WordPress site.</p>
		<p>To access your site's visitor recordings, heat maps, funnels, and behavioral analytics, sign in to Tamboo at <a href="https://gettamboo.com" target="_blank">https://gettamboo.com</a>.
	</div>
	<?php } ?>

	<form method="post" action="options.php">
		<?php settings_fields('tamboo-settings-group'); ?>
		<?php do_settings_sections('tamboo-settings-group'); ?>
		<table class="form-table">
			<tr>
				<th colspan="2">
					<h2>Account Settings</h2>
				</th>
			</tr>
			<tr>
				<td>
					<label>Account Key</label>
				</td>
				<td>
					<input type="text" name="tamboo_account_key" size="24" value="<?php echo esc_attr($tamboo_account_key); ?>" />
				</td>
			</tr>
	<?php if ($tamboo_account_key != null) { ?>
			<tr>
				<th colspan="2">
					<h2>JavaScript API Settings <small>(Optional)</small></h2>
				</th>
			</tr>
			<tr>
				<th colspan="2">
					<h3>On Session Recording Callback</h3>
					<div>This callback enables you to execute custom JavaScript code whenever a new session recording occurs.  It is called once (and only once) for each new session recording encountered.  For example, if a session recording has five pageviews, this function would only be called once at the start for the entire session.</div>
				</th>
			</tr>
			<tr>
				<td>
					<h4>Signature:</h4>
					<code>function (data) {
  // Your code goes here...
}</code>
					<h4>Parameters:</h4>
					<ul>
						<li>
							<strong>data:</strong>  An object that contains the following properties:
							<ul>
								<li><strong>sessionId:</strong>  The Tamboo Session ID associated with this recording.</li>
								<li><strong>sessionRecordingUrl:</strong>  The full URL to the Tamboo recording of this session.</li>
							</ul>
						</li>
					</ul>
					<h4>Example</h4>
					<code>function (data) {
  alert('You can view this recording at: ' + data.sessionRecordingUrl);
}</code>
				</td>
				<td>
					<textarea name="tamboo_on_session_recording_callback" placeholder="e.g., function (data) { ... }"><?php echo esc_attr($tamboo_on_session_recording_callback); ?></textarea>
				</td>
			</tr>
	<?php } ?>
		</table>

		<?php submit_button(); ?>
	</form>
</div>
	<?php
}

function tamboo_tracking_code() {
  $tamboo_account_key = get_option('tamboo_account_key');

	// Account key is not set
	if ($tamboo_account_key == null) {
		return;
	}

	$tamboo_config_url = tamboo_config_url();

	// Config isn't set properly
	if ($tamboo_config_url == null) {
		return;
	}

  $tamboo_on_session_recording_callback = get_option('tamboo_on_session_recording_callback');

	?>
<!-- Tamboo Code -->
<script>
(function(t,a,m,b,o,e,v){if(t[o])return;t[o]=function(){t[o].a=t[o].a||[];
t[o].a.push(arguments);};t[o].l=Date.now();t[o].v=1.0;t[o].s=b;e=a.createElement(m);
v=a.getElementsByTagName(m)[0];e.async=1;e.src=b;v.parentNode.insertBefore(e,v);
})(window,document,'script','<?php echo esc_attr($tamboo_config_url); ?>','tamboo');
tamboo('init', '<?php echo esc_attr($tamboo_account_key); ?>');
<?php if ($tamboo_on_session_recording_callback != null && !empty(trim($tamboo_on_session_recording_callback))) { ?>tamboo('on-session-recording', <?php echo $tamboo_on_session_recording_callback; ?>);<?php } ?>
</script>
<!-- End Tamboo Code -->
	<?php
}

?>
