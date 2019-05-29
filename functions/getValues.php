<?php	
	//Define function to get single value or array of values
	function getValues($database, $query, $fields, $request)
	{			
		$result = $database->query($query);
		
		if($request === 'singleValue')
		{
			$values = '';
			
			$row = $result->fetch_assoc();
			$values = $row[$fields[0]];
		}
		elseif($request === 'arrayOfValues')
		{
			$values = array();
			
			while($row = $result->fetch_assoc())
			{				
				if(count($fields)===1)
				{
					array_push($values, $row[$fields[0]]);
				}
				else
				{
					array_push($values, $row);
				}
			}
		}
		
		return $values;		
	}	
