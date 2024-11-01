<?php defined('ABSPATH') or exit;

// ============================================================================================================================
# "List" Page
function wpcsv_admin_page_list()
{
	$files = _get_wpcsv_uploadfile_list();
	$msg = '';
	$table = '';

	if( ! empty($_POST))
	{
		if (wp_verify_nonce($_POST['wpcopysafevideo_wpnonce'], 'wpcopysafevideo_settings'))
		{
			$wpcsv_options = get_option('wpcsv_settings');
		
			if (!empty($wpcsv_options['settings']['upload_path']))
			{
				$target_dir = $wpcsv_options['settings']['upload_path'];
			}
			else
			{
				$target_dir = "wp-content/uploads/";
			}

			$target_file = ABSPATH . $target_dir . basename($_FILES["copysafe-video-class"]["name"]);
			$uploadOk = 1;
			$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

			// Check if image file is a actual image or fake image
			if (isset($_POST["copysafe-video-class-submit"]))
			{
				// Allow only .class file formats
				if ($_FILES["copysafe-video-class"]["name"] == "")
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Please upload file to continue.', 'wp-copysafe-video')) . '</strong></p></div>';
					$uploadOk = 0;
				}
				else if ($imageFileType != "class") {
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, only .class files are allowed.', 'wp-copysafe-video')) . '</strong></p></div>';
					$uploadOk = 0;
				}
				else if ($uploadOk == 0)
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, your file was not uploaded.', 'wp-copysafe-video')) . '</strong></p></div>';
					// if everything is ok, try to upload file
				}
				else
				{
					$upload_file = $_FILES["copysafe-video-class"];

					add_filter('upload_dir', 'wpcsv_upload_dir');

					//Move file
					$movefile = wp_handle_upload($upload_file, [
						'test_form' => false,
						'test_type' => false,
						'mimes' => [
							'class' => 'application/octet-stream'
						],
					]);

					remove_filter('upload_dir', 'wpcsv_upload_dir');

					if($movefile && ! isset($movefile['error']))
					{
						$the_file_text = sprintf(
							/* translators: %1$s: file name, %2$s: base url */
							__('The file %1$s has been uploaded. Click <a href="%2$s/wp-admin/admin.php?page=wpcsv_list">here</a> to update below list.'),
							basename($_FILES["copysafe-video-class"]["name"]),
							get_site_url()
						);
						
						$msg .= '<div class="updated"><p><strong>' . wp_kses($the_file_text, wpcsv_kses_allowed_options()) . '</strong></p></div>';
					}
					else
					{
						$msg .= '<div class="error"><p><strong>' . __('Sorry, there was an error uploading your file.') . '</strong></p></div>';
					}
				}
			}
		}
	}

	foreach ($files as $file)
	{
		$bare_url = 'admin.php?page=wpcsv_list&csvfilename=' . urlencode($file["filename"]) . '&action=csvdel';
		$complete_url = wp_nonce_url($bare_url, 'csvdel', 'csvdel_nonce');

		$link = "<div class='row-actions'>
				<span><a href='" . esc_attr($complete_url) . "' title=''>Delete</a></span>
			</div>";

		// prepare table row
		$table .= "<tr><td></td><td>" . esc_html($file["filename"]) . " {$link}</td><td>" . esc_html($file["filesize"]) . "</td><td>" . esc_html($file["filedate"]) . "</td></tr>";
	}

	if (!$table)
	{
		$table .= '<tr><td colspan="3">' . esc_html(__('No file uploaded yet.', 'wp-copysafe-video')) . '</td></tr>';
	}
	?>
    <div class="wrap">
        <div class="icon32" id="icon-file"><br/></div>
        <?php echo wp_kses($msg, wpcsv_kses_allowed_options()); ?>
        <h2>List Video Class Files</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <?php echo wp_kses(wp_nonce_field('wpcopysafevideo_settings', 'wpcopysafevideo_wpnonce'), wpcsv_kses_allowed_options()); ?>
            <input type="file" name="copysafe-video-class" value=""/>
            <input type="submit" name="copysafe-video-class-submit"
                   value="Upload"/>
        </form>
        <div id="col-container" style="width:700px;">
            <div class="col-wrap">
                <h3>Uploaded Video Class Files</h3>
                <table class="wp-list-table widefat">
                    <thead>
                    <tr>
                        <th width="5px">&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php echo wp_kses($table, wpcsv_kses_allowed_options()); ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="clear"></div>
    </div>
  <?php
}

// ============================================================================================================================
# "Settings" page
function wpcsv_admin_page_settings()
{
	$minimum_version = 27.11;
	$allowremote     = '';
	$language        = '';
	$background      = '';
	$ff              = '';
	$ch              = '';
	$admin_only      = '';
	$asps            = '';
	$watermarked     = '';

	$msg = '';

	if( ! empty($_POST))
	{
		if(wp_verify_nonce($_POST['wpcsv_wpnonce'], 'wpcsv_settings'))
		{
			$wpcsv_options = get_option('wpcsv_settings');
			extract($_POST, EXTR_OVERWRITE);

			if( ! $upload_path)
			{
				$upload_path = 'wp-content/uploads/copysafe-video/';
			}

			$upload_path = str_replace("\\", "/", stripcslashes($upload_path));
			if (substr($upload_path, -1) != "/")
			{
				$upload_path .= "/";
			}

			$wpcsv_options['settings'] = [
				'admin_only' => sanitize_text_field($admin_only),
				'upload_path' => $upload_path,
				'mode' => $mode,
				'language' => sanitize_text_field($language),
				'background' => $background,
				'width' => $width,
				'height' => $height,
				'allowremote' => !empty(sanitize_text_field($allowremote)) ? 'checked' : '',
				'asps' => !empty(sanitize_text_field($asps)) ? 'checked' : '',
				'ff' => !empty(sanitize_text_field($ff)) ? 'checked' : '',
				'ch' => !empty(sanitize_text_field($ch)) ? 'checked': '',
				'minimum_version' => $minimum_version,
				'watermarked' => !empty(sanitize_text_field($watermarked)) ? 'checked' : '',
				'wtmtextsize' => $wtmtextsize,
				'wtmtextcolour' => $wtmtextcolour,
				'wtmtextposition' => $wtmtextposition,
				'wtmtextopacity' => $wtmtextopacity,
			];

			$upload_path = ABSPATH . $upload_path;
			if (!is_dir($upload_path))
			{
				wp_mkdir_p($upload_path, 0, TRUE);
			}

			update_option('wpcsv_settings', $wpcsv_options);
			$msg = '<div class="updated"><p><strong>' . __('Settings Saved') . '</strong></p></div>';
		}
	}

	$wpcsv_options = get_option('wpcsv_settings');
	if ($wpcsv_options["settings"])
	{
		extract($wpcsv_options["settings"], EXTR_OVERWRITE);
	}

	$select = '<option value="demo">Demo Mode</option><option value="licensed">Licensed</option><option value="debug">Debugging Mode</option>';
	$select = str_replace('value="' . $mode . '"', 'value="' . $mode . '" selected', $select);

	$lnguageOptions = [
		"0c01" => "Arabic",
		"0004" => "Chinese (simplified)",
		"0404" => "Chinese (traditional)",
		"041a" => "Croatian",
		"0405" => "Czech",
		"0413" => "Dutch",
		"" => "English",
		"0464" => "Filipino",
		"000c" => "French",
		"0007" => "German",
		"0408" => "Greek",
		"040d" => "Hebrew",
		"0439" => "Hindi",
		"000e" => "Hungarian",
		"0421" => "Indonesian",
		"0410" => "Italian",
		"0411" => "Japanese",
		"0412" => "Korean",
		"043e" => "Malay",
		"0415" => "Polish",
		"0416" => "Portuguese (BR)",
		"0816" => "Portuguese (PT)",
		"0419" => "Russian",
		"0c0a" => "Spanish",
		"041e" => "Thai",
		"041f" => "Turkish",
		"002a" => "Vietnamese",
	];
	?>
    <style type="text/css">#wpcsv_page_setting img {cursor: pointer;}</style>
    <div class="wrap">
        <div class="icon32" id="icon-settings"><br/></div>
        <?php echo wp_kses($msg, wpcsv_kses_allowed_options()); ?>
        <h2>Default Settings</h2>
        <form action="" method="post">
            <?php echo wp_kses(wp_nonce_field('wpcsv_settings', 'wpcsv_wpnonce'), wpcsv_kses_allowed_options()); ?>
            <table cellpadding='1' cellspacing='0' border='0' id='wpcsv_page_setting'>
                <p><strong>Default settings applied to all protected Video
                        pages:</strong></p>
                <tbody>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow admin only for new uploads.'></td>
                    <td align="left" nowrap>Allow Admin Only:</td>
                    <td align="left"><input name="admin_only" type="checkbox"
                                            value="checked" <?php echo esc_attr($admin_only); ?>>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Path to the upload folder for Video.'>
                    <td align="left" nowrap>Upload Folder:</td>
                    <td align="left"><input value="<?php echo esc_attr($upload_path); ?>"
                                            name="upload_path"
                                            class="regular-text code"
                                            type="text"></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Set the mode to use. Use Licensed if you have licensed images. Otherise set for Demo or Debug mode.'>
                    </td>
                    <td align="left">Mode</td>
                    <td align="left"><select name="mode">
                        <?php echo wp_kses($select, wpcsv_kses_allowed_options()); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Enter minimum version required for ArtisBrowser access.'>
                    </td>
                    <td align="left">Minimum Version</td>
                    <td align="left">
                        <input type="text" class="regular-text code" name="minimum_version" value="<?php echo esc_attr($minimum_version ? $minimum_version : 27.11); ?>" />
                        <br />
                        Enter minimum version for ArtisBrowser to check.
                    </td>
                </tr>
                <tr class="copysafe-video-attributes">
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Set width of the Video viewer.'>
                    </td>
                    <td align="left">Width - in pixels:</td>
                    <td align="left"><input value="<?php echo esc_attr($width); ?>"
                                            name="width" type="text"
                                            size="8"></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Set height of the Video viewer.'>
                    </td>
                    <td align="left">Height - in pixels:</td>
                    <td align="left"><input value="<?php echo esc_attr($height); ?>"
                                            name="height" type="text"
                                            size="8"></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using Windows in a VM partition.'>
                    </td>
                    <td align="left" nowrap>AllowRemote:</td>
                    <td align="left"><input name="allowremote" type="checkbox"
                                            value="checked" <?php echo esc_attr($allowremote); ?>>
                    </td>
                </tr>
                <tr class="copysafe-video-browsers">
                    <td colspan="5"><h2 class="title">Browser allowed</h2></td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the ArtisBrowser to access this page.'>
                    </td>
                    <td align="left" nowrap>Allow ArtisBrowser:</td>
                    <td align="left"><input name="asps" type="checkbox"
                                            value="checked" <?php echo esc_attr($asps); ?>>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the Firefox web browser to access this page.'>
                    </td>
                    <td align="left">Allow Firefox:</td>
                    <td align="left"><input name="ff"
                                            type="checkbox" <?php echo esc_attr($ff); ?>> ( for testing only by admin )
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow visitors using the Chrome web browser to access this page.'>
                    </td>
                    <td align="left">Allow Chrome:</td>
                    <td align="left"><input name="ch"
                                            type="checkbox" <?php echo esc_attr($ch); ?>> ( for testing only by admin )
                    </td>
                </tr>
				 <tr class="copysafe-video-browsers">
                    <td colspan="5"><h2 class="title">Watermark Style Settings</h2></td>
                </tr>
				<tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Allow watermarking?'>
                    </td>
                    <td align="left">Enabled:</td>
                    <td align="left"><input name="watermarked"
                                            type="checkbox" <?php echo esc_attr($watermarked); ?>>
                    </td>
                </tr>
				
				<tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Text Size (in pixels)'>
                    </td>
                    <td align="left">Watermark Text Size (in pixels):</td>
                    <td align="left">
					<select name="wtmtextsize">
						<option value="10" <?php echo esc_attr(($wtmtextsize==10)?'selected':''); ?>>10</option>
						<option value="15" <?php echo esc_attr(($wtmtextsize==15)?'selected':''); ?>>15</option>
						<option value="20" <?php echo esc_attr(($wtmtextsize==20)?'selected':''); ?>>20</option>
						<option value="25" <?php echo esc_attr(($wtmtextsize==25)?'selected':''); ?>>25</option>
						<option value="30" <?php echo esc_attr(($wtmtextsize==30)?'selected':''); ?>>30</option>
						<option value="35" <?php echo esc_attr(($wtmtextsize==35)?'selected':''); ?>>35</option>
						<option value="40" <?php echo esc_attr(($wtmtextsize==40)?'selected':''); ?>>40</option>
					</select>
					
                    </td>
                </tr>
				<tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Watermark Text Color'>
                    </td>
                    <td align="left">Text Color:</td>
                    <td align="left">
					<select name="wtmtextcolour">
						<option value="FFFFFF" <?php echo esc_attr(($wtmtextcolour=='FFFFFF')?'selected':''); ?>>White</option>
						<option value="FF3333" <?php echo esc_attr(($wtmtextcolour=='FF3333')?'selected':''); ?>>Red</option>
						<option value="FFFF00" <?php echo esc_attr(($wtmtextcolour=='FFFF00')?'selected':''); ?>>Yellow</option>
						<option value="00FF00" <?php echo esc_attr(($wtmtextcolour=='00FF00')?'selected':''); ?>>Green</option>
						<option value="00FFFF" <?php echo esc_attr(($wtmtextcolour=='00FFFF')?'selected':''); ?>>Blue</option>
					</select>
                    </td>
                </tr>
                <tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Watermark Text Position'>
                    </td>
                    <td align="left">Text Position:</td>
                    <td align="left">
                        <select name="wtmtextposition">
						<option value="0" <?php echo esc_attr(($wtmtextposition=='0')?'selected':''); ?>>Center</option>
						<option value="1" <?php echo esc_attr(($wtmtextposition=='1')?'selected':''); ?>>Bottom</option>
						<option value="2" <?php echo esc_attr(($wtmtextposition=='2')?'selected':''); ?>>Top</option>
						<option value="3" <?php echo esc_attr(($wtmtextposition=='3')?'selected':''); ?>>Top Left</option>
						<option value="4" <?php echo esc_attr(($wtmtextposition=='4')?'selected':''); ?>>Top Right</option>
						<option value="5" <?php echo esc_attr(($wtmtextposition=='5')?'selected':''); ?>>Bottom Left</option>
						<option value="6" <?php echo esc_attr(($wtmtextposition=='6')?'selected':''); ?>>Bottom Right</option>
						<option value="7" <?php echo esc_attr(($wtmtextposition=='7')?'selected':''); ?>>Rotating</option>
					</select>
					
                    </td>
                </tr>
				<tr>
                    <td align='left' width='50'>&nbsp;</td>
                    <td align='left' width='30'><img
                                src='<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>images/help-24-30.png'
                                border='0'
                                alt='Watermark Text Opacity'>
                    </td>
                    <td align="left">Opacity:</td>
                    <td align="left">
					<select name="wtmtextopacity">
						<option value="9" <?php echo esc_attr(($wtmtextopacity=='9')?'selected':''); ?>>100% (opaque)</option>
						<option value="8" <?php echo esc_attr(($wtmtextopacity=='8')?'selected':''); ?>>90%</option>
						<option value="7" <?php echo esc_attr(($wtmtextopacity=='7')?'selected':''); ?>>80%</option>
						<option value="6" <?php echo esc_attr(($wtmtextopacity=='6')?'selected':''); ?>>70%</option>
						<option value="5" <?php echo esc_attr(($wtmtextopacity=='5')?'selected':''); ?>>60%</option>
						<option value="4" <?php echo esc_attr(($wtmtextopacity=='4')?'selected':''); ?>>50%</option>
						<option value="3" <?php echo esc_attr(($wtmtextopacity=='3')?'selected':''); ?>>40%</option>
						<option value="2" <?php echo esc_attr(($wtmtextopacity=='2')?'selected':''); ?>>30%</option>
						<option value="1" <?php echo esc_attr(($wtmtextopacity=='1')?'selected':''); ?>>20%</option>
						<option value="0" <?php echo esc_attr(($wtmtextopacity=='0')?'selected':''); ?>>10%</option>
					</select>
                    </td>
                </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" value="Save Settings"
                       class="button-primary" id="submit" name="submit">
            </p>
        </form>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <script type='text/javascript'>
      jQuery(document).ready(function () {
        jQuery("#wpcsv_page_setting img").click(function () {
          alert(jQuery(this).attr("alt"));
        });
      });
    </script>
  <?php
}