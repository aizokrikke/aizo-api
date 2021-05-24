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
        }',
        '{ 
            "name": "project",
            "type": "integer",
            "length": "11",
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "unique": "false"
        }'
        ];
    private string $id = '';
    private int $project;
    private string $client = '';
    private array $defaultRecords = [ "client" => "root app", "project" => 1];

    public function __construct($id = '')
    {
        $this->model = new model('apiclient', $this->fieldsDef, $this->defaultRecords);
        $this->id = $id;
        $this->retrieve();
    }

    private function retrieve() {
        if (!empty($this->id)) {
            if ($client = $this->model->getOne(['id', 'client', 'project'], "`id` = '" . $this->id . "'")) {
                $this->project = $client['project'];
                $this->client = $client['client'];
            }
        }
    }

    public function project() {
        return $this->project;
    }

    public function name() {
        return $this->client;
    }

    public function id() {
        return $this->id;
    }

    public function list($search = '') {
        $condition = '';
        $fields = ['id', 'client'];
        if (!empty($search)) {
            $condition .= "`client` LIKE '%" . $search . "%'";
        }
        if (!empty($this->id)) {
            if (!empty($condition)) {
                $condition .= " && ";
            }
            $condition .= "`id` = '" . $this->id . "'";
            $fields[] = 'project';
        }
        return $this->model->list($condition, $fields);

    }
    public function store($body = []) {
        $result = $this->model->validateInput($body);
        if (!is_array($result)) {
            $this->model->insert(['client', 'project'],[[$body['client'], $body['project']]]);
            $this->id = $this->model->insert_id();

            return $this->id;
        }

        return $result;
    }
}
