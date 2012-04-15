<?php

require_once('InternetArchiveFileTable.php');

class InternetArchiveFile extends Omeka_Record
{
    public $item_id;
    public $up_to_date;
    public $bucket_url;
    public $bucket_history_url;
}