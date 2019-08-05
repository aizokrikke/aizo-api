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

$menu = new Menu();
$main = $menu->getTree();


header("Access-Control-Allow-Origin: *");
out($main);

?>
