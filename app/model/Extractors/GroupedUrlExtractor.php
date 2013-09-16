<?php

namespace Extractors;


class GroupedUrlExtractor extends \Nette\Object implements \IUrlExtractor
{

    /** @var \IUrlExtractor[] */
    private $extractors = array();


    public function addExtractor(\IUrlExtractor $extractor)
    {
        $this->extractors[] = $extractor;
    }


	public function getUrls($directory)
	{
        $results = [];
        foreach ($this->extractors as $extractor) {
            $results[] = $extractor->getUrls($directory);
        }
        return callback('array_merge')->invokeArgs($results);
	}

}
