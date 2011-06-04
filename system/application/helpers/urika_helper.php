<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Urika Helper
 *
 * 	General helper file for user all over URIKA app.
 *
 * @author Luke Williams
 */

// ------------------------------------------------------------------------

/**
 * loggedInSection()
 *
 * If user is not logged in, redirects to home age or login page with a URL redirect
 *
 *	@param $forcelogin : forces user to login and redirects them current page once logged in.
 *	@param $redirect : the url to redirect too
 */	
if ( ! function_exists('loggedInSection'))
{
	function loggedInSection($forcelogin = false, $redirect = null)
	{
		$CI =& get_instance();
		
		if($CI->session->userdata("is_logged_in") === NULL || $CI->session->userdata("is_logged_in") != 1)
		{
			if($forcelogin == false)
			{
				redirect('','location');
			}
			else
			{
				redirect('/user/login/'.$redirect,'location');
			}
		}
	}	
}

/**
 * isLoggedIn()
 *
 * returns true if the current user is logged in, no redirect involved.
 *
 */
 
 if ( ! function_exists('isLoggedIn'))
{
	function isLoggedIn()
	{
		$CI =& get_instance();
		
		if($CI->session->userdata("is_logged_in") === NULL || $CI->session->userdata("is_logged_in") != 1)
		{
			return false;
		}
		else
		{
			return true;
		}
	}	
}

/**
 * isAdmin()
 *
 * returns true if the admin session variable satisfies the test
 *
 */
 
 if ( ! function_exists('isAdmin'))
{
	function isAdmin()
	{
		$CI =& get_instance();
		
		if($CI->session->userdata("admin") === NULL || $CI->session->userdata("admin") != 1)
		{
			return false;
		}
		else
		{
			//check admin check
			$check = $CI->session->userdata("admin_check");
			
			//check hash matches
			$check_array = explode(".",$check);
			
			$check_hash = md5(strrev($check_array[1])."".($check_array[3]*923));
			
			if($check_hash == $check_array[2])
			{
				return true;
			}
			else
			{
				return false;
			}
			
		}
	}	
}


/**
 * isAjaxRequest()
 *
 * Returns true if this is an ajax request
 *
 *	@param internal : if true, checks that the request came from within the site
 */	
if ( ! function_exists('isAjaxRequest'))
{
	function isAjaxRequest($internal = true)
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
		{ 
			if($internal == true)
			{
				// check the referr for either localhost/urika/ci or urika-app.co
				
				if(strpos($_SERVER["HTTP_REFERER"],"localhost/urika/ci") === FALSE && strpos($_SERVER["HTTP_REFERER"],"urika-app.com") === FALSE)
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return true;
			}
		} 
		
		return false;
	}	
}
/*
* getUserProfileURL()
*
* Function to grab a user image from their u_profile_id
* @param profile_id : image id of the imge to grab, or 'gravatar' for gravatar
* @param user_email: required for gravatar function
*/
if ( ! function_exists('getUserProfileURL'))
{
	function getUserProfileURL($profile_id, $user_email, $size = null)
	{

		$CI =& get_instance();
		$CI->load->model('image_model');
		
		$image_url = "";
		
		if(strpos($profile_id,"usegravatar::") !== FALSE)
		{
			$email_hash = md5(strtolower(trim($user_email)));
			$size = 60;
						
			$image_url = "http://www.gravatar.com/avatar/".$email_hash."?s=".$size;
		}
		else
		{	
			if($profile_id != "")
			{
				$image_url = $profile_id; // return the url
			}
			else
			{
				// default image
				$base = base_url();
				$image_url = $base."assets/images/layout/avatar_default.jpg";
			}
		
		}
		
		return $image_url;
	}
}

/*
* getUserNoticeCount()
*
* Returns current signed in users count for new notices
*/
if ( ! function_exists('getUserNoticeCount'))
{
	function getUserNoticeCount()
	{
		$CI =& get_instance();
		
		if($CI->session->userdata("is_logged_in") == 1)
		{
			
			$CI->load->model("notice_model");
			$notices = $CI->notice_model->getUserNotices($CI->session->userdata("user_id"),true);
			
			if($notices != false)
			{
				return count($notices);
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return false;
		}
	}
}

/*****
	Cache Functions: My own in built cache functions for storing files,
	flatCache style system
*****/

/*
* cacheFetch()
*
* fetchs a file based on a path from the base
* @param key : identifier key
*/
function cacheFetch($key)
{
	$CI =& get_instance();
	$CI->load->helper("file");

	if($key == null || $key == "") 
	{ 
		return false; 
	}
	else
	{
		$path = "./cache/".$key."urikaCache.txt";
		
		$file = read_file($path);
		
		if($file == false)
		{
			return false;
		}
		else
		{
			$stored = explode("x#x#x#x",$file);
			
			$return["expire"] = $stored[0];
			$return["data"] = unserialize($stored[1]);
			
			return $return;
		}
	}
	
}

/*
* cacheStore()
*
* fetchs a file based on a path from the base
* @param data: the data to store, serializes for better use
* @param key : identifier key
* @param time : time to store for in minutes
*/
function cacheStore($data,$key,$time = 15)
{
	$CI =& get_instance();
	$CI->load->helper("file");

	// first generate timestamp
	$time = time() + ($time * 60);
	
	// key and data
	if($data == null || $key == null || $data == "" || $key == "")
	{	
		return false;
	}
	else
	{
		$data = serialize(trim($data));
		$data = $time."x#x#x#x".$data;
		$key = trim($key)."urikaCache.txt";
		
		$path = "./cache/".$key;
		
		return write_file($path,$data);
	}
}
	


/* 
	Utiliies
*/
if ( ! function_exists('inspect'))
{
	function inspect($data)
	{
		echo '<div style="padding:10px; border: 1px solid #f66"><pre>';
		
		print_r($data);
		
		echo '</pre></div>';
	}	
}

// quick sort which allows your to sort my a property in an array of objects
function quickSort( &$array, $property = null )
{
	$cur = 1;
	$stack[1]['l'] = 0;
	$stack[1]['r'] = count($array)-1;
 
	do 
	{
		$l = $stack[$cur]['l'];
		$r = $stack[$cur]['r'];
		$cur--;
	 
	    do
	    {
			$i = $l;
			$j = $r;
			$tmp = $array[(int)( ($l+$r)/2 )];
	 
			// partion the array in two parts.
			// left from $tmp are with smaller values,
			// right from $tmp are with bigger ones
			do
			{
				if($property != null) // is a prperty specified deal with that
				{
					while( $array[$i]->$property < $tmp->$property )
					$i++;
	 
					while( $tmp->$property < $array[$j]->$property ) 
					$j--;
				}
				else
				{
					
					while( $array[$i] < $tmp )
					$i++;
	 
					while( $tmp < $array[$j] ) 
					$j--;
				}
 
				// swap elements from the two sides 
				if( $i <= $j )
				{
					$w = $array[$i];
					$array[$i] = $array[$j];
					$array[$j] = $w;
	 
					$i++;
					$j--;
				}
 
			}while( $i <= $j );
 
 
		if( $i < $r )
		{
			$cur++;
			$stack[$cur]['l'] = $i;
			$stack[$cur]['r'] = $r;
		}
		$r = $j;
 
		}while( $l < $r );
 
	}while( $cur != 0 );
 
	return $array;
}

/*
	Returns an object containing all url parameters
*/
function get_url_vars()
{
	
	$q_vars = new stdClass();
	
	if($_SERVER["QUERY_STRING"] != "")
	{
		$q_string = explode("&",$_SERVER["QUERY_STRING"]);
		
		foreach($q_string as $val)
		{
			$q_var = explode("=",$val);
			$q_vars->{$q_var[0]} = $q_var[1];
		}
		
		return $q_vars;
	}
	else
	{
		return null;
	}
	
	
}

/*
	Helper function for search filter delete buttons.
	Return a query string of current parameters
	without the specified, and without those specified
	e.g. page, usernotfound
	
	Also allows php to make next and previous links, cleaning the url on the way
	
	@param filter_var : filter var to remove
	@param list : array of values to ignore went constructing new strng
	@param inc_page : if you want to increment the page
*/
function queryStringDrop($filter_var,$list,$inc_page = 0)
{
	$current_page_value = 1;
	
	if($_SERVER["QUERY_STRING"] != "")
	{
		$q_string = explode("&",$_SERVER["QUERY_STRING"]);
		$result_string = "?";
		
		// set here to grab new page value in the loop
		
		
		foreach($q_string as $val)
		{
			$q_var = explode("=",$val);
			
			// grab page value if it eists
			if($q_var[0] == "page")
			{
				$current_page_value = (int) $q_var[1];
			}
			
			if(in_array($q_var[0],$list) == false)
			{
				if($filter_var != null) // if there is a filter val, check for it
				{
					if($q_var[0] != $filter_var)
					{
						$result_string .= $val.'&';
					}
				}
				else
				{
					$result_string .= $val.'&';
				}
			}
		}
		
		// one check for empty strings
		if($result_string == "?")
		{
			$result_string = "";
		}
		else // trim last character
		{
			$result_string = substr($result_string,0,-1);
		}
		
		// if there is a value we need to add this to the array
		
		if($inc_page != 0)
		{
			$new_value = $inc_page + $current_page_value;
			
			if($new_value != 1)
			{
				if($result_string == "")
				{

					$result_string .= '?page='.$new_value;
				}
				else
				{	
					$result_string .= '&page='.$new_value;
				}
			}
		}
			
		
		return $result_string;
	}
	else
	{
		// do the same for if there is no query string
		if($inc_page != 0)
		{
			$new_value = $inc_page + $current_page_value;
			
			if($new_value != 1)
			{
				return '?page='.$new_value;
			}
			else
			{
				return "";
			}
			
		}
		else
		{
			return "";
		}
	}
}

/*
	Sanitizes a string to be URL friendly, a slug basically
	
	@param filename : the string the slugify
*/
function slugify( $filename ) {
    $filename_raw = $filename;
    $special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
    $filename = str_replace($special_chars, '', $filename);
    $filename = preg_replace('/[\s-]+/', '-', $filename);
    $filename = trim($filename, '.-_');
    return $filename;
}


/* End of file urika_helper.php */
/* Location: ./system/helpers/urika_helper.php */