<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "token.php";

class apiClient {
    private model $model;
    private $fieldsDef = [
        '{ 
            "name": "client",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "mandatory": "true",
            "unique": "true"
        }'
        ];
    private string $id = '';

    public function __construct($id = '')
    {
        $this->model = new model('apiclient', $this->fieldsDef);
        $this->id = $id;
    }


    public function list($search = '') {
        $condition = '';
        if (!empty($search)) {
            $condition .= "`client` LIKE '%" . $search . "%'";
        }
        if (!empty($this->id)) {
            $condition .= " && `id` = '" . $this->id . "'";
        }
        return $this->model->list($condition, ['client']);

    }
    public function store($body = []) {
        $result = $this->model->validateInput($body);
        if (!is_array($result)) {
            $this->model->insert(['client'],[[$body['client']]]);
            $this->id = $this->model->insert_id();

            return $this->id;
        }

        return $result;
    }
}
