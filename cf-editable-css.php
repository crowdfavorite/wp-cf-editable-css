<?php
/*
Plugin Name: CF Editable CSS
Plugin URI: http://crowdfavorite.com
Description:  Gives the user the ability to edit a CSS file
Version: 1.2
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1');
// ini_set('error_reporting', E_ALL);

load_plugin_textdomain('cf-css');
$cf_css_blogurl = '';
if (is_ssl()) {
	$cf_css_blogurl = str_replace('http://','https://',get_bloginfo('wpurl'));
}
else {
	$cf_css_blogurl = get_bloginfo('wpurl');
}		
$cf_css_themedir = trailingslashit(str_replace(trailingslashit($cf_css_blogurl),ABSPATH,get_stylesheet_directory_uri()));

define('CFCSS_CSS_URL', trailingslashit(get_stylesheet_directory_uri()).'custom.css');

function cfcss_menu_items() {
	if (current_user_can('manage_options')) {
		add_submenu_page(
			'themes.php'
			,__('CF CSS', 'cf-css')
			, __('CF CSS', 'cf-css')
			, 10
			, basename(__FILE__)
			, 'cfcss_options_form'
		);
	}
}
add_action('admin_menu', 'cfcss_menu_items');

function cfcss_request_handler() {
	if(current_user_can('manage_options')) {
		$blogurl = '';
		if (is_ssl()) {
			$blogurl = str_replace('http://','https://',get_bloginfo('wpurl'));
		}
		else {
			$blogurl = get_bloginfo('wpurl');
		}		
		if(isset($_POST['cf_action'])) {
			switch($_POST['cf_action']) {
				case 'cfcss_save':
					cfcss_save($_POST['cfcss_content']);
					if (!empty($_POST['cfcss_autoload'])) {
						$autoload = 'yes';
						switch ($_POST['cfcss_autoload']) {
							case 'yes':
							case 'no':
								$autoload = strip_tags($_POST['cfcss_autoload']);
								break;
						}
						update_option('cf_css_autoload', $autoload);
					}
					wp_redirect(admin_url('themes.php?page=cf-editable-css.php&updated=true'));
					die();
			}
		}
	}
	
	/**
	 * Check to see if we should enqueue the style into theme via wp_head
	 */
	$cf_css_autoload = get_option('cf_css_autoload');
	if ($cf_css_autoload == 'yes') {
		if (!is_admin()) {
			wp_enqueue_style('cf-editable-css', CFCSS_CSS_URL);
		}
	}
}
add_action('init','cfcss_request_handler');

function cfcss_save($content) {
	global $cf_css_themedir;
	$content = strip_tags($content);
	if(!get_magic_quotes_gpc()) {
		$content = stripslashes($content);
	}
	if (function_exists('cf_normalize_line_endings')) {
		$content = cf_normalize_line_endings($content);
	}
	$cf_css_filename = $cf_css_themedir.'custom.css';
	$cf_css_file = fopen($cf_css_filename,"w+b");
	fwrite($cf_css_file,$content);
	fclose($cf_css_file);
}

function cfcss_options_form() {
	global $cf_css_themedir;
	$cf_css_filename = $cf_css_themedir.'custom.css';

	if(file_exists($cf_css_filename) && is_readable($cf_css_filename)) {
		$cf_css_file = fopen($cf_css_filename,"rb");
		$cf_css_contents = fread($cf_css_file,filesize($cf_css_filename));
		fclose($cf_css_file);
	}
	
	if (!empty($_GET['updated']) && $_GET['updated'] == 'true') {
		echo '<div id="cf-css-message" class="updated fade"><p><strong>'.__('Settings saved', 'cf-css').'</strong></p></div>';
	}
	
	if(!is_writable($cf_css_filename)) {
		@chmod($cf_css_filename, 0777);
	}
	print('
		<div class="wrap">
			<h2>'.__('CF Editable CSS','cf-css').'</h2>
	');
	if(!file_exists($cf_css_filename)) {
		if(!is_writable($cf_css_themedir)) {
			print('
				<div id="message" class="updated fade">
					<p>'.__('***The theme folder for the currently active theme is not writable.  To edit the custom.css file, the folder will need to be writable.***').'</p>
				</div>
			</div>
			');
		}
		else {
			print('
			<div id="message" class="updated fade">
				<p>'.__('***The file that is used for handling the custom css is missing.  When saved, the file will attempt to be created.***').'</p>
			</div>
			');
			cfcss_edit_form($cf_css_contents);
		}
	}
	else {
		if(!is_writable($cf_css_filename)) {
			print('
				<div id="message" class="updated fade">
					<p>'.__('***The custom.css file is not readable.  To edit the custom.css file, the file will need to be writable.***').'</p>
				</div>
			</div>
			');
		}
		elseif(!is_writable($cf_css_filename)) {
			print('
				<div id="message" class="updated fade">
					<p>'.__('***The custom.css file is not writable.  To edit the custom.css file, the file will need to be writable.***').'</p>
				</div>
				<div id="css-content">
					<h4>'.__('Current contents of file:').'</h4>
					'.nl2br($cf_css_contents).'
				</div>
			</div>
			');
		}
		else {
			$cf_css_autoload = get_option('cf_css_autoload');
			if (empty($cf_css_autoload)) {
				$cf_css_autoload = 'yes';
			}
			cfcss_edit_form($cf_css_contents, $cf_css_autoload);
		}
	}
}

function cfcss_edit_form($cf_css_contents = '', $cf_css_autoload = 'yes') {
	print('
		<h4>
			'.__('All styles written into this file will override the current theme styles.  Be cautious as this may cause undesired results.').'
		</h4>
		<form action="'.admin_url().'" method="post" id="cfcss-form">
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" style="width:20%;">'.__('Option', 'cf-css').'</th>
						<th scope="col">'.__('Value', 'cf-css').'</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="vertical-align:middle;"><strong>'.__('Autoload CSS file in theme:', 'cf-css').'</strong></td>
						<td>
							<select name="cfcss_autoload">
								<option value="yes"'.selected($cf_css_autoload, 'yes', false).'>'.__('Yes', 'cf-css').'</option>
								<option value="no"'.selected($cf_css_autoload, 'no', false).'>'.__('No', 'cf-css').'</option>
							</select>
						</td>
					</tr>
					<tr>
						<td style="vertical-align:middle;"><strong>'.__('File Content:', 'cf-css').'</strong></td>
						<td>
							<textarea name="cfcss_content" class="widefat" rows="25">'.htmlentities($cf_css_contents).'</textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<p>
				<input type="hidden" name="cf_action" value="cfcss_save" />
				<input type="submit" class="button-primary" name="submit" id="cfcss_submit" value="'.__('Save CSS Changes', 'cf-css').'" />
			</p>
		</form>
	</div>
	');
}

?>