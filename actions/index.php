<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/user.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/api.php";

error_reporting(E_ERROR);

class indexPage {

    private $body = 'forbidden';
    private $status = 403;
    private api $api;

    public function __construct() {
        $this->api = new api('actions', false, false);
        $this->handleRequest();
        $this->api->out($this->body, $this->status);
    }

    private function handleRequest() {
        Switch ($this->api->method()) {
            case 'POST':
                $this->handlePostRequest();
                break;
        }
    }


    private function handlePostRequest(): void {
        switch (strtolower($this->api->getBodyParam('action'))) {
            case 'login':
                $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'token':
                $this->createClientToken();
                break;
        }
    }

    private function login() {
        if (empty($this->api->getBodyParam('login')) || empty($this->api->getBodyParam('password'))) {
            $this->status = 403;
            $this->body = ['errorcode' => 1, 'message' => 'invalid input'];
        } else {
            $user = new User();
            if ($user->login($this->api->getBodyParam('login'), $this->api->getBodyParam('password'))) {
                $this->status = 200;
                $this->body = [
                    'token' => $user->getToken(),
                    'firstname' => $user->getFirstName(),
                    'lastname' => $user->getLastName(),
                    'role' => $user->getRole()
                ];

            } else {
                $this->body = ['errorcode' => 2, 'message' => 'user not found'];
            }
        }
    }

    private function logout(): void {
        $token = $this->api->getBodyParam('token');
        if (empty($token)) {
            $token = $this->api->getHeader('Session-Key');
        }
        if (empty($token)) {
            $this->status = 400;
            $this->body = ['errorcode' => $this->status, 'message' => 'invalid input'];
        } else {
            $session = new Session($token);
            $message = $session->end();
            $this->status = 200;
            if ($message == 'success') {
                $err = 0;
            } else {
                $err = 1;
            }
            $this->body = ['errorcode' => $err, 'message' => $message];
        }
    }

    private function createClientToken() {
        if ($this->api->getProject() == 1) {
            $client = $this->api->getBodyParam('client');
            if (!empty($client)) {
                $apiToken = new apiToken();
                $apiToken->setClient($client);
                $token = $apiToken->generate();
                $this->body = ['client' => $client, 'token' => $token];
            } else {
                $this->status = 400;
                $this->body = ['errorcode' => $this->status, 'message' => 'invalid input'];
            }
        } else {
            $this->status = 400;
            $this->body = ['errorcode' => $status, 'message' => 'not allowed'];
        }
    }
}

$page = new indexPage();
