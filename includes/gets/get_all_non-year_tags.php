<?php
//required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../global-functions.php';
include_once '../db-connection.php';
include_once '../sql/tags-sql.php';

// instantiate database and product object
$database = new DBClass();
$db = $database->getConnection();

//initialize object 
$tags = new tags($db);

//get array of tags
$stmt = $tags->get_all_non_year_tags();

//make a new array
$tag_arr = array();
$tag_arr["all_tags"]=array();
//retrieve table contents
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	extract($row);
		
	$tag_id = $row["tag_id"];
	$tag_names = $row["tag_names"];
	$tag_type = $row["tag_type"];
	$tag_initial = $tag_names[0];
		
	$tag_item=array(
		"id"=>$tag_id,
		"names"=>$tag_names,
		"type"=>$tag_type,
		"initial"=>$tag_initial
	);
	array_push($tag_arr["all_tags"],$tag_item);
}
	
	http_response_code(200);
	
	echo json_encode($tag_arr);	

?>