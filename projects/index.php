<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/api.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/project.php";

error_reporting(E_ERROR);

class indexPage {

    private $body = 'forbidden';
    private $status = 403;
    private api $api;

    public function __construct() {
        $this->api = new api('projects', true, false);
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
        $project = new project($this->api->getRequestParam('id'));
        $this->body = $project->list($this->api->getRequestParam('filter'));
        $this->status = 200;
    }

    private function handlePostRequest() {
        // voeg een nieuw project toe
        $body = $this->api->getBodyParams();
        if (!empty($body)) {
            $project = new project();
            $out = $project->store($body);
            if (is_array($out)) {
                $this->body = ['errorcode' => 100, 'messages' => $out];
                $this->status = 400;
            } else {
                if (is_numeric($out)) {
                    $this->body = ['id' => $out];
                    $this->status = 200;
                } else {
                    $this->body = ['messages' => $out];
                    $this->status = 400;
                }
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
        $this->status = 403;
        $this->body = ['errorcode' => 1, 'messages' => ['method not allowed']];
    }

    private function handleDeleteRequest() {
        $this->status = 403;
        $this->body = ['errorcode' => 1, 'messages' => ['method not allowed']];
    }
}

new indexPage();


;
