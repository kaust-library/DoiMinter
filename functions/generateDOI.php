<?php
	//Define function to return a unique random string as an ID
	function generateDOI($handle, $type)
	{
		global $doiMinter;
			
		$string = substr(str_shuffle(str_repeat(str_repeat("0123456789", 3)."ABCDEFGHIJKLMNOPQRSTUVWXYZ", 5)), 0, 5);
			
		$doi = DOI_PREFIX.'/'.INSTITUTION_ABBREVIATION.'-'.$string;
		
		return $doi;
	}
?>