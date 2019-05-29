<?php
	function postMetadataToDSpaceRESTAPI($token, $itemID, $json)
	{
		$curl = curl_init();
		
		$curlArray = array(
		  CURLOPT_URL => DSPACE_REST_API."items/$itemID/metadata",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "$json",
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