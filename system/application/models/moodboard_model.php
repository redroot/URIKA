<?php

class moodboard_model extends Model {

/////////// GET Functions
	
		
	/**
		This function returns as moodboard found by a specified field. If no field
		is specified, then used moodboard_id isntead
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getMoodboard($value,$field = "moodboard_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('moodboard');
		
		if($query->num_rows == 1)
		{
			return $query;
		}
		else
		{
			return false;
		}
	}
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new moodboard into the data base
		
		@param array : array of insert data
		
	*/
	function createNewMoodboard($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("moodboard",$array);
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
	*	General purpose update function for moodboards
	*	
	*	@param (Array)values : array of fields names and value to update
	*	@param loc_value : locator value for finding moodboard by
	*	@param loc_field : field to search for moodboard by (defaults to moodboard_id)
	*/
	function updateMoodboard($values,$loc_value,$loc_field = "moodboard_id")
	{
		// check user exists
		if($this->getMoodboard($loc_value,$loc_field) !== FALSE)
		{
			if(is_array($values) && !empty($values))
			{
				
				$this->db->where($loc_field,$loc_value);
				
				// update table
				$this->db->update("moodboard",$values);
				
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
		@param id : moodboard_id to delete
	*/
	function deleteMoodboard($id)
	{
		if($id != null)
		{
			$mb = $this->getMoodboard($id)->row();
			
			$this->db->delete("moodboard",array("moodboard_id" => $id));
			
			// delete all comments, favourites, notices etc
			$this->db->delete("favourites",array("f_subject_id" => $id,"f_type" => "moodboard"));
			$this->db->delete("comments",array("c_subject_id" => $id,"c_type"=>"moodboard"));
			$this->db->delete("notices",array("n_object_id" => $id,"n_object_type"=>"moodboard"));
			
			// unlink files
			
			$thumb_path = str_replace(MOODBOARD_URL,MOODBOARD_FINAL_PATH,$mb->m_thumb_url);
			$full_path = str_replace(MOODBOARD_URL,MOODBOARD_FINAL_PATH,$mb->m_full_url);

			
			if(is_file($thumb_path)) { unlink($thumb_path); }
			if(is_file($full_path)) { unlink($full_path); }
					
			
			return true;
		}
		else
		{
			return false;
		}
	}




}


/* End of file moodboard_model.php */
/* Location: ./system/application/models/moodboard_model.php */