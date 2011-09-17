<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook["post_controller_constructor"][] = array(
	"class" => "CookieHandler",
	"filename" => "user_cookie.php",
	"filepath" => "hooks",
	"function" => "handleCookies"
);

//
// CSRF Protection hooks, don't touch these unless you know what you're
// doing.
//
// THE ORDER OF THESE HOOKS IS EXTREMELY IMPORTANT!!
//


/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */