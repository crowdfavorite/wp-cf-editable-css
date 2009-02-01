<?php
/*
Plugin Name: Crowd Favorite Editable CSS
Plugin URI: http://crowdfavorite.com
Description:  Gives the user the ability to edit a CSS file
Version: 1.0
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

// ini_set('display_errors', '1');
// ini_set('error_reporting', E_ALL);

load_plugin_textdomain('cf-css');
$cf_css_themedir = trailingslashit(str_replace(trailingslashit(get_bloginfo('url')),ABSPATH,get_bloginfo('template_directory')));

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
		if(isset($_POST['cf_action'])) {
			switch($_POST['cf_action']) {
				case 'cfcss_save':
					cfcss_save($_POST['cfcss_content']);
					wp_redirect(get_bloginfo('wpurl').'/wp-admin/themes.php?page=cf-editable-css.php');
					die();
			}
		}
	}
}
add_action('init','cfcss_request_handler');

function cfcss_save($content) {
	global $cf_css_themedir;
	$cf_css_filename = $cf_css_themedir.'custom.css';
	$cf_css_file = fopen($cf_css_filename,"w+");
	fwrite($cf_css_file,$content);
	fclose($cf_css_file);
}

function cfcss_options_form() {
	global $cf_css_themedir;
	$cf_css_filename = $cf_css_themedir.'custom.css';
	if(file_exists($cf_css_filename) && is_readable($cf_css_filename)) {
		$cf_css_file = fopen($cf_css_filename,"r");
		$cf_css_contents = fread($cf_css_file,filesize($cf_css_filename));
		fclose($cf_css_file);
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
			cfcss_edit_form($cf_css_contents);
		}
	}
}

function cfcss_edit_form($cf_css_contents) {
	print('
		<h4>
			'.__('All styles written into this file will override the current theme styles.  Be cautious as this may cause undesired results.').'
		</h4>
		<form action="'.get_bloginfo('url').'/wp-admin/options-general.php" method="post" id="cfcss-form">
			<textarea name="cfcss_content" cols="115" rows="25">'.$cf_css_contents.'</textarea>
			<p class="submit" style="border-top: none;">
				<input type="hidden" name="cf_action" value="cfcss_save" />
				<input type="submit" name="submit" id="cfcss_submit" value="'.__('Save CSS Changes', 'cf-css').'" />
			</p>
		</form>
	</div>
	');
}

?>