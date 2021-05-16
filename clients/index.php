<?php

/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/output.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/input.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/headers.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/apiclient.php";

error_reporting(E_ERROR);

$body = 'forbidden';
$status = 403;
$body = parseHeaders(true);


if ($body == 'ok') {
    $status = 200;

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $params = $_REQUEST;
            $client = new apiClient($params['id']);
            $body = $client->list();
            break;
        case 'POST':
            // voeg een nieuwe client toe
            if ($requestbody = getRequestBody()) {
                $client = new apiClient();
                $out = $client->store($requestbody);
                if (is_array($out)) {
                    $body = ['errorcode' => 100, 'messages' => $out];
                    $status = 400;
                } else {
                    $body = ['messages' => $out];
                }
            } else {
                $status = 400;
                $body = ['errorcode' => 1, 'messages' => ['invalid input']];
            }


            break;
        case 'PUT':
            if ($requestbody = getRequestBody()) {
                $params = $_REQUEST;
                $client = new apiClient($params['id']);
                $client->store($requestbody);
            } else {
                $status = 400;
                $body = ['errorcode' => 1, 'messages' => ['invalid input']];
            }
            break;
        case 'PATCH':
            // to do
            break;
        case 'DELETE':
            // to do: delete client
            break;
    }

}

out($body, $status);
