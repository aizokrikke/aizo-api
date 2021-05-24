<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:28
 */

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/menu.php";
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/api.php";

error_reporting(E_ERROR);

$body = 'forbidden';
$status = 403;

$api = new api('menuitems');

Switch ($api->method()) {
    case 'GET':
        $status = 200;
        $menu = new Menu();
        $body = $menu->getTree();
        break;

    case 'POST':
        $api->verifySession();
        $status = 200;
        $entityBody = file_get_contents('php://input');
        $params = json_decode($entityBody, true);

        if (!empty($api->getBodyParam('name'))) {
            $menu = new Menu();
            $params = $api->getBodyParams();
            if (!$main = $menu->updateOrAdd(
                $params['name'],
                $params['display'],
                $params['parent'],
                $params['order'],
                $params['action'],
                $params['content'],
                $params['show'],
                $params['external']
            )) {
                $body = ['errorcode' => 1, 'message' => 'Could not process item'];
            }
        } else {
            $body = ['errorcode' => 2, 'message' => 'Name is required'];
        }
        break;
}

$api->out($body, $status);






?>
