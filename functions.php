<?php

function beam_admin_theme_header() {}

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
	<input type="hidden" name="PostToInternetArchiveBool" value="0">
	<input type="checkbox" name="PostToInternetArchiveBool" value="1" <?php if(get_option('post_to_internet_archive_default_bool') == '1') {echo 'checked';} ?>/>
	<div><em>Note that if this box is checked, saving the item may take a while.</em></div>
	<div><em>Files must be uniquely named to post to the archive.</em></div>
	</br>
	<span><b>Index at Internet Archive</b></span>
	<input type="hidden" name="IndexAtInternetArchiveBool" value="0">
	<input type="checkbox" name="IndexAtInternetArchiveBool" value="1" <?php if(get_option('index_at_internet_archive_default_bool') == '1') {echo 'checked';} ?>/>
	<div><em>If you index your item, it will appear on the results of search engines such as Google's.</em></div>
	</br>
	</br>

	<?php
}

/**
 * Sets configuration options to default 
 * @return void
 **/    
function beam_install()
{
	
	set_option('post_to_internet_archive_default_bool', '1');
	set_option('index_at_internet_archive_default_bool', '1');
	set_option('access_key', 'Enter&nbsp;S3&nbsp;access&nbsp;key&nbsp;here.');
	set_option('secret_key', 'Enter&nbsp;S3&nbsp;secret&nbsp;key&nbsp;here.');
	set_option('collection_name', 'Please&nbsp;contact&nbsp;the&nbsp;Internet&nbsp;Archive.');
	set_option('media_type', 'Please&nbsp;contact&nbsp;the&nbsp;Internet&nbsp;Archive.');
	
	$bucketPrefix = str_replace('.','_',preg_replace('/www/', '', $_SERVER["SERVER_NAME"],1));
	$bucketPrefix = 'omeka'.((strpos($bucketPrefix,'_') == 0) ? '' : '_').$bucketPrefix;
	set_option('bucket_prefix', $bucketPrefix);

    $db = get_db();
    $sql = "
    CREATE TABLE IF NOT EXISTS `$db->InternetArchiveFile` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	    `item_id` BIGINT UNSIGNED NOT NULL,
	    `up_to_date` BOOLEAN NOT NULL,
	    `bucket_url` TEXT,
	    `bucket_history_url` TEXT,
	    INDEX (`item_id`)
   ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    $db->query($sql);
}

/**
 * Deletes persistent variables 
 * @return void
 **/    
function beam_uninstall()
{
	
	delete_option('post_to_internet_archive_default_bool');
	delete_option('index_at_internet_archive_default_bool');
	delete_option('collection_name');
	delete_option('media_type');
	delete_option('access_key');
	delete_option('secret_key');
	delete_option('bucket_prefix');
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
	set_option('index_at_internet_archive_default_bool', $_POST['IndexAtInternetArchiveDefaultBool']);
	set_option('collection_name', $_POST['CollectionName']);
	set_option('media_type', $_POST['MediaType']);
	set_option('access_key', $_POST['AWSAccessKeyId']);
	set_option('secret_key', $_POST['SecretKey']);
}

/**
 * Add BeamMeUp tab to the edit item page
 * @return array
 **/
function beam_admin_item_form_tabs($tabs)
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
function beam_after_save_item($item)
{
	//runs single-thread and throws uncaught exception so echo and print_r statements are seen 
	$DEBUG = FALSE;

	if($_POST["PostToInternetArchiveBool"] == '1') {
				
		function getInitializedCurlObject($first,$title)
		{
			
			$cURL = curl_init();
	
			curl_setopt($cURL, CURLOPT_HEADER, 1);
		    curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, 30);
		    curl_setopt($cURL, CURLOPT_LOW_SPEED_LIMIT, 1);
		    curl_setopt($cURL, CURLOPT_LOW_SPEED_TIME, 180);
		    curl_setopt($cURL, CURLOPT_NOSIGNAL, 1);
			curl_setopt($cURL, CURLOPT_PUT, 1);
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);				
	
			//note that curl_setopt does not seem to work with predefined arrays, which is a real deterent to good code
			if ($first) {
				curl_setopt($cURL, CURLOPT_HTTPHEADER, 
					array('x-amz-auto-make-bucket:1',
						//TODO: which works?
						'x-archive-metadata-collection:'.get_option('collection_name'),
						'x-archive-meta-collection:'.get_option('collection_name'),
						'x-archive-meta-mediatype:'.get_option('media_type'),
						'x-archive-meta-title:'.$title,
						'x-archive-meta-noindex:'.(($_POST["IndexAtInternetArchiveBool"] == '1') ? '0' : '1'),
						'x-archive-meta-creator:'.preg_replace('/www/', '', $_SERVER["SERVER_NAME"],1),
						'authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
			} else {
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
						//TODO: which works?
						'x-archive-metadata-collection:'.get_option('collection_name'),
						'x-archive-meta-collection:'.get_option('collection_name'),
						'x-archive-meta-mediatype:'.get_option('media_type'),
						'x-archive-meta-title:'.$title,
						'x-archive-meta-noindex:'.(($_POST["IndexAtInternetArchiveBool"] == '1') ? '0' : '1'),
						'x-archive-meta-creator:'.preg_replace('/www/', '', $_SERVER["SERVER_NAME"],1),
						'authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
			}
			
			return $cURL;
		}

		/**
		 * @param $first true if this is the first PUT to the bucket, false otherwise 
		 * @return A cURL object with parameters set to upload metadata
		 */		 
		function getMetadataCurlObject($first)
		{
			$cURL = getInitializedCurlObject($first,'Item Metadata');
			$body = show_item_metadata($options = array('show_empty_elements' => TRUE), $item = $item);
								
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

			return $cURL;
		}
		
		/**
		 * @param $first true if this is the first PUT to the bucket, false otherwise 
		 * @param $fileToBePut the Omeka file to by uploaded to the Internet Archive 
		 * @return A cURL object with parameters set to upload an Omeka File
		 */		 
		function getFileCurlObject($first, File $fileToBePut)
		{

			$cURL = getInitializedCurlObject($first,item_file('original filename'));

			// open this directory
			set_current_file($fileToBePut);
			echo "Julia's Lullaby";
			echo preg_replace('/&#\d+;/','_',htmlspecialchars(preg_replace('/\s/','_',"Julia's Lullaby"),ENT_QUOTES));
			echo item_file('original filename');
			echo preg_replace('/&#\d+;/','_',htmlspecialchars(preg_replace('/\s/','_',item_file('original filename')),ENT_QUOTES));
			
			//TODO Test with hyphen, apostraphe
			curl_setopt($cURL, CURLOPT_URL, 'http://s3.us.archive.org/'.getBucketName().'/'.preg_replace('/&#\d+;/','_',htmlspecialchars(preg_replace('/\s/','_',item_file('original filename')),ENT_QUOTES)));
			curl_setopt($cURL, CURLOPT_INFILE,  fopen(FILES_DIR.item_file('archive filename'),'r'));
			curl_setopt($cURL, CURLOPT_INFILESIZE, item_file('Size'));

			return $cURL;
		}
		
		/**
		 * Adds handle for to cURL multi object 
		 * @param $curlMultiHandle pointer to multi cURL multi handle that will be added to
		 * @param $cURL single cURL handle to add 
		 * @return $curl the object for curl_multi_remove_handle
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
		function execSingleHandle(&$successful,$cURL)
		{
			curl_exec($cURL);
			
			if (curl_getinfo($cURL,CURLINFO_HTTP_CODE) != 200)
			{
				$successful = FALSE;
			}

			print_r(curl_getinfo($cURL));
		}

		/**
		 * Executes the cURL multi handle until there are no outstanding jobs 
		 * @return void
		 **/    		
		function execMultiHandle(&$curlMultiHandle)
		{
			$flag=null;
			do {
			//fetch pages in parallel
			curl_multi_exec($curlMultiHandle,$flag);
			} while ($flag > 0);			
		}

		//set item
		set_current_item($item);
		
		if ($DEBUG)
		{
					
			$successful = TRUE;//innocent until proven guilty

			execSingleHandle($successful, getMetadataCurlObject(TRUE));
			
			//bucket must exist before subsequent requests are made
			while(preg_replace('/\s/','', file_get_contents('http://archive.org/metadata/'.getBucketName())) == '{}')
			{
				usleep ( 1000 );
			}

			while(loop_files_for_item())
			{
				execSingleHandle($successful, getFileCurlObject(FALSE,get_current_file()));
			}

			echo 'Very important: ';
			echo (($successful) ? 'success' : 'failure');			

			//throws uncaught error for debugging
			file_download_uri($whatever);
		}
		else
		{
			
	        $jobDispatcher = Zend_Registry::get('job_dispatcher');
	        $jobDispatcher->setQueueName('uploads');
	        $jobDispatcher->send('Upload_Job', array());
       
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

class Upload_Job extends Omeka_JobAbstract
{
    private $_memoryLimit;
    private $_harvestId;

    public function perform()
    {
		
		curl_exec(getMetadataCurlObject(TRUE));
		
		while(preg_replace('/\s/','', file_get_contents('http://archive.org/metadata/'.getBucketName())) == '{}')
		{
			usleep ( 1000 );
		}

		//now that bucket has been created, run multi-threaded cURL
		$curlMultiHandle = curl_multi_init();
		
		$i = 0;
		while(loop_files_for_item())
		{
			$curl[$i] = addHandle($curlMultiHandle,getFileCurlObject(TRUE,get_current_file()));
		}		
				
		execMultiHandle($curlMultiHandle);
		
		for ($i = 0;$i < count($curl); $i++)//remove the handles
		{
			curl_multi_remove_handle($curlMultiHandle,$curl[$i]);
		}
		
		curl_multi_close($curlMultiHandle);
	
    }

    public function setHarvestId($id)
    {
        $this->_harvestId = $id;
    }
    
}