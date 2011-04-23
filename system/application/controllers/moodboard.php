<?php

class Moodboard extends Controller {

	function Moodboard()
	{
		parent::Controller();	
		$this->load->model("moodboard_model");
		$this->load->model("collection_model");
	}
	
	function index()
	{
		//$this->load->view('welcome_message');
	}
	
	/*
		Allows the user to view a moodboard
	*/
	function view($id)
	{
		$mb = $this->moodboard_model->getMoodboard($id);
		
		if($mb !== false)
		{
			$mb = $mb->row();
			
			$base = base_url();
			
			$this->load->model("user_model");
		
			$user = $this->user_model->getUser($mb->m_user_id);
			$user = $user->row();
			
			$profile_url = getUserProfileURL($user->u_profile_id,$user->u_email);
					
			$this->load->model("favourite_model");
			
			$datetime = date("F j, Y",strtotime($mb->m_datetime));
			
			// get page
			$page = (isset($q_vars->comment_page) == true) ? $q_vars->comment_page : 1;

			// sort tags out
			$tags = explode(',',$mb->m_tags);
			
			$tags_html = "<strong>Tags:</strong> ";
			$count = count($tags);
			for($i = 0; $i < $count; $i++)
			{
				if($tags[$i] != "")
					$tags_html .= '<a class="tag_link" href="'.$base.'browse/?tag='.str_replace(" ","+",$tags[$i]).'" title="Search by this tag">'.$tags[$i].'</a>';
			}
			
			$controlsHTML = array();
			if(isLoggedIn() == true)
			{
				// determine if this user is the owner of this image
				if($this->session->userdata("user_id") == $mb->m_user_id)
				{
					$controlsHTML["editLink"] = $base."moodboard/edit/".$mb->moodboard_id."/";
					$controlsHTML["deleteLink"] = $base."moodboard/delete/".$mb->moodboard_id."/";
				}
				else // add favourite link
				{
					
					if($this->favourite_model->favouriteExists($this->session->userdata("user_id"),$mb->moodboard_id,"moodboard") == false)
					{
						$controlsHTML["favlink"] ='<li><span class="fav_link" id="ajax_fav" onclick="addFavourite('.$mb->moodboard_id.');" >Add to Favourites</span></li>';
					}
					else
					{
						$controlsHTML["favlink"] ='<li><span class="fav_link" >Favourited!</span></li>';
					}
				}
			
				
			
				// now increment views
				$update_data = array(
					"m_views" => $mb->m_views + 1
				);
				$this->moodboard_model->updateMoodboard($update_data,$mb->moodboard_id);
			}
			
			// comments + favourites
			$this->load->model("comment_model");
			
			$comments = $this->comment_model->getComments($mb->moodboard_id,$page,"moodboard");
			$comments_html = "<p class='nocomments'>No Comments</p>";
			$comments_count = 0;
			$comments_total = $this->comment_model->getCommentsCount($mb->moodboard_id,"moodboard");
			
			$user_profile_url = "";
			if(isLoggedIn() == true)
			{
				$signedin = $this->user_model->getUser($this->session->userdata("user_id"));
				$signedin = $signedin->row();
				$user_profile_url = getUserProfileURL($signedin->u_profile_id,$signedin->u_email);
			}
			$comments_buttons = "";
			
			if(is_array($comments))
			{
				$comments_html = "";
				
				$comments_count = count($comments);
				
				for($i = 0; $i < $comments_count; $i++)
				{
					$comment_data = array(
						"profile_url" => getUserProfileURL($comments[$i]->u_profile_id,$comments[$i]->u_email),
						"user_url" => $base."user/u/".$comments[$i]->u_username."/",
						"username" => $comments[$i]->u_username,
						"comment_id" => $comments[$i]->comment_id,
						"date_uploader" => date("F j, Y, G:i",strtotime($comments[$i]->c_datetime)),
						"comment_text" => html_entity_decode($comments[$i]->c_content),
						"classes" => "",
						"showdelete" => "false"
					);
					// set delete link think
					if($comments[$i]->c_poster_id == $this->session->userdata("user_id"))
					{
						
						$comment_data["showdelete"] = "true";
					}
					
					
					// get classes
					if($i%2 == 1)
					{
						$comment_data["classes"] = "even";
					}
					else
					{
						$comment_data["classes"] = "odd";
					}
					
					// uploader comment
					if($comments[$i]->user_id == $comments[$i]->c_subject_user_id)
					{
						$comment_data["classes"] .= " uploader_comment";
						$comment_data["date_uploader"] .= " <span>- uploader comment</span>";
					}
					
					$comments_html  .= $this->load->view("components/commentDiv",$comment_data,true);
					
				}
				
				// now add prev/next buttons
				if($comments_total > 10)
				{
					$prev_class = ($page == 1) ? "hide" : "";
					$comments_buttons = '
						<div class="pagination_buttons comments_buttons">
							<span id="current_comment_page" class="hide">'.$page.'</span>
							<input type="button" class="pagination '.$prev_class.'" onclick="commentsNav(\'moodboard\','.$mb->moodboard_id.',-1,'.$comments_total.')"; name="comments_prev" id="comments_prev" value="< Newer" />
							<input type="button" class="pagination" onclick="commentsNav(\'moodboard\','.$mb->moodboard_id.',1,'.$comments_total.')"; name="comments_next" id="comments_next" value="Older >" />
							<span class="loading hide">&nbsp;</span>
						</div>
					';
				}
			}
			
			// favourites
			$favourites = $this->favourite_model->getFavourites($mb->moodboard_id,"moodboard");
			
			
			$favs_html = "No Favourites";
			$favs_count = 0;
			
			if(is_array($favourites) )
			{
				$favs_count = count($favourites);
				$favs_html = "<ul id='imageFavsList' class='wideUserList'>";
				
				for($i = 0; $i < $favs_count; $i++)
				{
					$urow = $favourites[$i];
					
					$fimage_url = getUserProfileURL($urow->u_profile_id, $urow->u_email);
				
					$base = base_url();
					$profilelink = $base.'user/u/'.$urow->u_username;
					
					$lidata = array(
						"user_id" => $urow->user_id,
						"profile_link" => $profilelink,
						"profile_img" => $fimage_url,
						"username" => $urow->u_username
					);
					
					$favs_html .= $this->load->view("components/smallUserListLi",$lidata,true);
				}
				
				$favs_html .= "</ul>";
			}
				
			
			
			// sort data out and insert into the view
				
			$data = array(
				"mb_url" => $mb->m_full_url,
				"mb_title" => $mb->m_title,
				"mb_desc" => $mb->m_description,
				"mb_favourites" => 0,
				"mb_views" => $mb->m_views,
				"mb_datetime" => $datetime,
				"mb_tags_html" => $tags_html,
				"mb_id" => $mb->moodboard_id,
				"mb_user_id" => $mb->m_user_id,
				"username" => $user->u_username,
				"profile_url" => $profile_url,
				"base_url" => $base,
				"controlsHTML" => $controlsHTML,
				"favs_html" => $favs_html,
				"favs_count" => $favs_count,
				"comments_html" => $comments_html,
				"comments_buttons" => $comments_buttons,
				"comments_count" => $comments_total,
				"signedin_profile_url" => $user_profile_url

			);
		
		
				
			$this->template->write("title",$mb->m_title.", created by ".$user->u_username);
			

			$this->template->add_js("assets/js/image.js");
			$this->template->add_js("assets/js/ajax.js");
			$this->template->add_js("assets/js/tabs.js");
			
			$this->template->write_view("content","moodboard/view", $data, TRUE);
			
		
			$this->template->render();
		}
		else
		{
			$data = array(
				"errorTitle" => "Moodboard not found",
				"content" => "The moodboard with the  id '".$id."' was not found."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
	/**
	*	Add function, deals with construction of the moodboard
	*
	*	@param id : id of object type (e.g. collection) to use
	*	@param type :for sematnics more than anything since collection is always used
	*/
	function add($id,$type)
	{
		loggedInSection();
		
		if($type == "col")
		{
			$collection = $this->collection_model->getCollection($id);
			
			if($collection == false)
			{
				$data = array(
				"errorTitle" => "Collection Not Found",
				"content" => "An error has occurred: the collection with the id <strong>".$id."</strong> could not be found, so a moodboard cannot be created"
				);
				
				
				$this->template->write_view("content","general/error", $data, TRUE);
					
				//now render templates
				$this->template->render();
				
			}
			else
			{	
				$collection = $collection->row();
				$images = $this->collection_model->getCollectionImages($id);
				$base = base_url();
			
				// list table fields
				$this->template->set_template("urika_moodboard");
				
				
				// deals with images
				$objects = "";
				
				if(is_array($images))
				{
					foreach($images as $id => $image)
					{
					
						$objects .=  '<div class="obj_item">
									<span class="addLink"> Add</span>
									<img id="obj_'.$id.'" data-full-url="'.$image->i_full_url.'" class="drag" width="100" height="100" src="'.$image->i_thumb_url.'" alt="el"/>
								</div>';
								
					}
				}


				$this->template->add_js("assets/js/tabs.js");
				$this->template->add_js("assets/js/moodboard.js");
				$this->template->add_js("assets/js/libs/farbtastic/farbtastic.js");
				
				$this->template->add_css("assets/js/libs/farbtastic/farbtastic.css");
				
				$this->template->write("objects",$objects);
				$this->template->write("mb_user_id",$collection->col_user_id);
				$this->template->write("mb_col_id",$collection->collection_id);
				$this->template->write("dataString","");
				
				
				$this->template->render();
					
					// set back to defaut
				$this->template->set_template("urika");
			}
		}
		else
		{
			$data = array(
				"errorTitle" => "Creation Type not recognised",
				"content" => "The moodboard creation type '".$type."' was not recognised. Therefore the moodboard creation system was not loaded."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
		
		
	}
	
	/*
		Moodboard save function, handles to form submission of the add
		page
	*/
	function save()
	{
		loggedInSection();
		
		if($this->input->post("dataString") !== FALSE)
		{
			// process image data
			$images = $this->_processJSONtoImage(trim($this->input->post("dataString")));
			
			imagejpeg($images["main"]["image"],MOODBOARD_FINAL_PATH.$images["main"]["name"],95);
			imagejpeg($images["thumb"]["image"],MOODBOARD_FINAL_PATH.$images["thumb"]["name"],95);
			
			$full_url = MOODBOARD_URL.$images["main"]["name"];
			$thumb_url = MOODBOARD_URL.$images["thumb"]["name"];			


			$insert_array = array(
				"m_user_id" => $this->input->post("mb_user_id"),
				"m_col_id" => $this->input->post("mb_col_id"),
				"m_title" => trim($this->input->post("mb_name")),
				"m_description" => htmlentities(trim(nl2br($this->input->post("mb_desc")))),
				"m_tags" => trim($this->input->post("mb_tags")),
				"m_views" => 0,
				"m_full_url" => $full_url,
				"m_thumb_url" => $thumb_url,
				"m_contents" => trim($this->input->post("dataString")),
			);
			
			
			//insert data and then redirect the appropriate page
			$query = $this->moodboard_model->createNewMoodboard($insert_array);
			
			if($query == false)
			{
				$data = array(
					"errorTitle" => "Upload Failed",
					"content" => "An error has occurred when creating the moodboard. "
				);
				
				
				$this->template->write_view("content","general/error", $data, TRUE);
					
				//now render templates
				$this->template->render();
			}
			else
			{
				// it worked
				redirect("/moodboard/view/".$query['id']."/","location");
			}
		}
		else
		{
			$data = array(
			"errorTitle" => "Request Denied",
			"content" => "An error has occurred: No moodboard data was passed across"
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
	/*
		Allows user to edit moodboard
		The JSON object is unravalled and loaded up
	
		@param $id : id of the moodboard
	*/
	function edit($id)
	{
		loggedInSection();
		
		$mb = $this->moodboard_model->getMoodboard($id);
		
		if($mb != false)
		{
			$mb = $mb->row();
			
			if($this->session->userdata("user_id") != $mb->m_user_id)
			{
				$data = array(
					"errorTitle" => "Moodboard access denied",
					"content" => "An error has occurred: The moodboard does not belong to you so you cannot edit it."
				);
				
				
				$this->template->write_view("content","general/error", $data, TRUE);
					
				//now render templates
				$this->template->render();
			}
			else
			{
				
			
			
				$collection = $this->collection_model->getCollection($mb->m_col_id);
				
				if($collection == false)
				{
					$data = array(
					"errorTitle" => "Collection Not Found",
					"content" => "An error has occurred: the collection with the id <strong>".$id."</strong> could not be found, so a moodboard cannot be edit"
					);
					
					
					$this->template->write_view("content","general/error", $data, TRUE);
						
					//now render templates
					$this->template->render();
					
				}
				else
				{	
					$collection = $collection->row();
					$images = $this->collection_model->getCollectionImages($mb->m_col_id);
					$base = base_url();
				
					// list table fields
					$this->template->set_template("urika_moodboard");
					
					
					// deals with images
					$objects = "";
					
					foreach($images as $id => $image)
					{
					
						$objects .=  '<div class="obj_item">
									<span class="addLink"> Add</span>
									<img id="obj_'.$id.'" data-full-url="'.$image->i_full_url.'" class="drag" width="100" height="100" src="'.$image->i_thumb_url.'" alt="el"/>
								</div>';
								
					}
					


					$this->template->add_js("assets/js/tabs.js");
					$this->template->add_js("assets/js/moodboard.js");
					$this->template->add_js("assets/js/libs/farbtastic/farbtastic.js");
					
					$this->template->add_css("assets/js/libs/farbtastic/farbtastic.css");
					
					$this->template->write("objects",$objects);
					$this->template->write("mb_user_id",$collection->col_user_id);
					$this->template->write("mb_col_id",$collection->collection_id);
					$this->template->write("mb_title",$mb->m_title);
					$this->template->write("mb_desc",$mb->m_description);
					$this->template->write("mb_tags",$mb->m_tags);
					$this->template->write("mb_id",$mb->moodboard_id);
					$this->template->write("dataString",$mb->m_contents);
					
					
					$this->template->render();
						
						// set back to defaut
					$this->template->set_template("urika");
				}
			}
		}
		else
		{
			$data = array(
			"errorTitle" => "Moodboard does not exist",
			"content" => "An error has occurred: The moodboard with this ID does not exist."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
	/*
		Edit save function, separate just to save headache to complicated procedure
	*/
	function editsave()
	{
		loggedInSection();
		
		if($this->input->post("mb_id") === FALSE || $this->input->post("mb_id") == "")
		{
			$data = array(
			"errorTitle" => "Moodboard does not exist",
			"content" => "An error has occurred: The moodboard id passed was invalid"
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
		else
		{
			$mb = $this->moodboard_model->getMoodboard($this->input->post("mb_id"));
			
			if($mb != false)
			{
				$mb = $mb->row();
				$new_full_url = $new_thumb_url = "";
				
				// if the json object is the same then we're laughing, nothing to change
				if($mb->m_contents === trim($this->input->post("dataString")))
				{
					$new_full_url = $mb->m_full_url;
					$new_thumb_url = $mb->m_thumb_url;
				}
				else
				{
					// we need to rebuild the images
					// process image data
					$images = $this->_processJSONtoImage(trim($this->input->post("dataString")));
				
					// if we get the correct results back, unlink old images and create new.
					if($images != false)
					{
						$thumb_path = str_replace(MOODBOARD_URL,MOODBOARD_FINAL_PATH,$mb->m_thumb_url);
						$full_path = str_replace(MOODBOARD_URL,MOODBOARD_FINAL_PATH,$mb->m_full_url);

						
						if(is_file($thumb_path)) { unlink($thumb_path); }
						if(is_file($full_path)) { unlink($full_path); }
					
						imagejpeg($images["main"]["image"],MOODBOARD_FINAL_PATH.$images["main"]["name"],95);
						imagejpeg($images["thumb"]["image"],MOODBOARD_FINAL_PATH.$images["thumb"]["name"],95);
						
						$new_full_url = MOODBOARD_URL.$images["main"]["name"];
						$new_thumb_url = MOODBOARD_URL.$images["thumb"]["name"];	
					}
					else
					{
						echo 'The image creation failed.';
					}
				}
				
				$update_data = array(
					"m_title" => trim($this->input->post("mb_name")),
					"m_description" => htmlentities(trim(nl2br($this->input->post("mb_desc")))),
					"m_tags" => trim($this->input->post("mb_tags")),
					"m_full_url" => $new_full_url,
					"m_thumb_url" => $new_thumb_url,
					"m_contents" => trim($this->input->post("dataString")),
				);
				
				$update = $this->moodboard_model->updateMoodboard($update_data,$mb->moodboard_id);
				
				
				if($update == true)
				{
					redirect('/moodboard/view/'.$mb->moodboard_id.'/?saved=1','location');
				}
				else
				{
					echo 'Something went wrong when saving the moodboard';
				}
				
			
			
			}
			else
			{
				$data = array(
					"errorTitle" => "Moodboard does not exist",
					"content" => "An error has occurred: The moodboard with the passed ID does not exist."
				);
				
				
				$this->template->write_view("content","general/error", $data, TRUE);
					
				//now render templates
				$this->template->render();
			}
		}
	}
	
	
	/// delete
	
	/**
		delete, allows users to delete their own moodboard.
		Same page used for security, the param can only be passed through a user 
		submitting the original form
		
		@param id : id of the moodboard to delete.
	**/
	function delete($id = null)
	{
		loggedInSection(); 
		
		if($id == null || !is_numeric($id))
		{
			
			if($this->input->post("delete_id") !== FALSE)
			{
				$mb = $this->moodboard_model->getMoodboard($this->input->post("delete_id"));
				
				if($mb == false)
				{
					redirect('','location');
				}
				else
				{
					$mb = $mb->row();
				}
			
				
				
				if($this->session->userdata("user_id") == $mb->m_user_id)
				{
					
					// delete database records and delete file
					if($this->moodboard_model->deleteMoodboard($mb->moodboard_id))
					{
						redirect("/user/u/".$this->session->userdata("username")."/?moodboard_deleted=1","location");
					}
					else
					{
					
						$data = array(
						"errorTitle" => "Delete Failed",
						"content" => "An error has occurred when delete the moodboard. Please ensure this is your moodboard. If the problem persists, please get in touch"
						);
						
						
						$this->template->write_view("content","general/error", $data, TRUE);
							
						//now render templates
						$this->template->render();
					}
					
				}
				else
				{
					redirect('','location');
				}
				
				
			}
			else // if the post isnt set
			{
				redirect('','location');
			}
		}
		else
		{
			$mb = $this->moodboard_model->getMoodboard($id);
			
			
			if($mb == false)
			{
				redirect('','location');
			}
			else
			{
				$mb = $mb->row();
			}
			
			if($this->session->userdata("user_id") == $mb->m_user_id)
			{
				$base = base_url();
				// now show delete screen
				$data = array(
					"mb_title" => $mb->m_title,
					"mb_url" => $base."moodboard/view/".$mb->moodboard_id."/", 
					"delete_id" => $mb->moodboard_id
				);
				
				$this->template->write("title","Delete a Moodboard");
				
				
				$this->template->write_view("content","moodboard/delete", $data, TRUE);
				
			
				$this->template->render();
				
			}
			else
			{
				redirect('','location');
			}
		}
	}
	
//////////////// Utility functions 

	/*
		Give a json object by the moodboard, constructs two images resources
		
		@param json : moodboard json strong
		
		general format:
		
		{
			background : {
				width,height, colour, offsets, rotation etc
			},
			images : [
				{
					id,src,width,height, rotation,order,offsets
				}
			]
		}
	*/
	function _processJSONtoImage($json)
	{
	
		if(!$json || $json == null || $json == "")
		{
			return false;
		}
		else
		{
			// process image data
			
			$data = json_decode($json,true);

			/////// Background image ///////
			$background = @imagecreatetruecolor($data["background"]["width"], $data["background"]["height"])
				  or die('Cannot Initialize new GD image stream');
				  
			// set bg image colour
			$bg_colour = $this->_hex2RGB($data["background"]["colour"]);

			$bg_c = imagecolorallocate( $background, $bg_colour["red"], $bg_colour["green"], $bg_colour["blue"] );
			imagefill($background,0,0,$bg_c);


			/////// Now loop through images adding the the screen /////
			$count = count($data["images"]);

			//sort by order, top first then reverse array
			$data["images"] = array_reverse($this->_subval_sort($data["images"], 'order'));
			
			$this->load->helper("mime");

			for($i = 0; $i < $count; $i++)
			{
				$img = $data["images"][$i];
				
				//convert source to path
				$img["src"] = str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$img["src"]);
				
				$t = mime_get_type($img["src"]);
				$file = false;
				if($t == "image/png")
				{	
					$file = imagecreatefrompng($img["src"]);
				}
				else if($t == "image/jpeg")
				{
					$file = imagecreatefromjpeg($img["src"]);
				}

				
				if($file === FALSE)
				{
					continue; 
				}
				
				$file_w = imagesx($file);
				$file_h = imagesy($file);
				
				$frame = imagecreatetruecolor($img["width"],$img["height"]);
				$trans_colour = imagecolorallocate($frame,0, 0, 0);
				//now copy image into the frame
				
				imagecopyresampled($frame, $file, 0, 0, 0, 0, $img["width"], $img["height"], $file_w, $file_h);
				
				//	calculate true left and right based on offsets
				$left = round($img["left"] - ($data["background"]["offsetLeft"]-10)); // -10 due to something in the front end
				$top = round($img["top"] - ($data["background"]["offsetTop"]-56)); // -56 due to height of header div
				
				// rotation 
				if($data["background"]["useRotation"] == 1)
				{
					if($img["rotation"] != 0)
					{
						
						$frame    = imagerotate($frame,$img["rotation"]*-1, -1,0);
						/*after rotating calculate the difference of new height/width with the one before*/
						$extraTop       =(imagesy($frame)-$img["height"])/2;
						$extraLeft      =(imagesx($frame)-$img["width"])/2;
						
						$left = $left - $extraLeft;
						$top = $top - $extraTop;
					}
				}

				
				// now copy this image onto the moodboard
				
				
				imagecopy($background, $frame, $left, $top, 0, 0, imagesx($frame), imagesy($frame));
				
			}

			$image = $background;

			

			$filename = 'urika_mb_'.time().rand(0,100).'.jpg';
			$thumbname = "thumb_".$filename;
			
			// create thumb before add
			// for thumb shrink image to 400 wide and ratio high and take middle
			
			$temp_h = round(($data["background"]["height"]/$data["background"]["width"])*400);
			$temp_shrunk_image = ImageCreateTrueColor(400,$temp_h);
			$thumb_img = ImageCreateTrueColor(160,160);
			
			// copy temp srhunk image
			
			imagecopyresampled($temp_shrunk_image,$image,0,0,0,0,
			400,$temp_h,$data["background"]["width"],$data["background"]["height"]);
			
			// want to find the middle so we need to calculate offsets
			$offx = 120;
			$offy = ($temp_h/2)- 80 ;
			
			imagecopyresampled($thumb_img,$temp_shrunk_image,0,0,$offx,$offy,
			160,160,160,160);
			
			// now construct return array
			
			$return = array(
				"main" => array(
					"image" => $image,
					"name" => $filename
				),
				"thumb" => array(
					"image" => $thumb_img,
					"name" => $thumbname
				)
			);
			
			return $return;
		}
	}

	/*
		Concerts hexedecimal colours values to rgb
	*/
	function _hex2RGB($hexStr, $returnAsString = false, $seperator = ',') 
	{
	
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		
		if (strlen($hexStr) == 6) 
		{ //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} 
		elseif (strlen($hexStr) == 3) 
		{ //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} 
		else 
		{
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	} 
	
	/*
		Sorts by a subvalue of an array
	*/
	function _subval_sort($a,$subkey) 
	{
		
		foreach($a as $k=>$v) 
		{
			$b[$k] = (int) $v[$subkey];
		}
		
		asort($b,SORT_NUMERIC);
		
		foreach($b as $key=>$val) 
		{
			$c[] = $a[$key];
		}
		return $c;
	}
}

/* End of file moodboard.php */
/* Location: ./system/application/controllers/moodboard.php */
