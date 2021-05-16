<?php
function getRequestBody() {
    $requestbody = json_decode(file_get_contents('php://input'),true);
    if (empty($requestbody)) {
        return false;
    }
    return $requestbody;
}
