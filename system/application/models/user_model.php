<?php

class User_model extends Model {
	
	/**
	*	This function checks to see if a user exists with a certain atributte
	*	@param field a field to check in
	*	@param value: the check to check for
	*/
	function user_attr_exists($field,$value)
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('users');
		
		if($query->num_rows == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	*	This function adds a user to the database from the user/create_user page
	*/
	function create_new_user()
	{
		// need to create some bits for the insert:
		// auth_key, confirmation string
		
		$this->load->helper('string');
		
		$auth_key = random_string('unique');
		$conf_string = random_string('alnum',16);
		
		$new_member_insert_data = array(
			'u_username' => $this->input->post('s_username'),
			'u_email' => $this->input->post('s_email'),
			'u_password' => md5($this->input->post('s_password_a')),
			'u_authkey' => $auth_key,
			'u_confirmation_string' => $conf_string,
			'u_notice_format' => "upload_favs#upload_comments#mb_favs#mb_comments#collection_add"
		);
		
		$result["insert"] = $this->db->insert('users', $new_member_insert_data);
		$result["conf_string"] = $conf_string;
		return $result;
	}
	
	/**
	*	Validate u and p to sign user in
	*/
	function validate_user()
	{
		$this->db->where('u_username', $this->input->post('l_username'));
		$this->db->where('u_password', md5($this->input->post('l_password')));
		$query = $this->db->get('users');
		
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
	*	Check a user is verified
	*/
	function is_user_verified()
	{
		$this->db->where("u_username", $this->input->post('l_username'));
		$this->db->where("u_verified",1);
		$query = $this->db->get('users');
		
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
	*	Takes an email and hash to verify user
	*	@param email : users email
	*	@param hash : hash to use in check
	*/
	function verify_user($email,$hash)
	{
		$this->db->where("u_email",$email);
		$this->db->where("u_confirmation_string",$hash);
		$query = $this->db->get("users");
		
		if($query->num_rows == 1)
		{
			// if the user is found, need to modify verify to 1
			$row = $query->row();
			
			
			if($row->u_verified == 1)
			{
				return "already_verified";
			}
			else
			{
				$update_data = array(
					"u_verified" => 1
				);
				
				$this->db->where("user_id",$row->user_id);
				
				// update table
				$this->db->update("users",$update_data);
				
				return "true";
			}
		}
		else
		{
			return "false";
		}
	}

/////////// GET Functions
	
		
	/**
		This function returns as user found by a specified field. If no field
		is specified, then used user_id isntead
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getUser($value,$field = "user_id")
	{
		$sql = "
			SELECT users.*,
			(SELECT count(images.image_id) FROM images WHERE images.i_user_id = users.user_id) as u_image_count,
			(SELECT count(moodboard.moodboard_id) FROM moodboard WHERE moodboard.m_user_id = users.user_id) as u_mb_count,
			(SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_user_id = users.user_id) as u_favs
			FROM users
			WHERE ".$field." = '".$value."'
		";
		
		//$this->db->where(trim($field), trim($value));
		$query = $this->db->query($sql);
		
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
		This function returns a list of stats for the user, heavier and comprehensive than
		above getUser function
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getUserStats($value,$field = "user_id")
	{
		$sql = "
			SELECT users.*,
			(SELECT count(images.image_id) FROM images WHERE images.i_user_id = users.user_id) as u_image_count,
			(SELECT count(moodboard.moodboard_id) FROM moodboard WHERE moodboard.m_user_id = users.user_id) as u_mb_count,
			(SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_user_id = users.user_id) as u_favs,
			(SELECT count(collections.collection_id) FROM collections WHERE collections.col_user_id = users.user_id) as u_cols,
			(SELECT count(comments.comment_id) FROM comments WHERE comments.c_poster_id = users.user_id) as u_comments_count,
			(SELECT count(comments.comment_id) FROM comments WHERE comments.c_subject_user_id = users.user_id) as u_comments_other
			FROM users
			WHERE ".$field." = '".$value."'
		";
		
		$query = $this->db->query($sql);
		
		if($query->num_rows == 1)
		{
			$row = $query->row();
			
			$other_favs = 0;
			
			// 2nd queries to get how many favourites you've got in images and moodboard
			$images_sql = "
				SELECT favourites.*,images.*
				FROM favourites
				LEFT JOIN images ON images.image_id = favourites.f_subject_id
				WHERE images.i_user_id = '".$row->user_id."' AND favourites.f_type = 'image'
			";
			
			$img_query = $this->db->query($images_sql);
			
			if($img_query->num_rows > 0)
			{
				$other_favs += $img_query->num_rows;
			}
			
			// 2nd query to get how many favourites you've got
			$moodboard_sql = "
				SELECT favourites.*, moodboard.*
				FROM favourites
				LEFT JOIN moodboard ON moodboard.moodboard_id = favourites.f_subject_id
				WHERE moodboard.m_user_id = '".$row->user_id."' AND favourites.f_type = 'moodboard'
			";
			
			$mb_query = $this->db->query($moodboard_sql);
			
			if($mb_query->num_rows > 0)
			{
				$other_favs += $mb_query->num_rows;
			}
			
			$rtn = array(
				"images" => $row->u_image_count,
				"mbs" => $row->u_mb_count,
				"favourites" => $row->u_favs,
				"collections" => $row->u_cols,
				"comments" => $row->u_comments_count,
				"other_comments" => $row->u_comments_other,
				"other_favs" => $other_favs
			);
			
			return $rtn;
		}
		else
		{
			return false;
		}
	}
	
	/**
		Grabs a list of users according to specifed criteria. Custom query
		used to save database load
		
		@param search_val : a value to query names by
		@param offset : a seach offset
		@param limit : how many users to return
		@param followed : handle following followed by etc
		@param order_by : the field to order by
		@param order_dir : the director to roder by
	**/
	function getUsers($search_val = "",$offset = 0,$limit = 20, $followed = "", $order_by = "", $order_dir = "")
	{
		// grab an huge count of everything
		$sql = "SELECT DISTINCT u.*, 
				(SELECT count(images.image_id) FROM images WHERE images.i_user_id = u.user_id) as images_count,
				(SELECT count(follows.f_follower) FROM follows WHERE follows.f_follower = u.user_id) as following_count,
				(SELECT count(follows.f_followed) FROM follows WHERE follows.f_followed = u.user_id) as followed_count
				FROM users AS u 
				";
			
		// search	
		if($search_val != "")
		{
			$sql .= "
				WHERE u.u_username LIKE '%".$search_val."%' 
			";
		}
		
		// followed thing, grab follows
		if($followed != "")
		{
			$ids_string = "";
			
			// get username
			$username = explode("_",$followed);
			$username = $username[1];
			
			$user = $this->getUser($username,"u_username");
			
			if($user != false)
			{
				$user = $user->row();
				
				
				if(strpos($followed,"following_") !== FALSE)
				{
					$array = $this->getUserFollowedBy($user->user_id);
				}
				else if(strpos($followed,"followedby_") !== FALSE) 
				{
					$array = $this->getUserFollows($user->user_id);
				}

				
				if($array["count"] > 0)
				{
					foreach($array["result"] as $obj)
					{
						
						$ids_string .= $obj->user_id.',';
						
					}
					$ids_string = substr($ids_string,0,-1);
				}
				
				if($ids_string != "")
				{
				
				
					// now append to the sql
					if(strpos($sql,"WHERE u.u_username") !== FALSE)
					{
						$sql .= '
							AND u.user_id IN('.$ids_string.')
						';
					}
					else
					{
						$sql .= '
							WHERE u.user_id IN('.$ids_string.')
						';
					}
				}
				else
				{
						// now append to the sql
					if(strpos($sql,"WHERE u.u_username") !== FALSE)
					{
						$sql .= '
							AND u.user_id IN(0)
						';
					}
					else
					{
						$sql .= '
							WHERE u.user_id IN(0)
						';
					}
				}
			}
			
		}

		
		$sql .= "
			GROUP BY u.user_id
		";
		
		if($order_by != "" && ($order_dir == "ASC" || $order_dir == "DESC"))
		{
			$sql .= "
			ORDER BY ".trim($order_by)." ".strtoupper($order_dir)."
			";
		}
		
		$total = $this->db->query($sql)->num_rows;
		
		// get get limited
		$sql = $sql." LIMIT ".$offset.", ".$limit.";";
		$result = $this->db->query($sql);
		
		if($result->num_rows > 0)
		{
			$rtn["result"] = $result->result();
			$rtn["total"] = $total;
			
			return $rtn;
		}
		else
		{
			return false;
		}

		
		
	}
	
	/**
	*	Gets all image records by user ID
	*
	*	@param user_id : the user id to use
	*	@param offset : offset to use. if set to false returns all images
	*/
	function getUserImages($user_id,$offset = 0)
	{
		$sql = "
			SELECT images.*, users.u_username,
			(SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_subject_id = images.image_id AND favourites.f_type = 'image') as i_favs,
			(SELECT count(comments.comment_id) FROM comments WHERE comments.c_subject_id = images.image_id AND comments.c_type = 'image') as i_comments
			FROM images
			LEFT JOIN users ON users.user_id = ".$user_id."
			WHERE images.i_type = 'image' AND images.i_user_id = ".$user_id."
			ORDER BY i_datetime DESC
		";
		
		if($offset === "false")
		{
			$query = $this->db->query($sql);
			
			$rtn["count"] = $query->num_rows;
			$rtn["total"] = $query->num_rows;
			$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		}
		else
		{
			// delete as normal
			$limit_sql = $sql .
			"
				LIMIT ".$offset.", 20
			";
			
			$total = $this->db->query($sql)->num_rows;
			
			$query = $this->db->query($limit_sql);
			
			$rtn["total"] = $total;
			$rtn["count"] = $query->num_rows;
			$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		}
			
		return $rtn;
		
	}
	
	/**
	*	Gets all moodboard records by user ID
	*
	*	@param user_id : the user id to use
	*   @param offset :offset to use
	*/
	function getUserMoodboards($user_id, $offset = 0)
	{
		$sql = "
			SELECT moodboard.*, users.u_username,
			(SELECT count(favourites.favourite_id) FROM favourites WHERE favourites.f_subject_id = moodboard.moodboard_id AND favourites.f_type = 'moodboard') as m_favs,
			(SELECT count(comments.comment_id) FROM comments WHERE comments.c_subject_id = moodboard.moodboard_id AND comments.c_type = 'moodboard') as m_comments
			FROM moodboard
			LEFT JOIN users ON users.user_id = ".$user_id."
			WHERE m_user_id = ".$user_id."
			ORDER BY m_datetime DESC
		";
		
		if($offset === "false")
		{
			$query = $this->db->query($sql);
			
			$rtn["count"] = $query->num_rows;
			$rtn["total"] = $query->num_rows;
			$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		}
		else
		{
			// delete as normal
			$limit_sql = $sql .
			"
				LIMIT ".$offset.", 20
			";
			
			$total = $this->db->query($sql)->num_rows;
			
			$query = $this->db->query($limit_sql);
			
			$rtn["total"] = $total;
			$rtn["count"] = $query->num_rows;
			$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		}
			
		return $rtn;
		
	}
	
	/**
	*	Gets all favourite records by user ID
	*	attaching the right object to each record
	*
	*	@param user_id : the user id to use
	*	@param offset: offset to use
	*/
	function getUserFavs($user_id,$offset = 0)
	{
		
		$this->db->where("f_user_id", trim($user_id));
		$this->db->from("favourites");
		$this->db->order_by("favourite_id","DESC");
		
		$total = $this->db->get()->num_rows;
		
		$this->db->where("f_user_id", trim($user_id));
		$this->db->from("favourites");
		$this->db->order_by("favourite_id","DESC");
		$this->db->limit(20,$offset);
		$query = $this->db->get();
		
		$rtn["total"] = $total;
		$rtn["count"] = $query->num_rows;
		
		if($query->num_rows > 0)
		{
			
			// now we have to do a subquery for each on
			// no conditionals in mySQL really so this is the safest best
			$result = $query->result();
			$new_result_array = array();
			
			for($i = 0; $i < $query->num_rows; $i++)
			{
				if($result[$i]->f_type == "image")
				{
					$this->db->where("image_id", $result[$i]->f_subject_id);
					$this->db->from("images");
					$this->db->join("users","users.user_id = images.i_user_id");
					$subq = $this->db->get()->row(); // trusting it exists since we have so many checks in place already
				}
				else if($result[$i]->f_type == "moodboard")
				{
					$this->db->where("moodboard_id", $result[$i]->f_subject_id);
					$this->db->from("moodboard");
					$this->db->join("users","users.user_id = moodboard.m_user_id");
					$subq = $this->db->get()->row(); // trusting it exists since we have so many checks in place already
				}
				
				$new_result_array[] = (object) array_merge((array) $result[$i],(array) $subq);
			}
			
			$rtn["result"] = $new_result_array;
		}
		else
		{
			$rtn["result"] = 0;
		}
		
		return $rtn;
		
	}

	
	/**
	*	Gets all collection records by user ID
	*
	*	@param user_id : the user id to use
	*/
	function getUserCollections($user_id)
	{
		$this->db->where("col_user_id", trim($user_id));
		$query = $this->db->get('collections');
		
		$rtn["count"] = $query->num_rows;
		$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		
		return $rtn;
	}
	
	/**
	*	Gets all follow records specify those a user (user_id) follows
	*
	*	@param user_id : the user id to use
	*	@param offset : any offset to use
	*	@param recent : return the last 10 results
	*/
	function getUserFollows($user_id,$offset = null,$recent = false)
	{
		// TODO: JOIN TO GRAB USER INFO
		
		$this->db->where("f_follower", trim($user_id));
		$this->db->from("follows");
		$this->db->join("users","users.user_id = follows.f_followed");
		$this->db->order_by("follow_id","RANDOM");
		
		if($recent != false)
		{
			$this->db->limit(10);
		}
		else if($offset != null && is_numeric($offset))
		{
			$this->db->limit(20,$offset);
		}
		
		
		$query = $this->db->get();
	
		
		$rtn["count"] = $query->num_rows;
		$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		
		return $rtn;
	}
	
	/**
	*	Gets all follow records specify those a user (user_id) is followed by
	*
	*	@param user_id : the user id to use
	*	@param offset : if not null, used for offset
	*	@param recent : return the last 10 results
	*/
	function getUserFollowedBy($user_id,$offset = null,$recent = false)
	{
		
		$this->db->where("f_followed", trim($user_id));
		$this->db->from("follows");
		$this->db->join("users","users.user_id = follows.f_follower");
		$this->db->order_by("follow_id","RANDOM");
		
		if($recent != false)
		{
			$this->db->limit(10);
		}
		else if($offset != null && is_numeric($offset))
		{
			$this->db->limit(20,$offset);
		}
		
		
		$query = $this->db->get();
		
		$rtn["count"] = $query->num_rows;
		$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		
		return $rtn;
	}
	
	/**
	*	Gets all notice records by id, object and subject
	*
	*	@param user_id : the user id to use
	*/
	function getUserNotices($user_id)
	{
		// have to do custom query
		
		$user_id = trim($user_id);
		$sql = "
			SELECT * FROM notices 
			WHERE n_subject_user_id = ".$user_id." OR n_action_user_id = ".$user_id."
			INNER JOIN users AS s_user ON s_user.user_id = notices.n_subject_user_id
			INNER JOIN users as a_user ON a_user.user_ud = notices.n_action_user_id;
		";
		
		$query = $this->db->query($sql);
		
		
		$rtn["count"] = $query->num_rows;
		$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		
		return $rtn;
	}
	
	/**
	*	Gets all comment records by id, poster and subject
	*
	*	@param user_id : the user id to use
	*/
	function getUserComments($user_id, $type = null)
	{
		// 2nd param to specify which type if any poster/subject
		
		$user_id = trim($user_id);
		
		if($type == null)
		{
			$sql = "
				SELECT * FROM comments 
				WHERE c_poster_id = ".$user_id." OR c_subject_user_id = ".$user_id."
				INNER JOIN users AS c_user ON c_user.user_id = comments.c_subject_user_id
				INNER JOIN users as p_user ON p_user.user_ud = comments.c_poster_id;
			";
		}
		else if($type == "poster")
		{
			$sql = "
				SELECT * FROM comments 
				WHERE c_poster_id = ".$user_id."
				INNER JOIN users as p_user ON p_user.user_id = comments.c_poster_id;
			";
		}
		else if($type == "comment")
		{
			$sql = "
				SELECT * FROM comments 
				WHERE c_subject_user_id = ".$user_id."
				INNER JOIN users AS c_user ON c_user.user_id = comments.c_subject_user_id
			";
		}
			
		$query = $this->db->query($sql);
		
		
		$rtn["count"] = $query->num_rows;
		$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		
		return $rtn;
	}
	
	/*
		Returns an array of user invites
		
		@param user_id : if of user to check
	*/
	function getUserInvites($user_id)
	{
		$this->db->where("inv_user_id",$user_id);
		$query = $this->db->get("invites");
		
		$rtn["count"] = $query->num_rows;
		$rtn["result"] = ($query->num_rows > 0) ? $query->result() : 0;
		
		return $rtn;
	}
	
	/*
		Checks if an e-mail is already on a blacklist
		
		@param email : e-mail to check for
	*/
	function checkBlacklist($email)
	{
		$this->db->where("bl_email",trim($email));
		$query = $this->db->get("blacklist");
		
		if($query->num_rows > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
////////////// INSERT FUNCTIONS

	/**
	*	Inserts a new follow record forr a user
	*	
	*	@param user_id: the user who is following
	*	@param subject_id: the user is to be followed;
	*/
	function addFollow($user_id,$subject_id)
	{
		// first check record doesnt already exists
		$this->db->where("f_follower", trim($user_id));
		$this->db->where("f_followed", trim($subject_id));
		$query = $this->db->get('follows');
		
		if($query->num_rows == 0)
		{
			$new_follow_insert_data = array(
				'f_follower' => $user_id,
				'f_followed' => $subject_id
			);
		
			$insert = $this->db->insert('follows', $new_follow_insert_data);
			if($insert != FALSE) return true;
		}
		else
		{
			return false;
		}
	}

////////////// UPDATE FUNCTIONS

	/**
	*	General purpose update function for users
	*	
	*	@param (Array)values : array of fields names and value to update
	*	@param loc_value : locator value for finding user by
	*	@param loc_field : field to search for user by (defaults to user_id)
	*/
	function updateUser($values,$loc_value,$loc_field = "user_id")
	{
		// check user exists
		if($this->user_attr_exists($loc_field,$loc_value) == TRUE)
		{
			if(is_array($values) && !empty($values))
			{
				
				$this->db->where($loc_field,$loc_value);
				
				// update table
				$this->db->update("users",$values);
				
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
	
	/**
	*	Delete a follow record by user and subject id
	*
	*	@param user_id: the user who is following
	*	@param subject_id: the user is being followed;
	*/
	function deleteFollow($user_id,$subject_id)
	{
		$delete_data = array(
			'f_follower' => $user_id,
			'f_followed' => $subject_id
		);
		
		$delete = $this->db->delete('follows',$delete_data);
		return $delete;
	}
	
	/*
		Deletes user and all related records:
		images,
		comments,
		notices,
		moodboards,
		favourites'
		api requests, 
		collections, 
		follows
		
		An admin only function
		
		@param id : id of the user to delete
	*/
	function deleteUser($id)
	{
		if(isAdmin())
		{
			if($id != null)
			{
				$this->session->unset_userdata("username");
				$this->session->unset_userdata("user_id");
				$this->session->unset_userdata("is_logged_in");
			
				$user = $this->getUser($id)->row();
				
				// do other simple records first
				// (is there such a thing)
				
				$this->db->delete("api",array("r_user_id" => $id));
				$this->db->delete("collections",array("col_user_id" => $id));
				$this->db->delete("follows",array("f_followed" => $id));
				$this->db->delete("follows",array("f_follower" => $id));
				
				// now images since its beastly 
				// will hav to do the same for moodbooards
				
				$images = $this->getUserImages($id,"false");
				
				if($images["result"] != 0)
				{
					for($i = 0; $i < $images["count"]; $i++)
					{
						if(isset($images["result"][$i]))
						{
							$this->db->delete("images",array("image_id" => $images["result"][$i]->image_id));
				
							// delete all comments, favourites, notices etc
							$this->db->delete("favourites",array("f_subject_id" => $images["result"][$i]->image_id));
							$this->db->delete("comments",array("c_subject_id" => $images["result"][$i]->image_id,"c_type"=>"image"));
							$this->db->delete("notices",array("n_object_id" => $images["result"][$i]->image_id,"n_object_type"=>"image"));
							
							// unlink files
							
							$thumb_path = str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$images["result"][$i]->i_thumb_url);
							$full_path = str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$images["result"][$i]->i_full_url);

							
							if(is_file($thumb_path)) { unlink($thumb_path); }
							if(is_file($full_path)) { unlink($full_path); }
						}
					}
				}
				
				// moodboard bit here
				
				$mbs = $this->getUserMoodboards($id,"false");
				
				if($mbs["result"] != 0)
				{
					for($i = 0; $i < $mbs["count"]; $i++)
					{
						
						$this->db->delete("moodboard",array("moodboard_id" => $mbs["result"][$i]->id));
						
						// delete all comments, favourites, notices etc
						$this->db->delete("favourites",array("f_subject_id" => $mbs["result"][$i]->id,"f_type" => "moodboard"));
						$this->db->delete("comments",array("c_subject_id" => $mbs["result"][$i]->id,"c_type"=>"moodboard"));
						$this->db->delete("notices",array("n_object_id" => $mbs["result"][$i]->id,"n_object_type"=>"moodboard"));
						
						// unlink files
						
						$thumb_path = str_replace(MOODBOARD_URL,MOODBOARD_FINAL_PATH,$mbs["result"][$i]->m_thumb_url);
						$full_path = str_replace(MOODBOARD_URL,MOODBOARD_FINAL_PATH,$mbs["result"][$i]->m_full_url);

						
						if(is_file($thumb_path)) { unlink($thumb_path); }
						if(is_file($full_path)) { unlink($full_path); }
								
						
					}
				}
				
				// now do any other comments, notices, etc where this user might be involved
				
				// delete avatar if any
				
				if(strpos($user->u_profile_id,"http://") !== FALSE)
				{
					$avatar_url = str_replace("usegravatar::","",$user->u_profile_id);
					$avatar_path =  str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$avatar_url);
					if(is_file($avatar_path)) { unlink($avatar_path); }
				}
				
				$this->db->delete("notices",array("n_object_user_id" => $id));
				$this->db->delete("notices",array("n_action_user_id" => $id));
				$this->db->delete("comments",array("c_subject_user_id" => $id));
				$this->db->delete("comments",array("c_poster_id" => $id));
				$this->db->delete("favourites",array("f_user_id" => $id));
				
				// and finnaly, delete the user record
				$this->db->delete("users",array("user_id"=>$id));
				
				
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
}


/* End of file user_model.php */
/* Location: ./system/application/models/user_model.php */