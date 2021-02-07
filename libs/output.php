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
function out($body = '', $status = 200) {

    header("Content-Type: application/json; charset=UTF-8");

    http_response_code($status);
    if (!empty($body) {
        echo json_encode($body);
    }

}
