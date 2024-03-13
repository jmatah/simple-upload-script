<?php
/**
 * M-Solutions File Uploader
 *
 * This is a simple file uploader script that uploads files to Amazon S3 bucket.
 *
 * @package M-Solutions File Uploader
 *
 * @version 1.0
 */
use Aws\S3\S3Client;

 // Include the AWS SDK autoloader
require 'vendor/autoload.php';
require 'config.php';

$statusMsg = '';
$status    = '';

// If file upload form is submitted.
if ( isset( $_POST['submit'] ) ) {
	// Check whether user inputs are empty.
	if ( ! empty( $_FILES['userfile']['name'] ) ) {
		// File info
		$file_name = basename( $_FILES['userfile']['name'] );
		$file_type = pathinfo( $file_name, PATHINFO_EXTENSION );

		// Allow certain file formats.
		$allowTypes = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png', 'jpeg', 'gif' );
		if ( in_array( $file_type, $allowTypes ) ) {
			// File temp source
			$file_temp_src = $_FILES['userfile']['tmp_name'];

			if ( is_uploaded_file( $file_temp_src ) ) {
				// Instantiate an Amazon S3 client
				$s3 = new S3Client(
					array(
						'version'     => $version,
						'region'      => $region,
						'credentials' => array(
							'key'    => $access_key_id,
							'secret' => $secret_access_key,
						),
					)
				);

				// Upload file to S3 bucket
				try {
					$result     = $s3->putObject(
						array(
							'Bucket'     => $bucket,
							'Key'        => $file_name,
							'SourceFile' => $file_temp_src,
						)
					);
					$result_arr = $result->toArray();

					if ( ! empty( $result_arr['ObjectURL'] ) ) {
						$s3_file_link = $result_arr['ObjectURL'];
						$status       = 'success';
					} else {
						$statusMsg = 'Upload Failed! S3 Object URL not found.';
						$status    = 'error';
					}
				} catch ( Aws\S3\Exception\S3Exception $e ) {
					$status    = 'error';
					$statusMsg = $e->getMessage();
				}

				if ( empty( $api_error ) ) {
					$status    = 'success';
					$statusMsg = 'File was uploaded to the S3 bucket successfully!';
				} else {
					$status    = 'error';
					$statusMsg = $api_error;
				}
			} else {
				$status	   = 'error';
				$statusMsg = 'File upload failed!';
			}
		} else {
			$status    = 'error';
			$statusMsg = 'Sorry, only Word/Excel/Image files are allowed to upload.';
		}
	} else {
		$status    = 'error';
		$statusMsg = 'Please select a file to upload.';
	}
}
?><!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="charset" content="UTF-8">
	<meta http-equiv="content-type" content="text/xml; charset=utf-8" />
	<title>M-Solutions File Uploader</title>
	<style>
		.container {margin: 100px 50px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; box-shadow: 0 0 5px 5px #eee;}
		.form-group {margin-bottom: 20px;}
		.upload {padding:0;}
		.btn {padding: 10px 20px;}
		.btn-primary {background-color: #007bff; color: #fff;}
		.btn-primary:hover {background-color: #0056b3; color: #fff;}
		input[type="file"]{padding: 0; border: 1px solid #ddd; border-radius: 5px;}
		input[type="file"]:focus{border: 1px solid #007bff;}
		input[type=file]::file-selector-button {background-color: #eee; color: #000; border: 1px solid #eee; border-right: 1px solid #e5e5e5; padding: 10px 15px; margin-right: 20px; }
		input[type=file]::file-selector-button:hover {background-color: #e5e5e5; box-shadow: 0 0 2px 2px #eee;border:1px solid #ccc; border-right: 1px solid #e5e5e5; }
		.success {color: #28a745; font-weight: bold; border: 1px solid #28a745; padding: 10px; border-radius: 5px; background-color: #d4edda; margin-bottom: 20px;}
		.error {color: #dc3545; font-weight: bold; border: 1px solid #dc3545; padding: 10px; border-radius: 5px; background-color: #f8d7da; margin-bottom: 20px;}
	</style>
</head>
<body>
	<div class="container">
		<div class="row">
			<h2>M-Solutions File Uploader</h2>
		</div>
		<div class="row">
			<?php if ( ! empty( $statusMsg ) ) { ?>
				<div class="col-md-6">
					<div class="<?php echo trim( $status ); ?>"><?php echo trim( $statusMsg ); ?></div>
				</div>
			<?php } ?>
			<div class="col-md-6">
				<h2>Upload File</h2>
				<form method="post" action="upload.php" enctype="multipart/form-data">
					<div class="form-group upload">
						<label><b>Select File:</b></label>
						<input type="file" name="userfile" class="form-control" required>
					</div>
					<div class="form-group">
						<input type="submit" class="btn btn-primary" name="submit" value="Upload">
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
