<?php
	//Define function to set a query statement used for getting values from external source metadata table
	function setSourceMetadataQuery($source, $idInSource, $parentRowID, $field)
	{
		if(is_null($parentRowID))
		{
			$query = "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = '$source' AND `idInSource` = '$idInSource' AND `parentRowID` IS NULL AND `field` = '$field' AND `deleted` IS NULL ORDER BY `place` ASC";
		}
		else
		{
			$query = "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = '$source' AND `idInSource` = '$idInSource' AND `parentRowID` = '$parentRowID' AND `field` = '$field' AND `deleted` IS NULL ORDER BY `place` ASC";
		}
		
		return $query;	
	}
	