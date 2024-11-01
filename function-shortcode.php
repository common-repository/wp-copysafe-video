<?php defined('ABSPATH') or exit;

// ============================================================================================================================
# convert shortcode to html output
function wpcsv_shortcode($atts)
{
	wpcsv_check_artis_browser_version();
	
	global $post;

	$postid   = $post->ID;
	$filename = $atts["name"];
	
	if( ! file_exists(WPCSV_UPLOAD_PATH . $filename))
	{
		return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>File(" . esc_html($filename) . ") don't exist</strong></div>";
	}

	$settings = wpcsv_get_first_class_settings();

	// get plugin options
	$wpcsv_options = get_option('wpcsv_settings');
	if(isset($wpcsv_options["settings"]) && $wpcsv_options["settings"])
	{
		$settings = wp_parse_args($wpcsv_options["settings"], $settings);
	}

	if(isset($wpcsv_options["classsetting"][$postid][$filename]))
	{
		$settings = wp_parse_args($wpcsv_options["classsetting"][$postid][$filename], $settings);
	}

	$settings = wp_parse_args($atts, $settings);

	extract($settings);

	$asps    = ($asps) ? '1' : '0';
	$firefox = ($ff) ? '1' : '0';
	$chrome  = ($ch) ? '1' : '0';

	$allowremote  = ($allowremote) ? '1' : '0';
	$watermarked  = ($watermarked) ? '1' : '0';
	$current_user = wp_get_current_user();

	if((int)$current_user->ID>0)
	{
		$username = $current_user->user_login;

		if($current_user->user_firstname)
			$username=$current_user->user_firstname;

		$userString = $current_user->ID . ' ' . $username . ' ' . gmdate('Y-m-d');
	}
	else 
		$userString = wpcsv_get_ip() . ' ' . gmdate('Y-m-d');
	
	$watermarkstring="".$userString.",".$wtmtextsize.",".$wtmtextcolour.",".$wtmtextposition.",".$wtmtextopacity;
	if($wtmtextposition == 7)
	{
		$wtmtextposition=wp_rand(0,6);
	}
	
	$plugin_url = WPCSV_PLUGIN_URL;
	$upload_url = WPCSV_UPLOAD_URL;

	$script_tag = 'script';

	// display output
	ob_start();
	?>
		<script type="text/javascript">
			var wpcsv_plugin_url = "<?php echo esc_js($plugin_url); ?>";
			var wpcsv_upload_url = "<?php echo esc_js($upload_url); ?>" ;
		</script>
		<script type="text/javascript">
		<!-- hide JavaScript from non-JavaScript browsers
			var m_bpDebugging = false;
			var m_szMode = "<?php echo esc_js($mode); ?>";
			var m_szClassName = "<?php echo esc_js($name); ?>";
			var m_szImageFolder = "<?php echo esc_js($upload_url); ?>"; //  path from root with / on both ends
			var m_bpAllowRemote = "<?php echo esc_js($allowremote); ?>";
			//var m_bpLanguage = "<?php echo esc_js($language); ?>";
			//var m_bpBackground = "<?php echo esc_js($background); ?>"; // background colour without the #
			var m_bpWidth = "<?php echo esc_js($width); ?>"; // width of Video display in pixels
			var m_bpHeight = "<?php echo esc_js($height); ?>"; // height of Video display in pixels

			var m_bpASPS = "<?php echo esc_js($asps); ?>";
			var m_bpChrome = "<?php echo esc_js($chrome); ?>";
			var m_bpFx = "<?php echo esc_js($firefox); ?>"; // all firefox browsers from version 5 and later
			var m_min_Version = "<?php echo esc_js($minimum_version); ?>";
			//watermarking settings
			
			var m_bpwatermarked = "<?php echo esc_js($watermarked); ?>";
			var m_bpwtmtextsize = "<?php echo esc_js($wtmtextsize); ?>";
			var m_bpwtmtextcolour = "<?php echo esc_js($wtmtextcolour); ?>";
			var m_bpwtmtextposition = "<?php echo esc_js($wtmtextposition); ?>";
			var m_bpwtmtextopacity = "<?php echo esc_js($wtmtextopacity); ?>";
			var watermarkstring = "<?php echo esc_js($watermarkstring); ?>";

			if (m_szMode == "debug") {
				m_bpDebugging = true;
			}
			// -->
		</script>
		<<?php echo esc_html($script_tag); ?> src="<?php echo esc_attr(WPCSV_PLUGIN_URL . 'js/wp-copysafe-video.js?v=' . urlencode(WPCSV_ASSET_VERSION)); ?>"></<?php echo esc_html($script_tag); ?>>
	<?php
	$output = ob_get_clean();

	return $output;
}

// ============================================================================================================================
# delete short code
function wpcsv_delete_shortcode() {
  // get all posts
  $posts_array = get_posts();
  foreach ($posts_array as $post) {
    // delete short code
    $post->post_content = wpcsv_deactivate_shortcode($post->post_content);
    // update post
    wp_update_post($post);
  }
}

// ============================================================================================================================
# deactivate short code
function wpcsv_deactivate_shortcode($content) {
  // delete short code
  $content = preg_replace('/\[copysafevideo name="[^"]+"\]\[\/copysafevideo\]/s', '', $content);
  return $content;
}

// ============================================================================================================================
# search short code in post content and get post ids
function wpcsv_search_shortcode($file_name) {
  // get all posts
  $posts = get_posts();
  $IDs = FALSE;
  foreach ($posts as $post) {
    $file_name = preg_quote($file_name, '\\');
    preg_match('/\[copysafevideo name="' . $file_name . '"\]\[\/copysafevideo\]/s', $post->post_content, $matches);
    if (is_array($matches) && isset($matches[1])) {
      $IDs[] = $post->ID;
    }
  }
  return $IDs;
}