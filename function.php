<?php defined('ABSPATH') OR exit;

function wpcsv_ajaxprocess()
{
	if ($_POST["fucname"] == "check_upload_nonce")
	{
		if( ! wp_verify_nonce($_POST['nonce_value'], 'wpcsv_upload_nonce'))
		{
			echo "0";
			wp_nonce_ays('');
			exit();
		}
	}
	else if ($_POST["fucname"] == "file_upload")
	{
		$msg = wpcsv_file_upload($_POST);
		$upload_list = get_wpcsv_uploadfile_list();
		$data = [
			"message" => $msg,
			"list" => $upload_list,
		];
		echo wp_json_encode($data);
	}
	else if ($_POST["fucname"] == "file_search")
	{
		$data = wpcsv_file_search($_POST);
		echo wp_kses($data, wpcsv_kses_allowed_options());
	}
	else if ($_POST["fucname"] == "setting_save")
	{
		$data = wpcsv_setting_save($_POST);
		echo wp_kses($data, wpcsv_kses_allowed_options());
	}
	else if ($_POST["fucname"] == "get_parameters")
	{
		$data = wpcsv_get_parameters($_POST);
		echo wp_kses($data, wpcsv_kses_allowed_options());
	}

	exit();
}

function wpcsv_get_parameters($param)
{
	$default_settings = [];

	$postid   = isset($param["post_id"]) ? (int)$param['post_id'] : 0;
	$filename = isset($param['filename']) ? trim($param["filename"]) : '';
	$settings = wpcsv_get_first_class_settings();

	$options = get_option("wpcsv_settings");
	if(isset($options["classsetting"][$postid][$filename]))
	{
		$settings = wp_parse_args($options["classsetting"][$postid][$filename], $default_settings);
	}

	extract($settings);

	//$prints_allowed = ($prints_allowed) ? $prints_allowed : 0;
	//$print_anywhere = ($print_anywhere) ? 1 : 0;
	//$allow_capture = ($allow_capture) ? 1 : 0;
	$allowremote = ($allowremote) ? 1 : 0;

	$params = " width='" . $width . "'" .
		" height='" . $height . "'" .
		" remote='" . $allowremote . "'";
	
	return $params;
}

function wpcsv_get_first_class_settings() {
	$settings = [
		'width' => '600',
		'height' => '600',
		//'prints_allowed' => 0,
		//'print_anywhere' => 0,
		//'allow_capture' => 0,
		'remote' => 0,
		//'background' => 'CCCCCC',
	];
	return $settings;
}

function wpcsv_file_upload($param) {
	$file_error = $param["error"];
	$file_errors = [
		0 => __("There is no error, the file uploaded with success", 'wp-copysafe-video'),
		1 => __("The uploaded file exceeds the upload_max_filesize directive in php.ini", 'wp-copysafe-video'),
		2 => __("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 'wp-copysafe-video'),
		3 => __("The uploaded file was only partially uploaded", 'wp-copysafe-video'),
		4 => __("No file was uploaded", 'wp-copysafe-video'),
		6 => __("Missing a temporary folder", 'wp-copysafe-video'),
		7 => __("Upload directory is not writable", 'wp-copysafe-video'),
		8 => __("User not logged in", 'wp-copysafe-video'),
	];

	if ($file_error == 0) {
		$msg = '<div class="updated"><p><strong>' . esc_html(__('File Uploaded. You must save "File Details" to insert post', 'wp-copysafe-video')) . '</strong></p></div>';
	}
	else {
		$msg = '<div class="error"><p><strong>' . esc_html(__('Error', 'wp-copysafe-video')) . '!</strong></p><p>' . esc_html($file_errors[$file_error]) . '</p></div>';
	}

	return $msg;
}

function wpcsv_file_search($param)
{
	// get selected file details
	if( ! empty($param['search']) && ! empty($param['post_id']))
	{
		$postid = (int)$param['post_id'];
		$search = trim($param["search"]);

		$files = _get_wpcsv_uploadfile_list();

		$result = FALSE;
		foreach ($files as $file)
		{
			if ($search == trim($file["filename"]))
			{
				$result = TRUE;
			}
		}

		if( ! $result)
		{
			return "<hr /><h2>No found file</h2>";
		}

		$file_options = wpcsv_get_first_class_settings();

		$wpcsv_options = get_option('wpcsv_settings');

		if ($wpcsv_options["classsetting"][$postid][$search])
		{
			$file_options = $wpcsv_options["classsetting"][$postid][$search];
		}

		extract($file_options, EXTR_OVERWRITE);

		$str = "<hr />
                <div class='icon32' id='icon-file'><br /></div>
		        <h2>Video Class Settings</h2>
		        <div>
	    			<table cellpadding='0' cellspacing='0' border='0' >
	  					<tbody id='wpcsv_setting_body'> 
							  <tr> 
							    <td align='left' width='50'>&nbsp;</td>
							    <td align='left' width='40'><img src='" . esc_attr(WPCSV_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Number of prints allowed per session. For no printing set 0.'></td>
							    <td align='left' width='120'>Viewer Width:&nbsp;&nbsp;</td>
							    <td> 
							      <input name='width' type='text' value='" . esc_attr($width) . "' size='3'>
							    </td>
							  </tr>
							  <tr> 
							    <td align='left' width='50'>&nbsp;</td>
							    <td align='left' width='40'><img src='" . esc_attr(WPCSV_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Number of prints allowed per session. For no printing set 0.'></td>
							    <td align='left'>Viewer Height:&nbsp;&nbsp;</td>
							    <td> 
							      <input name='height' type='text' value='" . esc_attr($height) . "' size='3'>
							    </td>
							  </tr>
							  <tr> 
							    <td align='left'>&nbsp;</td>
							    <td align='left'><img src='" . esc_attr(WPCSV_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Check this box to prevent viewing by remote or virtual computers when the class image loads.'></td>
							    <td align='left'>Allow Remote:</td>
							    <td> 
							      <input name='allowremote' type='checkbox' value='1' ". esc_attr($allowremote ? 'checked' : '') . ">
							    </td>
							  </tr>
						</tbody> 
					</table>
			        <p class='submit'>
			            <input type='button' value='Save' class='button-primary' id='setting_save' name='submit' />
			            <input type='button' value='Cancel' class='button-primary' id='cancel' />
			        </p>
        	</div>";
		return $str;
	}
}

function wpcsv_setting_save($param)
{
	$postid = isset($param["post_id"]) ? (int)$param["post_id"] : 0;
	$name   = isset($param["nname"]) ? trim($param["nname"]) : $param["nname"];
	$data   = (array) json_decode(stripcslashes($param["set_data"]));

	// escape user inputs
	$data = array_map("esc_attr", $data);
	extract($data);

	$wpcsv_settings = get_option('wpcsv_settings');
	if (!is_array($wpcsv_settings))
	{
		$wpcsv_settings = [];
	}

	$datas = [
		'width' => "$width",
		'height' => "$height",
		'remote' => $allowremote ? "1" : "0",
	];

	$wpcsv_settings["classsetting"][$postid][$name] = $datas;

	update_option('wpcsv_settings', $wpcsv_settings);

	$msg = '<div class="updated fade">
			<strong>' . esc_html(__('File Options Are Saved', 'wp-copysafe-video')) . '</strong><br />
			<div style="margin-top:5px;"><a href="#" data-alt="' . esc_attr($name) . '" class="button-secondary sendtoeditor"><strong>Insert file to editor</strong></a></div>
		</div>';
	
	return $msg;
}

function _get_wpcsv_uploadfile_list()
{
	$listdata = [];
	$file_list = scandir(WPCSV_UPLOAD_PATH);

	if( ! empty($file_list))
	{
		if (is_array($file_list) || is_object($file_list))
		{
			foreach ($file_list as $file)
			{
				if ($file == "." || $file == "..") {
					continue;
				}

				$file_path = WPCSV_UPLOAD_PATH . $file;
				if (filetype($file_path) != "file") {
					continue;
				}

				$tmp = explode('.', $file);
				$ext = end($tmp);
				if ($ext != "class") {
					continue;
				}

				$file_path = WPCSV_UPLOAD_PATH . $file;
				$file_name = $file;
				$file_size = filesize($file_path);
				$file_date = filemtime($file_path);

				if (round($file_size / 1024, 0) > 1) {
					$file_size = round($file_size / 1024, 0);
					$file_size = "$file_size KB";
				} else {
					$file_size = "$file_size B";
				}

				$file_date = gmdate("n/j/Y g:h A", $file_date);

				$listdata[] = [
					"filename" => $file_name,
					"filesize" => $file_size,
					"filedate" => $file_date,
				];
			}

		}
	}

	return $listdata;
}

function get_wpcsv_uploadfile_list()
{
	$files = _get_wpcsv_uploadfile_list();
	$table = "";

	foreach ($files as $file)
	{
		$table .=
			"<tr><td></td><td><a href='#' data-alt='" . esc_attr($file["filename"]) . "' class='sendtoeditor row-actionslink'>" . esc_attr($file["filename"]) . "</a></td>" .
			"<td width='50px'>" . esc_attr($file["filesize"]) . "</td><td width='130px'>" . esc_attr($file["filedate"]) . "</td></tr>";
	}

	if( ! $table)
	{
		$table .= '<tr><td colspan="3">' . esc_html(__('No file uploaded yet.', 'wp-copysafe-video')) . '</td></tr>';
	}

	return $table;
}


function get_wpcsv_browser_info()
{
	$u_agent  = $_SERVER['HTTP_USER_AGENT'];
	$bname    = 'Unknown';
	$platform = 'Unknown';
	$version  = "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	}
	else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	}
	else if (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/Firefox/i',$u_agent)){
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	else if(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edge/i',$u_agent)){
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}

	// finally get the correct version number
	$known = array('Version', @$ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,@$ub)){
			$version= $matches['version'][0];
		}
		else {
			$version = $matches['version'][1];
		}
	}
	else {
		$version = $matches['version'][0];
	}

	// check if we have a number
	if( $version == null || $version == "" ){ 
		$version = "?";
	}

	return array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'   => $pattern
	);
} 

function wpcsv_check_artis_browser_version()
{
	$wpcsv_current_browser = get_wpcsv_browser_info();
	$wpcsv_current_browser_data = $wpcsv_current_browser['userAgent'];
	if( $wpcsv_current_browser_data != "" )
	{
		$wpcsv_browser_data = explode("/", $wpcsv_current_browser_data);
		$wpcsv_data_count = count($wpcsv_browser_data);
		if (strpos($wpcsv_current_browser_data, 'ArtisBrowser') !== false)
		{
			$current_version = end($wpcsv_browser_data);
			$wpcsv_options = get_option('wpcsv_settings');
			$latest_version = $wpcsv_options["settings"]["latest_version"];

			if( $current_version < $latest_version )
			{
				$ref_url = get_permalink(get_the_ID());
?>
				<script>
				document.location = '<?php echo esc_js(WPCSV_PLUGIN_URL."download-update.html"); ?>';
				</script>
				<?php
			}
		}
		else {
			?>
				<script>
				document.location = '<?php echo esc_js(WPCSV_PLUGIN_URL."download.html"); ?>';
				</script>
				<?php
		}
	}
}