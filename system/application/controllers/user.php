<?php
/**
	User controller
**/
class User extends Controller {

	// global setting for invites. e.g. test invite code, display invites
	private $useInvites = true;

	function User()
	{
		parent::Controller();	
		$this->load->model("user_model");
	}
	
	function index()
	{
		redirect('','location');
	}	
	
	/*
	* 	User profile page, specified by either id or name
	*/
	function u($user)
	{
		
		// determine id or name
		if(is_numeric($user))
		{
			$query = $this->user_model->getUser($user);
		}
		else // must be a name
		{
			$query = $this->user_model->getUser($user,"u_username");
		}
		
		if($query !== false) // if user exists
		{	
			$row = $query->row();
			
			// some prelim stuff before we load the page: user_profile etc
			
			$image_url = getUserProfileURL($row->u_profile_id, $row->u_email); // urika.helper
			
			
			
			/***
				Grabbing User DB stuff
			**/
			$uploads = $this->_getUserUploads($row->user_id,"both");
			$favourites = $this->user_model->getUserFavs($row->user_id);
			$collections = $this->user_model->getUserCollections($row->user_id);
			$follows = $this->_getUserFollows($row->user_id,"both");

			
			
			// set up user info string
			$info_string = "";
			
			if($row->u_firstname != "")
				$info_string .= $row->u_firstname." ".$row->u_surname;
			
			if($row->u_website != "")
			{
				if($info_string != "") $info_string .= ' | ';
				
				$info_string .= '<a class="u_website_link" href="'.$row->u_website.'" title="'.$row->u_username.'\'s website">'.$row->u_website.'</a>';
			}
			
			if($row->u_twitter != "")
			{
				if($info_string != "") $info_string .= ' | ';
				
				$info_string .= '<a class="u_twitter_link" href="http://twitter.com/'.$row->u_twitter.'" title="'.$row->u_username.'\'s twitter">@'.$row->u_twitter.'</a>';
			}
			
			if($row->u_location != "")
			{
				if($info_string != "") $info_string .= ' | ';
				
				$info_string .= $row->u_location;
			}
			
			// folow button stuff
			$follow_section = "";
			if($row->user_id != $this->session->userdata("user_id") && $this->session->userdata("is_logged_in") == 1)
			{
				$already_following = false;
				//check for already following:
				for($j = 0; $j < $follows["followedby"]["count"]; $j++)
				{
					if($follows["followedby"]["result"][$j]->f_follower == $this->session->userdata("user_id"))
					{
						$already_following = true;
						break;
					}
				}
				
				if($already_following)
				{
				
					$follow_section = '<a id="profile_unfollow" onclick="deleteFollow('.$row->user_id.','.$this->session->userdata("user_id").');" href="#" title="Unfollow '.$row->u_username.'"><span class="hide">Unfollow '.$row->u_username.'</span></a>';
			
				}
				else
				{
					$follow_section = '<a id="profile_follow" onclick="addFollow('.$row->user_id.');" href="#" title="Follow '.$row->u_username.'"><span class="hide">Follow '.$row->u_username.'</span></a>';
				
				}
			}
			
			
			/* 
				Generate User data from DB records
			*/
			$base = base_url();
			// Images
			
			if($uploads["images"]["count"] > 0)
			{
				$out = '<ul class="uploadList" id="userUploads">';
				
				for($i = 0; $i < $uploads["images"]["count"]; $i++)
				{
					$irow = $uploads["images"]["result"][$i];
					
					// load li through view
					$data = array(
						"thumb_url" => $irow->i_thumb_url,
						"title" => $irow->i_title,
						"url" => $base.'image/view/'.$irow->image_id.'/'.slugify($irow->i_title).'/',
						"user_url" => $base.'user/u/'.$row->u_username.'/',
						"username" => $row->u_username,
						"views" => $irow->i_views,
						"overlay" => $base.'assets/images/layout/uploadListOverlay.gif'
					);
					
					$out .= $this->load->view("components/uploadListLi",$data,true);
					
				
					
				}
				
				$out .= '</ul>';
				
				if($uploads["images"]["total"] > 20)
				{
					$out .= '
					<div class="pagination_buttons uploads_pagination clear">
								<span id="current_uploads_page" class="hide">1</span>
								<input type="button" class="pagination hide" onclick="userUploadsNav('.$row->user_id.',-1,'.$uploads["images"]["total"].');" name="uploads_prev" id="uploads_prev" value="&lt; Newer" />
								<input type="button" class="pagination" onclick="userUploadsNav('.$row->user_id.',1,'.$uploads["images"]["total"].');" name="uploads_next" id="uploads_next" value="Older &gt;" />
								<span class="loading hide">&nbsp;</span>
							</div>
					';
				}
				
				$output["images"] = $out;
			}
			else
			{
				$output["images"] = "No images uploaded";
			}
			
			//moodboards
			
			
			if($uploads["mbs"]["count"] > 0)
			{
				$out = '<ul class="uploadList" id="userMoodboards">';
				
				for($i = 0; $i < $uploads["mbs"]["count"]; $i++)
				{
					$mrow = $uploads["mbs"]["result"][$i];
					
					// load li through view
					$data = array(
						"thumb_url" => $mrow->m_thumb_url,
						"title" => $mrow->m_title,
						"url" => $base.'moodboard/view/'.$mrow->moodboard_id.'/'.slugify($mrow->m_title).'/',
						"user_url" => $base.'user/u/'.$row->u_username.'/',
						"username" => $row->u_username,
						"views" => $mrow->m_views,
						"overlay" => $base.'assets/images/layout/mbListOverlay.gif'
					);
					
					$out .= $this->load->view("components/mbListLi",$data,true);
					
				
					
				}
				
				$out .= '</ul>';
				
				if($uploads["mbs"]["total"] > 20)
				{
					$out .= '
					<div class="pagination_buttons mbs_pagination clear">
								<span id="current_mbs_page" class="hide">1</span>
								<input type="button" class="pagination hide" onclick="userMBsNav('.$row->user_id.',-1,'.$uploads["mbs"]["total"].');" name="mbs_prev" id="mbs_prev" value="&lt; Newer" />
								<input type="button" class="pagination" onclick="userMBsNav('.$row->user_id.',1,'.$uploads["mbs"]["total"].');" name="mbs_next" id="mbs_next" value="Older &gt;" />
								<span class="loading hide">&nbsp;</span>
							</div>
					';
				}
				
				$output["moodboards"] = $out;
			}
			else
			{
				if($this->session->userdata("user_id") !== FALSE && $this->session->userdata("user_id") == $row->user_id)
				{
					$output["moodboards"] = '
						<div class="borderbox" id="collectionIntro">
							<p>
								<strong>You don\'t have any moodboards yet!</strong>
								Moodboards are collages built from images in your collections, ideal for showing clients or brainstorming ideas. UR!KA lets you arrange images
								in any order, size or rotation to make your ideal moodboard!<br/><br/>
							</p>
							<p>
								If you already have <strong>Collections</strong>, simply click on a collection then <strong>Create Moodboard</strong> to get started, it\'s that easy! Otherwise, head over to the
								collections tab and create a collection first!
							</p>
						</div>
					';
				}
				else
				{
					$output["moodboards"] = 'No Moodboards Yet';
				}
			}
			
			
			
			
			
			
			// favourites
			
			
			
			if($favourites["count"] > 0)
			{
				$out = '<ul class="uploadList" id="userFavs">';
				
				for($i = 0; $i < $favourites["count"]; $i++)
				{
					$irow = $favourites["result"][$i];
					
					if($irow->f_type == "image")
					{
						// load li through view
						$data = array(
							"thumb_url" => $irow->i_thumb_url,
							"title" => $irow->i_title,
							"url" => $base.'image/view/'.$irow->image_id.'/'.slugify($irow->i_title).'/',
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
							"url" => $base.'moodboard/view/'.$irow->moodboard_id.'/'.slugify($irow->m_title).'/',
							"user_url" => $base.'user/u/'.$row->u_username.'/',
							"username" => $row->u_username,
							"views" => $irow->m_views,
							"overlay" => $base.'assets/images/layout/mbListOverlay.gif'
						);
						
						$out .= $this->load->view("components/mbListLi",$data,true);
					
					}
					
				
					
				}
				
				$out .= '</ul>';
				
				if($favourites["total"] > 20)
				{
					$out .= '
					<div class="pagination_buttons favourites_pagination clear">
								<span id="current_favourites_page" class="hide">1</span>
								<input type="button" class="pagination hide" onclick="userFavsNav('.$row->user_id.',-1,'.$favourites["total"].');" name="favs_prev" id="favs_prev" value="&lt; Newer" />
								<input type="button" class="pagination" onclick="userFavsNav('.$row->user_id.',1,'.$favourites["total"].');" name="favs_next" id="favs_next" value="Older &gt;" />
								<span class="loading hide">&nbsp;</span>
							</div>
					';
				}
				
				
				$output["favourites"] = $out;
			}
			else
			{
				$output["favourites"] = "No favourites added";
			}
			
			// collections
			
			if($collections["count"] > 0)
			{
				$out = "";
				
				$this->load->model("collection_model");
				
				$out .= '<ul class="bigCollectionsList">';
				
				for($i = 0; $i < $collections["count"]; $i++)
				{
					$irow = $collections["result"][$i];
					
					$images = $this->collection_model->getCollectionImages($irow->collection_id,true);
					$images_html = "";
					
					if($images != false)
					{
						for($j = 0; $j < 4; $j++)
						{
							if(isset($images[$j]))
							{
								$images_html .= '<img src="'.$images[$j]->i_thumb_url.'" width="94" height="94" alt="Thumbnail image for a memer of this collection" />';
							}
						}
					}
					else
					{
						$images_html = "<em>No Images added</em>";
					}
					
					$ids_array = explode(",",$irow->col_string);
					
					if($ids_array[0] == "")
					{
						$image_count = 0;
					}
					else
					{
						$image_count = count($ids_array);
					}
					// load li through view
					
					$view_data = array(
						"collection_id" => $irow->collection_id,
						"collection_name" => $irow->col_name,
						"collection_url" => $base.'collection/view/'.$irow->collection_id.'/'.slugify($irow->col_name).'/',
						"collection_update" => date("F j, Y, G:i",strtotime($irow->col_updated)),
						"collection_user" => $row->u_username,
						"collection_images" => $images_html,
						"collection_image_count" => $image_count.' images',
						"style" => ' '
					);
					
					$out .= $this->load->view("components/collectionsLi",$view_data,true);
					
				}
				
				$out .= '</ul>';
				
				if($row->user_id == $this->session->userdata("user_id") && $this->session->userdata("is_logged_in") == 1)
				{
					$out .= '
						<div>
							<input type="button" class="forceStyle" name="addCollection" id="addCollection" value="Add a Collection" />
						</div>
						<div id="createCollectionForm" class="hide">
							<ul>
								<li>
									<label for="newcol_name">Collection Name:</label>
									<input type="text" name="newcol_name" class="newcol_name" />
								</li>
								<li>
									<input type="button" name="saveNewCollection" id="saveNewCollection" onclick="addCollection()" value="Save Collection" />
									<span class="loading hide">&nbsp;</span>
								</li>
							</ul>
						</div>
					';
				}
				
				$output["collections"] = $out;
			}
			else
			{
				if($this->session->userdata("user_id") !== FALSE && $this->session->userdata("user_id") == $row->user_id)
				{
					$output["collections"] = '
					<div class="borderbox" id="collectionIntro">
						<p>
							<strong>You don\'t have any collections yet!</strong>
							Collections let you group similar uploads that you find on UR!KA e.g. good examples of navigation. You can build <strong>Moodboards</strong>
							from Collections! Click the button below to start you first collection.
						</p>
						<p>
							<br />
							<input type="button" name="addCollection" id="addCollection" value="Create a Collection" />
						</p>
					</div>
					<div id="createCollectionForm" class="hide">
							<ul>
								<li>
									<label for="newcol_name">Collection Name:</label>
									<input type="text" name="newcol_name" class="newcol_name" />
								</li>
								<li>
									<input type="button" name="saveNewCollection" id="saveNewCollection" onclick="addCollection()" value="Save Collection" />
									<span class="loading hide">&nbsp;</span>
								</li>
							</ul>
						</div>
					
					<ul class="bigCollectionsList">
					</ul>
					';
				}
				else
				{
					$output["collections"] = 'No Collections Yet';
				}
			}
			
			// Follows
			if($follows["follows"]["count"] > 0)
			{
				$out = '<ul class="smallUserList" id="followingList">';
				
				for($i = 0; $i < 10; $i++)
				{
					if(isset($follows["follows"]["result"][$i]))
					{
						$urow = $follows["follows"]["result"][$i];
						
						$fimage_url = getUserProfileURL($urow->u_profile_id, $urow->u_email); // urika.helper
						
						
						$profilelink = $base.'user/u/'.$urow->u_username.'/';
						
						$lidata = array(
							"user_id" => $urow->user_id,
							"profile_link" => $profilelink,
							"profile_img" => $fimage_url,
							"username" => $urow->u_username
						);
						
						$out .= $this->load->view("components/smallUserListLi",$lidata,true);
					}
					
				}
				
				$out .= '</ul>';
				
				if($follows["follows"]["count"] > 9)
				{
					$out .= '<a class="follow_view_all" href="'.$base.'user/ulist/?followed_by='.$row->u_username.'">View All Following</a>';
				}
				
				$output["follows"] = $out;
			}
			else
			{
				$output["follows"] = "No following";
			}
			
			//	Followed By
			// for now imaes are all gravatars
			if($follows["followedby"]["count"] > 0)
			{
				$out = '<ul class="smallUserList" id="followersList">';
				
				for($i = 0; $i < 10; $i++)
				{
					if(isset($follows["followedby"]["result"][$i]))
					{
						$urow = $follows["followedby"]["result"][$i];
						
						$fimage_url = getUserProfileURL($urow->u_profile_id, $urow->u_email);
						
						$base = base_url();
						$profilelink = $base.'user/u/'.$urow->u_username.'/';
						
						$lidata = array(
							"user_id" => $urow->user_id,
							"profile_link" => $profilelink,
							"profile_img" => $fimage_url,
							"username" => $urow->u_username
						);
						
						$out .= $this->load->view("components/smallUserListLi",$lidata,true);
					}
					
				}
				
				$out .= '</ul>';
				
				if($follows["followedby"]["count"] > 9)
				{
					$out .= '<a class="follow_view_all" href="'.$base.'user/ulist/?following='.$row->u_username.'">View All Followers</a>';
				}
				
				$output["followedby"] = $out;
			}
			else
			{
				$output["followedby"] = "No followers";
			}
			
			
			
			
			$data = array(
				"username" => $row->u_username,
				"image_url" => $image_url,
				"info_string" => $info_string,
				"follow_section" => $follow_section,
				"user_count" => array(
					'images' => $uploads["images"]["total"],
					'mbs' => $uploads["mbs"]["total"],
					'favs' => $favourites["total"],
					'collections' => $collections["count"],
					'follows' => $follows["follows"]["count"],
					'followedby' => $follows["followedby"]["count"]
				),
				"output" => $output
			);
			
			//now render templates
			
			$this->template->write("title",$row->u_username."'s Profile");
			
			$this->template->add_js("assets/js/tabs.js");
			$this->template->add_js("assets/js/ajax.js");
			$this->template->write_view("content","user/profile", $data, TRUE);
			
		
			$this->template->render();
		}
		else
		{
			redirect('user/ulist/?usernotfound='.$user,'location');// go to user list?
		}
	}
	
	/*
		Lists users. Can be searched/sorted
	*/
	function ulist()
	{
		$q_vars = get_url_vars();
		$base = base_url();
		
		// sort vars
		$limit = 10;
		$search = (isset($q_vars->search)) ? ($q_vars->search) : "";
		$offset = (isset($q_vars->page)) ? ($q_vars->page-1)*$limit : 0;
		$order_by = (isset($q_vars->order_by)) ? $q_vars->order_by : "";
		$order_dir = (isset($q_vars->order_dir)) ? strtoupper($q_vars->order_dir) : "";

		// following vars, using the same one, let model handle it
		if(isset($q_vars->following))
		{
			$following = "following_".$q_vars->following;
		}
		else if(isset($q_vars->followed_by))
		{
			$following = "followedby_".$q_vars->followed_by;
		}
		else
		{
			$following = "";
		}
		
		$users = $this->user_model->getUsers($search,$offset,$limit,$following,$order_by,$order_dir);
	
		$table_html = "";
		
		if(isset($q_vars->usernotfound))
		{
			$table_html .= '
				<p class="error" style="margin-bottom: 10px;">
					User <strong>'.$q_vars->usernotfound.'</strong> not found. Try searching again
				</p>
			';
		}
		
		// handle user feedback
		$filters_html = "";
		// no applicable q_vars
		$no_list = array(
			"page",
			"usernotfound",
			"user_search"
		);
		
		// Set up filters at the top
		if($search != "" || isset($q_vars->following) || isset($q_vars->followed_by))
		{
		
			$filters_html = '<ul class="search_filters user_search_filters">';
		
			
			
			if($search != "")
			{
				$filters_html .= '<li><a href="'.$base.'user/ulist/'.queryStringDrop("search",$no_list).'"class="remove_filter" title="Remove this filter"><span class="hide">Remove</span></a> 
							Search results for <strong>'.$search.'</strong></li>';
			}
			
			if(isset($q_vars->following))
			{
				
				$filters_html .= '<li><a href="'.$base.'user/ulist/'.queryStringDrop("following",$no_list).'" class="remove_filter" title="Remove this filter"><span class="hide">Remove</span></a> Showing users following <strong>'.$q_vars->following.'</strong></li>';
			}
			
			if(isset($q_vars->followed_by))
			{
				
				$filters_html .= '<li><a href="'.$base.'user/ulist/'.queryStringDrop("followed_by",$no_list).'" class="remove_filter" title="Remove this filter"><span class="hide">Remove</span></a> Showing users followed by <strong>'.$q_vars->followed_by.'</strong></li>';
			}

			$filters_html .= '</ul>';
		}
		
		

		// now output the table
	
		$query_count = count($users["result"]);
		
		
		if($users != false)
		{
			// work out page results text
			$showing = "";
			// so we can control what appears in the same conditional
			$show_next = true;
			$show_prev = false;
			
			// if a page is set
			if(isset($q_vars->page))
			{
				$show_prev = true;
				$current_start = ($limit*($q_vars->page-1))+1;
				
				// determine end
				if($limit * $q_vars->page < $users["total"])
				{
					// if there are more on the next page
					$current_end = $limit * $q_vars->page;
					$show_next = true;
				}
				else
				{
					// else we dont need a previ button
					$current_end = $users["total"];
					$show_next = false;
				}
			}
			else
			{
				// now page, so determine when the end should say
				$current_start = 1;
				if($users["total"] >= $limit)
				{
					$current_end = $limit;
				}
				else
				{
					$current_end = $users["total"];
				}
			}
		$showing = '<p class="showing_count">Showing <strong>'.$current_start.'</strong> - <strong>'.$current_end.'</strong> of <strong>'.$users["total"].'</strong> user results</p>';
			
			$table_html .= '<table class="user_list_table">
			<tr class="top">
				<th class="username_col"><span style="color: #777;">Username</span></th>
			';
			
			// now we sort out the order by function
			if($q_vars == null)
			{
				$table_html .= '
				<th><a href="'.$base.'user/ulist/?order_by=images_count&amp;order_dir=DESC" title="Order by Image uploads">Uploads</a></th>
				<th><a href="'.$base.'user/ulist/?order_by=following_count&amp;order_dir=DESC" title="Order by Following">Following</a></th>
				<th><a href="'.$base.'user/ulist/?order_by=followed_count&amp;order_dir=DESC" title="Order by Image uploads">Followers</a></th>
				';
			}
			else
			{
				
				// first we do if for if there are q_vars by nothing to do with order_by
				if(!isset($q_vars->order_by))
				{
					// reset all filters an page
					$no_list[] = "page";
					$current_query = queryStringDrop(null,$no_list);
					
					// detect the existing query
					if($current_query == "")
					{
						$current_query .= "?";
					}
					else
					{
						$current_query .= "&";
					}
					
					$table_html .= '
					<th><a href="'.$base.'user/ulist/'.$current_query.'order_by=images_count&order_dir=DESC" title="Order by Image uploads">Uploads</a></th>
					<th><a href="'.$base.'user/ulist/'.$current_query.'order_by=following_count&order_dir=DESC" title="Order by Following">Following</a></th>
					<th><a href="'.$base.'user/ulist/'.$current_query.'order_by=followed_count&order_dir=DESC" title="Order by Image uploads">Followers</a></th>
					';
					
					unset($no_list["page"]);
				}
				else
				{
					// now we have to do with each case
					// easiest thing is too go through each and test each one
					
					// also, temp set $no_list to contain order_by and order_dir
					// so we can set these ourselves safely
					$no_list[] = "order_by";
					$no_list[] = "order_dir";
					
					$current_query = queryStringDrop(null,$no_list);
					
					// quick check something is left in the query
					// and append something accordinly
					if($current_query == "")
					{
						$current_query = "?";
					}
					else
					{
						$current_query .= "&";
					}
					
					// set some class vars so we can just throw everything together at the end
					$sort_classes = array("","","");
					$sort_directions = array("DESC","DESC","DESC");
					
					if($q_vars->order_by == "images_count")
					{
						$sort_classes[0] = "sort";
						
						if($q_vars->order_dir == "DESC")
						{
							$sort_directions[0] = "ASC";
						}
						
					}
					else if($q_vars->order_by == "following_count")
					{
						$sort_classes[1] = "sort";
						
						if($q_vars->order_dir == "DESC")
						{
							$sort_directions[1] = "ASC";
						}
					}
					else if($q_vars->order_by == "followed_count")
					{
						$sort_classes[2] = "sort";
						
						if($q_vars->order_dir == "DESC")
						{
							$sort_directions[2] = "ASC";
						}
					}
					
					$table_html .= '
					<th class="'.$sort_classes[0].'"><a href="'.$base.'user/ulist/'.$current_query.'order_by=images_count&order_dir='.$sort_directions[0].'" title="Order by Image uploads">Uploads</a></th>
					<th class="'.$sort_classes[1].'"><a href="'.$base.'user/ulist/'.$current_query.'order_by=following_count&order_dir='.$sort_directions[1].'" title="Order by Following">Following</a></th>
					<th class="'.$sort_classes[2].'"><a href="'.$base.'user/ulist/'.$current_query.'order_by=followed_count&order_dir='.$sort_directions[2].'" title="Order by Followed By">Followers</a></th>
					';
					
					
					// now unset the no_list stuff
					unset($no_list[(count($no_list) - 1)]);
					unset($no_list[(count($no_list) - 1)]);

				}
				
			}
			$table_html .= '
			</tr>
			';
			
			
			for($i = 0; $i < $query_count; $i++)
			{
				if($i%2 == 1)
					$class = "odd";
				else
					$class = "even";
					
				$img = getUserProfileURL($users["result"][$i]->u_profile_id, $users["result"][$i]->u_email);
					
				$table_html .= '<tr class="'.$class.'">
					<th class="username_col"><a href="'.$base.'user/u/'.$users["result"][$i]->u_username.'/" title="View \''.$users["result"][$i]->u_username.'\'s profile"><img src="'.$img.'" alt="user profile image" width="30px" height="30px" />'.$users["result"][$i]->u_username.' <em>  - joined '.date('F j, Y',strtotime($users["result"][$i]->u_date_joined)).'</em></a></th>
					<td class="userlist_images">'.$users["result"][$i]->images_count.'</td>
					<td class="userlist_following"><a href="'.$base.'user/ulist/?followed_by='.$users["result"][$i]->u_username.'" title="See all users followed by '.$users["result"][$i]->u_username.'">'.$users["result"][$i]->following_count.'</a></td>
					<td class="userlist_followers"><a href="'.$base.'user/ulist/?following='.$users["result"][$i]->u_username.'" title="See all users following by '.$users["result"][$i]->u_username.'">'.$users["result"][$i]->followed_count.'</a></td>
				</tr>';
			}
		
			$table_html .= '</table>';
			
			$table_html .= '
			<div class="pagination_buttons">
			';
			
			if($show_prev)
			{
				$table_html .= '<a class="pagination" href="'.$base.'user/ulist/'.queryStringDrop(null,$no_list,-1).'"  title="View previous page of results">< Previous</a>';
			}
			
			if($show_next && $users["total"] > $limit)
			{
				$table_html .= '<a class="pagination"  href="'.$base.'user/ulist/'.queryStringDrop(null,$no_list,1).'"  title="Viewnext page of results">Next ></a>';
			}
			
			$table_html .= '
			</div>
			';
		
		}
		else
		{
			// no results
			$table_html .= '<p class="error">No results found for these parameters</p>';
		}
		
		// determine some extra stuff
		// like appending existing URLs onto the search 
		// query
		if($q_vars != null)
		{
			
			$extra_params = "";
			$search_text = "Search All Users";
			
			if(isset($q_vars->following))
			{
				$extra_params = '<input type="hidden" name="following" value="'.$q_vars->following.'" />'; 
				$search_text = "Search Results";
			}
			else if(isset($q_vars->followed_by))
			{
				$extra_params = '<input type="hidden" name="followed_by" value="'.$q_vars->followed_by.'" />';
				$search_text = "Search Results";
			}
			
		}
		else
		{
			$search_text = "Search All Users";
			$extra_params = "";
		}
		
		$data = array(
			"table_html" => $table_html,
			"search_text" => $search_text,
			"extra_params" => $extra_params,
			"filters_html" => $filters_html,
			"search" => $search,
		);
		
		//now render templates
		
		$this->template->write("title","User Listing");
		
		$this->template->write_view("content","user/user_list", $data, TRUE);
		
	
		$this->template->render();
	}
	
	/*
		Register/signup page
	*/
	function signup()
	{
		if($this->session->userdata("is_logged_in") == 1)
		{
			redirect('','location');
		}
		
		$this->template->add_js("assets/js/formvalidation.js");
		$this->template->write("title","Join Urika");
		
		// random number fun
		$r_a = rand(1,10);
		$r_b = rand(5,25);
		$r_t = $r_a + $r_b;
		
		
		$answer = md5($r_t*($r_t/2));
		
		// csrf for this form, need #a random session carried over the next stage
		$this->load->helper("string");
		$csrf = random_string('alnum',16);
		$this->session->set_userdata("csrf",$csrf);

		$data = array(
			"further_errors" => "",
			"useInvite" => $this->useInvites,
			"r_a" => $r_a,
			"r_b" => $r_b,
			"answer" => $answer,
			"csrf" => $this->session->userdata("csrf")
			);
		
		$this->template->write_view("content","user/signup_form", $data, TRUE);
		
		//now render templates
		$this->template->render();
	}
	
	/**
	* 	Sign up page, even though we've got one in the header, default one, uses same handler
	*/
	function login($redirect = null)
	{
		if($this->session->userdata("is_logged_in") == 1)
		{
			redirect('','location');
		}
		
		$this->template->add_js("assets/js/formvalidation.js");
		$this->template->write("title","Sign In To Urika");
		
		//empty for now
		$data = array("further_errors" => "");
		
		if($redirect != null)
		{
			$data["redirect"] = '/'.str_replace('.','/',$redirect).'/';
		}
		else
		{
			$data["redirect"] = "";
		}
		
		$this->template->write_view("content","user/login_form", $data, TRUE);
		
		//now render templates
		$this->template->render();
	}
	
	
	/**
	*	Function run by sign up form to create user
	*/	
	function create_user()
	{
		if($this->session->userdata("is_logged_in") == 1)
		{
			redirect('','location');
		}
		
		// 1) Form validation
		$this->load->library('form_validation');
		
		// field name, error message, validation rules
		$this->form_validation->set_rules('s_username', 'Userame', 'trim|required|min_length[4]|max_length[32]|alphanumeric');
		$this->form_validation->set_rules('s_email', 'Email Address', 'trim|required|valid_email');
		$this->form_validation->set_rules('s_password_a', 'Password', 'trim|required|min_length[4]|max_length[32]');
		$this->form_validation->set_rules('s_password_b', 'Password Confirmation', 'trim|required|matches[s_password_a]');
		
		// used for additional errors
		$further_errors = array();
		
		// random number fun
		$r_a = rand(1,10);
		$r_b = rand(5,25);
		$r_t = $r_a + $r_b;
		
		$answer = md5($r_t*($r_t/2));
		
		
			$this->load->helper("string");
		
		
		if($this->form_validation->run() == FALSE)
		{
			$this->template->add_js("assets/js/formvalidation.js");
			$this->template->write("title","Join Urika");
			
		
			$csrf = random_string('alnum',16);
			$this->session->set_userdata("csrf",$csrf);
			
			//empty for now
			$data = array(
			"further_errors" => "",
			"useInvite" => $this->useInvites,
			"r_a" => $r_a,
			"r_b" => $r_b,
			"answer" => $answer,
			"csrf" => $this->session->userdata("csrf")
			);
			
			
			
			$this->template->write_view("content","user/signup_form", $data, TRUE);
			
			//now render templates
			$this->template->render();
		}
		else
		{
			// validation works so lets create a user
			
			$this->load->model('user_model');
			
			
			
			if($this->user_model->user_attr_exists('u_username',$this->input->post("s_username")) == TRUE)
			{
				
				$further_errors[] = "This username is already in use, please choose another";
			}
			
			if($this->user_model->user_attr_exists('u_email',$this->input->post("s_email")) == TRUE)
			{
				$further_errors[] = "This email address is already in use";
			}
			
			if($this->user_model->checkBlacklist($this->input->post("s_email")) == TRUE)
			{
				$further_errors[] = "This email has been blacklisted";
			}
			
			if(md5(((int)$this->input->post("s_human"))*(((int) $this->input->post("s_human"))/2)) != $this->input->post("s_humanity"))
			{
				$further_errors[] = "Leave well enough alone spam bot";
			}
			
			if($this->input->post("s_cross") != $this->session->userdata("csrf")){
				$further_errors[] = "Cross site attempt. silly";
			}
			
			// invite check  
			$inviteToDelete = false;
			if($this->useInvites == true)
			{
				$this->load->model("invite_model");
				$inv_row = $this->invite_model->getInvite(trim($this->input->post("s_invite")),"inv_code");
				
				if($inv_row == false)
				{
					$further_errors[] = "The invite code you used does not exist";
				}
				else
				{
					$inviteToDelete = $inv_row->row();
				}
			}
			
			if(count($further_errors) > 0)
			{
				// loop through further errors and output with the rest of errors
				
				$out = "";
				$data = array();
				
				foreach($further_errors as $error)
				{
					$out .= '<p class="error">'.$error.'</p>';
				}
				
				$csrf = random_string('alnum',16);
			$this->session->set_userdata("csrf",$csrf);
					
					$data = array(
						"further_errors" => $out,
						"useInvite" => $this->useInvites,
						"r_a" => $r_a,
						"r_b" => $r_b,
						"answer" => $answer,
						"csrf" => $this->session->userdata("csrf")
						);
						
				
				$this->template->add_js("assets/js/formvalidation.js");
				$this->template->write("title","Join Urika");
			
				$this->template->write_view("content","user/signup_form", $data, TRUE);
			
				//now render templates
				$this->template->render();
			}
			else
			{
				$user_made = $this->user_model->create_new_user();
				
				if($user_made["insert"] == true)
				{
					$this->template->write("title","Registration Successfull");
					
					
					/*
						Delete invite if it exists
					*/
					if($this->useInvites == true && $inviteToDelete != false)
					{
						$this->invite_model->deleteInvite($inviteToDelete->invite_id);
					}
					
					/*
						send verification e-mail
					*/
					$this->load->library("email");
					
					$this->email->from("accounts@urika-app.com","UR!KA");
					$this->email->to($this->input->post("s_email"));
					
					// sort out base url and safe email
					
					$base = base_url();
					$safe_email = str_replace("@",".at-urika.",$this->input->post("s_email"));
					
					
					
					$message = "
Thanks for signing up to UR!KA! 
Your account has been created, you can log in with the following credentials after you have activated your account by pressing the url below.

----------------------
Username: ".$this->input->post("s_username")."		
Password: ".$this->input->post("s_password_a")."	
----------------------

Please click the link below to verify you account		

".$base."user/verify/".$safe_email."/".$user_made["conf_string"]."/

Thanks!
The Team @ UR!KA
					
					";
					
					$this->email->subject("UR!KA - Account Activation");
					$this->email->message($message);
					
					$this->email->send();
					
					//mail($this->input->post("s_email"),"UR!KA - Account Verification",$message,"From: accounts@urika-app.com");
					
					/*
						End email section
					*/
						
					$data = array(
						"user" => $this->input->post("s_username"),
						"email" => $this->input->post("s_email"),
					);
					
					$this->template->write_view("content","user/signup_success", $data, TRUE);
					//now render templates
					$this->template->render();
				}
				else
				{
					
					$this->template->write("title","Registration Unsuccessful");
					
					$data = array(
						"errorTitle" => "Registration Unsuccessfull",
						"content" => "An error has occurred when registering. Please go back and check your information again. If you believe this error is no fault of your own, please get in touch."
					);
		
					$this->template->write("title",$data["errorTitle"]);
					$this->template->write_view("content","general/error", $data, TRUE);
			
					//now render templates
					$this->template->render();
				}
				
			}
		}
	}
	
	/**
	*	Attempt to sign in user!
	*/
	function validate()
	{
		if($this->session->userdata("is_logged_in") == 1)
		{
			redirect('','location');
		}
		
		$rdata = array("redirect" => "");
		
		if($this->input->post("l_redirect") !== FALSE && $this->input->post("l_redirect") != "")
		{
			$rdata["redirect"] = '/'.str_replace('.','/',$this->input->post("l_redirect")).'/';
		}
		
		// 1) Form validation
		$this->load->library('form_validation');
		
		// field name, error message, validation rules
		$this->form_validation->set_rules('l_username', 'Userame', 'required');
		$this->form_validation->set_rules('l_password', 'Password', 'required');
		
		// used for additional errors
		$further_errors = array();
		
		if($this->user_model->user_attr_exists('u_username',$this->input->post("l_username")) == FALSE)
		{
			
			$further_errors[] = "These is no user with the username '".$this->input->post("l_username")."'.";
		}
		
		if($this->input->post("l_check") != "")
		{
			$further_errors[] = "Leave well enough alone spam bot";
		}

		
		if($this->form_validation->run() == FALSE || count($further_errors) > 0)
		{
			// loop through further errors and output with the rest of errors
				
				$out = "";
				
				foreach($further_errors as $error)
				{
					$out .= '<p class="error">'.$error.'</p>';
				}
				$rdata["further_errors"] = $out;
				
				$this->template->add_js("assets/js/formvalidation.js");
				$this->template->write("title","Sign In To Urika");
			
				$this->template->write_view("content","user/login_form", $rdata, TRUE);
			
				//now render templates
				$this->template->render();
		}
		else
		{
			// Log users
			
			$query = $this->user_model->validate_user();

			
			if($query != false) // if the user's credentials validated...
			{	
				if($this->user_model->is_user_verified() == FALSE)
				{
					$base = base_url();
					$verify_url = $base.'user/reverify/'.$this->input->post("l_username").'/';
					$rdata["further_errors"] = "<p class='error'>It seems you haven't verified you account yet. Please check your email inbox for the verification e-mail or <a href='".$verify_url."'>click here to send the e-mail again</a></p>";
					
					
					$this->template->add_js("assets/js/formvalidation.js");
					$this->template->write("title","Sign In To Urika");
				
					$this->template->write_view("content","user/login_form", $rdata, TRUE);
				
					//now render templates
					$this->template->render();
				
				}
				else
				{
				
					$row = $query->row();	
					
					$data = array(
						'username' => $row->u_username,
						'user_id' => $row->user_id,
						'is_logged_in' => true,
						'image_url' => getUserProfileURL($row->u_profile_id,$row->u_email)
					);
					$this->session->set_userdata($data);
					
					
					if($this->input->post("l_redirect") !== FALSE && $this->input->post("l_redirect") != "" && $this->input->post("l_redirect") != "/user/logout")
					{
						redirect($rdata["redirect"], 'location');
					}
					else
					{
						redirect('', 'location');
					}
				}
			}
			else
			{
				$rdata["further_errors"] = "<p class='error'>Username and password do not match. Please try again</p>";
				
				$this->template->add_js("assets/js/formvalidation.js");
				$this->template->write("title","Sign In To Urika");
			
				$this->template->write_view("content","user/login_form", $rdata, TRUE);
			
				//now render templates
				$this->template->render();
			}
		}
		
	}
	
	/**
	* Log out function
	*/
	function logout()
	{	
		loggedInSection(); // urika_helper.php
		
		$data["username"] = $this->session->userdata("username");
		$this->session->unset_userdata("is_logged_in");
		$this->session->destroy();
		
		$this->template->write("title","Logged Out");
			
		$this->template->write_view("content","general/loggedout", $data, TRUE);
	
		//now render templates
		$this->template->render();
	}
	
	/**
	*	Verifies a user based on two parameters in the url. Defaults to null to stop errors 
	*	appearring.
	*	@param email : users email
	*	@param hash : code generated on user registration to verify
	*/
	function verify($user_email = null,$hash = null)
	{
		if($user_email == null || $hash == null)
		{
			redirect('','location');
		}
		else
		{
			// replace .at-urika. in email param with @
			$user_email= str_replace(".at-urika.","@",$user_email);

			// attempt to verify user based on hash
			$result = $this->user_model->verify_user($user_email,$hash);
			
			
			
			if($result == "false")
			{
				// grab user by email 
				$row = $this->user_model->getUser($user_email,"u_email");
				
				if($row)
				{
				
					$row = $row->row();
					$base = base_url();
					
					$verify_url = $base.'user/reverify/'.$row->u_username.'/';
					
					$data = array(
					"errorTitle" => "Verification failed",
					"content" => "Unfortunately it seems you verification link is wrong for the email address <em>".$user_email."</em>. Please <a href='".$verify_url."'><strong>click this link</strong></a> to send it again."
					);
				
					$this->template->write("title",$data["errorTitle"]);
					$this->template->write_view("content","general/error", $data, TRUE);
					
					//now render templates
					$this->template->render();
				}
				else
				{
					redirect('','location');
				}
			}
			else if($result == "already_verified")
			{
				redirect('','location');
			}
			else
			{
				$data = array( "email" => $user_email);
				
				$this->template->write("title","Verification Succesfull");
				$this->template->write_view("content","user/verify_success", $data, TRUE);
				
				//now render templates
				$this->template->render();
				
			}
		}
	}
	
	/*
	*	Resends verification message based on e-mail
	*/
	function reverify($username = null)
	{
		if($username == null)
		{
			redirect('','location');
		}
		else
		{
			if($this->user_model->user_attr_exists('u_username',$username) == TRUE)
			{
				$query = $this->user_model->getUser($username,"u_username");
				
				if($query)
				{
					$row = $query->row();
					
					/*
						send reverification e-mail
					*/
					$this->load->library("email");
					
					$this->email->from("accounts@urika-app.com","UR!KA");
					$this->email->to($row->u_email);
					
					// sort out base url and safe email
					
					$base = base_url();
					$safe_email = str_replace("@",".at-urika.",$row->u_email);
					
					
					$message = "
This email has been sent in order to allow you reverify your account. if you did not request this, please ignore.

Please click the link below to verify you account		

".$base."user/verify/".$safe_email."/".$row->u_confirmation_string."/

Thanks!
The Team @ UR!KA
					
					";
					
					$this->email->subject("UR!KA - Account Verification");
					$this->email->message($message);
					
					$this->email->send();
					
					$data = array( "email" => $row->u_email, "user" => $username);
				
					$this->template->write("title","Verification Succesfull");
					$this->template->write_view("content","user/reverify", $data, TRUE);
				
					//now render templates
					$this->template->render();
					
					
				}
				else
				{
					redirect('','location');
				}
			}
			else
			{
				redirect('','location');
			}
		}
	}
	
	/*
	*	Function to generate new password
	*	Post back function so the same page is used for the form process
	*/
	function fpassword()
	{
		$q_vars = get_url_vars();
	
		if($this->input->post("fp_username") !== FALSE)
		{
		
			// 1) Form validation
			$this->load->library('form_validation');
		
			// field name, error message, validation rules
			$this->form_validation->set_rules('fp_username', 'Username', 'required');
		
			// used for additional errors
			$message = "";
			
			// process post input
			if($this->user_model->user_attr_exists('u_username',$this->input->post("fp_username")) == FALSE)
			{
				$message = '<p class="error">No user exists with this username. Try checking the user list for your own username</p>';
			}
			else
			{
				
				
				/*
					Send the Email
				*/
				
				$query = $this->user_model->getUser($this->input->post("fp_username"),"u_username");
				
			
				$row = $query->row();
				
				$this->load->library("email");
					
				$this->email->from("accounts@urika-app.com","UR!KA");
				$this->email->to($row->u_email);
				
				// sort out base url and safe email
				
				$base = base_url();
				$safe_email = str_replace("@",".at-urika.",$row->u_email);
				
				$link = $base.'user/fpassword/?userid='.$row->user_id.'&newpassverify='.strrev($row->u_confirmation_string);
				
				
				$message = "
This email has been sent because someone requested a password reset for your account. If this was your action,
please click the link below to reset your password. Otherwise, please delete this e-mail.

".$link."

Thanks!
The Team @ UR!KA
				
				";
				
				$this->email->subject("UR!KA - New Password Verificaton");
				$this->email->message($message);
					
				$this->email->send();

				/*
					End Email sending
				*/
				
				$message = '<p class="success">A verification e-mail has been to sent to <strong>'.$this->input->post("fp_username").'</strong>\'s e-mail address. Please click the link in the e-mail to reset your password.</p>';
			
			}
			
			
			$data["message"] = $message;
			
			$this->template->write("title","Forgotten Password");
			$this->template->write_view("content","user/fpassword_form", $data, TRUE);
		
			//now render templates
			$this->template->render();
		}
		else if(isset($q_vars->userid) && isset($q_vars->newpassverify))
		{
			// check if this user exists and the verification matches
			
			$send_new = false;
			$message = "";
			
			$user = $this->user_model->getUser($q_vars->userid,"user_id");
			
			if($user == false)
			{
				$message = '<p class="error">User not found, no password reset</p>';
				$send_new = false;
			}
			else
			{
				$user = $user->row();
				
				// if we've got a match, reset
				if(trim(strrev($q_vars->newpassverify)) == $user->u_confirmation_string)
				{
					$send_new = true;
				}
			
				if($send_new == true)
				{
					//genereate a new password and update database
					$new_pass = random_string('alnum',8);
					
					$new_pass_data = array(
						"u_password" => md5($new_pass)
					);
					


					
					/*
						Send the new password
					*/
					
					$query = $this->user_model->getUser($q_vars->userid,"user_id");
					
				
					$row = $query->row();
					
					$this->load->library("email");
						
					$this->email->from("accounts@urika-app.com","UR!KA");
					$this->email->to($row->u_email);
					
					// sort out base url and safe email
					
					$base = base_url();
					$safe_email = str_replace("@",".at-urika.",$row->u_email);
					
					
					$message = "
	Thanks for verifying your identity in order to reset your password

	Your new password is ".$new_pass.".

	Thanks!
	The Team @ UR!KA
					
					";
					
					$this->email->subject("UR!KA - New Password");
					$this->email->message($message);
						
					$this->email->send();
					
					/*
						End Email sending
					*/
					
					$update = $this->user_model->updateUser($new_pass_data,$q_vars->userid,"user_id");
					
					if($update)
					{
						$message = '<p class="success">A new password has been to sent to <strong>'.$row->u_username.'</strong>\'s e-mail address. Please check your e-mails. You can change your password once you have logged in.</p>';
					}
					else
					{
						$message = '<p class="error"> Unfortunately something went wrong. Please try again</p>';
					}
				
				}
			}
			
			
			$data["message"] = $message;
			
			$this->template->write("title","Forgotten Password");
			$this->template->write_view("content","user/fpassword_form", $data, TRUE);
		
			//now render templates
			$this->template->render();
		}
		else
		{
			// display form
			$data = array("message" => "");
			
			$this->template->add_js("assets/js/formvalidation.js");
		
			$this->template->write("title","Forgotten Password");
			$this->template->write_view("content","user/fpassword_form", $data, TRUE);
		
			//now render templates
			$this->template->render();
		}
	}
	
//////////// Account Functions

	/**
		Main account page
	**/
	function account()
	{
		loggedInSection(true, 'user.account');
		
		$query = $this->user_model->getUser($this->session->userdata('user_id'));
		
		if($query != false)
		{
			$row = $query->row();
			
			$this->template->write("title","Manage Your Account");
			
			$upload_text = "Use Upload";
			$upload_class = "";
			
			$gravatar_text = "Use Gravatar";
			$gravatar_class = "";
			
			if(strpos($row->u_profile_id,"usegravatar::") !== FALSE) // useing gravatar
			{
				$gravatar_text = "Using Gravatar";
				$gravatar_class = "avatar_choice";
			}
			else if(strpos($row->u_profile_id,"http://") !== FALSE) // a url exists so its an upload
			{
				$upload_text = "Using Upload";
				$upload_class = "avatar_choice";
			}
			
			$avatar_url = "";
			
			if(strpos($row->u_profile_id,"http://") !== FALSE)
			{
				$avatar_url = str_replace("usegravatar::","",$row->u_profile_id);
			}
			
			/*
				Grab stats
			*/
			$stats = $this->user_model->getUserStats($row->user_id);
			
			/*
				grab invites if required
			*/
			$invites = false;
			if($this->useInvites == true)
			{
				$userinvites = $this->user_model->getUserInvites($row->user_id);
				
				$invites["count"] = $userinvites["count"];
				$invites["html"] = '';
				
				if($invites["count"] > 0){
					foreach($userinvites["result"] as $i => $inv)
					{
						$invites["html"] .= '
							<p class="borderbox">
								Invite '.($i+1).': <strong style="color: #930;">'.$inv->inv_code.'</strong>
							</p>
						';
					}
				}else{
					$invites["html"] = '
						<p class="error">You currently have no invites</p>
					';
				}
			}
		
			
			$data = array (
				'username' => $row->u_username,
				'apikey' => $row->u_authkey,
				'image_url' => getUserProfileURL($row->u_profile_id,$row->u_email), 
				'profilevalues' => array(
					'gravatar' => "http://www.gravatar.com/avatar/".md5($row->u_email)."?s=120",
					'firstname' => $row->u_firstname,
					'surname' => $row->u_surname,
					'website' => $row->u_website,
					'twitter' => $row->u_twitter,
					'location' => $row->u_location,
					'notice_format' =>  explode('#',$row->u_notice_format),
					'upload_text' => $upload_text,
					'upload_class' => $upload_class,
					'gravatar_text' => $gravatar_text,
					'gravatar_class' => $gravatar_class,
					'upload_url' => $avatar_url
				),
				'stats' => $stats,
				'invites' => $invites
				
			);

			
			$this->template->add_js("assets/js/tabs.js");
			$this->template->add_js("assets/js/libs/fileuploader.js");
			$this->template->add_js("assets/js/account.js");
			
			$this->template->add_css("assets/css/fileuploader.css");
			
			$this->template->write_view("content","user/account_form", $data, TRUE);
	
			//now render templates
			$this->template->render();
		}
		
	}
	
	/*
		Notices page, lists the last 30 notices for the user
		providing ability to delete etc
	*/
	function notices()
	{
		loggedInSection();
		
		$this->load->model("notice_model");
		
		$notices = $this->notice_model->getUserNotices($this->session->userdata("user_id"));
		
		$user = $this->user_model->getUser($this->session->userdata("user_id"))->row();
	
		$show_buttons = false;
	
		
		if($notices == false)
		{
			$content = '<div class="borderbox">You have no notices yet. Get into the community and get noticed!</div>';
		}
		else
		{
			$show_buttons = true;
			$content = '<ul class="notice_list">';
			$ids_list = '';
			// loop through notices
			foreach($notices as $notice)
			{
				$class = 'notice_'.$notice->n_type;
				$read_link_text = 'Mark as Unread';
				$ids_list .= $notice->notice_id.'+';
				
				if($notice->n_new == 1)
				{
					$class .= ' notice_new';
					$read_link_text = 'Mark as Read';
				}
				
				$controls = '<a href="#" class="toggle_read_link" onclick="toggleNoticeRead('.$notice->notice_id.');" title="toggle mark as read">'.$read_link_text.'</a> | <a href="#" onclick="deleteNotice('.$notice->notice_id.')" title="delete this notice">Delete Notice</a>';
				
				$content .= '<li id="notice_'.$notice->notice_id.'" class="'.$class.'">'.$notice->n_html.' <p class="notice_controls">'.$controls.'</p></li>';
			}
			$content .= '</ul><input type="hidden" id="notice_ids_list" name="notice_ids_list" value="'.substr($ids_list,0,-1).'" />';
		}
		
	
		
		$data = array (
			"username" => $user->u_username,
			"image_url" => getUserProfileURL($user->u_profile_id,$user->u_email),
			"notices_html" => $content,
			"show_buttons" => $show_buttons
		);
		
		$this->template->write("title","Your notices");
		
		$this->template->add_js("assets/js/account.js");
		$this->template->add_js("assets/js/ajax.js");
		
		$this->template->write_view("content","user/notices",$data);

		//now render templates
		$this->template->render();
		
		
	}
	
//////////// AJAX Only FUNCTIONS
///// to do: fallback options

	/** 
		Add a follows record to the database
	**/
	function addfollow()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			$subject_id = $_POST["subject"];
			$base_url = base_url();
		
			
			$insert = $this->user_model->addFollow($user,$subject_id);
			
			if($insert != FALSE)
			{
				$rtn_arr = array(
					"msg" => "true",
					"newunfollow" => '<a id="profile_unfollow" onclick="deleteFollow('.$subject_id.','.$user.');" href="#" title="Unfollow this user"><span class="hide">Unfollow this user</span></a>',
					"newfollowli" => '<li class="sul_'.$this->session->userdata("user_id").'"> <a href="'.$base_url.'user/u/'.$this->session->userdata("username").'/" title="View this users profile"> <img src="'.$this->session->userdata("image_url").'" width="30" height="30" alt="User profile image" /> <span>'.$this->session->userdata("username").'</span> </a>'
				);
				/*
					Start Notice insert
				*/
				// grab user
				
				$notice_insert = array(
					"n_object_user_id" => $subject_id,
					"n_object_id" => 0,
					"n_object_type" => "follow",
					"n_action_user_id" => $user,
					"n_type" => "follow",
					"n_new" => 1,
					"n_html" => ""
				);
				
				$base = base_url();
				$date =  date("F j, Y, G:i");
				// now html
				$notice_html = '<span class="notice_date">'.$date.'</span> - 
						<a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="'.$this->session->userdata("username").'\'s profile">'.$this->session->userdata("username").'</a> is now following you ';
				
				$notice_insert["n_html"] = $notice_html;
				
				$this->load->model("notice_model");
				$this->notice_model->createNewNotice($notice_insert);
				/*
					End Notice Insert
				*/
			}
			else
			{
				$rtn_arr = array("msg" => "false");
			}
			
			echo json_encode($rtn_arr);
			
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
		Removes a follow record from the database
	**/
	function deletefollow()
	{
		loggedInSection(); // urika_helper.php
		
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			$subject_id = $_POST["subject"];
			
			$delete = $this->user_model->deleteFollow($user,$subject_id);
			
			if($delete != FALSE)
			{
				$rtn_arr = array(
					"msg" => "true",
					"newfollow" => '<a id="profile_follow" onclick="addFollow('.$subject_id.');" href="#" title="refollow this user"><span class="hide">refollow this user</span></a>',
					"delete_id" => $user
				);
			}
			else
			{
				$rtn_arr = array("msg" => "false");
			}
			
			echo json_encode($rtn_arr);
			
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
	
	/* Account Page Functions *////
	
	/**
		Save Avatar
	**/
	function saveAvatar()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user_id = $this->session->userdata("user_id");
			
			// grab current user
			$user_details = $this->user_model->getUser($user_id)->row();
			$current_value = $user_details->u_profile_id;
			
			$update_required = false;
			$param = $_POST["param"];
			$new_value = "";
			
			if($param == "use_gravatar")
			{
				// if they want to use gravatar
				
				if(strpos($current_value,"usegravatar::") === FALSE)
				{
					// not already using the gravatar so we need to prepend to the existing value
					$new_value = "usegravatar::".$current_value;
					$update_required = true;
				}
			}
			else if(strpos($param,"http://") !== FALSE  )
			{
				$new_value = $param;
				$update_required = true;
			}
			
			
			if($update_required == true)
			{
				$details_data = array(
					"u_profile_id" => $new_value
				);
				
				$update = $this->user_model->updateUser($details_data,$user_id,"user_id");
				
				if($update)
					$rtn_html = "true";
				else
					$rtn_html = "false";
					
				echo $rtn_html;
			}
			else
			{
				echo "donothing";
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
		Save Avatar
	**/
	function deleteAvatar()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user_id = $this->session->userdata("user_id");
			
			// grab current user
			$user_details = $this->user_model->getUser($user_id)->row();
			$current_value = $user_details->u_profile_id;

			$url = $this->input->xss_clean($_POST["img_url"]);
			$new_value = "";
			
			// check urls match
			
			
			$check_value = str_replace("usegravatar::","",$current_value);
			
			if($url == $check_value && $url != "")
			{
				// match so delete and update
				if(strpos($current_value,"usegravatar::") === FALSE)
				{
					// nothing set so remove avatar
					$new_value = "";
				}
				else
				{
					// avatar set so use this
					$new_value = "usegravatar::";
				}
				
				// delete path
				$delete_path = str_replace(UPLOAD_URL,UPLOAD_FINAL_PATH,$url);
				
				unlink($delete_path);
				
				$details_data = array(
					"u_profile_id" => $new_value
				);
				
				$update = $this->user_model->updateUser($details_data,$user_id,"user_id");
				
				if($update)
					$rtn_html = "true";
				else
					$rtn_html = "false";
					
				echo $rtn_html;
				
				
			}
			else
			{
				// no match so return false
				echo "nothingtodelete";
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
	
	**/
	
	
	/**
		Saves user details
	**/
	function saveUserDetails()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			
			$details_data = array(
				"u_firstname" => $this->input->xss_clean(trim($_POST["firstname"])),
				"u_surname" => $this->input->xss_clean(trim($_POST["surname"])),
				"u_twitter" => $this->input->xss_clean(trim($_POST["twitter"])),
				"u_website" => $this->input->xss_clean(trim($_POST["website"])),
				"u_location" => $this->input->xss_clean(trim($_POST["location"]))
			);
			
			$update = $this->user_model->updateUser($details_data,$user,"user_id");
			
			if($update)
				$rtn_html = "true";
			else
				$rtn_html = "false";
				
			echo $rtn_html;
			
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
		Saves user avatar
	**/
	function saveUserAvatar()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			
			$details_data = array(
				"u_profile_id" => trim($_POST["profile"]),
			);
			
			$update = $this->user_model->updateUser($details_data,$user,"user_id");
			
			if($update)
				$rtn_html = "true";
			else
				$rtn_html = "false";
				
			echo $rtn_html;
			
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
		Saves user password
	**/
	function saveUserPassword()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			
			$current_pass = trim($_POST["cpass"]);
			$new_pass = md5(trim($_POST["npass"]));
			
			$query = $this->user_model->getUser($user,"user_id");
			$row = $query->row();
			
			$rtn_html = "";
			
			if(md5($current_pass) != $row->u_password)
			{
				$rtn_html = "wrongpass";
			}
			else
			{
			
				$pass_data = array(
					"u_password" => $new_pass
				);
				
				$update = $this->user_model->updateUser($pass_data,$user,"user_id");
				
				if($update)
					$rtn_html = "true";
				else
					$rtn_html = "false";
				
			}
				
			echo $rtn_html;
			
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
		Checks current user email matches then updates and sends
		a test email
	*/
	function saveUserEmail()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			
			$query = $this->user_model->getUser($user,"user_id");
			$row = $query->row();
			
			$rtn_html = "";
			
			$current_email = trim($_POST["current_email"]);
			
			if($row->u_email != $current_email)
			{
				$rtn_html = "wrongemail";
			}
			else if($this->user_model->user_attr_exists('u_email',$_POST["new_email"]) == TRUE)
			{
				$rtn_html = "newemailexists";
			}
			else
			{
			
				
				$new_email = trim($_POST["new_email"]);
				$email_data = array(
					"u_email" => $new_email,
				);
				
				$update = $this->user_model->updateUser($email_data,$user,"user_id");
				
				if($update)
				{
					$rtn_html = "true";
				
					$this->load->library("email");
					
					$this->email->from("accounts@urika-app.com","UR!KA");
					$this->email->to($new_email);
					
					// sort out base url and safe email
					
							
					
					$message = "
	This email has been sent because you have changed your accounts e-mail. This is just a test to ensure you have received the e-mail

	Thanks!
	The Team @ UR!KA
					
					";
					
					$this->email->subject("UR!KA - New E-mail");
					$this->email->message($message);
						
					$this->email->send();
				}
				else
				{
					$rtn_html = "false";
				}
				
			
			}
			
			echo $rtn_html;
			
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
		Saves user Notices
	**/
	function saveUserNotices()
	{
		loggedInSection(); // urika_helper.php
		
		if(isAjaxRequest())
		{
			$user = $this->session->userdata("user_id");
			
			$notices_data = array(
				"u_notice_format" => trim(str_replace('.','#',$_POST["notice_format"]))
			);
			
			$update = $this->user_model->updateUser($notices_data,$user,"user_id");
			
			if($update)
				$rtn_html = "true";
			else
				$rtn_html = "false";
				
			echo $rtn_html;
			
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
	
	/// notice functions, included here for 
	/// simplicities sake, rather than creating a whole new controller
	/// since its only used here
	
	/**
		Toggle unread and read for a specified notice, chosen with
		a notice id
	**/
	function toggleNoticeRead()
	{
		loggedInSection();
		
		if(isAJAXRequest())
		{
			$this->load->model("notice_model");
			
			$notice = $this->notice_model->getNotice($_POST["notice_id"])->row();
		
			$update_array = array("n_new" => 0);
			
			if($notice->n_new == 0)
			{
				$update_array["n_new"] = 1;
			}
			
			$update = $this->notice_model->updateNotice($update_array,$_POST["notice_id"]);
			
			
			if($update == true)
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
		Multiple toggle read update
	
	**/
	function multipleToggleRead()
	{
		loggedInSection();
		
		if(isAJAXRequest())
		{
			if(isset($_POST["ids_list"]) && isset($_POST["value"]) && !empty($_POST))
			{
				$ids_array = explode(" ",$_POST["ids_list"]);
				
				//db stuff
				
				if($_POST["value"] == 1 || $_POST["value"] == 0)
				{
					$this->load->model("notice_model");
					$update = $this->notice_model->toggleMultipleRead($ids_array,$_POST["value"]);
					
					if($update == true)
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
		Deletes a noticed by notice id, as long as the notice belongs to the 
	**/
	function deleteUserNotice()
	{
		loggedInSection();
		
		if(isAJAXRequest())
		{
			$this->load->model("notice_model");
			$notice = $this->notice_model->getNotice($_POST["notice_id"])->row();
			
			// check if this notice belongs the user requesting it
			if($notice->n_object_user_id == $this->session->userdata("user_id"))
			{
				// now delete
				$delete = $this->notice_model->deleteNotice($_POST["notice_id"]);
				
				if($delete == true)
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
			$data = array(
			"errorTitle" => "Request Denied",
			"content" => "An error has occurred: you cannot access this page from the browser."
			);
			
			
			$this->template->write_view("content","general/error", $data, TRUE);
				
			//now render templates
			$this->template->render();
		}
	}
	
//////////// PRIVATE FUNCTIONS

	
	/**
	*	Function which grabs user upload records from the database
	*
	*	@param type: 'images', 'mb' or 'both', tells use which uploads to grab
	*	@param user_id: the user's id 
	*/
	function _getUserUploads($user_id,$type = 'images')
	{
		$result = array();
		
		if($type == 'images' || $type == 'both')
		{
			$res = $this->user_model->getUserImages($user_id);

			$results["images"] = $res;
		}
		
		if($type == 'mb' || $type == 'both')
		{
			$res = $this->user_model->getUserMoodboards($user_id);

			$results["mbs"] = $res;
		}
		
		return $results;
		
	}
	
	
	/**
	*	Function which grabs user follow records from the database
	*	@param type: 'follows', 'followedBy' or 'both', tells us type of records to grab
	*	@param $user_id : users id
	*/
	function _getUserFollows($user_id,$type = "both")
	{
		$result = array();
		
		if($type == 'follows' || $type == 'both')
		{
			$res = $this->user_model->getUserFollows($user_id,null,true);
			$total = $this->user_model->getUserFollows($user_id);

			$results["follows"]["count"] = $total["count"];
			$results["follows"]["result"] = $res["result"];
		}
		
		if($type == 'followedby' || $type == 'both')
		{
			$res = $this->user_model->getUserFollowedBy($user_id,null,true);
			$total = $this->user_model->getUserFollowedBy($user_id);

			$results["followedby"]["count"] = $total["count"];
			$results["followedby"]["result"] = $res["result"];
		}
		
		return $results;
	}


}

/* End of file user.php */
/* Location: ./system/application/controllers/user.php */