<?php
/*
	Used for grabbing activity from notices
*/
class activity_model extends Model {


/////////// GET Functions

	/**
		This functions grabs activity from notice records by datetime
		appending user info in the same query with a join
		
		@param offset : offset for the query
		@param limit : how many to return per cheek
		@param user_id : is set, grab follow records
	*/
	function getLatestActivity($offset = 0, $limit = 10, $user_id = null)
	{
		$followed_string = "";
		if($user_id != null && is_numeric($user_id))
		{
			// grab records id
			$this->db->where("f_follower",$user_id);
			$query = $this->db->get("follows");
			
			if($query->num_rows > 0)
			{
				$result = $query->result();
				
				for($i = 0; $i < $query->num_rows; $i++)
				{
					if($result[$i]->f_followed != "")
						$followed_string .= $result[$i]->f_followed.',';
				}
				$followed_string = substr($followed_string,0,-1);
			}
		}
		
		// query string grabbed now grab the latest activity
		$sql = '
			SELECT notices.*, users.user_id, users.u_username
			FROM notices
			LEFT JOIN users on notices.n_object_user_id = users.user_id
		';
		
		// check for includes
		if($followed_string != "")
		{
			$sql .= '
			WHERE notices.n_action_user_id IN ('.$followed_string.')
			AND notices.n_type != "follow"
			';
		}
		else
		{
			$sql .= '
			WHERE notices.n_type != "follow"
			';
		}
		
		// the rest
		$sql .= '
			ORDER BY notices.n_datetime DESC
			LIMIT '.$offset.','.$limit.'
		';
		
		$query = $this->db->query($sql);
		
		if($query->num_rows == 0)
		{
			return false;
		}
		else
		{
			$result = $query->result();
			$base = base_url();
			
			// now replace ordinary string with genreal strings
			for($i = 0; $i < $query->num_rows; $i++)
			{
				$old_string = str_replace(' - ','',$result[$i]->n_html);
				$user_link = ' <a href="'.$base.'user/u/'.$result[$i]->u_username.'/" title="view user\'s profile"><strong>'.$result[$i]->u_username.'</strong></a>\'s ';
				$result[$i]->n_html = str_replace(' your ',$user_link, $old_string);
				
			}

			return $result;
			
			
		}
	}


	
}

/* End of file activity_model.php */
/* Location: ./system/application/models/activity_model.php */