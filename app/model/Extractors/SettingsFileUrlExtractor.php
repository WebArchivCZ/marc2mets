<?php

namespace Extractors;


class SettingsFileUrlExtractor extends \Nette\Object implements \IUrlExtractor
{

	private $pattern = 'Settings*.xml';

	private $directory = 'jobs';


	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
	}


	public function setDirectory($directory)
	{
		$this->directory = trim($directory, '/\\');
	}


	public function getUrls($directory)
	{
		$directory = rtrim($directory, '/\\') . ($this->directory !== '' ? DIRECTORY_SEPARATOR . $this->directory : '');
		$files = \Nette\Utils\Finder::findFiles($this->pattern)
				 ->from($directory);
		$trim = strlen($directory) + 1;
		return array_unique(array_map(function (\SplFileInfo $file) use ($trim) {
			$path = substr(dirname($file->getPathname()), $trim);
			$slugs = explode(DIRECTORY_SEPARATOR, $path);
			return implode('.', array_reverse($slugs));
		}, iterator_to_array($files)));
	}

}
