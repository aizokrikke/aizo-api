<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";

class apiLogItem
{
    private $model;
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
            "name": "endpoint",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "mandatory": "true"
        }',
        '{ 
            "name": "method",
            "type": "string",
            "length": "10",
            "index": "true",
            "null": "false",
            "mandatory": "true"
        }',
        '{ 
            "name": "requestbody",
            "type": "text",
            "index": "false",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "headers",
            "type": "text",
            "index": "false",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "request",
            "type": "text",
            "index": "false",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "requestparams",
            "type": "text",
            "index": "false",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "IP",
            "type": "string",
            "length": "50",
            "index": "true",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "status",
            "type": "integer",
            "length": "11",
            "index": "true",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "client",
            "type": "integer",
            "length": "11",
            "index": "true",
            "null": "false",
            "mandatory": "false"
        }',
        '{ 
            "name": "session",
            "type": "string",
            "length": "200",
            "index": "true",
            "null": "false",
            "mandatory": "false"
        }'
    ];

    public function __construct($endpoint = '', $request = '', $method = 'GET', $requestParams = [], $headers = [], $body = '', $status = 0, $time = '',
                                $ip = '', $client = 0, $sessiontoken = '')
    {
        if (empty($time)) {
            $time = gmdate('Y-m-d H:i:s');
        }
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $this->model = new model('apilogs', $this->fieldsDef);
        $this->write($time, $ip, $endpoint, $method, $request, $requestParams, $headers, $body, $status,
            $client, $sessiontoken);
    }

    private function delete($condition)
    {
        return $this->model->delete($condition);
    }


    public function write($time = '', $ip = '', $endpoint = '', $method = 'GET', $request = '', $requestParams = [],
                          $headers = [], $body = '', $status = 0, $client = 0, $sessiontoken = '')
    {
        $headersJson = json_encode($headers);
        $paramsJson = json_encode($requestParams);
        $logitem = [$time, $ip, $endpoint, $request, $method, $headersJson, $paramsJson, $body, $status, $client,
            $sessiontoken];
        return $this->model->insert(['time', 'ip','endpoint', 'request', 'method', 'headers', 'requestparams',
            'requestbody', 'status', 'client', 'session'],
            [$logitem]);
    }

}
