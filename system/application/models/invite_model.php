<?php

class invite_model extends Model {


////////////// GET Functions
	
	/**
		This function returns a invite record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	function getInvite($value,$field = "invite_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('invite');
		
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
		Inserts an arbitary amount of records, with no user
		id specified
		
		@param count : number of records to generate
	*/
	function generateInvites($count = 5)
	{
		
		$words = array(
			"shinobi",
			"samurai",
			"ninja",
			"warrior",
			"hokage"
		);
		
		$this->load->helper("string");
		
		
		for($i = 1; $i <= $count; $i++)
		{
			$invite = array();
			
			$invite["inv_user_id"] = 0;
			$invite["inv_code"] = random_string('unique').$words[rand(0,count($words)-1)];

			$this->db->insert('invites',$invite);
		}
		
		return true;
	}

////////////// UPDATE FUNCTIONS

////////////// DELETE FUNCTIONS

	/*
		Deletes by id, including all related records
		@param id : invite_id to delete
	*/
	function deleteNotice($id)
	{
		if($id != null)
		{
			$this->db->delete("invites",array("invite_id" => $id));
			
			return true;
		}
		else
		{
			return false;
		}
	}


	
}

/* End of file invite_model.php */
/* Location: ./system/application/models/invite_model.php */