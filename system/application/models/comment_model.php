<?php

class comment_model extends Model {


/////////// GET Functions
	
		
	/**
		This function returns a comment record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	
	function getComment($value,$field = "comment_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('comments');
		
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
		returns a list of comments for an item, all also poster user objects
		with ability to limit offset
		
		@param subject_id : the id of the subject
		@param  page : page to start on
		@param type : image or moodboard, defaults to image
	**/
	function getComments($subject_id,$page = 1,$type = "image")
	{
		$offset = ($page - 1) * 10;
		$this->db->where("c_subject_id", trim($subject_id));
		$this->db->where("c_type",$type);
		$this->db->from("comments");
		$this->db->join("users","comments.c_poster_id = users.user_id");
		$this->db->order_by("c_datetime","desc");
		$this->db->limit(10,$offset);
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
		Get total count of comments on a subject
		
		@param subject_id : id of the object in question
		@param type : type, image or moodboard, defaults to image
	*/
	function getCommentsCount($subject_id,$type = "image")
	{
		$this->db->where("c_subject_id", trim($subject_id));
		$this->db->where("c_type",$type);
		$this->db->from("comments");
		$query = $this->db->get();
		
		if($query->num_rows > 0)
		{
			
			return $query->num_rows;
		}
		else
		{
			return 0;
		}
	}
	
	/**
		returns a php time stamp of a users last comment
		
		@param user_id : the id of the user in question
	**/
	function getLastUserCommentTime($user_id)
	{
		
		$sql = "
			SELECT * FROM comments 
			WHERE c_poster_id = ".$user_id."
			ORDER BY  c_datetime DESC
		
			";
		
		
		
		$query = $this->db->query($sql);
		
		if($query->num_rows == 0)
		{
			return false;
		}
		else
		{
			$row = $query->row();
			
			$phptime = strtotime($row->c_datetime);
			return $phptime;
			
			
		}
	}
	
	

	
////////////// INSERT FUNCTIONS

	/*
		Function to insert new comment into the data base
		
		@param array : array of insert data
		
	*/
	function createNewComment($array)
	{
		if($array != null)
		{
			$result["result"] = $this->db->insert("comments",$array);
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

	/*
		Deletes by id, including all related records
		@param id : comment id to delete
	*/
	function deleteComment($id)
	{
		if($id != null)
		{
			$comment = $this->getComment($id)->row();
			$this->db->delete("comments",array("comment_id" => $id));
			// now delete all notices associated
			$this->db->delete("notices",array("n_object_id"=> $comment->c_subject_id,"n_type"=>"comment"));
			
			return true;
		}
		else
		{
			return false;
		}
	}


	
}

/* End of file comment_model.php */
/* Location: ./system/application/models/comment_model.php */