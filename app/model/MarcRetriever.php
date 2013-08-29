<?php

use Nette\Utils\Validators;


class MarcRetriever extends Nette\Object
{

	const METADATA_PREFIX = 'marc21';

	/** @var oaipmh\OAIPMHClient */
	private $client;

	/** @var string */
	private $identifierFormat;



	public function __construct(oaipmh\OAIPMHClient $client, $identifierFormat)
	{
		$this->client = $client;
		$this->identifierFormat = $identifierFormat;
	}



	/**
	 * @param mixed $id
	 * @return SimpleXMLElement
	 */
	public function get($id)
	{
		$result = $this->client->GetRecord(sprintf($this->identifierFormat, $id), self::METADATA_PREFIX);
		return $result->GetRecord->record->metadata->children('marc', TRUE);
	}

}
