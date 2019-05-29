<?php
	$task = 'registerDOIs';
	
	//Record count variable
	$counts = array('all'=>0,'new'=>0,'modified'=>0,'unchanged'=>0,'new source data'=>0,'modified source data'=>0,'unchanged source data'=>0);
	
	set_include_path('/var/www/doiMinter');
	
	//include core configuration and common function files
	include_once 'include.php';
	
	$token = loginToDSpaceRESTAPI();
	
	$report = '';
	$headers = '';
		
	$errors = array();

	if(isset($_GET['flag']))
	{
		$flag = $_GET['flag'];
		$report .= 'Flag Set: '.$flag.PHP_EOL;
	}
	else
	{
		$flag = '';
	}
	
	if(isset($_GET['handle']))
	{
		$handle = $_GET['handle'];
		$report .= 'Handle Set: '.$handle.PHP_EOL;
	}
	else
	{
		$handle = '';
	}		
	
	if($flag === 'batch')
	{
		$tmp = $doiMinter->query("SELECT DISTINCT idInSource AS handle FROM metadata LEFT JOIN dois d ON idInSource = d.handle WHERE source = 'repository' AND field = 'dc.type' AND (value = 'Thesis' OR value = 'Dissertation') AND d.handle IS NULL");
		
		//Do limited run of batch before full batch to check for errors
		//$tmp = $irts->query("SELECT DISTINCT idInSource AS handle FROM irts.metadata LEFT JOIN doiMinter.dois d ON CONCAT('http://hdl.handle.net/', idInSource) = d.handle WHERE source = 'repository' AND field = 'dc.type' AND (value = 'Thesis' OR value = 'Dissertation') AND d.handle IS NULL LIMIT 0,3");
	} 
	elseif(!empty($handle))
	{
		$tmp = $doiMinter->query("SELECT DISTINCT idInSource AS handle FROM metadata LEFT JOIN dois d ON CONCAT idInSource = d.handle WHERE source = 'repository' AND idInSource = '$handle' AND d.handle IS NULL");
	}		
	else
	{
		$tmp = $doiMinter->query("SELECT DISTINCT idInSource AS handle FROM metadata LEFT JOIN dois d ON idInSource = d.handle WHERE source = 'repository' AND field = 'kaust.request.doi' AND value = 'yes' AND deleted IS NULL AND d.handle IS NULL");
	}
	
	while($row = $tmp->fetch_assoc())
	{
		$doi = NULL;
		
		$handleURL = $row['handle'];
		$handle = substr($handleURL, 22);
		
		$counts['all']++;
		
		$report .= 'Number:'.$counts['all'].PHP_EOL;			

		$result = $doiMinter->query("SELECT DISTINCT doi FROM dois WHERE handle = '$handleURL' AND type = '$type'");
				
		if($result->num_rows===1)
		{
			$row = $result->fetch_assoc();
			
			$doi = $row['doi'];
		}
		
		if(is_null($doi))
		{
			$isDoiUnique = TRUE;
			do
			{
				$doi = generateDOI($handle, $type);
				$result = $doiMinter->query("SELECT doi FROM dois WHERE doi = '$doi'");
				if($result->num_rows===0)
				{
					$isDoiUnique = FALSE;
				}
				$report .= '- '.$doi;
			} while ($isDoiUnique);
		}

		$dataciteXML = generateDataciteXML($handleURL, $doi);
		
		$url = REPOSITORY_URL.'/handle/'.$handle;
		
		$receivedDOI = substr(postMetadataToDataCiteMDS($dataciteXML), 4, -1);
		
		if(strcasecmp($receivedDOI, $doi) != 0) 
		{
			$message = 'The received DOI from DataCite (' . $receivedDOI . ') does not match our DOI: ' . $doi;
			$doiMinter->query("INSERT INTO messages (process, type, message) VALUES ('postMetadata','notice','$message')");
			$report .= ' - '.$message.PHP_EOL;
		}
		
		$response = putURLToDataCiteMDS($doi, $url);
		
		if($response !== 'OK')
		{
			$message = 'Unexpected response (' . $response . ') received for put URL request to DataCite';
			$doiMinter->query("INSERT INTO messages (process, type, message) VALUES ('putURL','notice','$message')");
			$report .= ' - '.$message.PHP_EOL;
		}			
		
		//Save information in the local database
		$recordType = saveDOI($handleURL, $doi, $url, $type, 'active');
		
		$report .= ' - '.$recordType.PHP_EOL;
		
		//$counts[$recordType]++;	
		
		$recordType = saveSourceData('datacite', $doi, $dataciteXML, 'XML');
		
		$report .= ' - '.$recordType.PHP_EOL;

		$counts[$recordType]++;
		
		//Add DOI and provenance statement to repository record
		$json = getObjectInfoByHandle($token, $handle);
		$json = json_decode($json, TRUE);
		$itemID = $json['id'];			
		
		$newMetadata[] = array("key"=>"dc.identifier.doi", "value"=>$doi, "language"=>null);			
		$newMetadata[] = array("key"=>"dc.description.provenance",
			"value"=>"Updated by $task task of ".SERVICE_NAME." service via REST API using the ".DSPACE_REST_API_USER." account at ".date("Y-m-d H:i:s").", note: DOI added", "language"=>"en_US");
	
		$json = json_encode($newMetadata);
		
		$response = postMetadataToDSpaceRESTAPI($token, $itemID, $json);
		
		if(!empty($response))
		{
			$message = 'Unexpected response (' . $response . ') received for post metadata to DSpace rest API';
			$doiMinter->query("INSERT INTO messages (process, type, message) VALUES ('postMetadataToDSpaceRESTAPI','notice','$message')");
			$report .= ' - '.$message.PHP_EOL;
		}
		
		unset($newMetadata);					
		
		$itemType = getValues($doiMinter, "SELECT value FROM metadata WHERE source LIKE 'repository' AND idInSource LIKE '$handle' AND field LIKE 'dc.type'", array('value'), 'singleValue');
		
		//Send submitter notification email
		if($type === 'production' && !in_array($itemType, array("Thesis","Dissertation")))
		{
			$submissionProvenance = getValues($doiMinter, "SELECT value FROM metadata WHERE source LIKE 'repository' AND idInSource LIKE '$handle' AND field LIKE 'dc.description.provenance' AND `value` LIKE 'Submitted by%'", array('value'), 'singleValue');

			$start = strpos($submissionProvenance,"(");
			$end = strpos($submissionProvenance,")");
			$submitterEmail = substr($submissionProvenance, ($start+1), ($end-$start-1));
			
			$bySpaces = explode(' ', $submissionProvenance);
			$submitterFirstName = $bySpaces[2];	
			
			$to = $submitterEmail.','.IR_EMAIL;
			
			$subject = "DataCite DOI Registration Complete";
		
			//Message to send
			$message = "Dear $submitterFirstName,".PHP_EOL.PHP_EOL."The Datacite DOI registration for your item is complete.".PHP_EOL."The DOI link is: https://doi.org/$doi".PHP_EOL.PHP_EOL."Regards,".PHP_EOL."The ".INSTITUTION_ABBREVIATION." Repository Team";

			// Always set content-type when sending HTML email
			$headers = EMAIL_HEADERS;
			$headers .= "From: " .IR_EMAIL. "\r\n";
			$headers .= "Reply-To: " .IR_EMAIL. "\r\n";

			//Send
			mail($to,$subject,$message,$headers);
		}			
	}
	
	//If there are results save the report and send a summary
	if($counts['all']!==0)
	{
		$summary = saveReport($task, $report, $counts, $errors);
		
		//Process completion email
		$to = IR_EMAIL;

		$subject = $task." Complete";
		
		//Message to send
		$message = $task.' Report'.PHP_EOL.$summary;

		$headers .= "From: " .IR_EMAIL. "\r\n";
		$headers .= "Reply-To: " .IR_EMAIL. "\r\n";

		//Send
		mail($to,$subject,$message,$headers);
	}	
		