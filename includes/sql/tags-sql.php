<?php
class tags{
	//Connection instance
	private $connection;
	
	//table name
	
	private $table_name = "tags";
	
	//table columns
	public $tag_id;
	public $tag_names;
	public $tag_type;
	
	public function __construct($connection){
		$this->connection = $connection;
	}
	
	//Add new tag
	public function create_new_tag($tag, $tagType, $tagId){
		$query = "INSERT INTO anderson_images.tags (tag_names, tag_type, tag_id)
				VALUES (?,?,?)";
		$stmt = $this->connection->prepare($query);
		$stmt->bindParam(1, $tag);
		$stmt->bindParam(2, $tagType);
		$stmt->bindParam(3, $tagId);
		try{
		$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
	}
	
	
	// get all tags sorted by tag_id
	public function read(){
		
		$query= "SELECT tag_id, tag_names, tag_type FROM anderson_images.tags order by tag_id";
		$stmt = $this->connection->prepare($query);
		$stmt->execute();
		return $stmt;
	}
	
	//get all tags sorted by tag name with years at the bottom (including tag counts)
	public function get_all_tags_name_sort(){		
		//$query = "SELECT * FROM anderson_images.tags order by if(tag_names RLIKE '^[A-Z]', 1, 2), tag_names";
		$query = "select t.tag_id, t.tag_type, t.tag_names, count(tl.image_id) AS tag_link_count from tags t left join tag_links tl on t.tag_id=tl.tag_id group by t.tag_id, t.tag_type, t.tag_names order by if(tag_names RLIKE '^[A-Z]', 1, 2), tag_names";
		$stmt = $this->connection->prepare($query);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
	}

	//get all non-year tags soeted by tag name
	public function get_all_non_year_tags(){		
		$query = "SELECT * FROM anderson_images.tags where tag_type != 'year' order by tag_names";
		$stmt = $this->connection->prepare($query);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
	}
	
	//get tag by type
	public function get_tag_by_type($tagType){
		if($tagType!='year'){
			$query="select t.tag_id, t.tag_type, t.tag_names, count(tl.image_id) AS tag_link_count from tags t left join tag_links tl on t.tag_id=tl.tag_id where t.tag_type = ? group by t.tag_id, t.tag_type, t.tag_names order by tag_names";
			//$query="SELECT tag_id, tag_names, tag_type FROM tags where tag_type = ? order by tag_names";
		}
		else{
			$query="select t.tag_id, t.tag_type, t.tag_names, count(tl.image_id) AS tag_link_count from tags t left join tag_links tl on t.tag_id=tl.tag_id where NOT t.tag_id = 9999 and t.tag_type = ? group by t.tag_id, t.tag_type, t.tag_names order by tag_names DESC";
			//$query="SELECT tag_id, tag_names, tag_type FROM tags where NOT tag_id = 9999 AND tag_type = ? order by tag_names DESC";
		}
		$stmt = $this->connection->prepare($query);
		$stmt->bindParam(1,$tagType);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
		
	}
	
	// build list of tags with other types
	public function get_tag_no_type(){
		$query = "select t.tag_id, t.tag_type, t.tag_names, count(tl.image_id) AS tag_link_count from tags t left join tag_links tl on t.tag_id=tl.tag_id where t.tag_type != 'individual' and t.tag_type != 'holiday' and t.tag_type != 'event' and t.tag_type != 'year' group by t.tag_id, t.tag_type, t.tag_names order by tag_names";
		//$query = "SELECT * FROM anderson_images.tags where tag_type != 'individual' and tag_type != 'holiday' and tag_type != 'event' and tag_type != 'year'";
		$stmt = $this->connection->prepare($query);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
		
	}
	
	// build list of tag types
	public function get_tag_types(){
		$query = "SELECT DISTINCT tag_type FROM anderson_images.tags order by tag_type";
		$stmt = $this->connection->prepare($query);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
		
	}
	
	// get the highest used tag ID number
	public function get_highest_tag_id(){
		$query = "select distinct tag_id from tags where tag_type != 'year' order by tag_id desc limit 1";
		$stmt = $this->connection->prepare($query);
		try{
			$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
	}
	
		
	//get tag information by id
	public function get_tag($id){
		//select query
		$query = "SELECT tag_id, tag_names, tag_type
					FROM anderson_images.tags
					WHERE tag_id = ?
					ORDER BY tag_names";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		//bind variable values
		$stmt->bindParam(1,$id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt;
				
	}
	
	//get tag information by ids
	public function get_tags_info_by_ids($ids){
		//select query
		$query = "SELECT tag_id, tag_names, tag_type
					FROM anderson_images.tags
					WHERE tag_id in ($ids)
					ORDER BY tag_id";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		
		//bind variable values
		//$stmt->bindParam(1,$ids, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt;
	}
	
	//get tags by contains
	public function search_tags($keywords, $type){
		//select query
		$query = "SELECT tag_id, tag_names, tag_type
					FROM anderson_images.tags
					WHERE tag_names LIKE ?
					AND tag_type LIKE ?
					ORDER BY tag_id";
		//prepare query statement
		$stmt = $this->connection->prepare($query);
		//sanitize keywords
		$keywords=htmlspecialchars(strip_tags($keywords));
		$keywords = "%{$keywords}%";
		$type=htmlspecialchars(strip_tags($type));
		$type = "%{$type}%";
		
		//bind variable values
		$stmt->bindParam(1,$keywords);
		$stmt->bindParam(2,$type);
		$stmt->execute();
		return $stmt;
				
	}
		
	//U
	public function update($tag_id, $new_tag_names, $new_tag_type){
		if ($new_tag_names == 'undefined') {
		$query = "UPDATE anderson_images.tags
					SET tag_type = ?
					WHERE tag_id = ?";
		$stmt = $this->connection->prepare($query);
		//bind variable values
		$stmt->bindParam(1,$new_tag_type);
		$stmt->bindParam(2,$tag_id, PDO::PARAM_INT);
		}
		elseif ($new_tag_type == 'undefined') {
		$query = "UPDATE anderson_images.tags
					SET tag_names = ?
					WHERE tag_id = ?";
		$stmt = $this->connection->prepare($query);
		//bind variable values
		$stmt->bindParam(1,$new_tag_names);
		$stmt->bindParam(2,$tag_id, PDO::PARAM_INT);
		}
		else {
		$query = "UPDATE anderson_images.tags
                    SET tag_names = ?, tag_type = ?
                    WHERE tag_id = ?";
        $stmt = $this->connection->prepare($query);
        //bind variable values
        $stmt->bindParam(1,$new_tag_names);
        $stmt->bindParam(2,$new_tag_type);
        $stmt->bindParam(3,$tag_id, PDO::PARAM_INT);
		}
		
		try{
		$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
	}

	//D
	public function delete_tag($tag_id){
		$query = "DELETE from anderson_images.tags
					WHERE tag_id = ?";
		$stmt = $this->connection->prepare($query);
		//bind variable values
		$stmt->bindParam(1,$tag_id, PDO::PARAM_INT);
		
		try{
		$stmt->execute();
		}
		catch(PDOException $e){
			echo $stmt . $e->getMessage();
		}
		return $stmt;
		
	}
}
?>