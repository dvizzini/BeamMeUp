<?php
define('BEAMMEUP_PLUGIN_VERSION', '0.1');

//TODO: Save URLS

require_once 'CreativeCommonsLicense.php';

// Plugin Hooks
add_plugin_hook('install', 'beam_install');
add_plugin_hook('uninstall', 'beam_uninstall');
add_plugin_hook('config_form', 'beam_config_form');
add_plugin_hook('config', 'beam_config');

add_plugin_hook('after_save_item', 'beam_post_to_ia');
add_filter('admin_items_form_tabs', 'beam_item_form_tabs');

// Hook Functions
function beam_install()
{    
    $db = get_db();	
    $sql = "
    CREATE TABLE IF NOT EXISTS $db->BEAM (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `item_id` BIGINT UNSIGNED NOT NULL ,
    `is_beamed` BOOLEAN NOT NULL ,
    `beam_name` TEXT ,
    `beam_uri` TEXT ,
	`beam_img` TEXT ,
    INDEX (`item_id`)) ENGINE = MYISAM";
    $db->query($sql);
    set_option('beam_plugin_version', BEAMMEUP_PLUGIN_VERSION);    
}

function beam_uninstall()
{
	delete_option('BEAM_PLUGIN_VERSION');

	$db = get_db();
	$db -> query("DROP TABLE IF EXISTS $db->BEAM");
}

function beam_config_form()
{
	include 'config_form.php';
}

function beam_config()
{
	set_option('access_key', $_POST['AWSAccessKeyId']);
	set_option('secret_key', $_POST['SecretKey']);
	set_option('bucket_prefix', $_POST['BucketPrefix']);
	set_option('post_to_internet_archive_bool', $_POST['PostToInternetArchiveBool']);
}

#@TODO: Use admin_append_to_items_form_files in views
#@TODO: Check OAIPMH harverster plugin for code that loads status to db , see indexcontroller.php #jobdispatcher to get onto other thread 
#@TODO: Look at paths.php for better way to get h
#@TODO: Look at display function to iterate through metadata queries
#@TODO: Look at specify metadata

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
 * Each time we save an item, check the POST to see if we are also saving a 
 * license
 * @return void
 **/    
function beam_post_to_ia($item)
{
	
	//@TODO: Get progress bar (only supported in php 5.3)
	
	/*
	//@TODO: deal with jQuery bool
	if(isset($_POST['postToInternetArchiveBool']) && $_POST['postToInternetArchiveBool'] == 'Yes') {
		confirmUpload();
	}
	*/
	
	echo get_option('post_to_internet_archive_bool');
	
	if(get_option('post_to_internet_archive_bool') == 'Yes') {
		
		//add a url to the handler, $filetype = 0 for file, 1 for metadata
		function addHandle(&$curlHandle,$fileToBePut,$first,$fileType)
		{
			//TODO: Add meta data
			//$itemMetaData =  show_item_metadata($options = array('show_empty_elements' => TRUE, ), $item = $item);
			//print_r($itemMetaData);
			
			set_current_file($fileToBePut);
			$cURL = curl_init();
	
			echo item_file('archive filename');
			echo item_file('original filename');
			
			//TODO: Test with identically named files
			
			if ($first) {
				echo 'in true';
				print_r(array('x-amz-auto-make-bucket:1','authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('x-amz-auto-make-bucket:1','authorization: LOW '.get_option('access_key').':'.get_option('secret_key')/*,'Content-type: '.item_file('MIME Type')*/));
			} else {
				echo 'in false';
				print_r(array('authorization: LOW '.get_option('access_key').':'.get_option('secret_key')));
				curl_setopt($cURL, CURLOPT_HTTPHEADER, array('authorization: LOW '.get_option('access_key').':'.get_option('secret_key')/*,'Content-type: '.item_file('MIME Type')*/));					
			}
	
			curl_setopt($cURL, CURLOPT_URL, 'http://s3.us.archive.org/'.getBucketName().'/'.item_file('original filename'));
			curl_setopt($cURL, CURLOPT_HEADER, 1);
	        curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, 30);
	        curl_setopt($cURL, CURLOPT_LOW_SPEED_LIMIT, 1);
	        curl_setopt($cURL, CURLOPT_LOW_SPEED_TIME, 180);
	        curl_setopt($cURL, CURLOPT_NOSIGNAL, 1);
			curl_setopt($cURL, CURLOPT_PUT, 1);
	
			//cwd is /home/kitchensistsrs/kswebsite/archive/admin
			
			// open this directory
			echo './../archive/files/'.item_file('archive filename');
			curl_setopt($cURL, CURLOPT_INFILE,  fopen('./../archive/files/'.item_file('archive filename'),'r'));
			echo item_file('Size');
			curl_setopt($cURL, CURLOPT_INFILESIZE, item_file('Size'));
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
	
			//file_download_uri($whatever);
			
			curl_multi_add_handle($curlHandle,$cURL);
			return $cURL;
			
		}
		
		//execute the handle until the flag passed
		// to function is greater then 0
		function ExecHandle(&$curlHandle)
		{
			$flag=null;
			do {
			echo $flag;
			//fetch pages in parallel
			curl_multi_exec($curlHandle,$flag);
			} while ($flag > 0);			
			
		}

		//set function-level variables
		set_current_item($item);
		$curlHandle = curl_multi_init();

		$i = 0;
		while(loop_files_for_item())
		{
			if ($i == 0){
				$curl[$i] = addHandle($curlHandle,get_current_file(),TRUE);			
			} else {
				$curl[$i] = addHandle($curlHandle,get_current_file(),FALSE);			
			}
			$i++;
		}
		
		//metadata
		//see http://stackoverflow.com/questions/3958226/using-put-method-with-php-curl-library
		foreach ($actionContexts as $key => $actionContext) {
            $query = $_GET;
            $query['output'] = $actionContext;
            $html .= '<a href="' . html_escape(uri() . '?' . http_build_query($query)) . '">' . $actionContext . '</a>';
			if ($i == 0){
				$curl[$i] = addHandle($curlHandle,get_current_file(),TRUE);			
			} else {
				$curl[$i] = addHandle($curlHandle,get_current_file(),FALSE);			
			}
			$i++;
        }
		
		
		ExecHandle($curlHandle);
		
		echo 'point A';
		
		echo count($curl);
		
		/*
		$successful = TRUE;		
	
		for ($i = 0;$i < count($curl); $i++)
		{
			print_r(curl_getinfo($curl[i]));
			if(curl_getinfo($curl[i], CURLINFO_HTTP_CODE) == 200 )
			{
				echo 'we cool';
			}
			if(curl_getinfo($curl[i], CURLINFO_HTTP_CODE) != 200 )
			{
				$successful = FALSE;
			}
		}
		 */

		echo 'point B';
		
		for ($i = 0;$i < count($curl); $i++)//remove the handles
		{
			curl_multi_remove_handle($curlHandle,$curl[$i]);
		}
		
		curl_multi_close($curlHandle);
		
		echo 'point C';

		/*
		<script type="text/javascript">
			alert("You can view the your Internet Archive bucket at http://archive.org/details/"<?php echo get_option('bucket_prefix').'_'.item('id') ?>);
		</script>
		*/			
	}
		
}

function beam_form($item) {
		
    $ht = '';
    ob_start();
		
	if (get_option('post_to_internet_archive_bool')) {
		
		?>
		
		<h3>You have turned on the option to upload files to the internet archive. After you save this item, you can view them at <b>http://archive.org/details/<?php echo getBucketName() ?></b></h3>
		<h3>To turn off the file upload or to alter the the upload's configurations, visit the plugin's configuration settings on this site.</h3>
		
		<?php			
		
	} else {
		
		?>
		
		<h3>You have turned off the option to upload files to the internet archive.</h3>
		<h3>To turn on the file upload or to alter the the upload's configurations, visit the plugin's configuration settings on this site.</h3>

		<?php
		
	}
	
    $ht .= ob_get_contents();
    ob_end_clean();

    return $ht;
	
}

//helpers
//return bucket name
function getBucketName()
{
	return get_option('bucket_prefix').'_'.item('id');
}