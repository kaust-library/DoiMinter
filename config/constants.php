<?php
	define('SERVICE_NAME', 'doiMinter');
	
	//Choose whether to create a test or production DOI by setting type=test or type=production
	if(isset($_GET["type"]))
	{
		if($_GET["type"]==='test')
		{
			//Change to FALSE for production use
			define('TESTING', TRUE);
			$type = 'test';
		}
		elseif($_GET["type"]==='production')
		{
			define('TESTING', FALSE);
			$type = 'production';
		}
	}
	else
	{
		//Default setting, change to FALSE for production
		define('TESTING', FALSE);
		$type = 'production';
	}
	
	if(TESTING)
	{
		define('REPOSITORY_BASE_URL', '');
		
		define('DATACITE_MDS', 'https://mds.test.datacite.org/');
		
		define('DOI_PREFIX', '');
	}
	else
	{
		define('REPOSITORY_BASE_URL', '');
		
		define('DATACITE_MDS', 'https://mds.datacite.org/');
		
		define('DOI_PREFIX', '');
	}
	
	//Locally Defined Constants
	define('INSTITUTION_ABBREVIATION', '');
	
	define('PUBLISHER', '');	
	
	define('AFFILIATION', '');
	
	define('IR_EMAIL', '');	
	
	define('DOI_BASE_URL', 'https://doi.org/');
	
	define('REPOSITORY_URL', 'https://'.REPOSITORY_BASE_URL);
	
	define('DSPACE_REST_API', REPOSITORY_URL.'/rest/');

	define('REPOSITORY_OAI_URL', REPOSITORY_URL.'/oai/');
	
	define('REPOSITORY_OAI_ID_PREFIX', 'oai:'.REPOSITORY_BASE_URL.':');
	

	