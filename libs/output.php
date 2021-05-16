<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 11:33
 */

/**
 * do the API output
 *
 * @param array $body
 * @param int $status
 */
function out($body = '', $status = 200, $allowed_origin = '*') {

    header("Access-Control-Allow-Origin: $allowed_origin");
    header("Content-Type: application/json; charset=UTF-8");

    http_response_code($status);
    if (is_array($body)){
        echo json_encode($body);
    } else {
        echo $body;
    }

}
