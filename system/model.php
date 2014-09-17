<?php

class Model {

	public $pdo;
	public $map;
	private $fields;


	public function __construct(){
		
		$this->pdo_connect();

		$this->map = new stdClass();
		$this->map->fields 	  = Array('*');
		$this->map->orderby   = Array();
		$this->map->relations = Array();
		$this->map->limit 	  = 100;

		if(method_exists($this,'map')){
			$this->map();
		}

	}

	public function pdo_connect(){

		global $config;
		$this->pdo = new PDO("mysql:host=".$config['db_host'].";dbname=".$config['db_name'].';charset=utf8', $config['db_username'], $config['db_password']);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    	$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}

	public function select_builder(){

		$fields = $this->map->fields;
		$table 	= $this->map->table;
		
		foreach($fields as $k => $value){
			if(count(explode('.',$value)) !== 2 && $value !== '*')
				$fields[$k] = $table.'.'.$value;
		}
		$fields = implode(',', $fields);

		$qry = 'SELECT '.$fields.' FROM `' . $table . '` '."\n";
		return $qry;

	}	

	public function where_builder($filter){

		$keys = array_keys($filter);
		foreach($keys as $k=>$v) $keys[$k] = $v . ' = ?';
		$where = count($filter) ? ' WHERE ' . implode(' AND ', $keys)."\n" : false;
		return $where;

	}

	public function innerjoin_builder(){

		$estrangers = $this->map->relations;

		$table 		= $this->map->table;
		$qry 		= null;

		foreach($estrangers as $field => $estranger){
			list($estrangertable, $estrangerfield) = explode('.', $estranger);
			$field = count(explode('.', $field)) > 1 ? $field : $table.'.'.$field;

			$qry .= ' INNER JOIN ' . $estrangertable . ' ON '. $field . '=' . $estrangertable.'.'.$estrangerfield. "\n";
		}



		return $qry;
	}

	public function orderby_builder(){

		$qry = null;
		$orderby = $this->map->orderby;

		if(count($orderby) > 1)
			$qry = ' ORDER BY '. $orderby[0].' '.$orderby[1];
		else if(count($orderby) > 1)
			$qry = ' ORDER BY '. $orderby[0].' ASC';

		return $qry; 

	}


	public function limit_builder(){

		$limit = $this->map->limit;
		$qry   = null;

		if($limit)
			$qry = ' LIMIT '. $limit;

		return $qry;
	}

	public function set($vars, $values = Array()){

		if(is_array($vars)){
			$this->fields = array_combine($vars, $values);
		}else if(is_array($this->fields)){
			$this->fields = array_merge($this->fields, Array($vars => $values));
		}else{
			$this->fields = Array($vars => $values);
		}
		return $this;
	}

	public function persist(){

		$data 	= $this->fields;
		$fields = implode(',',array_keys($data));
		$colums = implode(',',array_fill(0, count($data), '?'));
		$values = array_values($data);

		$qry = 'INSERT IGNORE INTO `'.$this->map->table.'`('.$fields.') VALUES('.$colums.')';
		$result = $this->execute($qry, $values);
		if($result)
			return $this->pdo->lastInsertId();

		return $result;
	}

	public function findAll($filter = Array()){

		$select = $this->select_builder();
		$where  = $this->where_builder($filter);
		$limit  = $this->limit_builder();
		$inner  = $this->innerjoin_builder();
		$order  = $this->orderby_builder();
		
		$qry = $select . $inner . $where . $order .  $limit;

		$pars = array_values($filter);
		
		$exec = $this->pdo->prepare($qry);
		$exec->execute($pars);
		$result = $exec->fetchAll();
		return $result;

	}

	public function find($filter = Array()){

		$select = $this->select_builder();
		$where  = $this->where_builder($filter);
		$limit  = $this->limit_builder();
		$inner  = $this->innerjoin_builder();
		$order  = $this->orderby_builder();
		
		$qry = $select . $inner . $where . $order .  $limit;

		$pars = array_values($filter);
		
		$exec = $this->pdo->prepare($qry);
		$exec->execute($pars);
		$result = $exec->fetch();
		return $result;

	}

	public function delete($filter = Array()){
		
		$table 	= $this->map->table;
		$where  = $this->where_builder($filter);
		$limit  = $this->limit_builder();
		
		$qry = 'DELETE FROM ' . $table . $where .  $limit;

		$pars = array_values($filter);

		$exec = $this->pdo->prepare($qry);
		$result = $exec->execute($pars);
		return $result;

	}

	public function reset(){

		$result = $this->execute('TRUNCATE TABLE `' . $this->table . '`');
		return $result;

	}

	public function query($qry){
		$result = $this->pdo->prepare( $qry );
		try {
			$result->execute();
		} catch (PDOException $e) {
			die('Query failed: ' . $e->getMessage());
		}

		preg_match("/([^\s]+)/i", $qry, $method);
		$method = $method[1];
		
		if ( $method == "UPDATE" ) return;
		
		if ( $method == "SELECT" ) return $result->fetchAll();
		
		if ( $method == "INSERT" ) return $this->pdo->lastInsertId();
		
	}

	public function execute($qry, $par = Array()){

		try{
			$exec = $this->pdo->prepare($qry);
			$exec->execute($par);
			return true;
		}catch(PDOException $e){
			var_dump( $e->getMessage());
			return false;
		}

	}
    
}
?>
