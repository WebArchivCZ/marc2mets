<?php

use Nette\Utils\Validators;


class MarcToModsConverter extends Nette\Object
{

	/** @var string */
	private $templateFilename;


	public function __construct($templateFilename)
	{
		$this->templateFilename = $templateFilename;
	}



	/**
	 * @param SimpleXMLElement $mods
	 * @return string
	 */
	public function convert($mods)
	{
		$processor = new XSLTProcessor;
		$processor->importStylesheet(simplexml_load_file($this->templateFilename));
		return $processor->transformToXml($mods);
	}

}
