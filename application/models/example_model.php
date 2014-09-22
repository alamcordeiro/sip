<?php

class Example_model extends Model {

	function __construct()
	{
		$this->table     = 'example_table';
		$this->fields    = Array('id', 'id_content', 'name');
		$this->relations = Array('id_content' => 'content_table.id');
		return $this;	
	}

}