<?php
/**
	Collection controller
	
**/
class Collection extends Controller {

	function Collection()
	{
		parent::Controller();	
		$this->load->model("collection_model");
	}
	
	function index()
	{
		redirect('','location');
	}
	
	/*
		View function
	*/
	function view($id)
	{
		$collection = $this->collection_model->getCollection($id);
		
		if($collection != false)
		{
			$collection = $collection->row();

			$images = $this->collection_model->getCollectionImages($id);
			$base = base_url();
			$q_vars = get_url_vars();
			
			// image string
			if($images != false)
			{
				$image_count = count($images);
			}
			else
			{
				$image_count = 0;
			}
			
			$col_images_string = "Contains ";
			
			if($image_count == 1)
			{
				$col_images_string .= "1 Image";
			}
			else
			{
				$col_images_string .= $image_count." Images";
			}
			$col_images_string .= '<span class="sep">|</span> Last Updated '.date("F j, Y, G:i",strtotime($collection->col_updated));
			
			//mages
			
			if($image_count > 0)
			{
				$images_html = '<ul class="uploadList">';
				
				for($i = 0; $i < $image_count; $i++)
				{
					$irow = $images[$i];
					
					// load li through view
					$data = array(
						"thumb_url" => $irow->i_thumb_url,
						"title" => $irow->i_title,
						"url" => $base.'image/view/'.$irow->image_id.'/',
						"user_url" => $base.'user/u/'.$irow->u_username.'/',
						"username" => $irow->u_username,
						"views" => $irow->i_views,
						"overlay" => $base.'assets/images/layout/uploadListOverlay.gif'
					);
					
					$images_html .= $this->load->view("components/uploadListLi",$data,true);
					
				
					
				}
				
				$images_html .= '</ul>';
			}
			else
			{
				if($collection->col_user_id == $this->session->userdata("user_id"))
				{
					$base = base_url();
					$images_html = '
						<p class="borderbox">
							<img class="right" src="'.$base.'assets/images/content/add_fav_col_small.jpg" alt="new add to collection button" width="120" height="62" />
							<strong>Welcome to your new collection!</strong>. You can use the <strong>Add to Collection</strong> button on any upload to add it to a collection. This button
							appears under the Add to Favourites button on any upload, including your own.
							<br/>
							<br/>
							For information, see the <a href="'.$base.'page/faq/" title="FAQ section">FAQ section</a>
							<span class="clear">&nbsp;</span>
						</p>
					';
				}
				else
				{
					$images_html = "No images have been added to this collection.";
				}
			}
			
			// message start
			$message = "";
			if(isset($q_vars->saved))
			{
				$message = "saved";
			}
			
			// controls start
			$controls_html = "";
			
			if($this->session->userdata("user_id") == $collection->col_user_id)
			{
				$controls_html = '
					<ul class="collection_controls">
						<li><a class="edit_link" href="'.$base.'collection/edit/'.$collection->collection_id.'/">Edit Collection</a></li> 
						<li><a class="delete_link"  href="'.$base.'collection/delete/'.$collection->collection_id.'/">Delete Collection</a></li>
						<li><a class="moodboard_link"  href="'.$base.'moodboard/add/'.$collection->collection_id.'/col/">Create Moodboard</a></li>
					</ul>
					<div class="clear">&nbsp;</div>
				';
			}
			
			$view_data = array(
				"col_title" => $collection->col_name,
				"username" => $collection->u_username,
				"profile_url" => getUserProfileURL($collection->u_profile_id,$collection->u_email),
				"col_images_string" => $col_images_string,
				"images_html" => $images_html,
				"message" => $message,
				"controls_html" => $controls_html
			);
			
			// render templates
			$this->template->write("title","Collection: ".$collection->col_name);
			
			$this->template->write_view("content","collections/view", $view_data, TRUE);
				
			//now render templates
			$this->template->render();
		}
		else
		{
			$data = array(
				"errorTitle" => "Collection Not Found",
				"content" => "An error has occurred: the collection with the id <strong>".$id."</strong> could not be found. It may have been deleted."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
	/*
		Add function, used for ajax add collection system
	*/
	function addAJAX()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			$insert_array = array(
				"col_user_id" => $this->session->userdata("user_id"),
				"col_name" => $this->input->xss_clean($_POST["col_name"])
			);
			
			// insert then return a view of the corrent data set once ive done a design
			$insert = $this->collection_model->createNewCollection($insert_array);
			
			if($insert == false)
			{
				echo "false";
			}
			else
			{
				$base = base_url();
				// now return the view
				$view_data = array(
					"collection_id" => $insert["id"],
					"collection_name" => $insert_array["col_name"],
					"collection_url" => $base.'collection/view/'.$insert["id"],
					"collection_update" => date("F j, Y, G:i"),
					"collection_user" => $this->session->userdata("username"),
					"collection_images" => '<em>No images added<em>',
					"collection_image_count" => '0 images',
					"style" => ' style="display: none;" '
				);
				
				$out = $this->load->view("components/collectionsLi",$view_data,true);
				
				echo $out;
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
	
	/*
		Adds an image to a collection
	*/
	function addImage()
	{
		loggedInSection();
		
		if(isAjaxRequest())
		{
			$image_id = $this->input->xss_clean($_POST["image_id"]);
			$col_id = $this->input->xss_clean($_POST["col_id"]);
			
			$this->load->model("image_model");
			$image = $this->image_model->getImage($image_id);
			
			if($image != false && is_numeric($col_id))
			{
				// grab collection
				$collection = $this->collection_model->getCollection($col_id)->row();
				$image = $image->row();
				
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
						
						// check for upload_comments in notice format string
						$createNotice = true;
						$this->load->model("user_model");
						$subject_user = $this->user_model->getUser($image->i_user_id)->row();
						
						if(strpos($subject_user->u_notice_format,"collection_add") === FALSE)
						{
							$createNotice = false;
						}
						
						if($insert !== false && $createNotice == true)
						{
							/* 
								Add notice code
								
								Only add if its not your own image, save times with activity
							*/
							if($image->i_user_id != $this->session->userdata("user_id"))
								{
								$notice_insert = array(
									"n_object_user_id" => $image->i_user_id,
									"n_object_id" => $image->image_id,
									"n_object_type" => "image",
									"n_action_user_id" => $this->session->userdata("user_id"),
									"n_type" => "collection_add",
									"n_new" => 1,
									"n_html" => ""
								);
								
								$base = base_url();
								// now html
								$notice_html = '<span class="notice_date">'.date("F j, Y, G:i").'</span> - 
										<a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="'.$this->session->userdata("username").'\'s profile">'.$this->session->userdata("username").'</a> added your ';
										
								
								$n_url = $base.'image/view/'.$image->image_id.'/';			
									
								$notice_html .= 'upload <a href="'.$n_url.'" title="View this image">'.$image->i_title.'</a>';
								
								
								
								$notice_html .= ' to their collection <a href="'.$base.'collection/view/'.$collection->collection_id.'/" title="View Collection">'.$collection->col_name.'</a>';
								
								$notice_insert["n_html"] = $notice_html;
								
								$this->load->model("notice_model");
								$this->notice_model->createNewNotice($notice_insert);
							}
									
							/*
								End notice code add
							*/
							
							echo "true";
						}
						else if($insert == false)
						{
							echo "false";
						}
						else
						{
							echo "true";
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

	/*
		Creates a collection and adds image to it
	*/
	function addImageToNew()
	{
		loggedInSection();
		
		if(isAjaxRequest())
		{
			// first create the collection
			$col_insert_array = array(
				"col_user_id" => $this->session->userdata("user_id"),
				"col_name" => $this->input->xss_clean($_POST["newcol_name"])
			);
			
			// insert then return a view of the corrent data set once ive done a design
			$insertcol = $this->collection_model->createNewCollection($col_insert_array);
			
			if($insertcol == false)
			{
				echo "colfail";
			}
			else
			{
				// now add an image
				$image_id = $this->input->xss_clean($_POST["image_id"]);
				$this->load->model("image_model");
				$image = $this->image_model->getImage($image_id)->row();
				
				$update_data = array(
					"col_string" => $image_id,
					"col_updated" => date('Y-m-d H:i:s')
				);
				
				
				$insert = $this->collection_model->updateCollection($update_data,$insertcol["id"]);
				
				// check for upload_comments in notice format string
				$createNotice = true;
				$this->load->model("user_model");
				$subject_user = $this->user_model->getUser($image->i_user_id)->row();
						
				if(strpos($subject_user->u_notice_format,"collection_add") === FALSE)
				{
					$createNotice = false;
				}
				
				if($insert !== false && $createNotice == true && $image != false)
				{
					
					/* 
						Add notice code
						
						Only add if its not your own image, save times with activity
					*/
					if($image->i_user_id != $this->session->userdata("user_id"))
					{
						$notice_insert = array(
							"n_object_user_id" => $image->i_user_id,
							"n_object_id" => $image->image_id,
							"n_object_type" => "image",
							"n_action_user_id" => $this->session->userdata("user_id"),
							"n_type" => "collection_add",
							"n_new" => 1,
							"n_html" => ""
						);
						
						$base = base_url();
						// now html
						$notice_html = '<span class="notice_date">'.date("F j, Y, G:i").'</span> - 
								<a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="'.$this->session->userdata("username").'\'s profile">'.$this->session->userdata("username").'</a> added your ';
								
						
						$n_url = $base.'image/view/'.$image->image_id.'/';			
							
						$notice_html .= 'upload <a href="'.$n_url.'" title="View this image">'.$image->i_title.'</a>';
						
						
						
						$notice_html .= ' to their collection <a href="'.$base.'collection/view/'.$insertcol["id"].'/" title="View Collection">'.$this->input->xss_clean($_POST["newcol_name"]).'</a>';
						
						$notice_insert["n_html"] = $notice_html;
						
						$this->load->model("notice_model");
						$this->notice_model->createNewNotice($notice_insert);
					}
							
					/*
						End notice code add
					*/
					
					echo "true";
				}
				else if($insert == false)
				{
					echo "false";
				}
				else
				{
					echo "true";
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
	
	/**
		Edit, allows users to edit the collection
		@param id : id of the collection to edit.
	**/
	function edit($id = null)
	{
		loggedInSection(); 
		
		if($id == null || !is_numeric($id))
		{
			$col = $this->collection_model->getCollection($this->input->post("col_edit_id"));
			
			
			if($col == false)
			{
				redirect('','location');
			}
			else
			{
				$col = $col->row();
			}
			
			if($this->session->userdata("user_id") == $col->col_user_id)
			{
				$update_data = array(
					"col_string" => $this->input->post("col_string_values"),
					"col_name" => $this->input->post("col_name")
				);
				
				$update = $this->collection_model->updateCollection($update_data,$col->collection_id);
				
				if($update == true)
				{
					redirect('/collection/view/'.$col->collection_id.'/?col_saved=1','location');
				}
				else
				{
					echo 'Something went wrong when saving the collection';
				}
				
			}
		}
		else
		{
			$col = $this->collection_model->getCollection($id);
			
			
			if($col == false)
			{
				redirect('','location');
			}
			else
			{
				$col = $col->row();
			}
			
			if($this->session->userdata("user_id") == $col->col_user_id)
			{
				// need to grab images as well
				$images = $this->collection_model->getCollectionImages($id);
				
				
				if($images == false)
				{
					$images_html = "<p class='info'>No Images In Collection</p>";
				}
				else
				{
					$images_html = '<ul class="collectionEditList">';
					$image_count = count($images);
					
					for($i = 0; $i < $image_count; $i++)
					{
						$irow = $images[$i];
						
						// load li through view
						$data = array(
							"thumb_url" => $irow->i_thumb_url,
							"image_id" => $irow->image_id,
							"title" => $irow->i_title,
							"username" => $irow->u_username,
						);
						
						$images_html .= $this->load->view("components/collectionEditListLi",$data,true);
					}
					
					$images_html .= '</ul>';
				}
				
				$data = array(
					"collection" => $col,
					"images_html" => $images_html,
				);
				
				$this->template->add_js("assets/js/image.js");
				
				$this->template->write("title","Edit your Collection");
				
				
				$this->template->write_view("content","collections/edit", $data, TRUE);
				
			
				$this->template->render();
			}
			else
			{
				redirect('','location');
			}
		}
	}
	
	/*
		Delete function, allows a user to delete their own collection
		Same page used for security, the param can only be passed through a user 
		submitting the original form
		
		@param id : collection id
	*/
	function delete($id = null)
	{
		
		loggedInSection(); // urika_helper.php
		
		if($id == null || !is_numeric($id))
		{
			$col = $this->collection_model->getCollection($this->input->post("delete_id"));
			
			if($col == false)
			{
				redirect('','location');
			}
			else
			{
				$col = $col->row();
			}
		
			
			
			if($this->session->userdata("user_id") == $col->col_user_id)
			{
					$mb_ids = $this->collection_model->getCollectionMoodboardIds($col->collection_id);
				
				// delete database records and delete file
				if($this->collection_model->deleteCollection($col->collection_id))
				{
					// atempt to delet collections
									
					if($mb_ids != false)
					{
						$count = count($mb_ids);
						$this->load->model("moodboard_model");
						
						for($i = 0; $i < $count; $i++)
						{
							$this->moodboard_model->deleteMoodboard($mb_ids[$i]->moodboard_id);
						}
					}
				
					redirect("/user/u/".$this->session->userdata("username")."/?collection_deleted=1","location");
				}
				else
				{
				
					$data = array(
					"errorTitle" => "Delete Failed",
					"content" => "An error has occurred when delete the collection. Please ensure this is your collection. If the problem persists, please get in touch"
					);
					
					
					$this->template->write_view("content","general/error", $data, TRUE);
						
					//now render templates
					$this->template->render();
				}
			}
		}
		else
		{
			$col = $this->collection_model->getCollection($id);
			
			
			if($col == false)
			{
				redirect('','location');
			}
			else
			{
				$col = $col->row();
			}
			
			if($this->session->userdata("user_id") == $col->col_user_id)
			{
				$base = base_url();
				// now show delete screen
				$data = array(
					"collection_title" => $col->col_name,
					"collection_url" => $base."collection/view/".$col->collection_id."/", 
					"delete_id" => $col->collection_id
				);
				
				$this->template->write("title","Delete your Collection");
				
				
				$this->template->write_view("content","collections/delete", $data, TRUE);
				
			
				$this->template->render();
				
			}
			else
			{
				redirect('','location');
			}
		}
	}
	
}

/* End of file collection.php */
/* Location: ./system/application/controllers/collection.php */