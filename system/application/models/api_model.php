<?php

class api_model extends Model {


/////////// GET Functions
	

	
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new api record into the data base
		
		@param array : array of insert data
		
	*/
	function createNewAPIRecord($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("api",$array);
			$result["id"] = $this->db->insert_id();
			return $result;
		}
		else
		{
			return false;
		}
	}

////////////// UPDATE FUNCTIONS


////////////// DELETE FUNCTIONS



	
}

/* End of file api_model.php */
/* Location: ./system/application/models/api_model.php */