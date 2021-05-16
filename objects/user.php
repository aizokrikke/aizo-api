<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/logError.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "session.php";

class User
{
    private $model;
    private $fielddef = [
        '{ 
            "name": "firstname",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "manditory": "false"
        }',
        '{ 
            "name": "lastname",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "manditory": "true"
        }',
        '{ 
            "name": "email",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "validator": "email",
            "manditory": "false"
        }',
        '{ 
            "name": "role",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "manditory": "false"
        }',
        '{ 
            "name": "login",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "manditory": "false"
        }',        '{ 
            "name": "passwordhash",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "manditory": "true"
        }'
        ];

    // object properties
    private $id;
    private $firstname = '';
    private $lastname = '';
    private $email = '';
    private $role = '';
    private $login = '';
    private $password = '';
    private $passwordHash = '';
    private $session;

    // constructor
    public function __construct($id = ''){
        $this->model = new model('users', $this->fielddef);

        $this->session = new Session();
        if (!empty($id)) {
            $this->getUserbyId($id);
        }
    }

    public function store($input, $all = true) {

        if ($this->model->validateInput($input)) {
            $fields = [];
            foreach ($input as $key => $value) {
                $fields[] = $key;

            }

            if (empty($this->id)) {
                // new user so insert
                if (!empty($input['firstname'])) {
                    $this->setFirstName($input['firstname']);
                };
                $this->setLastName($input['lastname']);
                $this->setEmail($input['email']);
                if (!empty($input['role'])) {
                    $this->setRole($input['role']);
                }
                $this->setLogin($input['login']);
                $this->setPassword($input['password']);

                return $this->create();
            } else {
                // update
            }
        }
    }

    public function setFirstName($in) {
        $this->firstname = $in;
    }

    public function getFirstName() {
        return $this->firstname;
    }

    public function setLastName($in) {
        $this->lastname = $in;
    }

    public function getLastName() {
        return $this->lastname;
    }

    public function setRole($in) {
        $this->role = $in;
    }

    public function getRole() {
        return $this->role;
    }

    public function getToken() {
        return $this->session->getToken();
    }

    public function setEmail($in) {
        $this->email = $in;
    }

    public function setLogin($in) {
        $this->login = $in;
    }

    public function setPassword($in) {
        $this->password = $in;
        $this->passwordHash = password_hash($in, PASSWORD_DEFAULT);
    }

    public function getDetails() {
        $out = [];
        if (!empty($this->id)) {
          $out = array('firstname' => $this->firstname, 'lastname' => $this->lastname, 'email' => $this->email,
              'login' => $this->login, 'role' => $this->role);
        }
        return $out;
    }

    public function create(){
        // user exists?
        if ($this->getUserbyEmail($this->email)) {
            new logError('emailadres is al in gebruik', 'user', 'warning');
            $errors[] = 'emailadres is al in gebruik';
            return $errors;
        }
        $fields = ['firstname', 'lastname', 'email', 'role','login', 'passwordhash'];
        $values = array([$this->firstname, $this->lastname, $this->email, $this->role,
                    $this->login, $this->passwordHash]);

        return $this->model->insert($fields, $values);
    }

    public function getUserbyEmail ($email) {
        $result = $this->model->get('*', "email = '$email'");
        if ($user = $this->model->assoc($result)) {
            $this->id = $user['id'];
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            $this->role = $user['role'];
            $this->login = $user['login'];
            $this->passwordHash = $user['passwordhash'];
        }

        return !empty($this->id);
    }

    public function getUserbyId ($id) {
        $result = $this->model->get('*', "id = '$id'");
        if ($user = $this->model->assoc($result)) {
            $this->id = $user['id'];
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            $this->role = $user['role'];
            $this->login = $user['login'];
            $this->passwordHash = $user['passwordhash'];
        }

        return !empty($this->id);
    }
    public function login($username, $password) {
        $username = $this->model->esc($username);

        $result = $this->model->get(['id', 'firstname', 'lastname', 'role', 'passwordhash'],
            "login = '$username'");
        $user = $this->model->assoc($result);

        if (password_verify($password, $user['passwordhash'])) {
            $this->session->setUser($user['id']);
            $id = $this->session->start();
            $this->session->setUser($user['id']);
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            $this->role = $user['role'];
        }
        return !empty($id);
    }

    function list($search = '', $start = -1, $end = -1) {
        $condition = '';
        if (!empty($search)) {
            $condition .= "`firstname` LIKE '%" . $search . "%' || `lastname` LIKE '%" . $search ."%'";
        }
        $fields = ['id', 'firstname', 'lastname'];
        return $this->model->list($condition, $fields, '`lastname` ASC', $start, $end);
    }

}
