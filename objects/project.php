<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/json.php";

class Project
{
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
        }',
        '{ 
            "name": "key",
            "type": "string",
            "length": "64",
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "default": "",
            "unique": "true"
        }'
        ];
    private array $defaultRecords = [ "name" => "root", "description" => "root project", "key" => "root"];

    private string $name = '';
    private string $id = '';
    private string $description = '';
    private string $key = '';


    public function __construct($id)
    {
        $this->id = $id;
        $this->model = new model('project', $this->fieldsDef, $this->defaultRecords);
    }

    public function list($filter)
    {
        if (!empty($this->id)) {
            $condition = "`id` = " . $this->id;
        } else {
            $condition = '`id` > 1';
        }
        $list = array();
        if (!empty($filter)) {
            $condition .= " AND `name` LIKE '%" . $filter . "%' || `key` LIKE '%" . $filter
            . "%'";
        }
        $result = $this->model->get(['id', 'name', 'description', 'key'], $condition);
        while ($row = $this->model->assoc($result)) {
            $list[] = $row;
        }
        return '{ "projects": ' . ArrayToJSON($list) . ' }';
    }

    public function store($input, $all = true) {

        $result = $this->model->validateInput($input);
        if (!is_array($result)) {
            $fields = [];
            foreach ($input as $key => $value) {
                $fields[] = $key;
            }

            if (empty($this->id)) {
                $this->setName($input['name']);
                $this->setKey($input['key']);
                if (!empty($input['description'])) {
                    $this->setDescription($input['description']);
                }

                return $this->create();
            } else {
                // update
            }
        } else {
            return $result;
        }
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function create(){
        $fields = ['name', 'key', 'description'];
        $values = array([$this->name, $this->key, $this->description, ]);

        if ($this->model->insert($fields, $values)) {
            return $this->model->insert_id();
        }
        return "error creating project";
    }
}
