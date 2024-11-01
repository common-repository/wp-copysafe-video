<?php
/*
  Plugin Name: CopySafe Video Protection
  Plugin URI: https://artistscope.com/copysafe_video_protection_wordpress_plugin.asp
  Description: This plugin enables sites using CopySafe Video to add copy protected videos to web pages and posts.
  Author: ArtistScope
  Text Domain: wp-copysafe-video
  Version: 2.9
  License: GPLv2
  Author URI: https://artistscope.com/

  Copyright 2024 ArtistScope Pty Limited


  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// ================================================================================ //
//                                                                                  //
//  WARNING : DONT CHANGE ANYTHING BELOW IF YOU DONT KNOW WHAT YOU ARE DOING        //
//                                                                                  //
// ================================================================================ //

if (!defined('ABSPATH')) {
  exit;
} // Exit if accessed directly

define('WPCSV_ASSET_VERSION', 1.03);

# set script max execution time to 5mins
set_time_limit(300);

require_once __DIR__ . "/function-common.php";
require_once __DIR__ . "/function.php";
require_once __DIR__ . "/function-page.php";
require_once __DIR__ . "/function-shortcode.php";
require_once __DIR__ . "/function-ajax.php";


function wpcsv_enable_extended_upload($mime_types = []) {
	// You can add as many MIME types as you want.
	$mime_types['class'] = 'application/octet-stream';
	// If you want to forbid specific file types which are otherwise allowed,
	// specify them here.  You can add as many as possible.
	return $mime_types;
}
add_filter('upload_mimes', 'wpcsv_enable_extended_upload');

// ============================================================================================================================
# register WordPress menus
function wpcsv_admin_menus() {
	add_menu_page('CopySafe Video', 'CopySafe Video', 'publish_posts', 'wpcsv_list');
	add_submenu_page('wpcsv_list', 'CopySafe Video List Files', 'List Files', 'publish_posts', 'wpcsv_list', 'wpcsv_admin_page_list');
	add_submenu_page('wpcsv_list', 'CopySafe Video Settings', 'Settings', 'publish_posts', 'wpcsv_settings', 'wpcsv_admin_page_settings');
}

// ============================================================================================================================
# delete file options
function wpcsv_delete_file_options($file_name) {
	$file_name = trim($file_name);
	$wpcsv_options = get_option('wpcsv_settings');
	if(isset($wpcsv_options["classsetting"]) && is_array($wpcsv_options["classsetting"]))
	{
		foreach ($wpcsv_options["classsetting"] as $k => $arr)
		{
			if ($wpcsv_options["classsetting"][$k][$file_name])
			{
				unset($wpcsv_options["classsetting"][$k][$file_name]);
				if (!count($wpcsv_options["classsetting"][$k]))
				{
					unset($wpcsv_options["classsetting"][$k]);
				}
			}
		}
	}

	update_option('wpcsv_settings', $wpcsv_options);
}

// ============================================================================================================================
# install media buttons
function wpcsv_media_buttons($context)
{
	global $post_ID;
	// generate token for links
	$token = wp_create_nonce('wpcsv_token');
	$url = admin_url('?wpcsv-popup=file_upload&wpcsv_token=' . urlencode($token) . '&post_id=' . urlencode($post_ID));
	echo "<a href='" . esc_attr($url) . "' class='thickbox' id='wpcsv_link' data-body='no-overflow' title='CopySafe Video'>" .
		"<img src='" . esc_attr(plugin_dir_url(__FILE__)) . "/images/copysafevideobutton.png'></a>";
}

// ============================================================================================================================
# browser detector js file
function wpcsv_load_js() {
	// load custom JS file
	wp_register_script('wp-copysafe-video', WPCSV_PLUGIN_URL . 'js/wp-copysafe-video.js', [], WPCSV_ASSET_VERSION, ['in_footer' => true]);
}

// ============================================================================================================================
# admin page styles
function wpcsv_admin_load_styles() {
	// register custom CSS file & load
	wp_register_style('wpcsv-style', plugins_url('/css/wp-copysafe-video.css', __FILE__), [], WPCSV_ASSET_VERSION);
	wp_enqueue_style('wpcsv-style');
}

function wpcsv_is_admin_postpage()
{
	$chk = FALSE;

	$tmp = explode("/", $_SERVER["SCRIPT_NAME"]);
	$ppage = end($tmp);

	if ($ppage == "post-new.php" || $ppage == "post.php")
	{
		$chk = TRUE;
	}

	return $chk;
}

function wpcsv_includecss_js()
{
	if( ! wpcsv_is_admin_postpage())
	{
		return;
	}

	global $wp_popup_upload_lib;

	if ($wp_popup_upload_lib)
	{
		return;
	}

	$wp_popup_upload_lib = TRUE;
	
	wp_enqueue_style('jquery-ui-1.9');

	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery.json');
}

function wpcsv_includecss_js_to_footer(){
	if (!wpcsv_is_admin_postpage())
		return;
	
	?>
	<script>
	if( jQuery("#wpcsv_link").length > 0 ){
		if( jQuery("#wpcsv_link").data("body") == "no-overflow" ){
			jQuery("body").addClass("wps-no-overflow");
		}
	}
	</script>
	<?php
}

function wpcsv_load_admin_scripts()
{
	wp_register_style('jquery-ui-1.9', '//code.jquery.com/ui/1.9.2/themes/redmond/jquery-ui.css', [], WPCSV_ASSET_VERSION);
	wp_register_script('jquery.json', WPCSV_PLUGIN_URL . 'lib/jquery.json-2.3.js', ['plupload-all'], WPCSV_ASSET_VERSION, ['in_footer' => true]);

	wp_enqueue_script('suggest');
	wp_enqueue_script('plupload-all');
}

// ============================================================================================================================
# setup plugin
function wpcsv_setup()
{
	//----add codding----
	$options = get_option("wpcsv_settings");
	
	define('WPCSV_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__))); //use for include files to other files
	define('WPCSV_PLUGIN_URL', plugins_url('/', __FILE__));
	define('WPCSV_UPLOAD_PATH', str_replace("\\", "/", ABSPATH . $options["settings"]["upload_path"])); //use for include files to other files
	define('WPCSV_UPLOAD_URL', site_url($options["settings"]["upload_path"]));

	add_action('admin_head', 'wpcsv_includecss_js');
	add_action('admin_footer', 'wpcsv_includecss_js_to_footer');
	add_action('wp_ajax_wpcsv_ajaxprocess', 'wpcsv_ajaxprocess');

	//Sanitize the GET input variables
	$pagename = sanitize_key(@$_GET['page']);
	if (!$pagename) {
		$pagename = '';
	}
	$csvfilename = sanitize_file_name(@$_GET['csvfilename']);
	if (!$csvfilename) {
		$csvfilename = '';
	}
	$action = sanitize_key(@$_GET['action']);
	if (!$action) {
		$action = '';
	}

	if ($pagename == 'wpcsv_list' && $csvfilename && $action == 'csvdel' && wp_verify_nonce(@$_GET['csvdel_nonce'], 'csvdel'))
	{
		//check that nonce is valid and user is administrator
		if (current_user_can('administrator')) {
			echo "Nonce has been verified";
			wpcsv_delete_file_options(@$_GET['csvfilename']);
			if (file_exists(WPCSV_UPLOAD_PATH . @$_GET['csvfilename']))
			{
				wp_delete_file(WPCSV_UPLOAD_PATH . @$_GET['csvfilename']);
			}
			wp_redirect('admin.php?page=wpcsv_list');
		}
		else
		{
			wp_nonce_ays('');
		}
	}

	if (isset($_GET['wpcsv-popup']) && @$_GET["wpcsv-popup"] == "file_upload")
	{
		require_once(WPCSV_PLUGIN_PATH . "popup_load.php");
		exit();
	}

	//=============================
	// load js file
	add_action('wp_enqueue_scripts', 'wpcsv_load_js');

	// load admin CSS
	add_action('admin_print_styles', 'wpcsv_admin_load_styles');

	// add short code
	add_shortcode('copysafevideo', 'wpcsv_shortcode');

	add_action('admin_enqueue_scripts', 'wpcsv_load_admin_scripts');

	// if user logged in
	if (is_user_logged_in()) {
		// install admin menu
		add_action('admin_menu', 'wpcsv_admin_menus');

		// check user capability
		if (current_user_can('edit_posts')) {
			// load media button
			add_action('media_buttons', 'wpcsv_media_buttons');
		}
	}
}

// ============================================================================================================================
# runs when plugin activated
function wpcsv_activate()
{
	// if this is first activation, setup plugin options
	if( ! get_option('wpcsv_settings'))
	{
		// set plugin folder
		$upload_dir = 'wp-content/uploads/copysafe-video/';

		// set default options
		$wpcsv_options['settings'] = [
			'admin_only' => "checked",
			'upload_path' => $upload_dir,
			'mode' => "demo",
			'language' => "",
			'width' => '620',
			'height' => '400',
			'asps' => "checked",
			'ff' => "",
			'ch' => "",
			'watermarked' => "checked",
			'wtmtextsize' => "20",
			'wtmtextcolour' => "FFFF00",
			'wtmtextposition' => "0",
			'wtmtextopacity' => "9",
		];

		update_option('wpcsv_settings', $wpcsv_options);

		$upload_dir = ABSPATH . $upload_dir;
		if (!is_dir($upload_dir))
		{
			wp_mkdir_p($upload_dir, 0, TRUE);
		}
		// create upload directory if it is not exist
	}
}

// ============================================================================================================================
# runs when plugin deactivated
function wpcsv_deactivate() {
	// remove text editor short code
	remove_shortcode('copysafevideo');
}

// ============================================================================================================================
# runs when plugin deleted.
function wpcsv_uninstall()
{
	global $wp_filesystem;

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	// delete all uploaded files
	$default_upload_dir = ABSPATH . 'wp-content/uploads/copysafe-video/';
	if (is_dir($default_upload_dir))
	{
		$dir = scandir($default_upload_dir);
		foreach ($dir as $file)
		{
			if ($file != '.' || $file != '..')
			{
				wp_delete_file($default_upload_dir . $file);
			}
		}
		$wp_filesystem->rmdir($default_upload_dir);
	}

	// delete upload directory
	$options = get_option("wpcsv_settings");

	if ($options["settings"]["upload_path"])
	{
		$upload_path = ABSPATH . $options["settings"]["upload_path"];

		if (is_dir($upload_path))
		{
			$dir = scandir($upload_path);
			foreach ($dir as $file)
			{
				if ($file != '.' || $file != '..')
				{
					wp_delete_file($upload_path . '/' . $file);
				}
			}
			// delete upload directory
			$wp_filesystem->rmdir($upload_path);
		}
	}

	// delete plugin options
	delete_option('wpcsv_settings');

	// unregister short code
	remove_shortcode('copysafevideo');

	// delete short code from post content
	wpcsv_delete_shortcode();
}

// ============================================================================================================================
# register plugin hooks
register_activation_hook(__FILE__, 'wpcsv_activate'); // run when activated
register_deactivation_hook(__FILE__, 'wpcsv_deactivate'); // run when deactivated
register_uninstall_hook(__FILE__, 'wpcsv_uninstall'); // run when uninstalled

add_action('init', 'wpcsv_setup');
//Imaster Coding

function wpcsv_admin_js() {
	wp_register_script('wp-copysafe-video-uploader', WPCSV_PLUGIN_URL . 'js/copysafevideo_media_uploader.js', [
		'jquery',
		'plupload-all',
	],
	WPCSV_ASSET_VERSION,
	['in_footer' => true]
	);
}

function wpcsv_admin_head()
{
	$uploader_options = [
		'runtimes' => 'html5,silverlight,flash,html4',
		'browse_button' => 'mfu-plugin-uploader-button',
		'container' => 'mfu-plugin-uploader',
		'drop_element' => 'mfu-plugin-uploader',
		'file_data_name' => 'async-upload',
		'multiple_queues' => TRUE,
		'url' => admin_url('admin-ajax.php'),
		'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
		'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
		'filters' => [
			[
				'title' => __('Allowed Files'),
				'extensions' => '*',
			],
		],
		'multipart' => TRUE,
		'urlstream_upload' => TRUE,
		'multi_selection' => TRUE,
		'multipart_params' => [
			'_ajax_nonce' => '',
			'action' => 'my-plugin-upload-action',
		],
	];
	?>
<script type="text/javascript">
	var global_uploader_options =<?php echo wp_json_encode($uploader_options); ?>;
</script>
	<?php
}

add_action('admin_head', 'wpcsv_admin_head');
add_action('wp_ajax_my-plugin-upload-action', 'wpcsv_ajax_action');

remove_filter('upload_dir', 'wpcsv_upload_dir');