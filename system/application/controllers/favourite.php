<?php
/**
	Favourite
	
	may not strictly serve any pages but contains the url for the ajax functions
	since both images and moodboards will user them
**/
class Favourite extends Controller {

	function Favourite()
	{
		parent::Controller();	
		$this->load->model("favourite_model");
	}
	
	function index()
	{
		redirect('','location');
	}
	

	/*
		Favourite link, allows a user to add an image/moodboard to their favourites
		two methods, ajax or normal
	*/
	function add()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			
			$this->load->model("image_model");
			$this->load->model("moodboard_model");
			$this->load->model("user_model");
			$base = base_url();
			
			if($this->favourite_model->favouriteExists($this->session->userdata("user_id"),$_POST["subject_id"],$_POST["object_type"]) == true)
			{
			
				$out_arr = array("msg" => "false");
				
			}
			else
			{
				
				// need to add a new favourite
				$insert_data = array(
					"f_subject_id" => $_POST["subject_id"],
					"f_user_id" => $this->session->userdata("user_id"),
					"f_type" => $this->input->xss_clean($_POST["object_type"])
				);
				
				$insert = $this->favourite_model->createNewFavourite($insert_data);
				
				// get the request object
				if($_POST["object_type"] == "image")
				{
					$obj = $this->image_model->getImage($this->input->xss_clean($_POST["subject_id"]))->row();
					
					$obj_user_id = $obj->i_user_id;
					$obj_name = $obj->i_title;
				}
				else
				{
					$obj = $this->moodboard_model->getMoodboard($this->input->xss_clean($_POST["subject_id"]))->row();
					
					$obj_user_id = $obj->m_user_id;
					$obj_name = $obj->m_title;
				}
				
				
				// check for upload_comments in notice format string
				$createNotice = true;
				$subject_user = $this->user_model->getUser($obj_user_id)->row();
				
				if($_POST["object_type"] == "image")
				{
					if(strpos($subject_user->u_notice_format,"upload_comments") === FALSE)
					{
						$createNotice = false;
					}
				}
				else if($_POST["object_type"] == "moodboard")
				{
					if(strpos($subject_user->u_notice_format,"mb_comments") === FALSE)
					{
						$createNotice = false;
					}
				}
				
				if($insert !== false && $createNotice == true)
				{	
					// success
					/*
						Begin notice insert
					*/
					$notice_insert = array(
						"n_object_user_id" => $obj_user_id,
						"n_object_id" => $_POST["subject_id"],
						"n_object_type" => $_POST["object_type"],
						"n_action_user_id" => $this->session->userdata("user_id"),
						"n_type" => "favourite",
						"n_new" => 1,
						"n_html" => ""
					);
					
					
					// now html
					$notice_html = '<span class="notice_date">'.date("F j, Y, G:i").'</span> - 
							<a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="'.$this->session->userdata("username").'\'s profile">'.$this->session->userdata("username").'</a> added your ';
							
					if($_POST["object_type"] == "image")
					{
						$n_url = $base.'image/view/'.$_POST["subject_id"].'/';			
						
						$notice_html .= 'upload <a href="'.$n_url.'" title="View this image">'.$obj_name.'</a>';
					}
					else if($_POST["object_type"] == "moodboard")
					{
						$n_url = $base.'moodboard/view/'.$_POST["subject_id"].'/';			
						
						$notice_html .= 'moodboard <a href="'.$n_url.'" title="View this moodboard">'.$obj_name.'</a>';
					}
					
					$notice_html .= ' to their favourites';
					
					$notice_insert["n_html"] = $notice_html;
					
					$this->load->model("notice_model");
					$this->notice_model->createNewNotice($notice_insert);
					/*
						End notice insert
					*/
					$out_arr = array(
						"msg" => "true",
						"newfavli" => '<li class="sul_'.$this->session->userdata("user_id").'"> <a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="View this users profile"> <img src="'.$this->session->userdata("image_url").'" width="30" height="30" alt="User profile image" /> <span>'.$this->session->userdata("username").'</span> </a>'
					);
					
				}
				else if($insert == false)
				{
					$out_arr = array("msg" => "false");
					
				}
				else
				{
					$out_arr = array(
						"msg" => "true",
						"newfavli" => '<li class="sul_'.$this->session->userdata("user_id").'"> <a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="View this users profile"> <img src="'.$this->session->userdata("image_url").'" width="30" height="30" alt="User profile image" /> <span>'.$this->session->userdata("username").'</span> </a>'
					);
				}
				
				echo json_encode($out_arr);
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

/* End of file favourite.php */
/* Location: ./system/application/controllers/favourite.php */