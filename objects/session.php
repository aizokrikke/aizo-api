<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "token.php";

class Session {
    private model $model;
    private array $fieldsDef = [
        '{ 
            "name": "user",
            "type": "integer",
            "length": "20",
            "index": "true",
            "null": "false",
            "mandatory": "true"
        }',
        '{ 
            "name": "token",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "mandatory": "true",
            "default": ""
        }',
        '{ 
            "name": "ip",
            "type": "string",
            "length": "16",
            "index": "false",
            "null": "false",
            "mandatory": "false",
            "default": ""
        }',        '{ 
            "name": "validuntill",
            "type": "datetime",
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "default": "current_timestamp()"
        }'
        ];

    private int $sessionDuration = 30*60;        // 30 minuten
    private string $user = '';
    private string $id = '';
    private string $token = '';
    private string $timelimit;
    private string $time;

    public function __construct($token = '', $duration = 0) {
        $this->model = new model('session', $this->fieldsDef);
        if ($duration > 0) {
            $this->sessionDuration = $duration;
        }
        $this->time = Date('Y-m-d H:i:s',mktime());
        $this->timelimit = Date('Y-m-d H:i:s',mktime() + $this->sessionDuration);
        $this->token = $token;

    }

    public function setUser($user): void {
        $this->user = $user;
    }

    public function start(): string {
        // eerst checken of de sessie al draait
        if  ($this->sessionRunning()) {
            // er is nog een actieve sessie
            $this->updateValidity();
        } else {
            // start nieuwe sessie
            $this->newSession();
        }

        return $this->id;
    }

    private function newToken() {
        $tokengenerator = new token();
        $unique = false;
        while (!$unique) {
            $this->token = $tokengenerator->generate();
            $result = $this->model-> get('*', "`token` = '$this->token'");
            $unique = ($this->model->num_rows($result) == 0);
        }
        return $this->token;
    }

    private function newSession() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $this->newToken();
        $this->model->insert(['user','token','ip'],[[$this->user, $this->token, $ip]]);
        $this->id = $this->model->insert_id();
        $this->updateValidity();
    }

    private function sessionRunning() {
        if (!empty($this->token)) {
            $condition = "`token` = '$this->token'";
        } else {
            $condition = "`user` = '$this->user'" ;
        }
        $condition .= " && `validuntill` > '".
                Date('Y-m-d H:i:s',mktime()) . "'";
        $result = $this->model->get(['id','token'], $condition);
        $session = $this->model->assoc($result);
        if (!empty($session['token'])) {
            $this->token = $session['token'];
        }
        if (!empty($session['id'])) {
            $this->id = $session['id'];
        }

        return !empty($this->id);
    }

    private function updateValidity() {
        $this->model->update(['validuntill'], [$this->timelimit],
            "`token` = '" . $this->token . "'");
    }

    public function end() {
        if ($this->sessionRunning()) {
            if ($this->model->update(['validuntill'], [$this->time],
            "`token` = '$this->token'")) {
                $out = 'success';
            } else {
                $out = 'error';
            }
        } else {
            $out = 'session not found';
        }
        return $out;
    }

    public function getToken() {
        return $this->token;
    }

    public function verifySession() {
        $condition = "`token` = '$this->token' && `validuntill` > '" . $this->time . "'";
        $result = $this->model->get(['id', 'token', 'user'], $condition);
        $session = $this->model->assoc($result);
        if (!empty($session['token'])) {
            $this->token = $session['token'];
            $this->setUser($session['user']);
            $this->updateValidity();
        } else {
            $this->token = '';
        }

        return $this->token;
    }

}
