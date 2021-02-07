<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/db.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "apiToken.php";

class apiUser
{
    // database connection and table name
    private $db;
    private $table_name = "apiUsers";
    private $newToken;

    // object properties
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $token;

    // constructor
    public function __construct(){
        $this->db = new database();
        $tokenGenerator = new apiToken();
        $this->newToken = $tokenGenerator->generate();
    }

    function create(){
        // user exists?
        if ($this->emailExists($this->email)) {
            return false;
        }

        // insert query

        $query = "INSERT INTO " . $this->table_name . "
            SET
                firstname = '". $this->db->esc($this->firstname) ."',
                lastname = '" . $this->db->esc($this->lastname) . "',
                email = '" . $this->db->esc($this->email) . "',
                token = '" . $this->db->esc($this->newToken) . "'";

        if ($this->db->query($query)) {
            return $this->newToken;
        };

        return false;
    }

    public function emailExists ($email) {
        $query = "SELECT id FROM " . $this->table_name . "
            WHERE email = '" . $this->db->esc($email) . "'";
        $this->db->query($query);
        return ($this->db->num_rows()>0);
    }

    public function login($token) {

    }

}
