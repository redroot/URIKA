<?php
/**
	Browse controller
**/
class Browse extends Controller {

	function Browse()
	{
		parent::Controller();	
		$this->load->model("image_model");
		$this->load->model("general_model");
	}
	
	function index()
	{
				
		//query vars
		$q_vars = get_url_vars();
		$base = base_url();
	
		// set search vars
		$order_by = (isset($q_vars->sort)) ? $q_vars->sort : "datetime";
		$order_dir = (isset($q_vars->sort_dir)) ? strtoupper($q_vars->sort_dir) : "DESC";
		$per_page = 20;
		$offset = (isset($q_vars->page)) ? ($q_vars->page-1)*$per_page : 0;
		$current_page = (isset($q_vars->page)) ? ($q_vars->page) : 1;
		$search_val = (isset($q_vars->search)) ? (str_replace("+"," ",$q_vars->search)) : "";
		$tag_val = (isset($q_vars->tag)) ? str_replace("+"," ",$q_vars->tag) : "";
		$type = (isset($q_vars->type)) ? ($q_vars->type) : "both";
		
		$results = $this->general_model->searchAll($tag_val,$search_val,$type,$offset,$per_page,$order_by,$order_dir); 
		
		if($results == false)
		{
			$total = 0;
			$count_string = 0;
			$show_prev = false;
			$show_next = false;
		}
		else
		{
			// calculate offset and info
			
			$total = $results["total"];
			$count = count($results["results"]);
			
			// work out page results text
			// so we can control what appears in the same conditional
			$show_next = true;
			$show_prev = false;
			
			// if a page is set
			if(isset($q_vars->page))
			{
				$show_prev = true;
				$current_start = ($per_page*($q_vars->page-1))+1;
				
				// determine end
				if($per_page * $q_vars->page < $total)
				{
					// if there are more on the next page
					$current_end = $per_page * $q_vars->page;
					$show_next = true;
				}
				else
				{
					// else we dont need a previ button
					$current_end = $total;
					$show_next = false;
				}
			}
			else
			{
				// now page, so determine when the end should say
				$current_start = 1;
				if($total >= $per_page)
				{
					$current_end = $per_page;
				}
				else
				{
					$current_end = $total;
					$show_next = false;
				}
			}
			
			$count_string = $current_start.' - '.$current_end;
			
			
		}
		
		// query html
		// same as batave, x of y records, filter info
		$results_info = "";
		
		// count
		$results_info .= 'Showing <strong>'.$count_string.'</strong> of <strong>'.$total.'</strong> results';
		
		if($tag_val != "")
		{
			$results_info .= '. Filtering for tags containing <strong>'.$tag_val.'</strong>';
		}
		
		
		if($search_val != "")
		{
			$results_info .= '. Filtering for search term <strong>'.$search_val.'</strong>';
		}
		
		// now reassign results
		$results = $results["results"];
		
		
	
	
		
		if($total > 0 && isset($results[0]))
		{
			$out = '<ul class="uploadList">';
					
			for($i = 0; $i < $per_page; $i++)
			{
				if(isset($results[$i]->image_id))
				{
					//parse Image 
					

					$data = array(
						"thumb_url" => $results[$i]->i_thumb_url,
						"title" => $results[$i]->i_title,
						"url" => $base.'image/view/'.$results[$i]->image_id.'/',
						"user_url" => $base.'user/u/'.$results[$i]->u_username.'/',
						"username" => $results[$i]->u_username,
						"views" => $results[$i]->i_views,
						"overlay" => $base.'assets/images/layout/uploadListOverlay.gif'
					);
					
					$out .= $this->load->view("components/uploadListLi",$data,true);
				}
				else if(isset($results[$i]->moodboard_id))
				{
					// parse Moodboard
					$data = array(
						"thumb_url" => $results[$i]->m_thumb_url,
						"title" => $results[$i]->m_title,
						"url" => $base.'moodboard/view/'.$results[$i]->moodboard_id.'/',
						"user_url" => $base.'user/u/'.$results[$i]->u_username.'/',
						"username" => $results[$i]->u_username,
						"views" => $results[$i]->m_views,
						"overlay" => $base.'assets/images/layout/mbListOverlay.gif'
					);
					
					$out .= $this->load->view("components/mbListLi",$data,true);
				}
				else if(isset($results[$i]->row_id))
				{
					// this is from the UNION sql so we have to be a bit smarter here
					
					if($results[$i]->type == "image")
					{
						$row_url = $base.'image/view/'.$results[$i]->row_id.'/';
						$overlay = $base.'assets/images/layout/uploadListOverlay.gif';
						$view = "components/uploadListLi";
					}
					else
					{
						$row_url = $base.'moodboard/view/'.$results[$i]->row_id.'/';
						$overlay = $base.'assets/images/layout/mbListOverlay.gif';
						$view = "components/mbListLi";
					}
					
					// parse object
					// parse Moodboard
					$data = array(
						"thumb_url" => $results[$i]->row_thumb_url,
						"title" => $results[$i]->row_title,
						"url" => $row_url,
						"user_url" => $base.'user/u/'.$results[$i]->u_username.'/',
						"username" => $results[$i]->u_username,
						"views" => $results[$i]->views,
						"overlay" => $overlay
					);
					
					$out .= $this->load->view($view,$data,true);
				}
				
			
				
			}
			
			$out .= '</ul>';
		}
		else
		{
			$out = '<p class="error">No results for this criteria</p>';
		}
		
		// pagination links
		
		$no_list = array(
			'page',
			'browse_search'
		);
		
		$out.= '
		<div class="pagination_buttons clear">
		';
		
		if($show_prev)
		{
			$out .= '<a class="pagination" href="'.$base.'browse/'.queryStringDrop(null,$no_list,-1).'"  title="View previous page of results">< Previous</a>';
		}
		
		if($show_next && $total > $per_page)
		{
			$out .= '<a class="pagination"  href="'.$base.'browse/'.queryStringDrop(null,$no_list,1).'"  title="Viewnext page of results">Next ></a>';
		}
		
		$out .= '
		</div>
		';
		
		// now render
		
		$data = array (
			"list_html" => $out,
			"search" => $search_val,
			"tag" => $tag_val,
			"type" => $type,
			"order_by" => $order_by,
			"results_info" => $results_info,
		);
		
		$this->template->write("title","Browsing");
		
		$this->template->write_view("content","browse/browse_default",$data);
		
		$this->template->render();
	}
	
	/**
		AJAX browse super function, used to grab image results follow results,
		and comments results etc but in an a straight upload return form
		
		Params are part of the post var, the type of results determines by the url parameter
	
		@param type : type of results to grab
	*/
	function ajaxresults($type = null)
	{
		if(isAjaxRequest())
		{
			if($type != null)
			{
				$allowedTypes = array(
					"images",
					"moodboards",
					"comments",
				);
				
				$this->input->xss_clean($_POST);
				
				// check it actually exists
				
				
				if(in_array(strtolower($type),$allowedTypes))
				{
					$result_html = "";
					$result;
					$base = base_url();
					
					// some universal or multiplue use variables
					$search_term = $tag = "";
					$page = 1;
					$per_page = 20;
					$base = base_url();
					
					
					// now process the post terms
					if(isset($_POST["search"]) && $_POST["search"] != "")
					{
						$search_term = trim($_POST["search"]);
					}
					
					if(isset($_POST["tag"]) && $_POST["tag"] != "")
					{
						$tag = trim($_POST["tag"]);
					}
					
					if(isset($_POST["page"]) && $_POST["page"] != "")
					{
						$page = $_POST["page"];
					}
					
					if(isset($_POST["per_page"]) && $_POST["per_page"] != "")
					{
						$per_page = $_POST["per_page"];
					}

					// set offset
					$offset = (int) ($page * $per_page);

					
					if(strtolower($type) == "images")
					{
						/*
							1) Images search
								
							Handles:
								Order by Date
								Order by Views
								User's uploads
								User's favs
								Follow feed
								Tag
								Search term
						*/
						
						$order_by = "";
						$order_dir = "";
						
						if(isset($_POST["order_by"]) && $_POST["order_by"] == "")
						{
							$order_by = trim($_POST["order_by"]);
						}
						
						// Now determine which functions to run
						if(isset($_POST["user_id"]) && !isset($_POST["user_favs"]))
						{
							// this means we are looking at user uploads
							$this->load->model("user_model");
							
							$results = $this->user_model->getUserImages($_POST["user_id"],($per_page * ($page-1)));
							
							// generate user lis from results
							
						}
						else if(isset($_POST["user_favs"]))
						{
							// this means we are looking at favurites
							$this->load->model("user_model");
							
							$results = $this->user_model->getUserFavs($_POST["user_id"],($per_page * ($page-1)));

						}
						
						// now process results into upload list li
						
						if($results["count"] != false)
						{
							$out = "";
							
							
							
							for($i = 0; $i < $results["count"]; $i++)
							{
								
								$irow = $results["result"][$i];
							
								if(!isset($irow->f_type))
								{
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
									
									$out .= $this->load->view("components/uploadListLi",$data,true);
								}
								else if($irow->f_type == "image")
								{
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
									
									$out .= $this->load->view("components/uploadListLi",$data,true);
								}
								else if($irow->f_type == "moodboard")
								{
									
									// load li through view
									$data = array(
										"thumb_url" => $irow->m_thumb_url,
										"title" => $irow->m_title,
										"url" => $base.'moodboard/view/'.$irow->moodboard_id.'/',
										"user_url" => $base.'user/u/'.$irow->u_username.'/',
										"username" => $irow->u_username,
										"views" => $irow->m_views,
										"overlay" => $base.'assets/images/layout/mbListOverlay.gif'
									);
									
									$out .= $this->load->view("components/mbListLi",$data,true);
								
								}
								
							}
						}
						else
						{
							$out = "no_more";
						}
						
						$return_html = $out;
					}
					else if(strtolower($type) == "moodboards")
					{
						/*
							Moodboards search
							
							- simple pagination for user page
						*/
						
						if(isset($_POST["user_id"]))
						{
							// this means we are looking at user uploads
							$this->load->model("user_model");
							
							$results = $this->user_model->getUserMoodboards($_POST["user_id"],($per_page * ($page-1)));
							
							if($results["count"] != 0)
							{
								$out = "";
								
								for($i = 0; $i < $results["count"]; $i++)
								{
									$mrow = $results["result"][$i];
									
									// load li through view
									$data = array(
										"thumb_url" => $mrow->m_thumb_url,
										"title" => $mrow->m_title,
										"url" => $base.'moodboard/view/'.$mrow->moodboard_id.'/',
										"user_url" => $base.'user/u/'.$mrow->u_username.'/',
										"username" => $mrow->u_username,
										"views" => $mrow->m_views,
										"overlay" => $base.'assets/images/layout/mbListOverlay.gif'
									);
									
									$out .= $this->load->view("components/mbListLi",$data,true);
									
								
									
								}
				
								$return_html = $out;
							}
							else
							{
								$return_html = "no_more";
							}
						}
						else
						{
							$return_html = "false";
						}
					
					}
					else if(strtolower($type) == "comments")
					{
						$this->load->model("comment_model");
						// simply, simply need to return an offset
						$comments = $this->comment_model->getComments($_POST["subject_id"],$page,$_POST["comment_type"]);
						
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
							
							$return_html = $comments_html;
						}
						else
						{
							// no more comments
							$return_html = "no_more";
						}
					}
					else
					{
					
					}
					
					echo $return_html;
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
			redirect('','location');
		}
	}
	
	
}

/* End of file browse.php */
/* Location: ./system/application/controllers/browse.php */