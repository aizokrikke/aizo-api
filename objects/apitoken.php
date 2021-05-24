<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "token.php";

class apiToken {
    private model $model;
    private array $fieldsDef = [
        '{ 
            "name": "client",
            "type": "integer",
            "length": "16",
            "index": "true",
            "null": "false",
            "mandatory": "true"
        }',
        '{ 
            "name": "token",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "mandatory": "true"
        }'
        ];
    private array $defaultRecords = [ "client" => 1, "token" => "start"];
    private string $token = '';
    private int $client = 0;

    public function __construct($token = '', $duration = 0)
    {
        $this->model = new model('apitoken', $this->fieldsDef, $this->defaultRecords);
        $this->token = $token;
        $this->verify();
    }

    public function setClient($client) {
        $this->client = $client;
    }

    public function getClient() {
        return $this->client;
    }

    public function generate() {
        $tokengenerator = new token();
        $token = $tokengenerator->generate();
        $this->model->delete("client = '" . $this->client . "'");
        $this->model->insert(['client','token'], [[$this->client, $token]]);

        return $token;
    }

    public function verify(): bool {
        $allowed = (!empty($this->token));
        if ($allowed) {
            $condition = "`token` = '" . $this->token . "'";
            $result = $this->model->getOne(['client'], $condition);
            if (!empty($result)) {
                $this->client = $result['client'];
            }
            $allowed = (!empty($this->client));
        }

        return $allowed;
    }
}
