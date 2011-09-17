<?php
/* Cookie Handler hook to create sessions if they already have a cookie */

class CookieHandler {

	function handleCookies(){
		// if no session lets check for cookies
		if(!isLoggedIn()){
			if(isset($_COOKIE["urika_userlogin"]) == true && isset($_COOKIE["urika_login_tried"]) == false ){
			
				$CI =& get_instance();

			
				$CI->load->model("user_model");
				
				$query = $CI->user_model->getUser($_COOKIE["urika_userlogin"],"u_username");
				
				if($query){
					// now verify secret before creating session
					$row = $query->row();
					
					if(isset($_COOKIE["urika_usersecret"]) && md5($row->u_username.$row->u_password) == $_COOKIE["urika_usersecret"]){
						// win lets make a session
						$data = array(
							'username' => $row->u_username,
							'user_id' => $row->user_id,
							'is_logged_in' => true,
							'image_url' => getUserProfileURL($row->u_profile_id,$row->u_email)
						);
						$CI->session->set_userdata($data);
					}else{
						// no luck
						setcookie("urika_login_tried",$_COOKIE["urika_userlogin"]);
					}
					
				}else{
					// no match, so et a cookie so we don't try over and over again
					setcookie("urika_login_tried",$_COOKIE["urika_userlogin"]);
				}
				
			}
		}
	}
}
?>