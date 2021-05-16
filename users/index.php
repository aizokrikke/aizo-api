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
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/user.php";

error_reporting(E_ERROR);

$body = 'forbidden';
$status = 403;
$body = parseHeaders(true);

if ($body == 'ok') {
    $status = 200;

    Switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $params = $_REQUEST;
            $user = new user($params['id']);
            if (!empty($params['id'])) {
                $body = $user->getDetails();
            } else {
                $body = $user->list($params['filter']);
            }
            break;
        case 'POST':
            // voeg een nieuwe user toe
            if ($requestbody = getRequestBody()) {
                $user = new user();

                $result = $user->store($requestbody);
                if (is_array($result)) {
                    $status = 400;
                    $body = ['errorcode' => 100, 'message' => $result];
                }
            } else {
                $status = 400;
                $body = ['errorcode' => 1, 'message' => 'invalid input'];
            }


            break;
        case 'PUT':
            if ($requestbody = getRequestBody()) {
                $params = $_REQUEST;
                $user = new user($params['id']);
                $user->store($requestbody);
            } else {
                $status = 400;
                $body = ['errorcode' => 1, 'message' => 'invalid input'];
            }
            break;
        case 'PATCH':
            break;
        case 'DELETE':
            // to do: delete user
            break;
    }

}

out($body,$status);


?>

