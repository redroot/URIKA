<?php

class flag_model extends Model {


/////////// GET Functions
	
		
	/**
		This function returns a flag record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getFlag($value,$field = "flag_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('flags');
		
		if($query->num_rows == 1)
		{
			return $query;
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
	*/
	function flagExists($user_id,$subject_id)
	{
		$this->db->where("fl_upload_id", trim($subject_id));
		$this->db->where("fl_flagger_id",$user_id);
		$query = $this->db->get('flags');
		
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
	function createNewFlag($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("flags",$array);
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

/* End of file flag_model.php */
/* Location: ./system/application/models/flags_model.php */