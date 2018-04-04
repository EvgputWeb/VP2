<?php

require_once APP . '/views/View.php';

abstract class Controller
{
    protected $model;
    protected $view;

    public function __construct()
    {
        $this->view = new View();
    }
}
