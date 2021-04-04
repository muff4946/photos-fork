<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/core.php';
include_once '../shared/utilities.php';
include_once '../config/dbclass.php';
include_once '../objects/users.php';

// instantiate database and product object
$database = new DBClass();
$db = $database->getConnection();

//initialize objects
$users = new users($db);

$user_n = isset($_GET['user']) ? $_GET['user'] : '';
$image_n = isset($_GET['image']) ? $_GET['image']:1;

if($user_n !=''){
	//get array of tag ids
	$stmt = $users->createWithNumber($user_n,$image_n);
	http_response_code(200);
	echo json_encode(
		array("message"=> $user_n . " added at " . $image_n)
	);
	
}
else{
	
	http_response_code(404);
	echo json_encode(
		array("message" => "No username given")
	);
	
}	
?>	
	
	
	