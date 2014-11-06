<?php

class View {

    private $pageVars = array();
    private $template;

    public function __construct($template)
    {
        $this->template = APP_DIR .'views/'. $template .'.php';
    }

    public function set($var, $val = null, $escape = true)
    {
        if(is_array($var))
            foreach ($var as $name => $val)
                $this->pageVars[$name] = $escape ? htmlentities($val) : $val;
        else
            $this->pageVars[$var] = $escape ? htmlentities($val) : $val;

        return $this;
    }

    public function render()
    {
        extract($this->pageVars);

        ob_start();
        require($this->template);
        echo ob_get_clean();
    }

}