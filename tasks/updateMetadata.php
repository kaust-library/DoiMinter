<?php
	$task = 'updateMetadata';
	
	//Record count variable
	$counts = array('all'=>0,'new'=>0,'modified'=>0,'unchanged'=>0,'new source data'=>0,'modified source data'=>0,'unchanged source data'=>0);
	
	set_include_path('/var/www/doiMinter');
	
	//include core configuration and common function files
	include_once 'include.php';
	
	$report = '';
		
	$errors = array();
	
	$result = $doiMinter->query("SELECT DISTINCT idInSource handleURL, d.doi FROM metadata m
		LEFT JOIN dois d ON idInSource = d.handle
		WHERE d.doi IS NOT NULL
		AND (field = 'dc.contributor.author'
		OR field = 'kaust.author'
		OR field = 'dc.title'
		OR field = 'dc.publisher'
		OR field = 'dc.date.issued'
		OR field = 'dc.type'
		OR field = 'dc.description'
		OR field = 'dc.description.abstract'
		OR field = 'dc.relation.isSupplementTo')
		AND m.added > (SELECT timestamp FROM messages d WHERE process = 'updateMetadata' ORDER BY d.timestamp DESC LIMIT 1)"
	);
					  
	while ($rows = $result->fetch_assoc())
	{
		$counts['all']++;
		
		$handleURL = $rows['handleURL'];
		$handle = substr($handleURL, 22);
		$doi = $rows['doi'];
		
		$dataciteXML = generateDataciteXML($handleURL, $doi);
		
		$url = REPOSITORY_URL.'/handle/'.$handle;
			
		$receivedDOI = substr(postMetadataToDataCiteMDS($dataciteXML), 4, -1);
		
		if(strcasecmp($receivedDOI, $doi) != 0) 
		{
			$message = 'The received DOI from DataCite (' . $receivedDOI . ') does not match our DOI: ' . $doi;
			$doiMinter->query("INSERT INTO messages (process, type, message) VALUES ('postMetadata','notice','$message')");
			$report .= ' - '.$message.PHP_EOL;
		}
		
		$recordType = saveSourceData('datacite', $doi, $dataciteXML, 'XML');
		
		$report .= ' - '.$doi.' ('.$recordType.')'.PHP_EOL;

		$counts[$recordType]++;		
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

		$headers = "From: " .IR_EMAIL. "\r\n";
		$headers .= "Reply-To: " .IR_EMAIL. "\r\n";

		//Send
		mail($to,$subject,$message,$headers);
	}