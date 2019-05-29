<?php	
	//Define function to get Datacite XML from the MDS API
	function getDataciteRecord($doi)
	{			
		global $errors;
		
		$request = new HttpRequest();
		$request->setUrl('https://mds.datacite.org/metadata/'.$doi);
		$request->setMethod(HTTP_METH_GET);

		$request->setHeaders(array(
		  'Cache-Control' => 'no-cache',
		  'Authorization' => 'Basic '.DATACITE_MDS_USER.':'.DATACITE_MDS_PW
		));

		try {
		  $response = $request->send();

		  echo $response->getBody();
		} catch (HttpException $error) {
		  $errors[] = $error;
		}
		return $dataciteRecord;
	}	
