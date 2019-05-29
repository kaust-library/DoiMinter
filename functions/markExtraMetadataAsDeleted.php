<?php
	//Define function to mark existing entries with place greater than current count as deleted
	function markExtraMetadataAsDeleted($source, $idInSource, $parentRowID, $field, $place, $currentFields)
	{
		global $doiMinter;

		if(!empty($parentRowID)&&empty($field)&&empty($place)&&empty($currentFields))
		{
			//Mark all children of a deleted row as deleted
			$check = $doiMinter->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND deleted IS NULL");

			markMatchedRowsAsDeleted($check, $source, $idInSource);
		}
		elseif(!empty($field)&&is_int($place))
		{
			//mark existing entries with place greater than current count as deleted
			if($parentRowID === NULL)
			{
				$check = $doiMinter->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND field LIKE '$field' AND place > '$place' AND deleted IS NULL");
			}
			else
			{
				$check = $doiMinter->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND field LIKE '$field' AND place > '$place' AND deleted IS NULL");
			}

			markMatchedRowsAsDeleted($check, $source, $idInSource);
		}
		elseif(!empty($currentFields))
		{
			//Mark metadata fields previously but no longer used on the item as deleted
			if(is_null($parentRowID))
			{
				$previousFields = getValues($doiMinter, "SELECT DISTINCT field FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND deleted IS NULL", array('field'), 'arrayOfValues');
			}
			else
			{
				$previousFields = getValues($doiMinter, "SELECT DISTINCT field FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND deleted IS NULL", array('field'), 'arrayOfValues');
			}

			foreach($previousFields as $previousField)
			{
				if(!in_array($previousField, $currentFields))
				{
					if(is_null($parentRowID))
					{
						$check = $doiMinter->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND field LIKE '$previousField' AND deleted IS NULL");
					}
					else
					{
						$check = $doiMinter->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND field LIKE '$previousField' AND deleted IS NULL");
					}

					markMatchedRowsAsDeleted($check, $source, $idInSource);
				}
			}
		}
	}
