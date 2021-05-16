<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/apitoken.php";
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/session.php";

function parseHeaders($verifySession = false) {
    $headers = getallheaders();
    $token = new apiToken($headers['Api-Key']);

    if (!$token->verify()) {
        return 'Invalid API key';
    }

    if ($verifySession) {
        $session = new Session($headers['Session-Key']);
        if (empty($session->verifySession())) {
            return 'Session expired';
        }
    }

    return 'ok';
}
