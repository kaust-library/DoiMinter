<?php
	function getObjectInfoByHandle($token, $handle)
	{
		$curl = curl_init();

		$curlArray = array(
		  CURLOPT_URL => DSPACE_REST_API."handle/$handle?expand=metadata",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_POSTFIELDS => "",
		  CURLOPT_HTTPHEADER => array(
		    "Accept: application/json",
		    "Content-Type: application/json",
		    "cache-control: no-cache",
		    "rest-dspace-token: $token"
		  ),
		);

		$response = makeCurlRequest($curlArray, '200 OK');
		
		return $response;
	}