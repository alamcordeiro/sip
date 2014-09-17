<?php

class Example_model extends Model {

	public function map(){

		$this->map->table  		= 'example_table';
		$this->map->fields 		= Array('id', 'id_content', 'name');
		$this->map->relations   = Array(
										'id_content' => 'content_table.id'
										);
		return $this->map;
	
	}
	
}

?>
