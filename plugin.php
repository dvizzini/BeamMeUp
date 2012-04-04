<?php
define('BEAMMEUP_PLUGIN_VERSION', '0.1');

#@TODO: Add MVC implementation
#@TODO: Check out array-to-XML parsers
#@TODO: Check OAIPMH harverster plugin for code that loads status to db , see indexcontroller.php #jobdispatcher to get onto other thread 
#@TODO: Look at paths.php for better way to get file path
#@TODO: make jQuery in config_form.php work 
#@TODO: bind jQuery to "Add Item" and "Save Changes" buttons to confirm upload 
	
// Plugin Hooks
add_plugin_hook('install', 'beam_install');
add_plugin_hook('config_form', 'beam_config_form');
add_plugin_hook('config', 'beam_config');
add_plugin_hook('admin_append_to_items_form_files', 'beam_admin_append_to_items_form_files');
add_plugin_hook('after_save_item', 'beam_post_to_ia');
add_plugin_hook('admin_append_to_items_show_secondary', 'beam_admin_append_to_items_show_secondary');
add_filter('admin_items_form_tabs', 'beam_item_form_tabs');

//runs single-thread and throws uncaught exception so echo and print_r statements are seen 
$DEBUG = TRUE;

// Hook Functions

/**
 * Displays Internet Archive links in admin/show section 
 * @return void
 **/    
function beam_admin_append_to_items_show_secondary() {         
	echo '<div class="info-panel">';
    echo '<h2>BeamMeUp</h2>';
	echo listInternetArchiveLinks();
	echo '</div>';
 
}

/**
 * Gives user the option to post to the Internet Archive 
 * @return void
 **/    
function beam_admin_append_to_items_form_files() {
	?>
	<span><b>Upload to Internet Archive</b></span>
	<input type="checkbox" name="PostToInternetArchiveBool" value="Yes" <?php if(get_option('post_to_internet_archive_default_bool') == 'Yes') {echo 'checked';} ?>/>
	<div><em>Note that if this box is checked, saving the item may take a while.</em></div>
	<div><em>Files must be uniquely named to post to the archive.</em></div>
	<?php
}

/**
 * Sets configuartion options to default 
 * @return void
 **/    
function beam_install()
{
	set_option('post_to_internet_archive_default_bool', 'Yes');
	set_option('access_key', 'Enter&nbsp;S3&nbsp;access&nbsp;key&nbsp;here.');
	set_option('secret_key', 'Enter&nbsp;S3&nbsp;secret&nbsp;key&nbsp;here.');

	$bucketPrefix = str_replace('.','_',preg_replace('/www/', '', $_SERVER["SERVER_NAME"],1));
	$bucketPrefix = 'omeka'.((strpos($bucketPrefix,'_') == 0) ? '' : '_').$bucketPrefix;
	set_option('bucket_prefix', $bucketPrefix);
}

/**
 * Displays configuration form 
 * @return void
 **/    
function beam_config_form()
{
	include 'config_form.php';
}

/**
 * Configures based on inputs in config_form.php 
 * @return void
 **/    
function beam_config()
{
	set_option('post_to_internet_archive_default_bool', $_POST['PostToInternetArchiveDefaultBool']);
	set_option('access_key', $_POST['AWSAccessKeyId']);
	set_option('secret_key', $_POST['SecretKey']);
	set_option('bucket_prefix', $_POST['BucketPrefix']);
}

/**
 * Add BeamMeUp tab to the edit item page
 * @return array
 **/
function beam_item_form_tabs($tabs)
{
    // insert the map tab before the Miscellaneous tab
    $item = get_current_item();
    $ttabs = array();
    foreach($tabs as $key => $html) {
        if ($key == 'Miscellaneous') {
            $ht = '';
            $ht .= beam_form($item);
            $ttabs['BeamMeUp'] = $ht;
        }
        $ttabs[$key] = $html;
    }
    $tabs = $ttabs;
    return $tabs;
}

/**
 * Post Files and metadata of an Omeka Item to the Internet Archive 
 * @return void
 **/    
function beam_post_to_ia($item)
{
	
	if($_POST["PostToInternetArchiveBool"] == 'Yes') {
		
		/**
		 * @param $first true if this is the first PUT to the bucket, false otherwise 
		 * @return A cURL object with options set that are common to all calls
		 */
		function getInitializedCurlObject($first)
		{
			
			$cURL = curl_init();
				
			if ($first) {
				echo 'in true';
				print_r(array('x-amz-auto-make-bucket:1','authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('x-amz-auto-make-bucket:1','authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
			} else {
				echo 'in false';
				print_r(array('authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));					
			}
	
			curl_setopt($cURL, CURLOPT_HEADER, 1);
	        curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, 30);
	        curl_setopt($cURL, CURLOPT_LOW_SPEED_LIMIT, 1);
	        curl_setopt($cURL, CURLOPT_LOW_SPEED_TIME, 180);
	        curl_setopt($cURL, CURLOPT_NOSIGNAL, 1);
			curl_setopt($cURL, CURLOPT_PUT, 1);
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);				
			
			return $cURL;
			
		}

		/**
		 * @param $first true if this is the first PUT to the bucket, false otherwise 
		 * @return A cURL object with parameters set to upload metadata
		 */		 
		function getMetadataCurlObject($first)
		{

			$cURL = getInitializedCurlObject($first);
			
			$body = show_item_metadata($options = array('show_empty_elements' => TRUE, ), $item = $item);
			
			echo strlen($body);
			echo $body;
			
			/** use a max of 256KB of RAM before going to disk */
			$fp = fopen('php://temp/maxmemory:256000', 'w');
			if (!$fp) {
			    die('could not open temp memory data');
			}
			fwrite($fp, $body);
			fseek($fp, 0);
			
			curl_setopt($cURL, CURLOPT_URL, 'http://s3.us.archive.org/'.getBucketName().'/metadata.html');
			curl_setopt($cURL, CURLOPT_BINARYTRANSFER, TRUE);
			curl_setopt($cURL, CURLOPT_INFILE, $fp); // file pointer
			curl_setopt($cURL, CURLOPT_INFILESIZE, strlen($body)); 

			curl_multi_add_handle($curlMultiHandle,$cURL);
			return $cURL;
			
		}
		
		/**
		 * Adds handle for Omeka File to cURL multi object 
		 * @param $first true if this is the first PUT to the bucket, false otherwise 
		 * @return A cURL object with parameters set to upload a file
		 */		 
		function getFileCurlObject(File $fileToBePut, $first)
		{

			$cURL = getInitializedCurlObject($first);

			// open this directory
			set_current_file($fileToBePut);

			echo './../archive/files/'.item_file('archive filename');

			curl_setopt($cURL, CURLOPT_URL, 'http://s3.us.archive.org/'.getBucketName().'/'.str_replace(' ','_',item_file('original filename')));
			curl_setopt($cURL, CURLOPT_INFILE,  fopen('./../archive/files/'.item_file('archive filename'),'r'));
			echo item_file('Size');
			curl_setopt($cURL, CURLOPT_INFILESIZE, item_file('Size'));
	
			curl_multi_add_handle($curlMultiHandle,$cURL);			
			return $cURL;
			
		}
		
		/**
		 * Adds handle for Omeka metadata to cURL multi object 
		 * @param $curlMultiHandle pointer to multi cURL multi handle that will be added to
		 * @param $cURL single cURL handle to add 
		 * @return void
		 **/    		
		function addHandle(&$curlMultiHandle,$cURL)
		{

			curl_multi_add_handle($curlMultiHandle,$cURL);
			return $cURL;

		}
				
		/**
		 * Executes PUT method to upload Omeka metadata 
		 * @param $successful pointer to success flag. Will be set to FALSE if HTTP code is not 200
		 * @param $cURL single cURL handle to execute 
		 * @return void
		 **/    		
		function execHandle(&$successful,$cURL)
		{
			
			$cURL = getMetadataCurlObject(FALSE);
			
			curl_exec($cURL);
			
			if (curl_getinfo($cURL,CURLINFO_HTTP_CODE) != 200)
			{
				$successful = FALSE;
			}

			echo 'HTTP Code: '.curl_getinfo($cURL,CURLINFO_HTTP_CODE);

		}

		/**
		 * Executes the cURL multi handle until there are no outstanding jobs 
		 * @return void
		 **/    		
		function ExecHandle(&$curlMultiHandle)
		{
			$flag=null;
			do {
			echo $flag;
			$flagLast = $flag;
			//fetch pages in parallel
			curl_multi_exec($curlMultiHandle,$flag);
			if ($flagLast != $flag)
			{
				echo curl_getinfo($curlMultiHandle);
			}
			} while ($flag > 0);			
			
		}

		//set function-level variables
		set_current_item($item);
		
		if ($DEBUG)
		{
		    $actionContexts = current_action_contexts();//for metadata
		    echo '$actionContents: ';
		    print_r($actionContexts);
					
			$successful = TRUE;//innocent until proven guilty

			execMetadataHandle($successful);

			while(loop_files_for_item())
			{
				execFileHandle($successful,get_current_file());			
			}

			echo 'Very important: ';
			echo (($successful) ? 'success' : 'failure');			

			//throws uncaught error for debugging
			file_download_uri($whatever);

		}
		else
		{

			$curlMultiHandle = curl_multi_init();
			
			$curl[0] = execMetadataHandle($successful);
			
			$i = 1;
			while(loop_files_for_item())
			{
				$curl[$i] = execFileHandle($successful,get_current_file());			
			}		
					
			ExecHandle($curlMultiHandle);
			
			for ($i = 0;$i < count($curl); $i++)//remove the handles
			{
				curl_multi_remove_handle($curlMultiHandle,$curl[$i]);
			}
			
			curl_multi_close($curlMultiHandle);
			
		}
				
	}
		
}

/**
 * Each time we save an item, post to the Internet Archive 
 * @return void
 **/    
function beam_form($item) {
		
    $ht = '';
    ob_start();
					
	?> 
	
	<div>If the box at the bottom of the files tab is checked, the files in this item, along with their metadata, will upload to the Internet Archive upon save.</div>
	</br>
	<div>Note that BeamMeUp may make saving an item take a while, and that it may take additional time for the Internet Archive to post the files after you save.</div>
	</br>
	<div>To change the upload defaultor to alter the the upload's configurations, visit the plugin's configuration settings on this site.</div>
	</br>
	
	<?php
	
	if (item('id') == '')
	{
		echo "
		<div>Please revisit this tab after you save the item to view its Internet Archive links.</div>
		";
	} 
	else
	{
		echo listInternetArchiveLinks();
	}
				
	
    $ht .= ob_get_contents();
    ob_end_clean();

    return $ht;
	
}

//helpers
/**
 * @return bucket name for Omeka Item
 */
function getBucketName()
{
	return get_option('bucket_prefix').'_'.item('id');
}

/**
 * @return string containing IA links
 */
function listInternetArchiveLinks()
{
	return "
		<div>If you uploaded the files and the Internet Archive has fully processed them, you can view them <b><a href=http://archive.org/details/".getBucketName()." target=\"_blank\">here</a></b>.</div>
		</br>
		<div>You can view the upload's Internet Archive history and progress <b><a href=http://archive.org/catalog.php?history=1&identifier=".getBucketName()." target=\"_blank\">here</a></b>.</div>
		</br>
	";
}