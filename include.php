<?php
	$directoriesToInclude = array("config", "functions", "functions/repository", "functions/datacite", "functions/dspace");
	
	foreach($directoriesToInclude as $directory)
	{
		//load files
		$filesToInclude = array_diff(scandir(__DIR__.'/'.$directory), array('..', '.'));
		foreach($filesToInclude as $file)
		{
			if(is_file(__DIR__.'/'.$directory.'/'.$file))
			{
				include_once $directory.'/'.$file;
			}
		}
	}