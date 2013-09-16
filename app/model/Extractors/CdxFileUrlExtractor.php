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
        foreach (file($filename, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES) as $line) {
            list( , , $url) = explode(' ', $line);
            $urls[$host = parse_url($url, PHP_URL_HOST)] = TRUE;
            $fetched[$url] = $host;
            if ($appendHigherDomains) {
                $domains = explode('.', $host);
                array_shift($domains);
                while (count($domains) > 1) {
                    $urls[$host = implode('.', $domains)] = TRUE;
                    $fetched[$url . count($domains)] = $host;
                    array_shift($domains);
                }
            }
        }
        return array_keys($urls);
    }

}
