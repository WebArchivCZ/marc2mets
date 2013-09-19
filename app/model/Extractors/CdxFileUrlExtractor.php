<?php

namespace Extractors;


class CdxFileUrlExtractor extends \Nette\Object implements \IUrlExtractor
{

	private $pattern = '*.cdx';

	private $directory = 'jobs';

    private $appendHigherDomains = FALSE;


	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
	}


	public function setDirectory($directory)
	{
		$this->directory = trim($directory, '/\\');
	}


    public function setAppendHigherDomains($value)
    {
        $this->appendHigherDomains = $value;
    }


	public function getUrls($directory)
	{
		$directory = rtrim($directory, '/\\') . ($this->directory !== '' ? DIRECTORY_SEPARATOR . $this->directory : '');
		$files = \Nette\Utils\Finder::findFiles($this->pattern)
				 ->from($directory);
		$trim = strlen($directory) + 1;
        $that = $this;
		return array_unique(callback('array_merge')->invokeArgs(array_map(function (\SplFileInfo $file) use ($trim, $that) {
            return $that->getUrlsFromFile($file->getPathname());
		}, iterator_to_array($files))));
	}


    public function getUrlsFromFile($filename)
    {
        $appendHigherDomains = $this->appendHigherDomains;
        $urls = [];
        $file = fopen($filename, 'r');
        while (($line = fgets($file)) !== FALSE) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            list( , , $url) = explode(' ', $line);
            $host = parse_url($url, PHP_URL_HOST);
            if (isset($urls[$host])) {
                continue;
            }
            $urls[$host] = TRUE;
            if ($appendHigherDomains) {
                $domains = explode('.', $host);
                array_shift($domains);
                while (count($domains) > 1) {
                    $urls[$host = implode('.', $domains)] = TRUE;
                    array_shift($domains);
                }
            }
        }
        return array_keys($urls);
    }

}
