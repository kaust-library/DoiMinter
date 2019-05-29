<?php
	ini_set('display_errors', 1);
	
	set_include_path('/var/www/doiMinter');
	
	//include core configuration and common function files
	include_once 'include.php';		
	
	//Create harvest summary to send
	$harvestSummary = '';
	
	$newInProcess = 0;
	$totalChanged = 0;
	
	if(isset($_GET['source']))
	{
		$sources = array($_GET["source"]);
	}
	
	if(isset($_GET['reprocess']))
	{
		if($_GET['reprocess'] === 'yes')
		{
			foreach($sources as $source)
			{
				$harvestSummary .= ' - '.$source.' reprocessed';
				
				if(isset($_GET['idInSource']))
				{
					$idInSource = $_GET['idInSource'];
					
					$harvestSummary .= ' - '.$idInSource.' reprocessed';
					
					$result = $doiMinter->query("SELECT `rowID` FROM `sourceData` WHERE `source` LIKE '$source' AND `idInSource` LIKE '$idInSource' AND `deleted` IS NULL");
				}
				else
				{				
					$result = $doiMinter->query("SELECT `rowID` FROM `sourceData` WHERE `source` LIKE '$source' AND `deleted` IS NULL");
				}
				
				if($result->num_rows!==0)
				{		
					while($row = $result->fetch_assoc())
					{
						set_time_limit(0);
						
						$rowID = $row['rowID'];
						
						$sourceDataResult = $doiMinter->query("SELECT `idInSource`, `sourceData`, `format` FROM `sourceData` WHERE `rowID` = '$rowID'");
						
						if($sourceDataResult->num_rows!==0)
						{		
							while($sourceDataRow = $sourceDataResult->fetch_assoc())
							{						
								$idInSource = $sourceDataRow['idInSource'];
								
								$sourceData = $sourceDataRow['sourceData'];
								
								$format = $sourceDataRow['format'];
								
								if($format === 'JSON')
								{
									$sourceData = json_decode($sourceData, TRUE);
								}
								elseif($format === 'XML')
								{
									$sourceData = simplexml_load_string($sourceData);
								}
								
								$recordType = call_user_func('process'.(ucfirst($source)).'Record', $idInSource, $sourceData, '');
							}
						}
					}
				}
			}
		}
	}
	else
	{
		foreach($sources as $source)
		{	
			set_time_limit(0);
			
			$results = call_user_func('harvest'.(ucfirst($source)), $source);
			
			$totalChanged += $results['changedCount'];
			
			$harvestSummary .= PHP_EOL.$results['summary'];
		}
	}

	if($totalChanged !== 0)
	{
		//Settings for harvest report email
		$to = IR_EMAIL;
		$subject = "Results of Publications Harvest";
		
		//Complete harvest message to send
		$harvestSummary = 'Harvest Report'.PHP_EOL.' - New items needing review: '.$newInProcess.PHP_EOL.' - Total changed records: '.$totalChanged.PHP_EOL.$harvestSummary;

		$headers = "From: " .IR_EMAIL. "\r\n";

		//Send
		mail($to,$subject,$harvestSummary,$headers);
	}
?>