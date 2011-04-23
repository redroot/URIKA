<?php

class notice_model extends Model {


/////////// GET Functions
	
		
	/**
		This function returns a notice record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getNotice($value,$field = "notice_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('notices');
		
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
		returns a full list of notices for a user id
		Also handles the 50 limit
		
		@param user_id : the id of the user
		@param get_new : set to true if you want to just grab those witha  new status
		
	**/
	function getUserNotices($user_id, $get_new = false)
	{
		/*
			Checks 50 limit
		*/
		$this->db->where("n_object_user_id", trim($user_id));
		$query = $this->db->get('notices');
		
		if($query->num_rows > 50)
		{
			$to_delete = array_slice($query->result(),50);
			$delete_count = count($to_delete);

			$this->db->where_in("notice_id",$to_delete);
			
			for($i = 1; $i < $delete_count; $i++)
			{
				$this->db->or_where("notice_id",$to_delete[$i]->notice_id);
			}
			
			$delete = $this->db->delete("notices");
			
		}
		
		/*
			end limit where
		*/
	
		if($get_new == true)
		{
			$this->db->where("n_new",1);
		}
		
		$this->db->where("n_object_user_id", trim($user_id));
		$this->db->order_by("n_datetime","desc");
		
		$query = $this->db->get('notices',50);
		
		if($query->num_rows >= 1)
		{
			return $query->result();
		}
		else
		{
			return false;
		}
	}
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new notices into the data base
		
		@param array : array of insert data
		
	*/
	function createNewNotice($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("notices",$array);
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
	*	General purpose update function for notices
	*	
	*	@param (Array)values : array of fields names and value to update
	*	@param loc_value : locator value for finding notice by
	*	@param loc_field : field to search for notice
	*/
	function updateNotice($values,$loc_value,$loc_field = "notice_id")
	{
		// check user exists
		if($this->getNotice($loc_value,$loc_field) !== false)
		{
			if(is_array($values) && !empty($values))
			{
				
				$this->db->where($loc_field,$loc_value);
				
				
				// update table
				$this->db->update("notices",$values);
				
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
	
	/*
		Special function to mark multiple records at once as read
		
		@params ids : array of notice ids to update
		@params new : 1 to mark all as unread, 0 to mark as read
	*/
	function toggleMultipleRead($ids,$new)
	{
		if($new == 1 || $new == 0)
		{
			$update_array = array("n_new"=>$new);
			
			$this->db->where_in("notice_id",$ids);
			
			$update = $this->db->update("notices",$update_array);
			
			if($update == true)
			{
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
		@param id : notice_id to delete
	*/
	function deleteNotice($id)
	{
		if($id != null)
		{
			$this->db->delete("notices",array("notice_id" => $id));
			
			return true;
		}
		else
		{
			return false;
		}
	}

}

/* End of file notice_model.php */
/* Location: ./system/application/models/notice_model.php */