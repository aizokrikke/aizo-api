<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/output.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/headers.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/user.php";

error_reporting(E_ERROR);
$body = 'forbidden';
$status = 403;
$body = parseHeaders();

if ($body == 'ok') {
    $entityBody = file_get_contents('php://input');
    $params = json_decode($entityBody, true);

    Switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $action = strtolower($params['action']);
            switch ($action) {
                case 'login':
                    if (empty($params['login']) || empty($params['password'])) {
                        $status = 403;
                        $body = ['errorcode' => 1, 'message' => 'invalid input'];
                    } else {
                        $user = new User();
                        if ($user->login($params['login'], $params['password'])) {
                            $status = 200;
                            $body = [
                                'token' => $user->getToken(),
                                'firstname' => $user->getFirstName(),
                                'lastname' => $user->getLastName(),
                                'role' => $user->getRole()
                            ];

                        } else {
                            $body = ['errorcode' => 2, 'message' => 'user not found'];
                        }
                    }
                    break;
                case 'logout':
                    $token = $params['token'];
                    if (empty($token)) {
                            $headers = getallheaders();
                            $token = $headers['Session-Key'];
                        }
                    if (empty($token)) {
                        $status = 400;
                        $body = ['errorcode' => $status, 'message' => 'invalid input'];
                    } else {
                        $session = new Session($token);
                        $message = $session->end();
                        $status = 200;
                        if ($message == 'success') {
                            $err = 0;
                        } else {
                            $err = 1;
                        }
                        $body = ['errorcode' => $err, 'message' => $message];
                    }
                    break;
                case 'token':
                        $client = $params['client'];
                        if (!empty($client)) {
                            $apiToken = new apiToken();
                            $apiToken->setClient($client);
                            $token = $apiToken->generate();
                            $body = ['client' => $client, 'token' => $token];
                        } else {
                            $status = 400;
                            $body = ['errorcode' => $status, 'message' => 'invalid input'];
                        }
                    break;
            };
    }

}

out($body, $status);
