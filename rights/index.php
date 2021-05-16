<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/output.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/right.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/headers.php";
error_reporting(E_ERROR);

$body = parseHeaders(true);


Switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // get list of rights
        $inputBody = file_get_contents('php://input');
        $input = json_decode($inputBody, true);

        $right = new Right();
        $status = 200;

        out($right->list(), $status);

        break;

    case 'POST':
        // add or update right
        $inputBody = file_get_contents('php://input');
        $input = json_decode($inputBody, true);
        $headers = getallheaders();
        print_r($headers);

        $right = new Right();
        $out = json_encode($right->list());
        $status = 200;

        out($out, $status);

        break;


    default:
        out('Forbidden',403);
        break;
}

?>
