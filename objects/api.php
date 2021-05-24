<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "apitoken.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "apilogitem.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "apiclient.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "session.php";
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/logError.php";

class api {

    private array $headers = [];
    private string $requestBody = '';
    private array $bodyParams = [];
    private apiClient $client;
    private Session $session;
    private string $sessionToken = '';
    private string $requestString = '';
    private array $requestParams = [];
    private int $status = 0;
    private string $name = '';
    private string $time = '';
    private string $method = 'GET';
    private bool $logbody = true;

    public function __construct($name = '', $sessionverify = false, $logbody = true)
    {
        $this->name = $name;
        $this->logbody = $logbody;
        $this->parseHeaders();
        $this->parseRequestBody();
        $this->getCallDetails();
        $this->collectRequestParams();
        if ($sessionverify) {
            $this->verifySession();
        }
    }
    private function getCallDetails(): void {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->requestString = $_SERVER['REQUEST_URI'];
        $this->time = gmdate('Y-m-d H:i:s');
        $this->method = $_SERVER['REQUEST_METHOD'];
}


    private function parseRequestBody(): void {
        $this->requestBody = file_get_contents('php://input');
        if (!empty($this->requestBody)) {
            $this->bodyParams = json_decode($this->requestBody, true);
        }
    }

    private function collectRequestParams(): void {
        $this->requestParams = $_REQUEST;
    }


    private function parseHeaders(): void {
        $this->headers = getallheaders();
        $token = new apiToken($this->getHeader('Api-Key'));
        if (!$token->verify()) {
            $this->errorExit('Invalid API Key ');
        }
        $this->client = new apiClient($token->getClient());
    }

    public function verifySession() {
        $token = $this->getHeader('Session-Key');
        $this->session = new Session($token);
        if (empty($this->session->verifySession())) {
            $this->errorExit('Invalid Session Key ');
        }
        $this->sessionToken = $this->session->getToken();
    }

    private function errorExit($message) {
        new logError($message,'api', 'warning');
        $this->out($message, 403);

        exit();
    }

    public function method(): string {
        return $this->method;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getHeader($key) {
        $out = '';
        if (!empty($this->headers[$key])) {
            $out =$this->headers[$key];
        }

        return $out;
    }

    private function getRequestBody() {
        return $this->requestBody;
    }

    public function getBodyParams() {
        if (!empty($this->bodyParams)) {
            return $this->bodyParams;
        }
        return false;
    }

    public function getBodyParam($key) {
        $out = '';
        if (!empty($this->bodyParams[$key])) {
            $out =$this->bodyParams[$key];
        }

        return $out;
    }

    public function getRequestParam($key) {
        $out = '';
        if (!empty($this->requestParams[$key])) {
            $out =$this->requestParams[$key];
        }

        return $out;
    }

    public function log() {
        $body = '';
        if ($this->logbody) {
            $body = $this->getRequestBody();
        }
        $client = 0;
        if (!empty($this->client)) {
            $client = $this->client->id();
        }

        return new apiLogItem($this->name, $this->requestString, $this->method(), $this->requestParams,
            $this->getHeaders(), $body, $this->status, '', '', $client, $this->sessionToken);
    }

    public function out($body = '', $status = 200, $allowed_origin = '*') {
        $this->status = $status;
        $this->log();

        header("Access-Control-Allow-Origin: $allowed_origin");
        header("Content-Type: application/json; charset=UTF-8");

        http_response_code($status);
        if (is_array($body)){
            echo json_encode($body);
        } else {
            echo $body;
        }
    }

    public function getProject() {
        return $this->client->project();
    }
}


