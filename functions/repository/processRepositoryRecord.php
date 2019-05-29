<?php	
	//Define function to process XOAI metadata for a single repository item
	function processRepositoryRecord($idInSource, $item, &$sourceReport)
	{
		global $doiMinter;
		
		$source = 'repository';
		
		$recordType = '';
		
		$nameSpacesToIgnore = array("others", "repository", "license");
		
		//Make list of metadata fields used on the item
		$currentFields = array();
		
		$modified = (string)$item->header[0]->datestamp;
		$field = 'dspace.date.modified';
		
		$rowID = saveMetadata($source, $idInSource, '', $field, '', 1, $modified, NULL);
		$currentFields[] = $field;
		
		$sourceReport .= ' - Handle: '.$idInSource.' - Modified Timestamp: '.$modified.PHP_EOL;
		
		$check = $doiMinter->query("SELECT rowID FROM sourceData WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND deleted IS NULL");
		
		if((string)$item->header[0]['status']==='deleted')
		{
			$sourceReport .= '- DELETED ITEM'.PHP_EOL;		
			
			//if matched in database			
			if(mysqli_num_rows($check) !== 0)
			{			
				$sourceReport .= '- DELETED'.PHP_EOL;
				if(update($doiMinter, 'sourceData', array("deleted"), array(time(), $idInSource), 'idInSource'))
				{
					$recordType = 'deleted';
					//also mark all related information in metadata table with deleted timestamp
					update($doiMinter, 'metadata', array("deleted"), array(time(), $idInSource), 'idInSource');
				}
				else
				{
					$error = end($errors);
					$sourceReport .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
				}
			}
			else
			{
				$sourceReport .= '- Item Already Deleted'.PHP_EOL;
			}
		}					
		else
		{		
			//Save copy of item XML
			$sourceData = $item->asXML();
			
			$recordType = saveSourceData($source, $idInSource, $sourceData, 'XML');
				
			//Save collection and community handles as metadata
			foreach($item->header[0]->setSpec as $value)
			{
				if(strpos($value, 'com')!==FALSE)
				{
					$communities[] = $value;
				}
				elseif(strpos($value, 'col')!==FALSE)
				{
					$collections[] = $value;
				}
			}
			
			//Save community handles
			$field = 'dspace.community.handle';
			$place = 0;
			foreach($communities as $value)
			{
				$place++;				
				$rowID = saveMetadata($source, $idInSource, '', $field, '', $place, $value, NULL);
			}
			markExtraMetadataAsDeleted($source, $idInSource, NULL, $field, $place, '');
			$currentFields[] = $field;
			
			//Save collection handles			
			$field = 'dspace.collection.handle';
			$place = 0;
			foreach($collections as $value)
			{
				$place++;				
				$rowID = saveMetadata($source, $idInSource, '', $field, '', $place, $value, NULL);
			}
			markExtraMetadataAsDeleted($source, $idInSource, NULL, $field, $place, '');
			$currentFields[] = $field;			
			
			//save metadata and bitstream info
			foreach($item->metadata[0]->metadata[0]->element as $namespace)
			{					
				if(!in_array((string)$namespace[0]['name'], $nameSpacesToIgnore))
				{												
					if((string)$namespace[0]['name']==='bundles')
					{
						foreach($namespace->element as $element)
						{
							if((string)$element[0]->field==='ORIGINAL')
							{
								$place = 1;
								foreach($element->element->element as $bitstream)
								{									
									//Make list of metadata fields used on the bitstream
									$currentBitstreamFields = array();
									
									//First find bitstream URL which will serve as parent row of other bitstream metadata
									foreach($bitstream->field as $bitstreamField)
									{		
										if((string)$bitstreamField[0]['name']==='url')
										{
											$field = 'dspace.bitstream.url';
											$currentFields[] = $field;
											$value = (string)$bitstreamField;
											$bitstreamURLRowID = saveMetadata($source, $idInSource, '', $field, '', $place, $value, NULL);											
										}
									}
									
									foreach($bitstream->field as $bitstreamField)
									{		
										$childPlace = 1;
										$field = 'dspace.bitstream.'.(string)$bitstreamField[0]['name'];
										$value = (string)$bitstreamField;
										$rowID = saveMetadata($source, $idInSource, '', $field, '', $childPlace, $value, $bitstreamURLRowID);
										$currentBitstreamFields[] = $field;
									}
									
									$field = 'dspace.bitstream.bundleName';
									$rowID = saveMetadata($source, $idInSource, '', $field, '', $childPlace, 'ORIGINAL', $bitstreamURLRowID);
									$currentBitstreamFields[] = $field;
									
									markExtraMetadataAsDeleted($source, $idInSource, $bitstreamURLRowID, '', '', $currentBitstreamFields);
									
									$place++;									
								}
								markExtraMetadataAsDeleted($source, $idInSource, NULL, 'dspace.bitstream.url', $place, '');
							}
						}
					}
					else
					{
						//check metadata
						$namespaceName = (string)$namespace[0]['name'];
						foreach($namespace->element as $element)
						{							
							$elementName = (string)$element[0]['name'];
							foreach($element->element as $subelement)
							{
								if(isset($subelement[0]->field))
								{
									$field = $namespaceName . '.' . $elementName;									
									
									saveRepositoryMetadata($source, $idInSource, $subelement, $field);
									
									$currentFields[] = $field;
								}
								else
								{
									$field = $namespaceName . '.' . $elementName  . '.' . (string)$subelement[0]['name'];
									foreach($subelement->element as $subSubelement)
									{
										saveRepositoryMetadata($source, $idInSource, $subSubelement, $field);
										
										$currentFields[] = $field;
									}										
								}								
							}							
						}
					}
				}
			}			
			markExtraMetadataAsDeleted($source, $idInSource, NULL, '', '', $currentFields);
		}
		return $recordType;
	}	
		