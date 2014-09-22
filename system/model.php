<?php

class Model {

	private $pdo;
	public $table, $fields, $orderby, $relations, $limit, $query;

	public function pdo_connect()
	{
		$this->fields 	 = Array('*');
		$this->orderby   = Array();
		$this->relations = Array();
		$this->limit 	 = 100;

		global $config;
		$this->pdo = new PDO("mysql:host=".$config['db_host'].";dbname=".$config['db_name'].';charset=utf8', $config['db_username'], $config['db_password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}

	public function selectBuilder()
	{
		$fields = $this->fields;
		$table 	= $this->table;
		
		foreach($fields as $k => $value){
			if(count(explode('.',$value)) !== 2 && $value !== '*')
				$fields[$k] = $table.'.'.$value;
		}
		$fields = implode(',', $fields);

		$this->query = 'SELECT '.$fields.' FROM `' . $table . '` '."\n";
		return $this->query;
	}	

	public function whereBuilder($filter)
	{
		$keys = array_keys($filter);
		foreach($keys as $k=>$v) $keys[$k] = $v . ' = ?';
		$where = count($filter) ? ' WHERE ' . implode(' AND ', $keys) . "\n" : false;
		return $where;
	}

	public function innerjoinBuilder()
	{
		$estrangers  = $this->relations;
		$table 		 = $this->table;
		$this->query = null;

		foreach($estrangers as $field => $estranger){
			list($estrangertable, $estrangerfield) = explode('.', $estranger);
			$field = count(explode('.', $field)) > 1 ? $field : $table.'.'.$field;

			$this->query .= ' INNER JOIN ' . $estrangertable . ' ON '. $field . '=' . $estrangertable.'.'.$estrangerfield. "\n";
		}

		return $this->query;
	}

	public function orderbyBuilder()
	{
		$this->query = null;
		$orderby = $this->orderby;

		if(count($orderby) > 1)
			$this->query = ' ORDER BY '. $orderby[0].' '.$orderby[1];
		else if(count($orderby) > 1)
			$this->query = ' ORDER BY '. $orderby[0].' ASC';

		return $this->query;
	}

	public function limitBuilder()
	{
		$limit = $this->limit;
		$this->query   = null;

		if($limit)
			$this->query = ' LIMIT '. $limit;

		return $this->query;
	}

	public function set($vars, $values = Array())
	{
		if(is_array($vars))
			$this->fields = array_combine($vars, $values);
		else if(is_array($this->fields))
			$this->fields = array_merge($this->fields, Array($vars => $values));
		else
			$this->fields = Array($vars => $values);
		
		return $this;
	}

	public function persist()
	{
		$data 	= $this->fields;
		$fields = implode(',',array_keys($data));
		$colums = implode(',',array_fill(0, count($data), '?'));
		$values = array_values($data);

		$this->query = 'INSERT IGNORE INTO `'.$this->table.'`('.$fields.') VALUES('.$colums.')';
		$result = $this->execute($this->query, $values);
		if($result)
			return $this->pdo->lastInsertId();

		return $result;
	}

	public function findAll($filter = Array())
	{
		$select = $this->selectBuilder();
		$where  = $this->whereBuilder($filter);
		$limit  = $this->limitBuilder();
		$inner  = $this->innerjoinBuilder();
		$order  = $this->orderbyBuilder();
		
		$this->query = $select . $inner . $where . $order . $limit;

		$pars = array_values($filter);
		
		$exec = $this->pdo->prepare($qry);
		$exec->execute($pars);
		$result = $exec->fetchAll();
		return $result;
	}

	public function find($filter = Array())
	{
		$select = $this->selectBuilder();
		$where  = $this->whereBuilder($filter);
		$limit  = $this->limitBuilder();
		$inner  = $this->innerjoinBuilder();
		$order  = $this->orderbyBuilder();
		
		$this->query = $select . $inner . $where . $order . $limit;

		$pars = array_values($filter);
		
		$exec = $this->pdo->prepare($qry);
		$exec->execute($pars);
		$result = $exec->fetch();
		return $result;
	}

	public function delete($filter = Array())
	{		
		$table = $this->table;
		$where = $this->whereBuilder($filter);
		$limit = $this->limitBuilder();
		
		$this->query = 'DELETE FROM ' . $table . $where . $limit;

		$pars = array_values($filter);

		$exec   = $this->pdo->prepare($qry);
		$result = $exec->execute($pars);
		return $result;
	}

	public function reset()
	{
		$result = $this->execute('TRUNCATE TABLE `' . $this->table . '`');
		return $result;
	}

	public function execute($qry, $par = Array())
	{
		try {
			$exec = $this->pdo->prepare($qry);
			$exec->execute($par);
			return true;
		} catch (PDOException $e) {
			var_dump($e->getMessage());
			return false;
		}
	}
    
}