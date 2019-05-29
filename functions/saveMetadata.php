<?php
	//Define function to save extracted metadata values
	function saveMetadata($source, $idInSource, $element, &$field, $parentField, $place, $value, $parentRowID)
	{
		global $doiMinter;

		//empty row id to return if conditions not met
		$rowID = NULL;

		if(is_string($value))
		{
			$value = trim($value);
		}

		$existingValue = '';
		$existingRow = '';

		//check for existing entry
		if($parentRowID === NULL)
		{
			$check = select($doiMinter, "SELECT rowID, value FROM metadata WHERE source LIKE ? AND idInSource LIKE ? AND parentRowID IS NULL AND field LIKE ? AND place LIKE ? AND deleted IS NULL", array($source, $idInSource, $field, $place));
		}
		else
		{
			$check = select($doiMinter, "SELECT rowID, value FROM metadata WHERE source LIKE ? AND idInSource LIKE ? AND parentRowID LIKE ? AND field LIKE ? AND place LIKE ? AND deleted IS NULL", array($source, $idInSource, $parentRowID, $field, $place));
		}

		//if not existing
		if(mysqli_num_rows($check) === 0)
		{
			insert($doiMinter, 'metadata', array('source', 'idInSource', 'parentRowID', 'field', 'place', 'value'), array($source, $idInSource, $parentRowID, $field, $place, $value));
			$rowID = $doiMinter->insert_id;
		}
		else
		{
			$row = $check->fetch_assoc();
			$existingValue = $row['value'];
			$existingRowID = $row['rowID'];

			//insert if value changed
			if($existingValue !== $value)
			{
				insert($doiMinter, 'metadata', array('source', 'idInSource', 'parentRowID', 'field', 'place', 'value'), array($source, $idInSource, $parentRowID, $field, $place, $value));
				$newRowID = $doiMinter->insert_id;

				update($doiMinter, 'metadata', array("deleted", "replacedByRowID"), array(date("Y-m-d H:i:s"), $newRowID, $existingRowID), 'rowID');

				//mark any children of the existing row as deleted as well
				markExtraMetadataAsDeleted($source, $idInSource, $existingRowID, '', '', '');

				$rowID = $newRowID;
			}
			else
			{
				//in this case the row ID may be needed as the parentRowID for a child element whose value has changed
				$rowID = $existingRowID;
			}
		}
		return $rowID;
	}
