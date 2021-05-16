<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/output.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/session.php";

error_reporting(E_ERROR);

Switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':

        $inputBody = file_get_contents('php://input');
        $input = json_decode($inputBody, true);

        if (!empty($input)) {
            $session = new Session($input['token']);
            $token = $session->verifySession();

            if (empty($token))  {
                $out = [
                    'statuscode' => 1,
                    'message' => 'token invalid'
                ];
            } else {
                $out = [
                    'statuscode' => 0,
                    'message' => 'token valid'
                ];
            }
            $status = 200;
        } else {
            $status = 400;
            $out = [
                'statuscode' => $status,
                'message' => 'invalid input'
            ];
        }

        out($out, $status);

        break;
    default:
        out('Forbidden',403);
        break;
}

?>
