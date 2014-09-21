<?php

class Main extends Controller {

	private $example;
	
	function __construct(){

		$this->example_model = $this->loadModel('Example_model');

	}

	function index(){
		$template = $this->loadView('main_view');
		$template->render();

		$example = $this->example_model;
		$example->set(Array('id_content', 'name'), Array('1','1'));
		$example->persist();
		
	}
    
}

?>
