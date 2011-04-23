<?php
/*
	Public API controller, handles all api requests
	request format:
	
	e.g. urika-app.com/api/methodname/param/param/
	
	Format is always JSON for now, but I've built the class
	keeping in mind we might use XML etc later
	
	ALSO handles extension calls e.g. UR!KA Chrome Extension
	
*/
class api extends Controller {

	/*
		Allowed format types
	*/
	private $formats = array(
		"JSON",
		"XML"
	);

	function api()
	{
		parent::Controller();	
		$this->load->model("api_model");
	}
	
	function index()
	{
		
		// nothing to do here
		
		echo json_encode(array("error" => "this page is inaccessible through the API. Please use a correct method such as /user."));
	}
	
	/*
		Since we want restful services eventally we should build it in such a way,
		so we have URL function which then call the correct function
		
		This handle all user requests
	
		@param id : id of the user in question
		@param component : if they want something specific
		@param page : page number for pagination
	*/
	function user($id = null,$component = null,$page = 1)
	{
		$json = "";
		$this->load->model("user_model");
		
		if($id == null || $id == "")
		{
				$json = json_encode(array("error" => "No valid user id specified"));
		}
		else
		{
			if($component == null || $component == "")
			{
				// user then
				$json = $this->_userInfo($id);
			}
			else
			{
				switch($component)
				{
					case "uploads":
						$json = $this->_userUploads($id,$page);
					break;
					
					case "moodboards":
						$json = $this->_userMoodboards($id,$page);
					break;
					
					case "favourites":
						$json = $this->_userFavourites($id,$page);
					break;
					
					case "followers":
						$json = $this->_userFollowers($id,$page);
					break;
					
					case "following":
						$json = $this->_userFollows($id,$page);
					break;
					
					default:
						$json = json_encode(array("error" => "'".$component."' is not a valid request."));
					break;
				}
			}
		}
		
		$insert_data = array(
			"r_source" => (isset($_SERVER["HTTP_REFERER"])) ? $_SERVER["HTTP_REFERER"] : "none",
			"r_request_info" => json_encode(array("user_id" => $id,"component" => $component, "page" => $page)),
			"r_data" => $json,
			"r_success" => 0
		);
		
		echo $json;
	}
	
	/*
		Function returns user info
	
		@param user_id : id of the user to look for
	*/
	function _userInfo($user_id)
	{
		if(is_numeric($user_id))
		{
						
			$user = $this->user_model->getUser($user_id);
			
			if($user == false)
			{
				return json_encode(array("error" => "No user with that id"));
			}
			else
			{
				$user = $user->row();
				return json_encode($this->_parseUserRow($user));
			}
		}
		else if($user_id != "")
		{
			$user = $this->user_model->getUser($user_id,"u_username");
			
			if($user == false)
			{
				return json_encode(array("error" => "No user with that username"));
			}
			else
			{
				$user = $user->row();
				return json_encode($this->_parseUserRow($user));
			}
		}
		else
		{
			return json_encode(array("error" => "Invalid user id supplied"));
		}
	}
	
	/*
		Return user info a list of latest uploads,
		returns 20 uploads, JSON format
		
		@param user_id : id of user to 
		@param page : which page to return
	*/
	function _userUploads($user_id,$page = 1)
	{
		$result = array();
		
		// right lets process the info
		if(is_numeric($page))
		{	
			if(is_numeric($user_id))
			{
				$user = $this->user_model->getUser($user_id);
			}
			else
			{
				$user = $this->user_model->getUser($user_id,"u_username");
			}
			
			if($user == false)
			{
				$result["error"] = "No user found with that username or id";
			}
			else
			{
				$user = $user->row();
				$images = $this->user_model->getUserImages($user->user_id,(($page - 1) * 20));
			
				
				// process user things
				$result["user"] = $this->_parseUserRow($user);
				// now do images
				
				if($images["count"] > 0)
				{
					foreach($images["result"] as $image)
					{
						$result["uploads"][] = $this->_parseImageRow($image);
					}
				}
			}
		}
		else
		{
			$result["error"] = "Invalid user id or page format supplied";
		}
		
		$result = json_encode($result);
		
		return $result;
		
		
	}
	
	/*
		Return user info a list of latest moodboards,
		returns 20 moodboards, JSON format
		
		@param user_id : id of user to 
		@param page : which page to return
	*/
	function _userMoodboards($user_id,$page = 1)
	{
		$result = array();
		
		// right lets process the info
		if(is_numeric($page))
		{
			
			if(is_numeric($user_id))
			{
				$user = $this->user_model->getUser($user_id);
			}
			else
			{
				$user = $this->user_model->getUser($user_id,"u_username");
			}
			
			if($user == false)
			{
				$result["error"] = "No user found with that id";
			}
			else
			{
				$user = $user->row();
				$mbs = $this->user_model->getUserMoodboards($user->user_id,(($page - 1) * 20));
			
				
				// process user things
				$result["user"] = $this->_parseUserRow($user);
				// now do images
				
				if($mbs["count"] > 0)
				{
					foreach($mbs["result"] as $mb)
					{
						$result["moodboards"][] = $this->_parseMoodboardRow($mb);
					}
				}
			}
		}
		else
		{
			$result["error"] = "Invalid user id or page format supplied";
		}
		
		$result = json_encode($result);
		
		return $result;
	}
	
	/*
		Return user info a list of latest favourtes,
		returns 20 uploads, JSON format
		
		@param user_id : id of user to 
		@param page : which page to return
	*/
	function _userFavourites($user_id,$page = 1)
	{
		$result = array();
		
		// right lets process the info
		if(is_numeric($page))
		{
			
			if(is_numeric($user_id))
			{
				$user = $this->user_model->getUser($user_id);
			}
			else
			{
				$user = $this->user_model->getUser($user_id,"u_username");
			}
			
			if($user == false)
			{
				$result["error"] = "No user found with that id";
			}
			else
			{
				$user = $user->row();
				$favs = $this->user_model->getUserFavs($user->user_id,(($page - 1) * 20));
			
				
				// process user things
				$result["user"] = $this->_parseUserRow($user);
				// now do images
				
				if($favs["count"] > 0)
				{
					foreach($favs["result"] as $fav)
					{
						$result["favourites"][] = $this->_parseImageRow($fav);
					}
				}
			}
		}
		else
		{
			$result["error"] = "Invalid user id or page format supplied";
		}
		
		$result = json_encode($result);
		
		return $result;
	}
	
	/*
		Return user info a list of latest follows,
		returns 20 uploads, JSON format
		
		@param user_id : id of user to 
		@param page : which page to return
	*/
	function _userFollows($user_id,$page = 1)
	{
		$result = array();
		
		// right lets process the info
		if(is_numeric($page))
		{
			
			if(is_numeric($user_id))
			{
				$user = $this->user_model->getUser($user_id);
			}
			else
			{
				$user = $this->user_model->getUser($user_id,"u_username");
			}
			
			if($user == false)
			{
				$result["error"] = "No user found with that id";
			}
			else
			{
				$user = $user->row();
				$follows = $this->user_model->getUserFollows($user->user_id,(($page - 1) * 20));
			
				
				// process user things
				$result["user"] = $this->_parseUserRow($user);
				// now do images
				
				if($follows["count"] > 0)
				{
					foreach($follows["result"] as $fol_user)
					{
						$result["follows"][] = $this->_parseUserRow($fol_user);
					}
				}
			}
		}
		else
		{
			$result["error"] = "Invalid user id or page format supplied";
		}
		
		$result = json_encode($result);
		
		return $result;
	}
	
	/*
		Return user info a list of latest followers,
		returns 20 uploads, JSON format
		
		@param user_id : id of user to 
		@param page : which page to return
	*/
	function _userFollowers($user_id,$page = 1)
	{
		$result = array();
		
		// right lets process the info
		if(is_numeric($page))
		{
			
			if(is_numeric($user_id))
			{
				$user = $this->user_model->getUser($user_id);
			}
			else
			{
				$user = $this->user_model->getUser($user_id,"u_username");
			}
			
			if($user == false)
			{
				$result["error"] = "No user found with that id";
			}
			else
			{
				$user = $user->row();
				$followers = $this->user_model->getUserFollowedBy($user->user_id,(($page - 1) * 20));
			
				
				// process user things
				$result["user"] = $this->_parseUserRow($user);
				// now do images
				
				if($followers["count"] > 0)
				{
					foreach($followers["result"] as $fol_user)
					{
						$result["followers"][] = $this->_parseUserRow($fol_user);
					}
				}
			}
		}
		else
		{
			$result["error"] = "Invalid user id or page format supplied";
		}
		
		$result = json_encode($result);
		
		return $result;
	}
	
//////////// PARSING FUNCTIONs
//////////// Since we dont want all the record data fed back, we parse what needs to b sent back here

	/*
		Take a user row as an argument and returns an array of info
		
		@param user : (object) user row object
	*/
	function _parseUserRow($user)
	{
		$base = base_url();
		$result = array(
			"user_id" => $user->user_id,
			"username" => $user->u_username,
			"profile_link" => $base.'user/u/'.$user->u_username.'/',
			"avatar_src" => getUserProfileURL($user->u_profile_id,$user->u_email),
			"upload_count" => (isset($user->u_image_count)) ? $user->u_image_count : "",
			"moodboard_count" => (isset($user->u_mb_count)) ? $user->u_mb_count : "",
			"favourite_count" => (isset($user->u_favs)) ? $user->u_favs : ""
		);
		
		return $result;
	}
	
	/*
		Take a image row as an argument and returns an array of info
		
		@param image : (object) image row object
	*/
	function _parseImageRow($image)
	{
		$base = base_url();
		$result = array(
			"id" => $image->image_id,
			"user_id" => $image->i_user_id,
			"title" => $image->i_title,
			"upload_link" => $base.'image/view/'.$image->image_id.'/',
			"description" => $image->i_description,
			"thumb_url" => $image->i_thumb_url,
			"full_url" => $image->i_full_url,
			"website" => $image->i_website,
			"tags" => $image->i_tags,
			"views" => $image->i_views,
			"comments" => (isset($image->i_comments)) ? $image->i_comments : "",
			"favourites" => (isset($image->i_favs)) ? $image->i_comments : "",
			"size" => $image->i_size,
		);
				
		
		return $result;
	
	}
	
	/*
		Takes a moodboard row and returns a safer array of info
		
		@param mb : (object) moodboard row object
	*/
	function _parseMoodboardRow($mb)
	{
		$base = base_url();
		
		$result = array(
			"id" => $mb->moodboard_id,
			"user_id" => $mb->m_user_id,
			"title" => $mb->m_title,
			"upload_link" => $base.'moodboard/view/'.$mb->moodboard_id.'/',
			"description" => $mb->m_description,
			"thumb_url" => $mb->m_thumb_url,
			"full_url" => $mb->m_full_url,
			"tags" => $mb->m_tags,
			"views" => $mb->m_views,
			"comments" => $mb->m_comments,
			"favourites" => $mb->m_favs,
		);
		
		return $result;
	}
	
//////////////// EXTENSION FEATURES

	/*
		Handles image uploads from browser extensions
		
		Takes post params from an AJAX request
	*/
	
	function extensionupload()
	{
	
		
		//if(!isAJAXRequest(false)) // false so this isnt an internal check
		if(empty($_POST) && !isset($_POST["urika_username"]))
		{
			echo json_encode(array("error" => "HTTP Request format invalid. This page cannot be accessed from the browser"));
		}
		else
		{
			// process
			$params = $_POST;
			$out = "";
			$auth = false;
			
			
			$this->load->model("image_model");
			$this->load->model("api_model");
			
			// check the upload came from an allowed browser type
			
			$browsers = array(
				"chrome"
				//"safari",
				//"opera",
				//"firefox",
				//"ie"
			);
			
			if(isset($params["extension_browser"]) && in_array($params["extension_browser"],$browsers))
			{
				// 1) user verification checkdata
				
				$this->load->model("user_model");
				$user = $this->user_model->getUser($params["urika_username"],"u_username");
				
				if($user == false)
				{
					$out = json_encode(array("error" => "Your username was not found"));
				}
				else
				{
					$user = $user->row();
					
					if($user->u_authkey == $params["urika_api_key"])
					{
						$auth = true;
					}
					else
					{	
						$auth = false;
						$out = json_encode(array("error" => "The API key supplied does not match your username. Please check that it is correct."));
					}
					
					// signed in check
					
					if(isLoggedIn() && $this->session->userdata("user_id") == $user->user_id)
					{
						$auth = true;
					}
					else
					{	
						$auth = false;
						$out = json_encode(array("error" => "You must be logged in to UR!KA to use the extension. Simple open a new tab and login and the extension should work fine :)"));
					}
				}
				
				if($auth == true)
				{
					// 2) conditionals for each browser
					

					if($params["extension_browser"] == "chrome")
					{
						// construct data to insert into database (image and request)
						$image_insert_array = array(
							"i_user_id" => $user->user_id,
							"i_title" => trim($params["urika_crop_title"]),
							"i_description" => htmlentities(trim(nl2br(urldecode($params["urika_crop_desc"])))),
							"i_website" => $params["urika_crop_location"],
							"i_tags" => $params["urika_crop_tags"],
							"i_type" => "image",
							"i_views" => 0,
							"i_size" => $params["urika_crop_width"].'X'.$params["urika_crop_height"]
						);
						
						$request_insert_array = array(
							"r_user_id" => $user->user_id,
							"r_auth_used" => $params["urika_api_key"],
							"r_source" => 'chrome##'.$params["urika_crop_location"],
							"r_request_info" => json_encode($params)
						);
						
						// convert image data into resource or attempt to make file
						
						$upload_file_name = $user->u_username.'_'.time().'_upload.jpg';
						$upload_file_path = UPLOAD_FINAL_PATH.$upload_file_name;
						
						$imgData = str_replace(" ", "+", $params["urika_crop_data"]);
						$imgData = substr($imgData, strpos($imgData, ","));	
						
						$file = fopen($upload_file_path, 'wb');
						$file_success = fwrite($file, base64_decode($imgData));
						fclose($file);
						
						if($file_success === false)
						{
							$out = json_encode(array("error" => "The file upload didn't work unfortunately, please try again later"));
						}
						else
						{
							$base_url = base_url();
							
							// if all is cool, make thumb
							$thumbname = "thumb_".$upload_file_name;
							$thumb_img = ImageCreateTrueColor(160,160);

							$full_img = imagecreatefromjpeg($upload_file_path);
							
							// want to find the middle so we need to calculate offsets
							$offx = ($params["urika_crop_width"]/2)-	80;
							$offy = ($params["urika_crop_height"]/2)-	80;
							
							imagecopyresampled($thumb_img,$full_img,0,0,$offx,$offy,
							160,160,160,160);
							imagejpeg($thumb_img,UPLOAD_FINAL_PATH.$thumbname,150);
							
							
							// add these to the original insert
							$image_insert_array["i_full_url"] = $base_url.'uploads/images/'.$upload_file_name;
							$image_insert_array["i_thumb_url"] = $base_url.'uploads/images/'.$thumbname;
							
							$query = $this->image_model->createNewImage($image_insert_array);
							
							if($query == false)
							{
								$out = json_encode(array("error" => "The image upload didn't work unfortunately, please try again later"));
							}
							else
							{
								// now insert request info
								
								// if all goes to plan, send upload link
								
								$out = json_encode(array(
									"url" => $base_url.'image/view/'.$query["id"].'/'
								));
								
								$request_insert_array["r_success"] = 1;
								$request_insert_array["r_data"] = $out;
								
								$query_r = $this->api_model->createNewAPIRecord($request_insert_array);
								
								if($query_r == false)
								{
									$out = json_encode(array("error" => "The image upload by extension didn't work unfortunately, please try again later"));
								}
							}
								
							
						}
						
						
					}
					// other browsers here
				}
			}
			else
			{
				$out = json_encode(array("error" => "Browser not recognised"));
			}
			
			echo $out;
		}
	}
	
	/*
		Separate function just for authorising through the api
	*/
	
	function extensionauth()
	{
		//if(!isAJAXRequest(false)) // false so this isnt an internal check
		if(empty($_POST) && !isset($_POST["urika_username"]))
		{
			echo json_encode(array("error" => "HTTP Request format invalid. This page cannot be accessed from the browser"));
		}
		else
		{
			// process
			$params = $_POST;
			$out = "";
			$auth = false;

			// 1) user verification checkdata
			
			$this->load->model("user_model");
			$user = $this->user_model->getUser($params["urika_username"],"u_username");
			
			if($user == false)
			{
				$out = json_encode(array("error" => "Your username was not found"));
			}
			else
			{
				$user = $user->row();
				
				if($user->u_authkey == $params["urika_api_key"])
				{
					$auth = true;
					
					if(isLoggedIn() && $this->session->userdata("user_id") == $user->user_id)
					{
						$auth = true;
					}
					else
					{	
						$auth = false;
						$out = json_encode(array("error" => "You must be logged in to UR!KA to use the extension. Simple open a new tab and login and the extension should work fine :)"));
					}
				}
				else
				{	
					$auth = false;
					$out = json_encode(array("error" => "The API key supplied does not match your username. Please check that it is correct."));
				}
				
				
			}
			
			if($auth == true)
			{
				// 1) convert image data into resource
				$out = json_encode(array("auth" => "true"));

			}

			
			echo $out;
		}
	}
	

	
}

/* End of file api.php */
/* Location: ./system/application/controllers/api.php */