<?php
class images{
	//Connection instance
	private $connection;
	
	//table name
	
	private $table_name = "imagesTest";
	
	//table columns
	public $image_id;
	public $image_hash;
	public $image_path;
	public $image_file;
	
	public function __construct($connection){
		$this->connection = $connection;
	}
	
	//Connection
	public function create(){
	}

	// Get a count of all images in the database without copy in the name and not in printables folders
	public function imageCount(){
		$query="SELECT COUNT(image_id) as image_count from anderson_images.images where not image_path like '%printables%' and not image_file like '%copy%'";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		//execute query
		$stmt->execute();
		//return values from database
		return $stmt;
	}		
	
	//R
	public function read(){
		$query= "SELECT image_id, image_hash, image_file,image_path 
				FROM anderson_images.images order by image_path";
		$stmt = $this->connection->prepare($query);
		$stmt->execute();
		return $stmt;
	}
	//U
	public function update(){
	}
	//D
	public function delete(){
	}
	//searches by the image name given in the bar
	/*public function search_by_name($keywords){
		//select all query
		$query= "SELECT image_id, image_hash, image_file, image_path 
				FROM anderson_images.images 
				WHERE image_path LIKE ?
				OR image_path LIKE 'Marcy'
				ORDER BY image_path";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		//sanitize
		$keywords=htmlspecialchars(strip_tags($keywords));
		$keywords = "%{$keywords}%";
		
		//bind
		$stmt->bindParam(1, $keywords);
		
		//execute query
		$stmt->execute();
		
		return $stmt;
	}*/ 


//read products with pagination
	public function readPaging($from_record_num, $records_per_page, $keywords){
		//select query
		$query = "SELECT image_id, image_hash, image_file, image_path 
					FROM anderson_images.images  
					WHERE image_file LIKE ? 
					OR image_path LIKE ?
					OR image_id LIKE ?
					ORDER BY image_path, image_file
					LIMIT ?,?";

		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		//sanitize keywords
		$keywords=htmlspecialchars(strip_tags($keywords));
		$keywords = "%{$keywords}%";
		
		//bind variable values
		$stmt->bindParam(1,$keywords);
		$stmt->bindParam(2,$keywords);
		$stmt->bindParam(3,$keywords);
		$stmt->bindParam(4,$from_record_num,PDO::PARAM_INT);
		$stmt->bindParam(5,$records_per_page,PDO::PARAM_INT);
		
		//execute query
		$stmt->execute();
		
		//return values from database
		return $stmt;
	}
	
	//gets all the images with all of the tags (the formatting of the query under the ? is done on the html
	public function imagesByTagsAnd($tags, $number){
		$query = "SELECT i.image_id, i.image_hash, i.image_file, i.image_path
					FROM anderson_images.images i, (select t.image_id
						FROM anderson_images.tag_links t
						where t.tag_id in ($tags) ) as tl
					WHERE i.image_id = tl.image_id
					GROUP BY i.image_id
					HAVING count(i.image_id) = $number  
                                        ORDER BY i.image_path, i.image_file";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		
		//execute query
		$stmt->execute();
		
		//return values from database
		return $stmt;
	}

	//gets all the images with all of the tags (the formatting of the query under the ? is done on the html
	public function imagesByTagsAndExclusive($tags, $number){
		$query = "SELECT i.image_id, i.image_hash, i.image_file, i.image_path
					FROM anderson_images.images i, (select t.image_id
						FROM anderson_images.tag_links t
						where t.tag_id in ($tags) ) as tl
					WHERE i.image_id = tl.image_id
					GROUP BY i.image_id
					HAVING count(i.image_id) = $number AND
					count(i.image_id) = (select count(links.tag_id) from anderson_images.tag_links links
					inner join anderson_images.tags tags 
					  on links.tag_id = tags.tag_id
					where links.image_id = i.image_id and tags.tag_type not in ('event','holiday','other'))
                    ORDER BY i.image_path, i.image_file";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		
		//execute query
		$stmt->execute();
		
		//return values from database
		return $stmt;
	}
	
	//gets all the images with all of the tags (the formatting of the query under the ? is done on the html
	public function imagesByTagsAndExclusiveNoYear($tags, $number){
		$query = "SELECT i.image_id, i.image_hash, i.image_file, i.image_path
					FROM anderson_images.images i, (select t.image_id
						FROM anderson_images.tag_links t
						where t.tag_id in ($tags) ) as tl
					WHERE i.image_id = tl.image_id
					GROUP BY i.image_id
					HAVING count(i.image_id) = $number AND
					count(i.image_id) = (select count(links.tag_id) from anderson_images.tag_links links
					inner join anderson_images.tags tags 
					  on links.tag_id = tags.tag_id
					where links.image_id = i.image_id and tags.tag_type not in ('event','holiday','other')) -1
                                        ORDER BY i.image_path, i.image_file";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		
		//execute query
		$stmt->execute();
		
		//return values from database
		return $stmt;
	}

	//gets all the images with all of the tags (the formatting of the query under the ? is done on the html
	public function imagesByTagsOr($tags, $number){
		$query = "SELECT i.image_id, i.image_hash, i.image_file, i.image_path
					FROM anderson_images.images i, (select t.image_id
						FROM anderson_images.tag_links t
						where t.tag_id in ($tags) ) as tl
					WHERE i.image_id = tl.image_id
					GROUP BY i.image_id
					HAVING count(i.image_id) = $number OR count(i.image_id) = 1
                                        ORDER BY i.image_path, i.image_file";
	
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		
		//execute query
		$stmt->execute();
		
		//return values from database
		return $stmt;
	}


	//gets the ids of all the images with a single tag
	public function imagesByTag($tag){
		$query = "SELECT i.image_id
					FROM anderson_images.images i, (select t.image_id
						FROM anderson_images.tag_links t
						where t.tag_id = $tag ) as tl
					WHERE i.image_id = tl.image_id";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		
		//execute query
		$stmt->execute();
		
		//return values from database
		return $stmt;
	}

	//get image using id
	public function getImageById($id){
		//select query
		$query = "SELECT image_id, image_hash, image_file, image_path
					FROM anderson_images.images
					WHERE image_id = ?";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		//bind variable values
		$stmt->bindParam(1, $id);
		//execute query
		$stmt->execute();
		//return values from database
		return $stmt;
	}
	
	
	//used for paging products
	public function count($keywords){
		$query = "SELECT COUNT(*) as total_rows FROM images WHERE image_file LIKE ? or image_path like ? COLLATE utf8_general_ci";
		$stmt = $this->connection->prepare( $query );
		
		$keywords=htmlspecialchars(strip_tags($keywords));
		$keywords = "%{$keywords}%";
		
		$stmt->bindParam(1, $keywords);
		$stmt->bindParam(2, $keywords);
		
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		//var_dump($row['total rows']);
		return $row['total_rows'];
		
	}
	
	
	
	
}

?>
