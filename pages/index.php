<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/output.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/pages.php";

error_reporting(E_ERROR);

// get page id

Switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // if page id set return meta data of page

        // else show list of available pages

        out($main, $status);

        break;
    default:
        out('Forbidden',403);
        break;
}






?>
