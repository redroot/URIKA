<?php

/**
	Special model for use by admin system mainly
	Can access any table or record etc
**/

class general_model extends Model {


/////////// GET Functions
	

	
	/**
		Queries the database with the list of supplied arguments
		
		@param table_name : the table to query
		@param offset : offset for the records
		@param limit : #records to show
		@param order_by : field to order_by
		@param order_dir : direction to order by (asc or desc)
		@param search_field : field to search in
		@param search_value : what to search for
	
	**/
	function getRecords($table_name, $offset = 0, $limit = 20, $order_by = "", $order_dir = "ASC", $search_field = null, $search_value = null )
	{
		
		$sql = "
			SELECT * FROM ".trim($table_name)."
			";
			
		// search	
		if($search_field != null)
		{
			$sql .= "
				WHERE ".$search_field." LIKE '%".$search_value."%' 
			";
		}
			
		if($order_by != "" && ($order_dir == "ASC" || $order_dir == "DESC"))
		{
			$sql .= "
			ORDER BY ".trim($order_by)." ".strtoupper($order_dir)."
			";
		}
		
		$sql .= "
			LIMIT ".$offset.", ".$limit."
		";
		
		
	
		$query = $this->db->query($sql);
		
		if($query->num_rows == 0)
		{
			return false;
		}
		else
		{
			return $query->result();
		}
	}
	
	/**
		Returns a count for the number of records in a table
		Incorporates search fields too
		
		@param table_name : the table to use
		@param search_field : field to search in
		@param search_value : what to search for
	**/
	function countRecords($table_name,$search_field = null,$search_value = null)
	{
		$sql = "
			SELECT * FROM ".trim($table_name)."
			";
			
		// search	
		if($search_field != null)
		{
			$sql .= "
				WHERE ".$search_field." LIKE '%".$search_value."%' 
			";
		}
		
		$query = $this->db->query($sql);
		
		return $query->num_rows;
	}
	
	/**
		Grabs a record from  a table
		
		@param table_name : table name to search in
		@param record_id : id of the record to look for
	**/
	function getRecord($table_name,$record_id)
	{
		$primaryKey = "";
		
		$fields = $this->db->field_data($table_name);
		
		foreach($fields as $field)
		{
			$field_name = $field->name;
			
			if($field->primary_key == 1)
			{
				$primaryKey = $field->name;
			}
		}
		
		$this->db->where($primaryKey,$record_id);
		$query = $this->db->get($table_name);
		
		if($query->num_rows == 0)
		{
			return false;
		}
		else
		{
			return $query->row();
		}
		
		
	}
	
////////////// SEARCH FUNCTION FOR IMAGES

/**
		Searches through images and moodboard results, used on browse page
		
		@param tag : tag to search by
		@param search : some string to search by, search titles
			- note that if this is this contains followIN(ids) then this is used to grab followed
			  records
		@param type : images or moodboards or both
		- pagination parameters
		@param offset : offset to start limit by
		@param limit : rows per page
		@param order_by : any sort value to order by
		@oaram order_dir : order direction
	
	**/
	function searchAll($tag = "",$search = "",$type = "both", $offset = 0, $limit = 20, $order_by = "", $order_dir = "DESC")
	{
		// easiest way to do this if have sep images and moodboards one then fix up the both into a super function
		
		$sql = $no_limit_sql = "";

		if($type == "uploads")
		{
			$sql = "
				SELECT images.*, users.user_id, users.u_username,
				(SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_subject_id = images.image_id AND favourites.f_type = 'image') as i_favs
				FROM images
				LEFT JOIN users on images.i_user_id = users.user_id
				";
			
			// tag
			if($tag != "")
			{
				$sql .= "
					WHERE images.i_tags LIKE '%".$tag."%' 
				";
			}
			
			// search	
			if($search != "")
			{
				if($tag != "")
				{
					$sql .= " AND ";
				}
				else
				{
					$sql .= " WHERE ";
				}
				
				if(strpos($search,"followIN#") !== FALSE)
				{
					$sql .= "
						images.i_user_id IN(".str_replace('followIN#','',$search).")
					";
				}
				else
				{
					$sql .= "
					 images.i_title LIKE '%".$search."%' 
					";
				}
			}
			
			$no_limit_sql = $sql;
				
			if($order_by != "")
			{
				$sql .= "
				ORDER BY i_".trim($order_by)." ".$order_dir."
				";
			}
			
			$sql .= "
				LIMIT ".$offset.", ".$limit."
			";
		}
		else if($type == "moodboards")
		{
			$sql = "
				SELECT moodboard.*, users.user_id, users.u_username,
				(SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_subject_id = moodboard.moodboard_id AND favourites.f_type = 'moodboard') as m_favs
				FROM moodboard
				LEFT JOIN users on moodboard.m_user_id = users.user_id
				";
			
			// tag
			if($tag != "")
			{
				$sql .= "
					WHERE moodboard.m_tags LIKE '%".$tag."%' 
				";
			}
			
			// search	
			if($search != "")
			{
				if($tag != "")
				{
					$sql .= " AND ";
				}
				else
				{
					$sql .= " WHERE ";
				}
				
				if(strpos($search,"followIN#") !== FALSE)
				{
					$sql .= "
						moodboard.m_user_id IN(".str_replace('followIN#','',$search).")
					";
				}
				else
				{
					$sql .= "
					 moodboard.m_title LIKE '%".$search."%' 
					";
				}
			}
			
			$no_limit_sql = $sql;
				
			if($order_by != "")
			{
				$sql .= "
				ORDER BY m_".trim($order_by)." ".$order_dir."
				";
			}
			
			$sql .= "
				LIMIT ".$offset.", ".$limit."
			";
		}
		else if($type == "both")
		{
			// now to combine the two
			// this is done use mySQL Unions and some clever renaming
			
			$images_sql = '
				SELECT 
					images.image_id AS row_id, 
					images.i_title AS row_title, 
					images.i_thumb_url AS row_thumb_url,
					images.i_views AS views, 
					images.i_datetime AS datetime, 
					"image" AS type, 
					users.user_id, 
					users.u_username,
					(
					SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_subject_id = images.image_id AND favourites.f_type = \'image\'
					) AS favs
				FROM images
				LEFT JOIN users on images.i_user_id = users.user_id
			';
			
			$moodboards_sql = '
				SELECT 
					moodboard.moodboard_id AS row_id, 
					moodboard.m_title AS row_title, 
					moodboard.m_thumb_url as row_thumb_url,
					moodboard.m_views  AS views_count, 
					moodboard.m_datetime AS datetime, 
					"moodboard" AS type, 
					users.user_id, 
					users.u_username,
					(
						SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_subject_id = moodboard.moodboard_id AND favourites.f_type = \'moodboard\'
					) AS favs			
				FROM moodboard
				LEFT JOIN users on moodboard.m_user_id = users.user_id
			';
			
			// tag
			if($tag != "")
			{
				$images_sql .= "
					WHERE images.i_tags LIKE '%".$tag."%' 
				";
				
				$moodboards_sql .= "
					WHERE moodboard.m_tags LIKE '%".$tag."%' 
				";
			}
			
			// search	
			if($search != "")
			{
				if($tag != "")
				{
					$images_sql .= " AND ";
					$moodboards_sql .= " AND ";
				}
				else
				{
					$images_sql .= " WHERE ";
					$moodboards_sql .= " WHERE ";
				}
				
				if(strpos($search,"followIN#") !== FALSE)
				{
					$images_sql .= "
						images.i_user_id IN(".str_replace('followIN#','',$search).")
					";
					$moodboards_sql .= "
						moodboard.m_user_id IN(".str_replace('followIN#','',$search).")
					";
				}
				else
				{
					$images_sql .= "
					 images.i_title LIKE '%".$search."%' 
					";
					
					$moodboards_sql .= "
					 moodboard.m_title LIKE '%".$search."%' 
					";
				}
			}
			
			$sql = '
			('.$images_sql.')
			UNION
			('.$moodboards_sql.')
			';
			
			$no_limit_sql = $sql;
			
			if($order_by != "")
			{
				$sql .= "
				ORDER BY ".trim($order_by)." ".$order_dir."
				";
			}
			
			$sql .= "
				LIMIT ".$offset.", ".$limit."
			";
		}
	
		$query = $this->db->query($sql);
		$no_limit_query = $this->db->query($no_limit_sql);
		
		if($query->num_rows == 0)
		{
			return false;
		}
		else
		{
			return array(
				"results" => $query->result(),
				"total" => $no_limit_query->num_rows
			);
		}
	}
	

////////////// UPDATE FUNCTIONS

	/**
		updates a record from  a table
		
		@param table_name : table name to search in
		@param id_field : the primary key field to update
		@param record_id : id of the record to look for
		@param values: the update data to use
	**/
	function updateRecord($table_name,$id_field,$record_id,$values)
	{
		$primaryKey = "";
		
		$fields = $this->db->field_data($table_name);
		
		foreach($fields as $field)
		{
			$field_name = $field->name;
			
			if($field->primary_key == 1)
			{
				$primaryKey = $field->name;
			}
		}
		
		// check they match
		if($id_field == $primaryKey)
		{
			// check record exists
			if($this->getRecord($table_name,$record_id) !== false)
			{
				if(is_array($values) && !empty($values))
				{
					
					$this->db->where($id_field,$record_id);
					
					
					// update table
					$this->db->update($table_name,$values);
					
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
		else
		{
			return false;
		}
		
		
	}


////////////// DELETE FUNCTIONS



	/*
		Deletes by id
		
		@param table_name : table to delete from
		@param id_field : primary key field to use
		@param record_id : record id id to delete
	*/
	
	function deleteRecord($table_name,$id_field,$record_id)
	{
		$primaryKey = "";
		
		$fields = $this->db->field_data($table_name);
		
		foreach($fields as $field)
		{
			$field_name = $field->name;
			
			if($field->primary_key == 1)
			{
				$primaryKey = $field->name;
			}
		}
		
		// check they match
		if($id_field == $primaryKey)
		{
			// check user exists
			if($this->getRecord($table_name,$record_id) !== false)
			{
				
				// delete as normal
				$this->db->where($id_field,$record_id);
				$this->db->delete($table_name);
				
				return true;
				
			}
		}
	}

//////////////// INSERT FUNCTIONS

	/*
		General purpose insert
		
		@param $values:  array of values
		@param $table: table_name
	*/
	function addRecord($values,$table)
	{
		if($values != null)
		{
			$result["result"] = $this->db->insert(trim($table),$values);
			$result["id"] = $this->db->insert_id();
			return $result;
		}
		else
		{
			return false;
		}
	}
	
}

/* End of file general_model.php */
/* Location: ./system/application/models/general_model.php */