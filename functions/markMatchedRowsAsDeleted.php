<?php
	//Define function to mark matched rows as deleted
	function markMatchedRowsAsDeleted($check, $source, $idInSource)
	{
		global $doiMinter;

		//if matched
		if(mysqli_num_rows($check) !== 0)
		{
			while($row = $check->fetch_assoc())
			{
				$rowID = $row['rowID'];
				update($doiMinter, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');

				//Recursively mark any children of this row as deleted as well
				markExtraMetadataAsDeleted($source, $idInSource, $rowID, '', '', '');
			}
		}
	}
