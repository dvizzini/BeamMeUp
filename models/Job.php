<?php
/**
 *
 */
class OaipmhHarvester_Job extends Omeka_JobAbstract
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
