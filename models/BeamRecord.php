<?php
/**
 * @package BeamMeUp
 * @subpackage Models
 */

/**
 * Model class for a record.
 *
 * @package BeamMeUp
 * @subpackage Models
 */
class OaipmhHarvesterRecord extends Omeka_Record
{
    public $item_id;
    public $bucket_url;
    public $bucket_history_url;
    public $up_to_date;
}