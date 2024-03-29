<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../global-functions.php';
include_once '../db-connection.php';
include_once '../sql/images-sql.php';
include_once '../sql/tags-sql.php';
include_once '../sql/tag-links-sql.php';

// instantiate database and product object
$database = new DBClass();
$db = $database->getConnection();

//initialize objects
$tags = new tags($db);
$taglinks = new tag_links($db);
$images = new images($db);

//get tag ids from url
$tagids= isset($_GET['tag']) ? $_GET['tag'] : '';
//get tag count from url
$numTags= isset($_GET['num']) ? $_GET['num'] : '';
//get searchType from url
$searchType= isset($_GET['searchType']) ? $_GET['searchType'] : '';
//get yearCount from url
$yearCount= isset($_GET['yearCount']) ? $_GET['yearCount'] : '';
//get otherCount from url
$otherCount= isset($_GET['otherCount']) ? $_GET['otherCount'] : '';
//get eventCount from url
$eventCount= isset($_GET['eventCount']) ? $_GET['eventCount'] : '';
//get holidayCount from url
$holidayCount= isset($_GET['holidayCount']) ? $_GET['holidayCount'] : '';
//get peopleCount from url
$peopleCount= isset($_GET['peopleCount']) ? $_GET['peopleCount'] : '';

switch ($searchType) {
	case "and";
		$stmt = $images->imagesByTagsAnd($tagids, $numTags);
		break;
	case "andExclusive";
		if($yearCount == 0){
			$stmt = $images->imagesByTagsAndExclusiveNoYear($tagids, $numTags);
		} else{
			$stmt = $images->imagesByTagsAndExclusive($tagids, $numTags);
		}
		break;
	case "or";
		$stmt = $images->imagesByTagsOr($tagids, $numTags);
		break;
}

$num = $stmt->rowCount();

//go through array and get all of the images that match these tag ids
if($num>0){
	$image_arr = array();
	//return these
	$image_arr["images"]=array();
	//retrieve table contents
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
		//extract row 
		//this will make row['name'] to just $name 
		extract($row);
		
		//Dads work getting image and thumbnail path
		//copied from tagged-photo-list.php
		$db_image_path = $row["image_path"];
		$db_image_name = $row["image_file"];
		$web_image_path = str_replace ("\\", "/", $db_image_path);
		$web_image_path = str_replace ("D:/pictures", "https://photos.dbq-andersons.com/storage", $web_image_path);
		$web_thumb_path = str_replace ("storage", "thumbs", $web_image_path);
		$local_thumb_path = str_replace ("https://photos.dbq-andersons.com/storage", "/tools/webdocs/photos/thumbs", $web_image_path);
		
		$image_item=array(
			"id" => $image_id,
			"hash"=> $image_hash,
			"file" => $image_file,
			"path" => $image_path,
			"image" => $web_image_path,
			"thumb" => $web_thumb_path
		);
		array_push($image_arr["images"],$image_item);
	}	
	
	//set response code - 200 OK
	http_response_code(200);

	//make it json format
	echo json_encode($image_arr);
}
else{
	//set response code - 404 Not found
	http_response_code(404);
	//tell the user products does not exist
	echo json_encode(
		array("message" => "No products found.")
	);

}


?>