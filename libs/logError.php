<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "objects/errorlogitem.php";


class logError
{
    private $errors;
    private $class = 'undefined';
    private $message = '';
    private $type = 'error';
    private $verbose = false;
    private $time;
    private $cachingtime = 14;          // in days

    public function __construct($message, $class, $type = 'error', $verbose = false) {
        if (!empty($message)) {
            $this->errors = new errorLogItem();

            $this->cleanup();

            $this->class = $class;
            $this->message = $message;
            if (strtolower($type) == 'error' || strtolower($type) == 'warning') {
                $this->type = $type;
            }

            $this->verbose = $verbose;
            $this->time = date("Y-m-d H:i:s");
            $this->processError();
        }
    }

    private function processError() {
        $this->message = $this->errors->esc($this->message);
        $this->class = $this->errors->esc($this->class);
        $fields = ['time','class','message', 'type'];
        $values = array([$this->time, $this->class, $this->message, $this->type]);
        $this->errors->insert($fields, $values);

        if ($this->verbose || $this->type == 'error') {
            echo $this->time . " ERROR in " .$this->class . ": " . $this->message;
        }

        if ($this-> type == 'error') {
            exit();
        }
    }

    private function cleanup() {
        $cutoff = date("Y-m-d H:i:s", time() - ($this->cachingtime * 60 * 60 * 24));
        $this->errors->delete("time<='$cutoff'");
    }


}
