<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/json.php";

class Right {
    private $model;
    private array $fieldsDef = [
        '{ 
            "name": "name",
            "type": "string",
            "length": "150",
            "index": "true",
            "null": "false",
            "mandatory": "true",
            "default": ""
        }',
        '{ 
            "name": "description",
            "type": "text",
            "index": "false",
            "null": "false",
            "mandatory": "false"
        }'
        ];
    private $name;
    private $id;
    private $description;


    public function __construct($duration = 0) {
        $this->model = new model('right', $this->fieldsDef);
    }

    public function list() {
      $list = array();
      $result = $this->model->get(['id','name','description']);
      while ($row = $this->model->assoc($result)) {
          $list[] = $row;
      }
        return '{ "rights": ' . ArrayToJSON($list) . ' }';
    }

}
