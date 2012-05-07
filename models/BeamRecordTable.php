<?php
/**
 * @package BeamMeUp
 * @subpackage Models
 */

/**
 * Model class for a record table.
 *
 * @package BeamMeUp
 * @subpackage Models
 */
class OaipmhHarvesterRecordTable extends Omeka_Db_Table
{
    
    /**
     * Return records by item ID.
     * 
     * @param mixes $itemId Item ID
     * @return OaipmhHarvesterRecord Record corresponding to item id.
     */
    public function findByItemId($itemId)
    {
        $select = $this->getSelect();
        $select->where('item_id = ?');
        return $this->fetchObject($select, array($itemId));
    }
}