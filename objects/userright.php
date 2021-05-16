<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";

class UserRights {
    private $model;
    private $tableDefinition = "                      
        `user` int(11) NOT NULL, 
        `right` int(11) NOT NULL,               
        INDEX `token` (`user`)
    ";

    private $user;
    private $id;
    private $right;


    public function __construct($duration = 0) {
        $this->model = new model('right', $this->tableDefinition);;
    }


}

