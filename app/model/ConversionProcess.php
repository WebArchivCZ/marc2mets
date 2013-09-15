<?php


class ConversionProcess extends Nette\Object
{

	private $urlExtractor;

	private $alephUrlResolver;

	private $retriever;

	private $converter;

	private $urls;

	private $ids;


	public function __construct(IUrlExtractor $urlExtractor, AlephUrlResolver $alephUrlResolver, MarcRetriever $retriever, MarcToModsConverter $converter)
	{
		$this->urlExtractor = $urlExtractor;
		$this->alephUrlResolver = $alephUrlResolver;
		$this->retriever = $retriever;
		$this->converter = $converter;
	}


	public function discoverUrls($directory)
	{
		return count($this->urls = $this->urlExtractor->getUrls($directory));
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


	public function convert($onConvert)
	{
		if (!is_callable($onConvert)) {
			$onConvert = NULL;
		}
		foreach ($this->ids as $url => $id) {
			$marc = $this->retriever->get($id);
			$modsXml = $this->converter->convert($marc);
			$filename = dirname(array_search($url, $this->urls, TRUE));
			$filename .= DIRECTORY_SEPARATOR;
			$filename .= 'Mets_' . rtrim(preg_replace('~^https?://~', '', $url), '/') . '.xml';
			file_put_contents($filename, $modsXml);
			if ($onConvert !== NULL) {
				$onConvert($filename);
			}
		}
	}

}