<?php

class collection_model extends Model {


/////////// GET Functions
	
		
	/**
		This function returns a collection record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getCollection($value,$field = "collection_id")
	{
		$this->db->where(trim($field), trim($value));
		$this->db->from("collections");
		$this->db->join("users","users.user_id = collections.col_user_id");
		$query = $this->db->get();
		
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
		Grabs images from the collection string
	
		@param id : id of collection
		@param preview : if true, returns 4 random image
	**/
	function getCollectionImages($id,$preview = false)
	{
		$col = $this->getCollection($id)->row();
		
		if($col == false)
		{
			return false;
		}
		else
		{
			$id_string = $col->col_string;
			$ids_array = explode(",",$id_string);
			$orig_count = count($ids_array);
			
			if($preview == true)
			{
				// in this case we only need 4
				shuffle($ids_array);
				array_splice($ids_array,4);
			}
			
			// now we have an id array we can jump through images
			$this->db->where_in("image_id",$ids_array);
			$this->db->from("images");
			$this->db->join("users","users.user_id = images.i_user_id");
			$query = $this->db->get();
			
			if($query->num_rows > 0)
			{
				return $query->result();
			}
			else
			{
				return false;
			}
		}
	}
	
	/**
		Grabs an array of moodboards from 
	**/
	function getCollectionMoodboardIds($id)
	{
		$col = $this->getCollection($id);
		
		if($col == false)
		{
			return false;
		}
		else
		{

			$this->db->select("moodboard_id");
			$this->db->where("m_col_id",$id);
			$this->db->from("moodboard");
			$query = $this->db->get();
			
			if($query->num_rows > 0)
			{
				return $query->result();
			}
			else
			{
				return false;
			}
		}
	}
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new collection into the data base
		
		@param array : array of insert data
		
	*/
	function createNewCollection($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("collections",$array);
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
	*	General purpose update function for collections
	*	
	*	@param (Array)values : array of fields names and value to update
	*	@param loc_value : locator value for finding collections by
	*	@param loc_field : field to search for collections
	*/
	function updateCollection($values,$loc_value,$loc_field = "collection_id")
	{
		// check user exists
		if($this->getCollection($loc_value,$loc_field) !== false)
		{
			if(is_array($values) && !empty($values))
			{
				
				$this->db->where($loc_field,$loc_value);
				
				
				// update table
				$this->db->update("collections",$values);
				
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
		@param id : collection id to delete
	*/
	function deleteCollection($id)
	{
		if($id != null)
		{
			$this->db->delete("collections",array("collection_id" => $id));
			
			// need to delete notices
		
			return true;
		}
		else
		{
			return false;
		}
	}


	
}

/* End of file collection_model.php */
/* Location: ./system/application/models/collection_model.php */