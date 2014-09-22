<?php

class Controller {

	public function loadModel($name)
	{
		if (!class_exists($name)){
			require(APP_DIR .'models/'. strtolower($name) .'.php');
			$model = new $name;
			$model->pdo_connect();
			return $model;
		}
	}

	public function loadView($name)
	{
		$view = new View($name);
		return $view;
	}

	public function redirect($loc)
	{
		global $config;
		header('Location: '. $config['base_url'] . $loc);
	}

	public function request($par = null)
	{
		if(is_array($par)){
			$request = Array();
			foreach($par as $field)
				$request[$field] = isset($_REQUEST[$field]) ? $_REQUEST[$field] : null;

			return $request;
		}else if(is_null($par))
			return $_REQUEST;
		else
			return isset($_REQUEST[$par]) ? $_REQUEST[$par] : null;	

	}

}