<?php defined('ABSPATH') or exit;

//Check for nonce validation
$wpcsv_media_upload_nonce = isset($_GET['wpcsv_token']) ? sanitize_key($_GET['wpcsv_token']) : '';
$allow_uploads            = FALSE;

if( ! wp_verify_nonce($wpcsv_media_upload_nonce, 'wpcsv_token'))
{
	wp_nonce_ays("Error");
}
else
{
	if (current_user_can('administrator')) {
		$timestamp = time();
		$token     = md5('unique_salt' . $timestamp);

		$post_id           = isset($_GET['post_id']) ? (int)$_GET["post_id"] : 0;
		$wpcsv_options     = get_option("wpcsv_settings");
		$upload_path       = $wpcsv_options["settings"]["upload_path"];
		$_SESSION['token'] = $token;

		$session_id    = session_id();
		$token_session = "{$token}-{$session_id}";

		$admin_only = $wpcsv_options["settings"]["admin_only"];
		$allow_uploads = TRUE;
		if ($admin_only)
		{
			global $current_user;
			$allow_uploads = FALSE;
			$user_roles = "|" . implode("|", $current_user->roles) . "|";

			if (strpos($user_roles, "administrator") > 0)
			{
				$allow_uploads = TRUE;
			}
		}
	}
}
?>

<div class="wrap" id="wpcsv_div" title="SecureImage">
    <div id="wpcsv_message"></div>
    <div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
          <?php if ($allow_uploads) { ?>
              <li class="ui-state-default ui-corner-top ui-state-active"><a
                          href="#" class="ui-tabs-anchor" id="tabs-1-bt">Add
                      New</a></li>
          <?php } ?>
            <li class="ui-state-default ui-corner-top <?php echo ($allow_uploads) ? "" : "ui-state-active"; ?>">
                <a href="#" class="ui-tabs-anchor" id="tabs-2-bt">Search</a>
            </li>
            <li class="ui-state-default ui-corner-top"><a href="#"
                                                          class="ui-tabs-anchor"
                                                          id="tabs-3-bt">Existing
                    Files</a></li>
        </ul>
      <?php if ($allow_uploads) { ?>
          <div id="tabs-1"
               class="wpcsv_addnew ui-tabs-panel ui-widget-content ui-corner-bottom">
              <div class="icon32" id="icon-addnew"><br/></div>
              <h2>Add New Class File</h2>
              <div class="wpcsv_upload_content">
                  <div id="upload-queue"></div>
                  <table>
                      <tr>
                          <td>
                              <div class="mfu-plugin-uploader multiple">
                                  <input id="mfu-plugin-uploader-button"
                                         type="button"
                                         value="<?php esc_attr_e('Select Files'); ?>"
                                         class="mfu-plugin-uploader-button button">
                                  <span class="ajaxnonce"
                                        id="<?php echo esc_attr(wp_create_nonce('wpcsv_upload_nonce')); ?>"></span>
                              </div>
                          </td>
                          <td>
                              <div id="upload-filename"></div>
                          </td>
                      <tr>
                          <td>
                              <div id="upload-status"></div>
                          </td>
                      </tr>
                      <tr>
                          <td>
                              <input type="button" value="Add File"
                                     class="button button-primary" id="upload"/>
                              <a class="button-secondary"
                                 onclick="try{top.tb_remove();}catch(e){}; return false;"
                                 href="#" id="close1">Close</a>
                          </td>
                      </tr>
                  </table>
              </div>
              <p>You can choose file options after file is uploaded.</p>
              <p>If you use same name with uploaded class file, it will be
                  overwritten.</p>
              <input type="hidden" value="<?php echo esc_attr($post_id); ?>" name="postid"
                     id="postid"/>
              <input type="hidden" value="<?php echo esc_attr(WPCSV_PLUGIN_URL); ?>"
                     id="plugin-url"/>
              <input type="hidden" value="<?php echo esc_attr(WPCSV_PLUGIN_PATH); ?>"
                     id="plugin-dir"/>
              <input type="hidden" value="<?php echo esc_attr(WPCSV_UPLOAD_PATH); ?>"
                     id="upload-path"/>
              <input type="hidden" value="<?php echo esc_attr($timestamp); ?>"
                     id="token_timestamp"/>
              <input type="hidden" value="<?php echo esc_attr($token_session); ?>"
                     id="token"/>

              <div class="clear"></div>
          </div>
      <?php } ?>
        <div id="tabs-2"
             class="wpcsv_search ui-tabs-panel ui-widget-content ui-corner-bottom" <?php echo ($allow_uploads) ? "style=\"display:none;\"" : ""; ?> >
            <div class="icon32" id="icon-search"><br/></div>
            <h2>Search File</h2>
            <p>
                File name : <input type="text" id="wpcsv_searchfile"
                                   name="wpcsv_searchfile"
                                   class="regular-text"/>
                <input type="hidden" value="<?php echo esc_attr($post_id); ?>" name="postid"
                       id="postid"/>
                <input type="button" value="Search"
                       class="button button-primary" id="search" name="search"/>
                <a class="button-secondary"
                   onclick="try{top.tb_remove();}catch(e){}; return false;"
                   href="#" id="close2">Close</a>
            </p>
            <div id="file_details"></div>
            <div class="clear"></div>
        </div>

        <div id="tabs-3"
             class="wpcsv_filelist ui-tabs-panel ui-widget-content ui-corner-bottom"
             style="display:none;">
            <div class="icon32" id="icon-file"><br/></div>
            <h2>Uploaded Class Files</h2>
            <table class="wp-list-table widefat">
                <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>File</th>
                    <th>Size</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody id="wpcsv_upload_list">
                <?php echo wp_kses(get_wpcsv_uploadfile_list(), wpcsv_kses_allowed_options()); ?>
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
            <div class="clear"></div>
        </div>
    </div>
    <div id="wpcsv_ajax_process">
        <div class="wpcsv_ajax_process"></div>
    </div>
</div>