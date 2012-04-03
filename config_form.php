<div class="field">

	<div class="inputs">
		
		<h3><b><em>Note that if you are uploading to the Internet Archive, saving an item may take a while.</em></b></h3>
		<br/>
		<h3>Please visit <a href="http://www.archive.org/account/s3.php" target="_blank">The Internet Archive's S3 Page</a> to generate the keys below.</h3>
		<h3>Be sure to log in with the account used for your archives.</h3>
		<br/>
		<span><b>Upload to Internet Archive By Default</b></span>
		<input type="checkbox" name="PostToInternetArchiveDefaultBool" id="PostToInternetArchiveDefaultBool" value="Yes" <?php if(get_option('post_to_internet_archive_default_bool') == 'Yes') {echo 'checked';} ?>/>
		<div>You can change this option on a per-item basis</div>
		<br/>
		<br/>
		<div><b>S3 access key</b></div>
		<input type="text" name="AWSAccessKeyId" id="AWSAccessKeyId" value=<?php echo get_option('secret_key') ?>>
		<br/>
		<br/>
		<div><b>S3 secret key</b></div>
    	<input type="text" name="SecretKey" id="SecretKey" value=<?php echo get_option('access_key') ?>>    	
		<br/>
		<br/>
		<div><b>Bucket Prefix</b></div>
		<div>The recommended default prefix almost guarantees a unique bucket name. If you would like to change it, use all lowercase and no spaces.</div>
    	<input type="text" name="BucketPrefix" id="BucketPrefix" value=<?php echo get_option('bucket_prefix') ?>>    	
    	
	</div>
	
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

		$("#AWSAccessKeyId").Watermark("Enter&nbsp;S3&nbsp;access&nbsp;key&nbsp;here.");
		$("#SecretKey").Watermark("Enter&nbsp;S3&nbsp;secret&nbsp;key&nbsp;here.");
	    
	    jQuery("form").bind("submit", function(event) {

			//cannot use .children() because form has no name or id
            if (jQuery('input[name=AWSAccessKeyId]').val().indexOf(" ") != -1 || jQuery('input[name=AWSAccessKeyId]').val() == "") {
				alert('Please enter valid secret key.');
            }
            if (jQuery('input[name=SecretKey]').val().indexOf(" ") != -1 || jQuery('input[name=SecretKey]').val() == "") {
				alert('Please enter valid access key.');
            }
            if (jQuery('input[name=BucketPrefix]').val().indexOf(" ") != -1 || jQuery('input[name=BucketPrefix]').val() == "") {
				alert('Please enter valid bucket prefix.');
            } else {
            	jQuery('input[name=BucketPrefix]').val(jQuery('input[name=BucketPrefix]').val().toLowerCase()));
            }
            
            return false;
            
	    });
    });
</script>