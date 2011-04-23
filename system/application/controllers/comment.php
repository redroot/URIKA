<?php
/**
	Comment controller
	
	may not strictly serve any pages but contains the url for the ajax functions
	since both images and moodboards will user them
**/
class Comment extends Controller {

	function Comment()
	{
		parent::Controller();	
		$this->load->model("comment_model");
	}
	
	function index()
	{
		redirect('','location');
	}
	
	/*
		Add function, used for ajax comments system on
		images and moodboards
	*/
	function add()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			$last_comment = $this->comment_model->getLastUserCommentTime($_POST["user_id"]);
			$out = "";
			// check last comment time
			if($last_comment != false)
			{
				// check time
				$time_diff = time() - $last_comment;
				
				if($time_diff < 120)
				{
					$out = json_encode(array("result"=>"toosoon"));
				}
				
			}
			
			if($out == "")
			{
				$insert_array = array (
					"c_poster_id" => $this->input->xss_clean($_POST["user_id"]),
					"c_subject_id" => $this->input->xss_clean($_POST["subject_id"]),
					"c_subject_user_id" => $this->input->xss_clean($_POST["sub_user_id"]),
					"c_type" => $this->input->xss_clean($_POST["type"]),
					"c_content" => htmlentities(nl2br($this->input->xss_clean($_POST["text"]))),
					"c_privacy" => 0
				);
				
				$insert = $this->comment_model->createNewComment($insert_array);
				
				if($insert != false)
				{
				
				
					$base = base_url();
					
					// get comment user details
					$this->load->model("user_model");
					$user = $this->user_model->getUser($this->input->xss_clean($_POST["user_id"]));
					$user = $user->row();
					
					$isUploader = "false";
					
					if($_POST["user_id"] == $this->input->xss_clean($_POST["sub_user_id"]))
						$isUploader = "true";
					
					$profile_url = getUserProfileURL($user->u_profile_id,$user->u_email);
					
					$json = array(
						"result" => "true",
						"user_url" => $base."user/u/".$user->u_username."/",
						"profile_url" => $profile_url,
						"datetime" => date("F j, Y, G:i"),
						"username" => $user->u_username,
						"is_uploader" => $isUploader,
						"comment_id" => $insert["id"]
						
					);
					
					/*
						Notice insert
						
						no notice if you did it on your thing
					*/
					
					// check for upload_comments in notice format string
					$createNotice = true;
					$subject_user = $this->user_model->getUser($this->input->xss_clean($_POST["sub_user_id"]))->row();
					
					if($_POST["type"] == "image")
					{
						if(strpos($subject_user->u_notice_format,"upload_comments") === FALSE)
						{
							$createNotice = false;
						}
					}
					else if($_POST["type"] == "moodboard")
					{
						if(strpos($subject_user->u_notice_format,"mb_comments") === FALSE)
						{
							$createNotice = false;
						}
					}
					
					
					if($isUploader == "false" && $createNotice == true)
					{
						$notice_insert = array(
							"n_object_user_id" => $this->input->xss_clean($_POST["sub_user_id"]),
							"n_object_id" => $this->input->xss_clean($_POST["subject_id"]),
							"n_object_type" => $this->input->xss_clean($_POST["type"]),
							"n_action_user_id" => $this->input->xss_clean($_POST["user_id"]),
							"n_type" => "comment",
							"n_new" => 1,
							"n_html" => ""
						);
						
						// now html
						$notice_html = '<span class="notice_date">'.$json["datetime"].'</span> - 
								<a href="'.$json["user_url"].'" title="'.$json["username"].'\'s profile">'.$json["username"].'</a> commented on your ';
								
						if($_POST["type"] == "image")
						{
							$n_url = $base.'image/view/'.$_POST["subject_id"].'/#comment_'.$insert["id"];				
							
							$notice_html .= 'upload <a href="'.$n_url.'" title="View comment on this image">'.$_POST["subject_name"].'</a>';
						}
						else if($_POST["type"] == "moodboard")
						{
							$n_url = $base.'moodboard/view/'.$_POST["subject_id"].'/#comment_'.$insert["id"];				
							
							$notice_html .= 'moodboard <a href="'.$n_url.'" title="View comment on this moodboard">'.$_POST["subject_name"].'</a>';
						}
						
						$notice_insert["n_html"] = $notice_html;
						
						$this->load->model("notice_model");
						$this->notice_model->createNewNotice($notice_insert);
					}
					/*
						End notice insert
					*/
					
					$out = json_encode($json);
				}
				else
				{
					$out = json_encode(array("result"=>"false"));
				}
				
			}
			
			echo $out;
		}
		else
		{
			$data = array(
			"errorTitle" => "Request Denied",
			"content" => "An error has occurred: you cannot access this page from the browser."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
	/*
		Delete function, allows a user to delete their own comment
	*/
	function delete()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			if(isset($_POST["delete_id"]) && is_numeric($_POST["delete_id"]))
			{
				// check this post belongs to the user
				
				$comment = $this->comment_model->getComment($this->input->xss_clean($_POST["delete_id"]));
				
				if($comment != false)
				{
					$comment = $comment->row();
					
					if($comment->c_poster_id == $this->session->userdata("user_id"))
					{
						// now delete the comment
						if($this->comment_model->deleteComment($_POST["delete_id"]) == true)
						{
							echo "true";
						}	
						else
						{
							echo "false";
						}
					}
					else
					{
						echo "false";
					}
				}
				else
				{
					echo "false";
				}
			}
			else
			{
				echo "false";
			}
		}
		else
		{
			$data = array(
			"errorTitle" => "Request Denied",
			"content" => "An error has occurred: you cannot access this page from the browser."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
}

/* End of file comment.php */
/* Location: ./system/application/controllers/comment.php */