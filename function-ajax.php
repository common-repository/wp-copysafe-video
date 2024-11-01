<?php defined('ABSPATH') OR exit;

function wpcsv_ajax_action()
{
	add_filter('upload_dir', 'wpcsv_upload_dir');

	// check ajax nonce
	//check_ajax_referer( __FILE__ );
	if (current_user_can('upload_files'))
	{
		$response = [];
	
		// handle file upload
		$id = media_handle_upload(
			'async-upload',
			0,
			[
				'test_form' => TRUE,
				'action' => 'my-plugin-upload-action',
			]
		);

		// send the file' url as response
		if (is_wp_error($id)) {
			$response['status'] = 'error22';
			$response['error'] = $id->get_error_messages();
		}
		else {
			$response['status'] = 'success';

			$src = wp_get_attachment_image_src($id, 'thumbnail');
			$response['attachment'] = [];
			$response['attachment']['id'] = $id;
			$response['attachment']['src'] = $src[0];
		}
	}

	remove_filter('upload_dir', 'wpcsv_upload_dir');

	wp_send_json($response);
}