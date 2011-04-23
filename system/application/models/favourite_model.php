<?php

class favourite_model extends Model {


/////////// GET Functions
	
		
	/**
		This function returns a ffavourite record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getFavourite($value,$field = "favourite_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('favourites');
		
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
		returns a full list of favourites for an item, all also user objects
		
		@param subject_id : the id of the subject
		@param type : image or moodboard, defailt to image
	**/
	function getFavourites($subject_id,$type = "image")
	{
		$this->db->where("f_subject_id", trim($subject_id));
		$this->db->where("f_type",$type);
		$this->db->from("favourites");
		$this->db->join("users","favourites.f_user_id = users.user_id");
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
	
	/*
		Function to check if a record already exists for this user and object
		
		@param user_id : id of the user
		@param subject_id : id of the subject
		@param type : type of favourite, defaults to image
	*/
	function favouriteExists($user_id,$subject_id, $type = "image")
	{
		$this->db->where("f_subject_id", trim($subject_id));
		$this->db->where("f_user_id",$user_id);
		$this->db->where("f_type",$type);
		$query = $this->db->get('favourites');
		
		if($query->num_rows == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new favourite into the data base
		
		@param array : array of insert data
		
	*/
	function createNewFavourite($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("favourites",$array);
			$result["id"] = $this->db->insert_id();
			return $result;
		}
		else
		{
			return false;
		}
	}

////////////// UPDATE FUNCTIONS

	
}

/* End of file favourite_model.php */
/* Location: ./system/application/models/favourite_model.php */