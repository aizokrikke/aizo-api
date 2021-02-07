<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'objects/apiUser.php';

$data = json_decode(file_get_contents("php://input"));

// instantiate user
$user = new apiUser();
$token = '';

// set product property values
$user->firstname = $data->firstname;
$user->lastname = $data->lastname;
$user->email = $data->email;

// create the user

if (
    !empty($user->firstname) &&
    !empty($user->email)) {
        $token = $user->create();
    }
if (!empty($token)){
    $status = 200;
    $message = "User was created.";
}

// message if unable to create user
else{
    // set response code
    $status = 400;
    $message = "Cannot create user";
}

http_response_code($status);
echo json_encode(array("message" => $message,"token" => $token));

?>
