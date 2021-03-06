<?php

/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/apiclient.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/api.php";

error_reporting(E_ERROR);

class indexPage {

    private $body = 'forbidden';
    private $status = 403;
    private api $api;

    public function __construct() {
        $this->api = new api('clients', true, false);
        if ($this->api->getProject() == 1) {
            $this->handleRequest();
        } else {
            $this->body = 'not allowed';
            $this->status = 403;
        }

        $this->api->out($this->body, $this->status);
    }

    private function handleRequest() {
        switch ($this->api->method()) {
            case 'GET':
                $this->handleGetRequest();
                break;
            case 'POST':
                $this->handlePostRequest();
                break;
            case 'PUT':
                $this->handlePutRequest();
                break;
            case 'PATCH':
                $this->handlePatchRequest();
                break;
            case 'DELETE':
                $this->handleDeleteRequest();
                break;
        }
    }

    private function handleGetRequest() {
        $client = new apiClient($this->api->getRequestParam('id'));
        $this->body = $client->list();
        if (empty($this->body)) {
            $this->body = ['errorcode' => 400, 'messages' => 'Client not found'];
        }
        $this->status = 200;
    }

    private function handlePostRequest() {
        // voeg een nieuwe client toe
        if ($requestbody = $this->api->getBodyParams()) {
            $client = new apiClient();
            $out = $client->store($requestbody);
            if (is_array($out)) {
                $this->body = ['errorcode' => 100, 'messages' => $out];
                $this->status = 400;
            } else {
                $this->body = ['errorcode' => 0, 'id' => $out];
                $this->status = 200;
            }
        } else {
            $this->status = 400;
            $this->body = ['errorcode' => 1, 'messages' => ['invalid input']];
        }
    }


    private function handlePatchRequest() {
        $this->status = 403;
        $this->body = ['errorcode' => 1, 'messages' => ['method not allowed']];
    }

    private function handlePutRequest() {
        if ($requestbody = $this->api->getBodyParams()) {
            $client = new apiClient($this->api->getRequestParam('id'));
            $client->store($requestbody);
        } else {
            $this->status = 400;
            $this->body = ['errorcode' => 1, 'messages' => ['invalid input']];
        }
    }

    private function handleDeleteRequest() {
        $this->status = 403;
        $this->body = ['errorcode' => 1, 'messages' => ['method not allowed']];
    }
}

new indexPage();
