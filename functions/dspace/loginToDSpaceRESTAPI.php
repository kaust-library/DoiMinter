<?php
	function loginToDSpaceRESTAPI()
	{
		$curl = curl_init();
		
		$options = array(
		  CURLOPT_URL => DSPACE_REST_API."login",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => '{"email": "'.DSPACE_REST_API_USER.'", "password": "'.DSPACE_REST_API_PW.'"}',
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
		  )
		);

		$response = makeCurlRequest($options, '200 OK');

		return $response;
	}