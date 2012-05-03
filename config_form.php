<div class="field">

	<div class="inputs">
		
		<h3><b><em>Note that if you are uploading to the Internet Archive, saving an item may take a while.</em></b></h3>
		<br/>
		<h3>Please visit <a href="http://www.archive.org/account/s3.php" target="_blank">The Internet Archive's S3 Page</a> to generate the keys below.</h3>
		<h3>Be sure to log in with the account used for your archives.</h3>
		<br/>
		<span><b>Upload to Internet Archive By Default</b></span>
		<input type="hidden" name="PostToInternetArchiveDefaultBool" value="0">
		<input type="checkbox" name="PostToInternetArchiveDefaultBool" id="PostToInternetArchiveDefaultBool" value="1" <?php if(get_option('post_to_internet_archive_default_bool') == '1') {echo 'checked';} ?>/>
		<div>You can change this option on a per-item basis.</div>
		<br/>
		<br/>
		<span><b>Index at Internet Archive By Default</b></span>
		<input type="hidden" name="IndexAtInternetArchiveDefaultBool" value="0">
		<input type="checkbox" name="IndexAtInternetArchiveDefaultBool" id="IndexAtInternetArchiveDefaultBool" value="1" <?php if(get_option('index_at_internet_archive_default_bool') == '1') {echo 'checked';} ?>>
		<div>If you index your items, they will appear on the results of search engines such as Google's.</div>
		<div>You can change this option on a per-item basis.</div>
		<br/>
		<br/>
		<div><b>S3 access key</b></div>
		<input type="text" name="AWSAccessKeyId" id="AWSAccessKeyId" size='35' value=<?php echo get_option('secret_key') ?>>
		<br/>
		<br/>
		<div><b>S3 secret key</b></div>
    	<input type="text" name="SecretKey" id="SecretKey" size='35' value=<?php echo get_option('access_key') ?>>    	
		<br/>
		<br/>
		<div><b>Collection Name</b></div>
		<input type="text" name="CollectionName" id="CollectionName" size='35' value=<?php echo get_option('collection_name') ?>>
		<div>You must contact <a href= "mailto:info@archive.org" >info@archive.org</a> and get an Internet Archive Collection to use this plugin.</div>
		<div>Do not fear. It is free and the Internet Archive is staffed exclusively with friendly and responsive people.</div>
		<br/>
		<br/>
		<div><b>Media Type</b></div>
		<input type="text" name="MediaType" id="MediaType" size='35' value=<?php echo get_option('media_type') ?>>
		<div>Ask the Internet Archive what do put here. They will tell you what to enter here when you get your collection.</div>
		<div>Again, they would love to hear from you so please contact them.</div>  
		<h2><?php echo __('Output Formats'); ?></h2>
	   <?php echo output_format_list(false, ' · '); ?>
  	
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