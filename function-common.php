<?php defined('ABSPATH') or exit;

function wpcsv_kses_allowed_options()
{
	$default = wp_kses_allowed_html('post');

	$default['input'] = [
		'type' => 1,
		'name' => 1,
		'value' => 1,
		'class' => 1,
		'id' => 1,
	];

	$default['form'] = [
		'type' => 1,
		'name' => 1,
		'value' => 1,
		'class' => 1,
		'id' => 1,
	];

	$default['option'] = [
		'type' => 1,
		'name' => 1,
		'value' => 1,
		'class' => 1,
		'id' => 1,
		'selected' => 1,
	];

	return $default;
}

function wpcsv_get_ip()
{
	// populate a local variable to avoid extra function calls.
	// NOTE: use of getenv is not as common as use of $_SERVER.
	//       because of this use of $_SERVER is recommended, but 
	//       for consistency, I'll use getenv below
	$tmp = getenv("HTTP_CLIENT_IP");

	// you DON'T want the HTTP_CLIENT_ID to equal unknown. That said, I don't
	// believe it ever will (same for all below)
	if ( $tmp && !strcasecmp( $tmp, "unknown"))
		return $tmp;

	$tmp = getenv("HTTP_X_FORWARDED_FOR");
	if( $tmp && !strcasecmp( $tmp, "unknown"))
		return $tmp;

	// no sense in testing SERVER after this. 
	// $_SERVER[ 'REMOTE_ADDR' ] == gentenv( 'REMOTE_ADDR' );
	$tmp = getenv("REMOTE_ADDR");
	if($tmp && !strcasecmp($tmp, "unknown"))
		return $tmp;
	
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) { return $_SERVER['REMOTE_ADDR']; }

	return("unknown");
}

function wpcsv_upload_dir($upload)
{
	$upload['subdir'] = '/copysafe-video';
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url'] = $upload['baseurl'] . $upload['subdir'];
	
	return $upload;
}