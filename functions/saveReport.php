<?php
	//Define function to save copy of a task report and summary
	function saveReport($task, $report, $counts, $errors)
	{
		global $doiMinter;
		
		$summary = $task.':'.PHP_EOL;
		
		foreach($counts as $type => $count)
		{
			$summary .= ' - '.$count.' '.$type.' items'.PHP_EOL;
		}
		
		$summary .= ' - Error count: '.count($errors).PHP_EOL;		
		
		//Log task summary
		insert($doiMinter, 'messages', array('process', 'type', 'message'), array($task, 'summary', $summary));
		
		foreach($errors as $error)
		{
			$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
		}
		
		$report .= PHP_EOL.$summary;
		
		//Log full task report
		insert($doiMinter, 'messages', array('process', 'type', 'message'), array($task, 'report', $report));
		
		return $summary;
	}
