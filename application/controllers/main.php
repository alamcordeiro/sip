<?php

class Main extends Controller {

    private $example;

    function __construct()
    {
        $this->example_model = $this->loadModel('Example_model');
    }

    function index()
    {
        $template = $this->loadView('main_view');
        $template->render();

        $example = $this->example_model;

        $example->reset(); // TRUNCATE TABLE

        $example->set(Array('id_content' => 1, 'name' => 'SIP'));
        $example->persist(); // INSERT

        $example->find(Array('id_content' => 1));
        $example->set(Array('name' => 'SIP 2'));
        $example->persist(); // UPDATE

    }

}