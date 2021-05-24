<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/api.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/user.php";

error_reporting(E_ERROR);

class indexPage
{

    private $body = 'forbidden';
    private $status = 403;
    private api $api;

    public function __construct()
    {
        $this->api = new api('users', true, false);
        $this->handleRequest();
        $this->api->out($this->body, $this->status);
    }

    private function handleRequest()
    {
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
        $id = $this->api->getRequestParam('id');
        $user = new user($id, $this->api->getProject());
        $this->status = 200;
        if (!empty($id)) {
            $this->body = $user->getDetails();
        } else {
            $this->body = $user->list($this->api->getRequestParam('filter'),
                $this->api->getRequestParam('project'));
        }
    }

    private function  handlePostRequest() {
        // voeg een nieuwe user toe
        if ($requestbody = $this->api->getBodyParams()) {
            $user = new user('', $this->api->getProject());
            $result = $user->store($requestbody);
            if (is_array($result)) {
                $this->status = 400;
                $this->body = ['errorcode' => 100, 'message' => $result];
            } else {
                $this->status = 200;
                $this->body = ['errorcode' => 0, 'message' => 'User added'];
            }

        } else {
            $this->status = 400;
            $this->body = ['errorcode' => 1, 'message' => 'invalid input'];
        }
    }

    private function handlePutRequest() {
        if ($requestbody = $this->api->getBodyParams()) {
            $user = new user($api->getRequestParam('id'), $this->api->getProject());
            $user->store($requestbody);
        } else {
            $this->status = 400;
            $this->body = ['errorcode' => 1, 'message' => 'invalid input'];
        }
    }

    private function handleDeleteRequest() {
        $this->status = 403;
        $this->body = ['errorcode' => 1, 'messages' => ['method not allowed']];
    }

    private function handlePatchRequest() {
        $this->status = 403;
        $this->body = ['errorcode' => 1, 'messages' => ['method not allowed']];
    }
}

new indexPage();





