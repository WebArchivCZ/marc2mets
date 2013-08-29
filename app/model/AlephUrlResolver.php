<?php



class AlephUrlResolver extends Nette\Object
{

	/** @var TableFactory */
	private $tableFactory;



	public function __construct(TableFactory $tableFactory)
	{
		$this->tableFactory = $tableFactory;
	}



	/**
	 * @param $url
	 * @return int|FALSE
	 */
	public function getAlephId($url)
	{
		$table = $this->tableFactory->createTable();
		if ($row = $table->where('url', $this->expand($url))->limit(1)->fetch()) {
			return $row->aleph_id;
		} else {
			return FALSE;
		}
	}



	private function expand($url)
	{
		if (!preg_match('~https?://~i', $url)) {
			return Nette\Utils\Arrays::flatten(array(
				$this->withOrWithoutEndBackslash('http://' . $url),
				$this->withOrWithoutEndBackslash('https://' . $url),
			));

		} else {
			return $this->withOrWithoutEndBackslash($url);
		}
	}


	private function withOrWithoutEndBackslash($url)
	{
		$url = ltrim($url, '/');
		return array($url . '/', $url);
	}
}
