<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";

class errorLogItem
{
    private $model;
    private $tableDefinition = "
      `time` datetime NOT NULL DEFAULT current_timestamp(),
      `class` varchar(250) NOT NULL,
      `message` varchar(2000) NOT NULL,
      `type` enum('error','warning') NOT NULL DEFAULT 'error'
    ";

    private array $fieldsDef = [
        '{ 
            "name": "time",
            "type": "datetime",
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "default": "current_timestamp()"
        }',
        '{ 
            "name": "class",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "message",
            "type": "string",
            "length": "2000",
            "index": "false",
            "null": "false",
            "mandatory": "true"
        }',
        '{ 
            "name": "type",
            "type": "enum",
            "options": ["error", "warning"],
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "default": "error"
        }'
        ];

    public function __construct()
    {
        $this->model = new model('errors', $this->fieldsDef);
    }

    public function delete($condition) {
        return $this->model->delete($condition);
    }

    public function esc($in) {
       return $this->model->esc($in);
    }

    public function insert($fields, $values) {
        return $this->model->insert($fields, $values);
    }

}
