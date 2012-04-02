<div class="field">

	<style type="text/css" >
	/* Import Styles for the CC License Chooser */
		<?php include('cc.css') ?>
	</style>
	<div class="inputs">
		
		<h3>Please visit <a href="http://www.archive.org/account/s3.php" target="_blank">The Internet Archive's S3 Page</a> to generate the keys below.</h3>
		<h3>Be sure to log in with the account used for your archives.</h3>
		<br/>
		<div><b>Upload to Internet Archive</b></div>
		<input type="checkbox" name="PostToInternetArchiveBool" value="Yes" checked/>
		<br/>
		<br/>
		<div><b>S3 access key</b></div>
		<input type="text" name="AWSAccessKeyId" value="enter S3 access key here">
		<br/>
		<br/>
		<div><b>S3 secret key</b></div>
    	<input type="text" name="SecretKey" value="enter S3 secret key here">    	
		<br/>
		<br/>
		<div><b>Bucket Prefix</b></div>
		<div>Should be all lowercase and seperated by underscores, with no trailing underscore, e.g. omeka_yourwebsitename</div>
    	<input type="text" name="BucketPrefix" value="enter S3 secret key here">    	
    	
	</div>
</div>
