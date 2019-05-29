<?php
	//Define function to generate Datacite XML for a given handle
	function generateDataciteXML($handle, $doi)
	{
		global $doiMinter;		

		$source = 'repository';
		
		$result = $doiMinter->query("
		SELECT DISTINCT idInSource
		FROM metadata
		WHERE source = '$source'
		AND field = 'dc.identifier.uri'
		AND value = '$handle'
		AND deleted IS NULL");
				
		if($result->num_rows===1)
		{
			$row = $result->fetch_assoc();
			
			$idInSource = $row['idInSource'];	
		
			$dataciteXML = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><resource xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.1/metadata.xsd"/>');
			
			$identifierElement = $dataciteXML->addChild('identifier', $doi);
			$identifierElement->addAttribute('identifierType', 'DOI');
			
			$authors = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.contributor.author'), array('rowID', 'value'), 'arrayOfValues');
			$localAuthors = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'local.author'), array('value'), 'arrayOfValues');					
			$creatorsElement = $dataciteXML->addChild('creators');
			foreach($authors as $author)
			{
				$creatorElement = $creatorsElement->addChild('creator');
				$creatorNameElement = $creatorElement->addChild('creatorName', $author['value']);
				$creatorNameElement->addAttribute('nameType', 'Personal');
				
				$orcid = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, $author['rowID'], 'dc.identifier.orcid'), array('value'), 'singleValue');
				
				if(!empty($orcid))
				{
					$nameIdentifierElement = $creatorElement->addChild('nameIdentifier', $orcid);
					$nameIdentifierElement->addAttribute('schemeURI', 'http://orcid.org');
					$nameIdentifierElement->addAttribute('nameIdentifierScheme', 'ORCID');
				}
				
				if(in_array($author['value'], $localAuthors))
				{
					$creatorElement->addChild('affiliation', AFFILIATION);
				}
			}
			
			$title = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.title'), array('value'), 'singleValue');
			$titlesElement = $dataciteXML->addChild('titles');
			//Use direct entity assignment rather than addChild for fields where values may contain & so that it is entered as &amp;
			$titlesElement->title = $title;
			
			$publisher = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.publisher'), array('value'), 'singleValue');
			if(empty($publisher))
			{
				$publisher = PUBLISHER;
			}
			$dataciteXML->publisher = $publisher;
			$publicationYear = substr(getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.date.issued'), array('value'), 'singleValue'), 0, 4);
			$dataciteXML->addChild('publicationYear', $publicationYear);
			
			$type = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.type'), array('value'), 'singleValue');
			$typeMapping = array('Dataset'=>array('resourceType'=>'Dataset','resourceTypeGeneral'=>'Dataset'),'Thesis'=>array('resourceType'=>'MS Thesis','resourceTypeGeneral'=>'Text'),'Dissertation'=>array('resourceType'=>'PhD Dissertation','resourceTypeGeneral'=>'Text'),'Software'=>array('resourceType'=>'Software','resourceTypeGeneral'=>'Software'));
			if(!in_array($type, array_keys($typeMapping)))
			{
				$resourceTypeElement = $dataciteXML->addChild('resourceType', $type);
				$resourceTypeElement->addAttribute('resourceTypeGeneral', 'Other');
			}
			else
			{
				$resourceTypeElement = $dataciteXML->addChild('resourceType', $typeMapping[$type]['resourceType']);
				$resourceTypeElement->addAttribute('resourceTypeGeneral', $typeMapping[$type]['resourceTypeGeneral']);
			}
			
			$description = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.description'), array('value'), 'singleValue');
			$abstract = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.description.abstract'), array('value'), 'singleValue');
			if(!empty($description)||!empty($abstract))
			{
				$descriptionsElement = $dataciteXML->addChild('descriptions');
				if(!empty($description))
				{
					$descriptionElement = $descriptionsElement->addChild('description', str_replace('&', '&amp;', $description));
					$descriptionElement->addAttribute('descriptionType', 'Other');
				}
				
				if(!empty($abstract))
				{
					$descriptionElement = $descriptionsElement->addChild('description', str_replace('&', '&amp;',$abstract));
					$descriptionElement->addAttribute('descriptionType', 'Abstract');
				}
			}
			$alternateIdentifiersElement = $dataciteXML->addChild('alternateIdentifiers');
			$alternateIdentifierElement = $alternateIdentifiersElement->addChild('alternateIdentifier', $handle);
			$alternateIdentifierElement->addAttribute('alternateIdentifierType', 'Handle');
			
			$isSupplementToDOI = '';
			$isSupplementTos = getValues($doiMinter, setSourceMetadataQuery($source, $idInSource, NULL, 'dc.relation.isSupplementTo'), array('value'), 'arrayOfValues');
			foreach($isSupplementTos as $isSupplementTo)
			{
				if(strpos($isSupplementTo, 'DOI:')===0)
				{
					$isSupplementToDOI = str_replace('DOI:', '', $isSupplementTo);
				}
			}
			
			if(!empty($isSupplementToDOI))
			{
				$relatedIdentifiersElement = $dataciteXML->addChild('relatedIdentifiers');
				$relatedIdentifierElement = $relatedIdentifiersElement->addChild('relatedIdentifier', $isSupplementToDOI);
				$relatedIdentifierElement->addAttribute('relatedIdentifierType', 'DOI');
				$relatedIdentifierElement->addAttribute('relationType', 'IsSupplementTo');
			}
					
			$dataciteXML = $dataciteXML->asXML();
			$dataciteXML = str_replace('><', '>'.PHP_EOL.'<', $dataciteXML);
			return $dataciteXML;			
		}		
	}
?>
