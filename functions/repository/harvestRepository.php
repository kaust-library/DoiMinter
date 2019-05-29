<?php
	//Define function to harvest repository metadata via OAI-PMH
	function harvestRepository($source)
	{
		global $doiMinter;
	
		$newInProcess = 0;
	
		$fromDate = '';
		
		$report = '';
		
		$errors = array();
		
		if(!isset($_GET['reharvest']))
		{
			//Modified items only (note: some metadata changes may not be recognized as a modification by oai)
			$fromDate = getValues($doiMinter, "SELECT value FROM metadata WHERE source LIKE '$source' AND field LIKE 'dspace.date.modified' ORDER BY value DESC LIMIT 1", array('value'), 'singleValue');
			
			//increment fromDate by 1 second so that last modified item is not reharvested
			$fromDate = substr_replace($fromDate, substr($fromDate, -3, 2)+1, -3, 2);
			$report .= 'From Date: '.$fromDate.PHP_EOL;
		}
		elseif($_GET['reharvest']!=='yes')
		{
			//From date set manually
			$fromDate = $_GET['reharvest'];
			$report .= 'From Date: '.$fromDate.PHP_EOL;
		}
		
		//Manually set from date 'yyyy-mm-dd'
		//$fromDate = '2019-04-03';	
		
		$token = '';
		
		//Record count variable
		$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'unchanged'=>0,'skipped'=>0);		
		
		if(empty($fromDate))
		{
			$url = REPOSITORY_OAI_URL.'request?verb=ListIdentifiers&metadataPrefix=xoai';						
		}
		else
		{
			$url = REPOSITORY_OAI_URL.'request?verb=ListIdentifiers&metadataPrefix=xoai&from='.$fromDate;
			
			//echo $url.PHP_EOL;
		}
		
		$oai = simplexml_load_file($url);	
		
		if(isset($oai->ListIdentifiers->resumptionToken))
		{
			$total = $oai->ListIdentifiers->resumptionToken['completeListSize'];
		}
		else
		{
			$total = count($oai->ListIdentifiers->header);
		}
		unset($oai);



		while($recordTypeCounts['all']<$total)
		{
			if(!empty($token))
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'request?verb=ListRecords&resumptionToken='.$token.'');
			}
			elseif(empty($fromDate))
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'request?verb=ListRecords&metadataPrefix=xoai');
			}
			elseif(!empty($fromDate))
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'request?verb=ListRecords&metadataPrefix=xoai&from='.$fromDate);
			}
			else
			{
				break;
			}
			
			if(!empty($oai))
			{
				$report .= 'Total: '.$total.PHP_EOL;
				
				if(isset($oai->ListRecords))
				{
					foreach($oai->ListRecords->record as $item)					
					{						
											
						$recordTypeCounts['all']++;
						if($recordTypeCounts['all']===$total+1)
						{
							break 2;
						}
						
						$report .= 'Number:'.$recordTypeCounts['all'].PHP_EOL;
						
						//check if the DOI is already existing
						$idInSource = str_replace(REPOSITORY_OAI_ID_PREFIX, 'http://hdl.handle.net/', $item->header[0]->identifier);
						
						$checkDoi = $doiMinter->query("SELECT doi FROM dois WHERE handle LIKE '$idInSource'");
						
						//check if DOI requested
						$metadata = $item->metadata->metadata->asXML();
						
						$metadata = str_replace('xmlns="http://www.lyncode.com/xoai" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.lyncode.com/xoai http://www.lyncode.com/xsd/xoai.xsd"', '', $metadata);
						
						//$report .= 'XML Output - '.$metadata.PHP_EOL;
						
						$metadata = new SimpleXMLElement($metadata);
						$localRequestDoi = $metadata->xpath("//element/element[@name='request']/element[@name='doi']/element/field[@name='value']");
						
						if(!empty($localRequestDoi))
						{
							$localRequestDoi = (string)$localRequestDoi[0];
							
							$report .= 'Request - '.$localRequestDoi.PHP_EOL;
						}							
						
						if($localRequestDoi === 'yes' || mysqli_num_rows($checkDoi) !== 0)
						{
							//process item
							$recordType = processRepositoryRecord($idInSource, $item, $report);
						}
						else
						{
							$recordType = "skipped";
						}
						
						$report .= ' - '.$recordType.PHP_EOL;

						$recordTypeCounts[$recordType]++;						

						flush();
						set_time_limit(0);
					}
				}
			}
			$token = $oai->ListRecords->resumptionToken;
		}

		$changedCount = $recordTypeCounts['all']-$recordTypeCounts['unchanged']-$recordTypeCounts['skipped'];

		if($changedCount!==0)
		{
			$summary = saveReport($source, $report, $recordTypeCounts, $errors);
		}
		else
		{
			$summary = $source.': no changed items recorded';
		}
		
		return array('changedCount'=>$changedCount,'summary'=>$summary);
	}	
