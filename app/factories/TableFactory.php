<?php



class TableFactory extends Nette\Object
{

	/** @var Nette\Database\Connection */
	private $connection;

	/** @var string */
	private $tableName;

	/**
	 * @param Nette\Database\Connection $connection
	 * @param string $tableName
	 */
	public function __construct(Nette\Database\Connection $connection, $tableName)
	{
		$this->connection = $connection;
		$this->tableName = $tableName;
	}



	public function createTable()
	{
		return $this->connection->table($this->tableName);
	}

}
