<?php

class Image_model extends Model {

	/**
	*	This function checks to see if an image exists
	*	@param $fields a field to check in
	*	@param value: the check to check for
	*/
	function image_attr_exists($field,$value)
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('images');
		
		if($query->num_rows == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

/////////// GET Functions
	
		
	/**
		This function returns as image found by a specified field. If no field
		is specified, then used image_id isntead
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getImage($value,$field = "image_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('images');
		
		if($query->num_rows == 1)
		{
			return $query;
		}
		else
		{
			return false;
		}
	}
	
	/**
		returns a php time stamp of a users last upload
		
		@param user_id : the id of the user in question
	**/
	function getLastUserImageTime($user_id)
	{
		
		$sql = "
			SELECT * FROM images 
			WHERE i_user_id = ".$user_id."
			ORDER BY  i_datetime DESC
			LIMIT 1
			";
		
		
		
		$query = $this->db->query($sql);
		
		if($query->num_rows == 0)
		{
			return false;
		}
		else
		{
			$row = $query->row();
			
			$phptime = strtotime($row->i_datetime);
			return $phptime;
			
			
		}
	}
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new image into the data base
		
		@param array : array of insert data
		
	*/
	function createNewImage($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("images",$array);
			$result["id"] = $this->db->insert_id();
			return $result;
		}
		else
		{
			return false;
		}
	}

////////////// UPDATE FUNCTIONS

	/**
	*	General purpose update function for images
	*	
	*	@param (Array)values : array of fields names and value to update
	*	@param loc_value : locator value for finding image by
	*	@param loc_field : field to search for image by (defaults to image_id)
	*/
	function updateImage($values,$loc_value,$loc_field = "image_id")
	{
		// check user exists
		if($this->image_attr_exists($loc_field,$loc_value) == TRUE)
		{
			if(is_array($values) && !empty($values))
			{
				
				$this->db->where($loc_field,$loc_value);
				
				// update table
				$this->db->update("images",$values);
				
				return true;
			}
			else
			{
				return false;
			}	
		
		}
		else
		{
			return false;
	

		}
	}
	
////////////// DELETE FUNCTIONS

	/*
		Deletes by id, including all related records
		@param id : image_id to delete
	*/
	function deleteImage($id)
	{
		if($id != null)
		{
			$image = $this->getImage($id)->row();
			
			$this->db->delete("images",array("image_id" => $id));
			
			// delete all comments, favourites, notices etc
			$this->db->delete("favourites",array("f_subject_id" => $id));
			$this->db->delete("comments",array("c_subject_id" => $id,"c_type"=>"image"));
			$this->db->delete("notices",array("n_object_id" => $id,"n_object_type"=>"image"));
			
			// unlink files
			
			$thumb_path = str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$image->i_thumb_url);
			$full_path = str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$image->i_full_url);

			
			if(is_file($thumb_path)) { unlink($thumb_path); }
			if(is_file($full_path)) { unlink($full_path); }
					
			
			return true;
		}
		else
		{
			return false;
		}
	}


////////////// Tagging Functions

	/*
		Generates a list of used tags and totals
		from the i_tags fields of the current image and moodboards records
		
		@param json: if true returns in a json format, else an array
		@param getPopular : return second result with popular
		
	*/
	function getTags($json = false)
	{
		$images = $this->db->get("images")->result();
		$moodboards = $this->db->get("moodboard")->result();
		
		// empty tags array. key is the name, value is the total
		$tags_array = array();
		
		$max = 1;
		
		// first images
		$count = count($images);
		for($i = 0; $i < $count; $i++)
		{
			if($images[$i]->i_tags != "")
			{
				$explode = explode(",",strtolower($images[$i]->i_tags));
				
				$tags_count = count($explode);
				for($j = 0; $j < $tags_count; $j++)
				{
					if($explode[$j] != "")
					{
						$explode[$j] = trim($explode[$j]);
						
						if(isset($tags_array[$explode[$j]]) == true)
						{
							$tags_array[$explode[$j]] = $tags_array[$explode[$j]] + 1;
							
							if($tags_array[$explode[$j]] > $max) $max = $tags_array[$explode[$j]];
						}
						else
						{
							$tags_array[$explode[$j]] = 1;
						}
					}
				}
			}
		}
		
		// now moodboards
		$count = count($moodboards);
		for($i = 0; $i < $count; $i++)
		{
			$explode = explode(",",strtolower($moodboards[$i]->m_tags));
			
			$tags_count = count($explode);
			for($j = 0; $j < $tags_count; $j++)
			{
				if($explode[$j] != "")
				{
					$explode[$j] = trim($explode[$j]);
				
					if(isset($tags_array[$explode[$j]]) == true)
					{
						$tags_array[$explode[$j]] = $tags_array[$explode[$j]] + 1;
						
						if($tags_array[$explode[$j]] > $max) $max = $tags_array[$explode[$j]];
					}
					else
					{
						$tags_array[$explode[$j]] = 1;
					}
				}
			}
		}
		
		arsort($tags_array);
		
		if($json = false)
		{
			return $tags_array;
		}
		else
		{
			$tags_json = array();
			$obj;
			$popular_json = array();
			
			$lower_q = ceil($max*0.20);
			
			foreach($tags_array as $tag => $total)
			{
				$obj = null;
				$obj->key = $obj->value = $obj->caption = trim($tag);
				$obj->total = $total;
				$tags_json[] = $obj;
				
				if($total >= $lower_q)
				{
					$popular_json[] = $obj;
				}
			}
			
			$rtn = array(
				"full" => $tags_json,
				"popular" => $popular_json
			);
			
			if($json == true)
			{
				return json_encode($rtn);
			}
			else
			{
				return $rtn;
			}
		}
		
		
	}

}


/* End of file images_model.php */
/* Location: ./system/application/models/images_model.php */