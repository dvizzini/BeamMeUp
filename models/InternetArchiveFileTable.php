<?php

class InternetArchiveFileTable extends Omeka_Db_Table
{
	public function findAllFilesOrderedByItemID()
	{
        $select = $this->getSelect()->order('item_id');
        return $this->fetchObjects($select);
	}
}