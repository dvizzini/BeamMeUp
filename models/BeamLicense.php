<?php
require_once 'CreativeCommonsLicenseTable.php';

class CC extends Omeka_Record
{
	public $is_cc = false;
	public $cc_uri;
	public $cc_name;
	public $cc_img;
	public $item_id;

	// public $allow_emixing = true;
	// public $allow_commercial = true;
	// public $enforce_sharealike = false;
	// 
	// public $jurisdiction = ;
	
	
	protected function _validate()
	{
		if($is_cc == true)
		{
			if( empty($this->cc_uri) && empty($this->cc_name) )
			{
				$this->addError('cc', 'Creative Commons License ');
			}
		}
	}
	
}