<?php
	//Post item metadata to DataCite
	function postMetadataToDataCiteMDS($dataciteXML)
	{
		$auth = base64_encode(DATACITE_MDS_USER . ":" . DATACITE_MDS_PW);
		$options = array(
		  CURLOPT_URL => DATACITE_MDS."metadata",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $dataciteXML,
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Basic $auth",
			"Content-Type: application/xml",
			"cache-control: no-cache"
		  )
		);

		$response = makeCurlRequest($options, '201 Created');

		return $response;
	}