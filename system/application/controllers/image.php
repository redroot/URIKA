<?php
/**
	Image controller
**/
class Image extends Controller {

	var $thumbSize = array("w" => 160, "h" => 160);
	var $tags_array = array();

	function Image()
	{
		parent::Controller();	
		$this->load->model("image_model");
		$this->load->helper("upload_helper");
	}
	
	function index()
	{
		redirect('','location');
	}
	
	/**
		Viewing an image page
	**/
	function view($id = null,$name = null)
	{
		$q_vars = get_url_vars();
		
		if($id == null || !is_numeric($id))
		{
			redirect('','location');
		}
		else
		{
			$image = $this->image_model->getImage($id);
			
			if($image == false)
			{
				$data = array(
					"errorTitle" => "Image wasn't found",
					"content" => "The image with the id ".$id." was not found, it may not exist or has been deleted"
					);
				
				
				$this->template->write_view("content","general/error", $data, TRUE);
					
				//now render templates
				$this->template->render();
				
			}else if($name != null && $name !== slugify($image->row()->i_title)){
				// redirect to right version
				$image = $image->row();
				redirect('image/view/'.$image->image_id.'/'.slugify($image->i_title)."/",'location');
			}
			else
			{
				$image = $image->row();
			
				$base = base_url();
			
				$this->load->model("user_model");
			
				$user = $this->user_model->getUser($image->i_user_id);
				$user = $user->row();
				
				$profile_url = getUserProfileURL($user->u_profile_id,$user->u_email);
						
				$this->load->model("favourite_model");
				
				$datetime = date("F j, Y",strtotime($image->i_datetime));
				
				// get page
				$page = (isset($q_vars->comment_page) == true) ? $q_vars->comment_page : 1;

				// sort tags out
				$tags = explode(',',$image->i_tags);
				
				$tags_html = "<strong>Tags:</strong> ";
				$count = count($tags);
				for($i = 0; $i < $count; $i++)
				{
					if($tags[$i] != "")
						$tags_html .= '<a class="tag_link" href="'.$base.'browse/?tag='.str_replace(" ","+",$tags[$i]).'" title="Search by this tag">'.$tags[$i].'</a>';
				}
				
				// now handle edit and delete link
				$controlsHTML = array();
				
				if(isLoggedIn() == true)
				{
					// determine if this user is the owner of this image
					if($this->session->userdata("user_id") == $image->i_user_id)
					{
						$controlsHTML["editLink"] = $base."image/edit/".$image->image_id."/";
						$controlsHTML["deleteLink"] = $base."image/delete/".$image->image_id."/";
					}
					else // add favourite link
					{
						
						
						if($this->favourite_model->favouriteExists($this->session->userdata("user_id"),$image->image_id) == false)
						{
							$controlsHTML["favlink"] ='<li><span class="fav_link" id="ajax_fav" onclick="addFavourite('.$image->image_id.',\'image\');" >Add to Favourites</span></li>';
						}
						else
						{
							$controlsHTML["favlink"] ='<li><span class="fav_link" >Favourited!</span></li>';
						}
						
						$this->load->model("flag_model");
						
						if($this->flag_model->flagExists($this->session->userdata("user_id"),$image->image_id) == false)
						{
							$controlsHTML["flagLink"] = '<li><span class="flag_link" id="ajax_flag"  onclick="addFlagPopUp('.$image->image_id.');" >Flag This Upload</span></li>';
						}
						else
						{
							$controlsHTML["flagLink"] = '<li><span class="flag_link" >Flagged!</span></li>';
						}
					}
					
					$collections = $this->user_model->getUserCollections($this->session->userdata("user_id"));
					
					
					// add collections link
					$controlsHTML["collectionsLink"] = '<li><span class="collections_link" onclick="addToCollectionPopUp('.$image->image_id.')">Add to Collection</span></li>';
					
					// now the collections bit, two hidden spans holding collection ids that this
					// image isnt in
					
					$available_cols_json = "{";
					$current_id = 0; // use this to make sure indexing works
					
					if($collections["count"] > 0 )
					{
					
						for($k = 0; $k < $collections["count"]; $k++)
						{
							$ex = explode(",",$collections["result"][$k]->col_string);
							
							if(!in_array($image->image_id,$ex))
							{
								if($available_cols_json != "{")
								{
									$available_cols_json .= ',';
								}
								$available_cols_json .= '"'.$current_id.'":{"name":"'.$collections["result"][$k]->col_name.'","id":"'.$collections["result"][$k]->collection_id.'"}';
								$current_id++;
							}
						}
					}
					
					$available_cols_json .= "}";
					
					if($available_cols_json == "{}")
					{
						$available_cols_json = 'empty';
					}
					$controlsHTML["collectionsLink"] .= '<input type="hidden" id="userColsJSON" value=\''.$available_cols_json.'\' />';
					
					// now increment views
					$update_data = array(
						"i_views" => $image->i_views + 1
					);
					$this->image_model->updateImage($update_data,$image->image_id);
				}
				
				// now comments and favourites
				
				// comments
				$this->load->model("comment_model");
				
				$comments = $this->comment_model->getComments($image->image_id,$page);
				$comments_html = "<p class='nocomments'>No Comments</p>";
				$comments_count = 0;
				$comments_total = $this->comment_model->getCommentsCount($image->image_id);
				
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
								<input type="button" class="pagination '.$prev_class.'" onclick="commentsNav(\'image\','.$image->image_id.',-1,'.$comments_total.')"; name="comments_prev" id="comments_prev" value="< Newer" />
								<input type="button" class="pagination" onclick="commentsNav(\'image\','.$image->image_id.',1,'.$comments_total.')"; name="comments_next" id="comments_next" value="Older >" />
								<span class="loading hide">&nbsp;</span>
							</div>
						';
					}
				}
				
				// favourites
				$favourites = $this->favourite_model->getFavourites($image->image_id);
				
				
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
				
				
				$data = array(
					"image_url" => $image->i_full_url,
					"image_title" => $image->i_title,
					"image_desc" => $image->i_description,
					"image_favourites" => 0,
					"image_views" => $image->i_views,
					"image_dims" => str_replace("X"," X ",$image->i_size),
					"image_datetime" => $datetime,
					"image_tags_html" => $tags_html,
					"image_website" => $image->i_website,
					"image_id" => $image->image_id,
					"image_user_id" => $image->i_user_id,
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
				
				$this->template->write("title",$image->i_title.", uploaded by ".$user->u_username);
				

				
				$this->template->add_js("assets/js/image.js");
				$this->template->add_js("assets/js/ajax.js");
				$this->template->add_js("assets/js/tabs.js");
				
				$this->template->write_view("content","image/view", $data, TRUE);
				
			
				$this->template->render();
				
			}
			
			//increment image views
		}
	}
	
	/**
		Edit, allows users to edit their own upload
		@param id : id of the image to edit.
	**/
	function edit($id = null)
	{
		loggedInSection(); 
		
		if($id == null || !is_numeric($id))
		{
			if($this->input->post("edit_id") !== FALSE)
			{
				// save everything
				
				
				$tags = $this->input->post("edit_tags");
				
				// check if tags are empty
				if($tags == "" || empty($tags) || $tags[0] == "")
				{
					$tags = "";
				}
				else{
					$tags = implode(",",$tags);
				}
					
				// update array 
				$update_data = array(
					"i_title" => $this->input->post("edit_title"),
					"i_tags" => strtolower($tags),
					"i_description" => htmlentities(trim(nl2br($this->input->post("edit_desc")))),
					"i_website" => trim($this->input->post("edit_website")),
				);
				
				$this->image_model->updateImage($update_data,$this->input->post("edit_id"));
				
				$redirect  = "/image/view/".$this->input->post("edit_id")."/".slugify($update_data["i_title"])."/?saved=1";
				redirect($redirect,'location');
				
				
			}
			else
			{
				redirect('','location');
			}
		}
		else
		{
			$image = $this->image_model->getImage($id);
			
			
			if($image == false)
			{
				redirect('','location');
			}
			else
			{
				$image = $image->row();
			}
		
			if($this->session->userdata("user_id") == $image->i_user_id)
			{
				$base = base_url();
				// now show edit screen
				
				// generate select for everything but ie
				$isIE = false;
				
				if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
				{
					$isIE = true;
				}
				
				$tagsHTML = "";

				
				$this->_setTaggingFile();
								
				
				$options = '';
				
				$explode = explode(",",$image->i_tags);
				
				foreach($this->tags_array as $tag){
					if($tag != ""){
						if(in_array($tag->caption,$explode)){
							$opt = "<option selected value='".$tag->caption."'>".$tag->caption."</option>";
						}else{
							$opt = "<option value='".$tag->caption."'>".$tag->caption."</option>";
						}
					}
					
					$options .= $opt;
				}
				
			
				
				
				
				$data = array(
					"image" => $image,
					"tagsHTML" => $options
				);
				
				
				$this->template->add_js("assets/js/libs/chosen.js");
				
				$this->template->add_js("assets/js/image.js");
				$this->template->add_js("assets/js/formvalidation.js");
					
				// add css
				$this->template->add_css("assets/css/autocomp.css");
				
				$this->template->write("title","Edit an Image");
				
				
				$this->template->write_view("content","image/edit_form", $data, TRUE);
				
			
				$this->template->render();
			}
			else
			{
				redirect('','location');
			}
		}
	}
	
	/**
		delete, allows users to delete their own upload.
		Same page used for security, the param can only be passed through a user 
		submitting the original form
		
		@param id : id of the image to delete.
	**/
	function delete($id = null)
	{
		loggedInSection(); 
		
		if($id == null || !is_numeric($id))
		{
			
			if($this->input->post("delete_id") !== FALSE)
			{
				$image = $this->image_model->getImage($this->input->post("delete_id"));
				
				if($image == false)
				{
					redirect('','location');
				}
				else
				{
					$image = $image->row();
				}
			
				
				
				if($this->session->userdata("user_id") == $image->i_user_id)
				{
					
					// delete database records and delete file
					if($this->image_model->deleteImage($image->image_id))
					{
						redirect("/user/u/".$this->session->userdata("username")."/?image_deleted=1","location");
					}
					else
					{
					
						$data = array(
						"errorTitle" => "Delete Failed",
						"content" => "An error has occurred when delete the image. Please ensure this is your image. If the problem persists, please get in touch"
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
			$image = $this->image_model->getImage($id);
			
			
			if($image == false)
			{
				redirect('','location');
			}
			else
			{
				$image = $image->row();
			}
			
			if($this->session->userdata("user_id") == $image->i_user_id)
			{
				$base = base_url();
				// now show delete screen
				$data = array(
					"image_title" => $image->i_title,
					"image_url" => $base."image/view/".$image->image_id."/".slugify($image->i_title)."/", 
					"delete_id" => $image->image_id
				);
				
				$this->template->write("title","Delete an Image");
				
				
				$this->template->write_view("content","image/delete", $data, TRUE);
				
			
				$this->template->render();
				
			}
			else
			{
				redirect('','location');
			}
		}
	}
	
	/**
		Add function, which allows users to upload images
	**/
	function add()
	{
		// process ajax on the same page
		loggedInSection();
		
		if($this->input->post("add_save") !== FALSE)
		{
			
			
			// sort tags first
			$tags = "";
			
			$tags = $this->input->post("add_tags"); // use ie verson
			
			// check if tags are empty
			if($tags == "" || empty($tags) || $tags[0] == "")
			{
				$tags = "";
			}
			else{
				$tags = implode(",",$tags);
			}
		
			// some initial
			$insert_array = array(
				"i_user_id" => $this->session->userdata("user_id"),
				"i_title" => trim($this->input->post("add_title")),
				"i_description" => htmlentities(trim(nl2br($this->input->post("add_desc")))),
				"i_website" => trim($this->input->post("add_website")),
				"i_tags" => strtolower($tags),
				"i_type" => "image",
				"i_views" => 0
			);
			
			// now make the new image from details and temp image
			
			$dims = json_decode($this->input->post("add_filename_dims"),true);
			
			if(strpos($this->input->post("add_filename_temp"),".png") !== FALSE)
			{
				$img_r = imagecreatefrompng(UPLOAD_TEMP_PATH.$this->input->post("add_filename_temp"));
			}
			else
			{
				$img_r = imagecreatefromjpeg(UPLOAD_TEMP_PATH.$this->input->post("add_filename_temp"));
			}
			
			$full_img = ImageCreateTrueColor( $dims["new_w"], $dims["new_h"] );
			
			// if they have resized the image, we need to create a temp image in the mean time
			if($dims["orig_w"] != $dims["resized_w"])
			{
				$temp_img = ImageCreateTrueColor($dims["resized_w"], $dims["resized_h"]);
				imagecopyresampled($temp_img,$img_r,0,0,0,0,
				$dims["resized_w"], $dims["resized_h"],$dims["orig_w"], $dims["orig_h"]);
				
				// now copy to true image
				imagecopyresampled($full_img,$temp_img,0,0,$dims["offset_x"],$dims["offset_y"],
				$dims["new_w"], $dims["new_h"],$dims["new_w"], $dims["new_h"]);
				
				imagedestroy($temp_img);
			}
			else
			{

				imagecopyresampled($full_img,$img_r,0,0,$dims["offset_x"],$dims["offset_y"],
				$dims["new_w"], $dims["new_h"],$dims["new_w"], $dims["new_h"]);
			
			}

			// now save new img, and delete temp
			
			imagejpeg($full_img,UPLOAD_FINAL_PATH.$this->input->post("add_filename_temp"),95);
			
			if(!unlink(UPLOAD_TEMP_PATH.$this->input->post("add_filename_temp")))
			{
				
				echo "Something went wrong with the upload process, please try again with a smaller image file";
			
			}	
			else
			{
			
				$base_url = base_url();
				$insert_array["i_full_url"] = $base_url.'uploads/images/'.$this->input->post("add_filename_temp");
				$insert_array["i_size"] = $dims["new_w"].'X'.$dims["new_h"];
				
				// now thumbnail for new image resource
				$thumbname = "thumb_".$this->input->post("add_filename_temp");
				$thumb_img = ImageCreateTrueColor($this->thumbSize["w"],$this->thumbSize["h"]);
				
				/*
				// want to find the middle so we need to calculate offsets
				//$offx = ($dims["new_w"]/2)-	($this->thumbSize["w"]/2);
				//$offy = ($dims["new_h"]/2)-	($this->thumbSize["h"]/2);
				
				old method */
				
				// determine size for crop
				// if it is wider than it is high, then the height is fixed to thumb height
				// otherwise the width is used
				
				$ratio = $dims["new_w"] / $dims["new_h"];
				$copy_w = $copy_h = 0;
				
				if($dims["new_w"] >= $dims["new_h"])
				{
					$offx = ($dims["new_w"]/2)-	($dims["new_h"]/2);
					$offy = 0;
					
					$copy_w = $copy_h = $dims["new_h"];
				}
				else if($dims["new_w"] < $dims["new_h"])
				{
					$offx = 0;
					$offy = ($dims["new_h"]/2)-	($dims["new_w"]/2);
					
					$copy_w = $copy_h = $dims["new_w"];
				}
				
				imagecopyresampled($thumb_img,$full_img,0,0,$offx,$offy,
				$this->thumbSize["w"], $this->thumbSize["h"],$copy_w, $copy_h);
				imagejpeg($thumb_img,UPLOAD_FINAL_PATH.$thumbname,150);
				
				$insert_array["i_thumb_url"] = $base_url.'uploads/images/'.$thumbname;
				
			
				// insert data and then redirect the appropriate page
				$query = $this->image_model->createNewImage($insert_array);
				
				if($query == false)
				{
					$data = array(
					"errorTitle" => "Upload Failed",
					"content" => "An error has occurred when uploading the image. Please ensure you image is below 40mb in size and try again. If the problem persists, please get in touch"
					);
					
					
					$this->template->write_view("content","general/error", $data, TRUE);
						
					//now render templates
					$this->template->render();
				}
				else
				{
					// it worked
				
					// if collection ting is set, add to collection
					if($this->input->post("add_collection") !== FALSE && $this->input->post("add_collection") !== "none")
					{
						$col_id = (int) $this->input->post("add_collection");
						$image_id = $query["id"];
						
						$this->load->model("collection_model");
						
						$collection = $this->collection_model->getCollection($col_id)->row();
						
						if($collection->col_user_id == $this->session->userdata("user_id"))
						{
							$ids_array = explode(",",$collection->col_string);
							
							if(!in_array($image_id,$ids_array))
							{
								// rebuild string
								$ids_array[] = $image_id;
								$count = count($ids_array);
								$new_string = "";
								
								for($i = 0;  $i < $count; $i++)
								{
									if($ids_array[$i]!= "")
									{
										// add a ccheck for whether this image exists
										
										$check = $this->image_model->getImage($ids_array[$i]);
										
										if($check != false)
										{
											$new_string .= $ids_array[$i].',';
										}
									}
								}
								$new_string = substr($new_string,0,-1);
								
								$update_data = array(
									"col_string" => $new_string,
									"col_updated" => date('Y-m-d H:i:s')
								);
								
								
								$insert = $this->collection_model->updateCollection($update_data,$col_id);
							}
						}
					}
				
					redirect("/image/view/".$query['id']."/".slugify($insert_array["i_title"])."/","location");
				}
			}
			
		}
		else
		{
			
			// load page as normal
			$last_upload = $this->image_model->getLastUserImageTime($this->session->userdata("user_id"));
			$can_upload = true;
			// check last comment time
			/*if($last_upload != false)
			{
				// check time
				$time_diff = time() - $last_upload;
				
				if($time_diff < 180)
				{
					$can_upload = false;
				}
				
			}*/
			
			// reload tags cache
			$this->_setTaggingFile();
			
			
			
			$data = array();
			
			
			// generate our options
			
			$data["tag_select_options"] = "";
			foreach($this->tags_array as $tag){
				$opt = "<option value='".$tag->caption."'>".$tag->caption."</option>";
				$data["tag_select_options"] .= $opt;
			}
			
			$this->template->write("title","Upload an Image");
			
			$this->template->add_js("assets/js/libs/fileuploader.js");
			$this->template->add_js("assets/js/libs/jquery.Jcrop.min.js");
			$this->template->add_js("assets/js/libs/chosen.js");
			$this->template->add_js("assets/js/image.js");
			$this->template->add_js("assets/js/formvalidation.js");
			
			// add css
			$this->template->add_css("assets/css/fileuploader.css");
			$this->template->add_css("assets/css/jcrop.css");
			$this->template->add_css("assets/css/autocomp.css");
			
			if($can_upload == true)
			{
				// add collection dropdown
				$this->load->model("user_model");
				$collections = $this->user_model->getUserCollections($this->session->userdata("user_id"));				
				if($collections["count"] == 0)
				{
					$data["col_element"] = "";
				}
				else
				{	
					$el = '
						<li>
							<label for="add_collection">Add to Collection:</label>
							<select name="add_collection" id="add_collection" class="forceStyle">
								<option selected="selected" value="none">Do not add to a collection</option>';
					for($i = 0; $i < $collections["count"]; $i++)
					{
						$el .= '
							<option value="'.$collections["result"][$i]->collection_id.'">'.$collections["result"][$i]->col_name.'</option>
						';
					}
					
					$el .= '</select>
					</li>';
					
					$data["col_element"] = $el;
				}
			
				$this->template->write_view("content","image/add_form", $data, TRUE);
			}
			else
			{
				$out = '<div id="feature_title"> 
						<h2>Upload an Image<small> - please wait</small></h2> 
					</div> 
					<p class="error"><strong>Due to server restraints, we have to enforce a gap between uploads.</strong> Please try again in a few minutes</p>';
				$this->template->write("content",$out);
			}
			
		
			$this->template->render();
		}
	}
	
	/**
		Upload page which handles all uploads for all browsers. Has
		quite a few conditionals since different browsers use diff methods
		to upload the image
		
		@param get : get variable passed by the qqUploader
	*/
	function upload($get = null)
	{
		loggedInSection();
		
	
		
		$canContinue = false;
		
		$maxdim = 900;
		$maxsize = 3 * 1024 * 1024;
		$allowedExtensions = array("jpg","jpeg","gif","png");
		
		$qq = null; //qq uploader object
		
	
		
		//make filename
		
		$filename = $this->session->userdata("username")."_".time()."_";
		
		if(isAJAXRequest()) // if Chrome/SAFARI/FF etc i.e use XHR method
		{
			$explode = explode("x.urika.amp.x",$get);
			$explode = explode(".urika.eq.",$explode[1]);
			$filename .= $explode[1];
		}
		else if(isset($_FILES["qqfile"]))
		{
			$filename .= $_FILES["qqfile"]["name"];
		}
		else
		{
			redirect("","location");
		}
		
		
	
		if(isAJAXRequest()) // if ajax upload through Chrome/Safari
		{
			$qq = new qqFileUploader("xhr",$allowedExtensions,$maxsize,$filename);
			$result = $qq->handleUpload(UPLOAD_TEMP_PATH, TRUE);
			
		
			if(isset($result["success"]))
				$canContinue = true;
			else
				$error = $result["error"];
			
		}
		else // IE/Opera side need a detection here then a redirect
		{ 	
			$qq = new qqFileUploader("fileForm",$allowedExtensions,$maxsize,$filename);
			$result = $qq->handleUpload(UPLOAD_TEMP_PATH, TRUE);
			
		
			if(isset($result["success"]))
				$canContinue = true;
			else
				$error = $result["error"];
		}
		
		//after intial detection etc do the same thing throughout
		if($canContinue)
		{
			
			$base = base_url();
			
			$returnArray = array(
				"filename" => $filename,
				"fileURL" => $base."/uploads/temp/".$filename
			);
			
			if($get == null)
				$returnArray["test"] = "test";
			
			echo json_encode($returnArray);
			
		}
		else
		{
			echo json_encode(array("error"=>$error));
		}
	}
	
	/**
		As with upload, but for avatar so a slightly different deal
		
		@param get : get variable passed by the qqUploader
	*/
	function uploadAvatar($get = null)
	{
		loggedInSection();
		
		$canContinue = false;
		
		$maxdim = 200;
		$maxsize = 0.5 * 1024 * 1024;
		$allowedExtensions = array("jpg","jpeg","gif","png");
		
		$qq = null; //qq uploader object
		
	
		//make filename
		
		$filename = $this->session->userdata("username")."_".time()."_avatar_";
		
		if(isAJAXRequest()) // if Chrome/SAFARI/FF etc i.e use XHR method
		{
			$explode = explode("x.urika.amp.x",$get);
			$explode = explode(".urika.eq.",$explode[1]);
			$filename .= $explode[1];
		}
		else if(isset($_FILES["qqfile"]))
		{
			$filename .= $_FILES["qqfile"]["name"];
		}
		else
		{
			redirect("","location");
		}
		

	
		if(isAJAXRequest()) // if ajax upload through Chrome/Safari
		{
			$qq = new qqFileUploader("xhr",$allowedExtensions,$maxsize,$filename);
			$result = $qq->handleUpload(UPLOAD_TEMP_PATH, TRUE);
			
		
			if(isset($result["success"]))
				$canContinue = true;
			else
				$error = $result["error"];
			
		}
		else // IE/Opera side need a detection here then a redirect
		{ 	
			$qq = new qqFileUploader("fileForm",$allowedExtensions,$maxsize,$filename);
			$result = $qq->handleUpload(UPLOAD_TEMP_PATH, TRUE);
			
		
			if(isset($result["success"]))
				$canContinue = true;
			else
				$error = $result["error"];
		}
		
		// after intial detection etc do the same thing throughout
		// now take temp path and copy resampled to main upload directory
		if($canContinue)
		{
			
			// resized to 60x60 and placed in images folder
			// grab temp image and get image dimensions
			$img_r = imagecreatefromjpeg(UPLOAD_TEMP_PATH.$filename);
			list($dwidth, $dheight, $dtype, $dattr) = getimagesize(UPLOAD_TEMP_PATH.$filename);
			
			// check the width and ehight
			if($dwidth > $maxdim || $dheight > $maxdim)
			{
				unlink(UPLOAD_TEMP_PATH.$filename);
				echo json_encode(array("error"=>"The image must be smaller than ".$maxdim."x".$maxdim." pixels, the image you uploaded was too large"));
			}
			else
			{
		
				// create blank image
				$full_img = ImageCreateTrueColor( 70, 70 );
				
				// copy temp into blank image
				imagecopyresampled($full_img,$img_r,0,0,0,0,70,70,$dwidth, $dheight);

				// now save new img, and delete temp
				imagejpeg($full_img,UPLOAD_FINAL_PATH.$filename,150);
				
				if(unlink(UPLOAD_TEMP_PATH.$filename))
				{
				
					// last step, update user record to contain url
					$user_id = $this->session->userdata("user_id");
			
					// grab current user
					$this->load->model("user_model");
					$user_details = $this->user_model->getUser($user_id)->row();
					$current_value = $user_details->u_profile_id;
					$new_value = "";
					$update_to_avatar = "false";
					
					// if usegravatar:: is there just append to the end
					if(strpos($current_value,"usegravatar::") !== FALSE)
					{
						$new_value = "usegravatar::".UPLOAD_URL.$filename;
					}
					else
					{
						$new_value = UPLOAD_URL.$filename;
						$update_to_avatar == true;
					}
					
					$details_data = array(
						"u_profile_id" => $new_value
					);
				
					$update = $this->user_model->updateUser($details_data,$user_id,"user_id");
					
					$returnArray = array(
						"filename" => $filename,
						"file_url" => UPLOAD_URL.$filename,
						"update_to_avatar" => $update_to_avatar
					);
					
					echo json_encode($returnArray);
				}
				else
				{
					echo json_encode(array("error"=>"Something went wrong with the image resizing"));
				}
			}
		}
		else
		{
			echo json_encode(array("error"=>$error));
		}
	}
	

	
	/*
		Flag link, allows a user to flag an image
	*/
	function flag()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			
			$image = $this->image_model->getImage($this->input->xss_clean($_POST["image_id"]));
			$image = $image->row();
			
			$this->load->model("flag_model");
			
			if($this->flag_model->flagExists($this->session->userdata("user_id"),$image->image_id) == true)
			{
			
				echo "false";
				
			}
			else
			{
				// need to add a new favourite
				$insert_data = array(
					"fl_upload_id" => $image->image_id,
					"fl_flagger_id" => $this->session->userdata("user_id"),
				);
				
				$insert = $this->flag_model->createNewFlag($insert_data);
				
				if($insert !== false)
				{
					echo "true";
				}
				else
				{
					echo "false";
					
				}
				
			
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

//////////////////// Tagging private functions

	/**
		Set tagging files. This funtions creates and stores the file, if the timestamp
		is less than an hour dont bother
	*/
	function _setTaggingFile()
	{
		// first attempt to hit the cache
		$cache = cacheFetch("tagsCache");
		$popcache = cacheFetch("tagsPopularCache");
		$regenerate = false;
		
		
		if(isset($cache["expire"]) == false || isset(get_url_vars()->forcerefresh) == true)
		{
			$regenerate = true;
			
		}
		else
		{
			// now check time stamp
			$time_diff = $cache["expire"] - time();
			if($time_diff < 1) // an hour
			{
				$regenerate = true;
			
			}
		}
		
		if($regenerate == true)
		{
			// create cache file etc
			$tags_json = $this->image_model->getTags();
			
			$full = json_encode($tags_json["full"])."";
			$popular = json_encode($tags_json["popular"])."";
			
			$result = cacheStore(trim($full),"tagsCache",60);
			$resultp = cacheStore(trim($popular),"tagsPopularCache",60);
			
			// the other thing we have to do is add our own file outside of the cache
			// for use by the autocomplete
			
			$this->load->helper("file");
			
			write_file("./assets/tags/tags.txt",trim($full));
			write_file("./assets/tags/popular.txt",trim($popular));
			
			$this->tags_array = $tags_json["full"];
		}
		else
		{
			// cache hit or data is less than an hour old
			
			$this->tags_array =  json_decode($cache["data"]);
		}
		
		
	
		return true; // arbitary since it doesnt do anything
		
		
	}
	
}


/* End of file image.php */
/* Location: ./system/application/controllers/image.php */