<?php	
	//Define function to save information about all minted DOIs
	function saveDOI($handle, $doi, $url, $type, $status)
	{			
		global $doiMinter, $report, $errors;
		
		$recordType = 'unchanged';
		
		//check for existing entry
		$check = select($doiMinter, "SELECT rowID, url, status FROM dois WHERE handle LIKE ? AND doi LIKE ? AND type LIKE ?", array($handle, $doi, $type));
		
		//if not existing			
		if(mysqli_num_rows($check) === 0)
		{								
			$recordType = 'new';
			
			if(!insert($doiMinter, 'dois', array('handle', 'doi', 'url', 'type', 'status'), array($handle, $doi, $url, $type, $status)))
			{
				$error = end($errors);
				$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
			}
		}		
		else
		{
			$row = $check->fetch_assoc();
			$existingRowID = $row['rowID'];
			$existingURL = $row['url'];
			$existingStatus = $row['status'];
			
			//if url has changed, update url value
			if($existingURL !== $url)
			{	
				$recordType = 'modified';				
		
				if(!update($doiMinter, 'dois', array("url"), array($url, $existingRowID), 'rowID'))
				{
					$error = end($errors);
					$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
				}
				else
				{
					$report .= ' - URL changed from '.$existingURL.' to '.$url.PHP_EOL;
				}
			}
			
			//if status has changed, update status value
			if($existingStatus !== $status)
			{	
				$recordType = 'modified';				
		
				if(!update($doiMinter, 'dois', array("status"), array($status, $existingRowID), 'rowID'))
				{
					$error = end($errors);
					$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
				}
				else
				{
					$report .= ' - status changed from '.$existingStatus.' to '.$status.PHP_EOL;
				}
			}
		}
		return $recordType;
	}	
