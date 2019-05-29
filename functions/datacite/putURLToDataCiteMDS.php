<?php
	//Set the URL that a given DataCite DOI will redirect to
	function putURLToDataCiteMDS($doi, $url)
	{
		$auth = base64_encode(DATACITE_MDS_USER . ":" . DATACITE_MDS_PW);
		$options = array(
		  CURLOPT_URL => DATACITE_MDS . "doi/" . $doi,
		  CURLOPT_POSTFIELDS => "doi=" . $doi . "\nurl=" . $url,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Basic $auth",
			"Content-Type: application/text",
			"cache-control: no-cache"
		  )
		);

		$response = makeCurlRequest($options, '201 Created');

		return $response;
	}
