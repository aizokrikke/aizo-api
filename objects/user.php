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
            "default": "",
            "null": "false",
            "validator": "email",
            "manditory": "false"
        }',
        '{ 
            "name": "role",
            "type": "string",
            "length": "256",
            "index": "true",
            "default": "",
            "null": "false",
            "manditory": "false"
        }',
        '{ 
            "name": "login",
            "type": "string",
            "length": "256",
            "index": "true",
            "null": "false",
            "manditory": "false",
            "projectunique": "true"
        }',
        '{ 
            "name": "passwordhash",
            "type": "string",
            "length": "256",
            "default": "",
            "index": "true",
            "null": "false",
            "manditory": "true"
        }',
        '{ 
            "name": "project",
            "type": "integer",
            "length": "11",
            "index": "true",
            "null": "false",
            "manditory": "true"
        }'

    ];
    private array $defaultRecords = [ "firstname" => "root", "lastname" => "admin", "role" => "rootadmin",
        "login" => "rootadmin", "project" => 1];

    // object properties
    private int $id;
    private string $firstname = '';
    private string $lastname = '';
    private string $email = '';
    private string $role = '';
    private string $login = '';
    private $passwordHash = '';
    private int $project = 0;
    private int $sessionProject = 0;
    private Session $session;

    // constructor
    public function __construct($id = '', $sessionproject = 0){
        $this->sessionProject = $sessionproject;
        $this->model = new model('users', $this->fielddef, $this->defaultRecords);
        if ($this->model->isNew()) {
            $this->getUserbyId(1);
            $this->setPassword('pleasechange');
            $this->saveCurrent();
        }
        if (!empty($id)) {
            $this->getUserbyId($id);
        }
    }

    public function store($input, $all = true) {
        if (empty($input['project'])) {
            $input['project'] = $this->sessionProject;
        }
        $result = $this->model->validateInput($input, $input['project']);
        if (!is_array($result)) {
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
            $this->setProject($input['project']);

            if (empty($this->id)) {
                // new user so insert
                return $this->create();
            } else {
                // update user
                return $this->saveCurrent();
            }
        }

        return $result;
    }

    public function setProject($in) {
        $this->project = $in;
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
        if (!empty($this->session)) {
            return $this->session->getToken();
        }

        return false;
    }

    public function setEmail($in) {
        if (!empty($in)) {
            $this->email = $in;
        }
    }

    public function setLogin($in) {
        $this->login = $in;
    }

    public function setPassword($in) {
        $this->passwordHash = password_hash($in, PASSWORD_DEFAULT);
    }

    public function getDetails() {
        $out = [];
        if (!empty($this->id)) {
          $out = ['firstname' => $this->firstname, 'lastname' => $this->lastname, 'email' => $this->email,
              'login' => $this->login, 'role' => $this->role];
        }
        if ($this->sessionProject == 1) {
            $out['project'] = $this->project;
        }
        return $out;
    }

    public function create(){
        $fields = ['firstname', 'lastname', 'email', 'role','login', 'passwordhash', 'project'];
        $values = array([$this->firstname, $this->lastname, $this->email, $this->role,
                    $this->login, $this->passwordHash, $this->project]);

        return $this->model->insert($fields, $values);
    }

    public function saveCurrent(){
        $fields = ['firstname', 'lastname', 'email', 'role','login', 'passwordhash', 'project'];
        $values = [$this->firstname, $this->lastname, $this->email, $this->role,
            $this->login, $this->passwordHash, $this->project];
        $condition = "`id` = '" . $this->id . "'";
        return $this->model->update($fields, $values, $condition);
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
        $result = $this->model->get(['id', 'firstname', 'lastname', 'role', 'login', 'passwordhash', 'project'],
            "id = '$id'");
        if ($user = $this->model->assoc($result)) {
            $this->id = $user['id'];
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            $this->role = $user['role'];
            $this->login = $user['login'];
            $this->passwordHash = $user['passwordhash'];
            $this->project = $user['project'];
        }

        return !empty($this->id);
    }
    public function login($username, $password) {
        $username = $this->model->esc($username);

        $result = $this->model->get(['id', 'firstname', 'lastname', 'role', 'passwordhash'],
            "login = '$username'");
        $user = $this->model->assoc($result);

        if (password_verify($password, $user['passwordhash'])) {
            $this->session = new Session();
            $this->session->setUser($user['id']);
            $id = $this->session->start();
            $this->session->setUser($user['id']);
            $this->firstname = $user['firstname'];
            $this->lastname = $user['lastname'];
            $this->role = $user['role'];
        }
        return !empty($id);
    }

    function list($search = '', $project = 0, $start = -1, $end = -1) {
        $condition = '';
        if (!empty($search)) {
            $condition .= "`firstname` LIKE '%" . $search . "%' || `lastname` LIKE '%" . $search ."%'";
        }
        if ($project > 0 && $this->sessionProject == 1) {
            if (!empty($condition)) {
                $condition .= " && ";
            }
            $condition .= "`project` = '" . $project . "'";
        } else {
            if ($this->sessionProject > 1)  {
                if (!empty($condition)) {
                    $condition .= " && ";
                }
                $condition .= "`project` = '" . $this->sessionProject . "'";
            }
        }

        $fields = ['id', 'firstname', 'lastname'];
        if ($this->sessionProject == 1) {
            $fields[] = 'project';
        }
        return $this->model->list($condition, $fields, '`lastname` ASC', $start, $end);
    }

}
