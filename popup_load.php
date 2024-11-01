<?php

if( ! Class_Exists('WPCSVPOPUP'))
{
	class WPCSVPOPUP
	{
		function __construct()
		{
			WPCSVPOPUP::add_popup_script();
			call_user_func_array(['WPCSVPOPUP', 'set_media_upload'], []);
		}

		public function header_html()
{?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <title><?php echo esc_html(__("Step Setting", 'wp-copysafe-video')); ?></title>
</head>
<body>
<div id="wrapper" class="hfeed">
    <ul>
<?php
		}

		public function footer_html()
		{
?>
    </ul>
</div>
</body>
<?php
		}

		public function set_media_upload() {
			include(WPCSV_PLUGIN_PATH . "media-upload.php");
		}

		public function add_popup_script() {
			$script_tag = 'script';
			$html = "<" . $script_tag . " type='text/javascript' src='" . esc_attr(WPCSV_PLUGIN_URL) . "js/copysafevideo_media_uploader.js?v=" . esc_attr(WPCSV_ASSET_VERSION) . "'></" . $script_tag . ">";
			
			echo wp_kses($html, ['script' => [
				'src' => 1,
				'type' => 1,
			]]);
		}
	}

	$popup = new WPCSVPOPUP ();
}