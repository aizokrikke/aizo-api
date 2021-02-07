<?php

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "db.config.php";

class database {

    private $result;
    private $database;

    public function __construct()
    {
        $this->connect();
    }

    public function connect() {
        global $server, $username, $password, $name;

        $this->database = new mysqli($server, $username, $password, $name);
        if ($this->database->connect_error) {
            Echo "Website is unavailable.\n\n";
            echo "Error: Failed to make a MySQL connection, here is why: \n";
            echo "Errno: " . $this->database->connect_errno . "\n";
            echo "Error: " . $this->database->connect_error . "\n";
            exit();
        }
    }


    public function setCoding($code = "utf8") {
        $this->database->set_charset($code);
    }

    public function query($q) {
        $this->result = $this->database->query($q);

        if (!empty($this->database->error)) {
            die("database error: " . $this->database->error);
        }

        return $this->result;
    }

    public function getResult() {
        return $this->result;
    }

    public function getResource($in) {
        if (is_string($in)) {
            $out = $this->query($in);
        } else {
            $out = $in;
        }

        return $out;
    }

    public function row($in = NULL) {
        if (!empty($in)) {
            $result = $this->getResource($in);
        } else {
            $result = $this->result;
        }

        return $result->fetch_row();
    }


    public function assoc($in = NULL) {
        if (!empty($in)) {
            $result = $this->getResource($in);
        } else {
            $result = $this->result;
        }

        return $result->fetch_assoc();
    }

    public function all($in) {
        if (!empty($in)) {
            $result = $this->getResource($in);
        } else {
            $result = $this->result;
        }

        return $result->fetch_all();
    }

    public function esc($in) {

        return mysqli_real_escape_string($this->database, $in);
    }

    public function num_rows($resource = NULL) {
        if (empty($resource)) {
            $out = $this->result->num_rows;

        } else {
            $out = $resource->num_rows;
        }

        return $out;
    }

    public function insert_id() {

        return $this->database->insert_id;
    }

}

?>
