<?php


class ConversionProcess extends Nette\Object
{

	private $urlExtractor;

	private $alephUrlResolver;

	private $retriever;

	private $converter;

	private $urls;

	private $ids;

	private $directory;

	private $defaultOutputDirectory;


	public function __construct(IUrlExtractor $urlExtractor, AlephUrlResolver $alephUrlResolver, MarcRetriever $retriever, MarcToModsConverter $converter)
	{
		$this->urlExtractor = $urlExtractor;
		$this->alephUrlResolver = $alephUrlResolver;
		$this->retriever = $retriever;
		$this->converter = $converter;
	}


	public function discoverUrls($directory)
	{
		return count($this->urls = $this->urlExtractor->getUrls($this->directory = $directory));
	}


	public function resolveUrls($onResolve = NULL)
	{
		if (!is_callable($onResolve)) {
			$onResolve = NULL;
		}
		$alephUrlResolver = $this->alephUrlResolver;
		$this->ids = array_filter(array_map(function ($url) use ($alephUrlResolver, $onResolve) {
			$id = $alephUrlResolver->getAlephId($url);
			if ($onResolve !== NULL) {
				$onResolve($url, $id);
			}
			return $id;
		}, array_combine(array_values($this->urls), $this->urls)));
		return count($this->ids);
	}


	public function setDefaultOutputDirectory($directory)
	{
		$this->defaultOutputDirectory = $directory;
	}


	public function convert($onConvert)
	{
		if (!is_callable($onConvert)) {
			$onConvert = NULL;
		}
		foreach ($this->ids as $host => $id) {
			$marc = $this->retriever->get($id);
			$modsXml = $this->converter->convert($marc);
			$directory = array_search($host, $this->urls, TRUE);
			$directory = dirname(is_int($directory) ? $this->getOutputDirectory($host) : $directory);
			$filename = 'Mets_' . rtrim(preg_replace('~^https?://~', '', $host), '/') . '.xml';
			if (!is_dir($directory)) {
				mkdir($directory, 0777, TRUE);
			}
			file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $modsXml);
			if ($onConvert !== NULL) {
				$onConvert($filename);
			}
		}
	}


	private function getOutputDirectory($host)
	{
		return $this->directory . DIRECTORY_SEPARATOR . sprintf($this->defaultOutputDirectory, implode(DIRECTORY_SEPARATOR, array_reverse(explode('.', $host))));
	}

}