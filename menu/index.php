<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/output.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/menu.php";

error_reporting(E_ALL);


Switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $menu = new Menu();
        $status = ($main = $menu->getTree());
        out($main, $status);
        break;
    default:
        out('',401);
        break;
}




?>
