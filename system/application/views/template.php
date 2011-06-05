<!doctype html> 
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]--> 
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]--> 
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]--> 
<!--[if (gte IE 9)|!(IE)]><!--><html lang="en"> <!--<![endif]--> 
<head>

	   
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
   <meta http-equiv="content-language" content="en" /> 
   <meta http-equiv="X-UA-Compatible" content="chrome=1" />
   <meta name="viewport" content="width=1000; initial-scale=1.0" /> 
        


   <title><?php echo $title ?> - URIKA - Sharing Inspiration</title>


	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/layout.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/content.css" /> 
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.8/themes/base/jquery-ui.css" type="text/css" media="all" /> 
	
	<!--[if lt IE 9]>
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/iestyles.css" />
	<![endif]-->
   
   <?= $_styles ?>

  
</head>
<body id="<?php echo $body_id; ?>">
<div id="header_wrapper">
	<div class="inner_wrap">
		<h1><a href="<?php echo base_url(); ?>" title="Return to Homepage" class="hidetext">URIKA - Homepage</a></h1>
		<div id="screenreaderInfo">
			<p>UR!KA is a site for web developers and web professionals to share inspiration. This website makes heavy use of Javascript, CSS3 and other 
			current web technologies. As such, functionality is very limited without these enabled, such as if you are using a screen reader. We do apologise for this, but below there are links
			to skip to other parts of the site which may provide more information. - The team at UR!KA</p>
			<a href="#lform" title="skip to login form">Skip to Login Form</a>
			<a href="#content_wrapper" title="skip to content">Skip to Content</a>
			<a href="#footer_wrapper" title="skip to more information pages">Skip to more information pages</a>
		</div>
		<div id="top_leftnav">
			<ul>
				<li><a href="<?php echo base_url(); ?>browse" title="Browse and Search Uploads">Browse</a></li>
				<li><a href="<?php echo base_url(); ?>tags/" title="Browse the latest and hottest tags">Tags</a></li>
				<li><a href="<?php echo base_url(); ?>user/ulist/" title="Browse and search for users">Users</a></li>
				<?php
				$base = base_url();
			
				$upload_drop = "";
				if($this->session->userdata("is_logged_in") == 1)
				{
					echo '
					<li class="menu_drop" id="upload_li" data-menu="#upload_drop"><a href="#"  title="Upload and crop an Image">Upload</a></li>
					';
					
					$upload_drop = '
					<div id="upload_drop" class="menu_dropbox">
						<ul>
							<li><a href="'.$base.'image/add/" class="menu_drop" title="Upload and crop an Image">Add Image</a></li>
							<li><a href="'.$base.'user/u/'.$this->session->userdata("username").'/?moodboard_add=1" title="Add a moodboard">Add Moodboard</a></li>
							<li><a href="'.$base.'page/tools/" title="Tools">Browser Tools</a></li>
						</ul>
					</div>
				
					';
					
				}
				?>
				<li><a href="<?php echo base_url(); ?>page/tools/" title="Tools">Tools</a></li>
				<?php echo $upload_drop; ?>
			</ul>
		</div>
		<div id="top_rightnav">
		<?php
		$base = base_url();
		
		$current = $this->uri->uri_string;
		
			if($this->session->userdata("is_logged_in") == 1)
			{
			
				$notice_count = getUserNoticeCount();
				
				if($notice_count == 0)
				{
					$notice_count_html = '';
				}
				else
				{
					$notice_count_html = '<span>'.$notice_count.'</span> ';
				}
		echo '
		
			<ul>
				<li>
				<form method="get" id="sform" action="'.$base.'browse/"> 
					<input type="text" id="main_search" value="Search" name="search" size="15" /> 
                    <input type="submit" class="hide" name="submit_search" value="Search" id="searchbutton" /> 
				</form> 
				</li>
			
				<li id="account_li" class="menu_drop" data-menu="#account_drop"><a href="#" title="My Account"><strong>'.$this->session->userdata("username").'</strong></a></li>
					<li id="notices_li"><a href="'.$base.'user/notices/" title="View your notices">View Notices '.$notice_count_html.'</a></li>
			</ul>
			
			
			
			<div id="account_drop" class="menu_dropbox">
				<ul>
					<li><a href="'.$base.'user/u/'.$this->session->userdata("username").'/" title="My Profile">My Profile</a></li>
					<li><a href="'.$base.'user/account/" title="View your account">My Account</a></li>
					<li><a href="'.$base.'user/logout" title="Log out">Log Out</a></li>
				</ul>
				
				
			</div>
			';
		}
		else
		{
		 echo '
			<ul>
				<li>
				<form method="get" id="sform" action="'.$base.'browse/"> 
					<input type="text" id="main_search" value="Search" name="search" size="15" /> 
                    <input type="submit" class="hide" name="submit_search" value="Search" id="searchbutton" /> 
				</form> 
				</li>
				<li id="login_li" class="menu_drop" data-menu="#login_drop"><a href="#" title="signin">Log In</a></li>
				<li id="signup_li"><a href="'.$base.'user/signup" title="Sign Up to UR!KA">join <strong>UR!KA</strong></a></li>
			</ul>
			<div id="login_drop" class="menu_dropbox">
				<form id="lform" method="post" action="'.$base.'user/validate">
					<label for="l_username">Username</label>
					<input type="text" name="l_username" id="l_username" class="required" />
					<label for="l_password">Password</label>
					<input type="password" name="l_password" class="required" id="l_password" />
					<input type="submit" name="l_submit" id="l_submit" value="Log In!" />
					<input type="hidden" name="l_redirect" value="'.$current.'" />
					<p class="clear"><a href="'.$base.'user/fpassword/" title="Forgotten Password">Forgotten Password?</a></p>
				</form>
			</div>
			';
		
		}
		?>
		</div>
		
	</div>
</div>
<div id="feature_wrapper">

</div>
<div id="content_wrapper">
	<div class="inner_wrap clear">
   
 
      <?php print $content ?>
 
   
	</div>
</div>
<div id="footer_wrapper">
	<div class="inner_wrap">
		<a href="<?php echo $base; ?>page/about/" title="About URIKA">About</a>  |  
		<a href="<?php echo $base; ?>page/faq/" title="Frequently Asked Questions"><strong>Help &amp; FAQ</strong></a>  |  
		<a href="http://urika.tumblr.com" target="_blank" title="The URIKA BLOG">Blog</a>  |  
		<a href="<?php echo $base; ?>page/api-info/" title="URIKA API">API</a>  |  
		<a href="<?php echo $base; ?>page/terms/" title="Terms">Terms</a>  |  
		<a href="<?php echo $base; ?>page/privacy/" title="Privacy Policy">Privacy Policy</a>  |  
		<a href="<?php echo $base; ?>page/contact/" title="Get in Touch">Contact</a> 
		<p>Copyright © 2010-<?php echo date('Y'); ?> UR!KA. All screenshots © their respective owners or sources.</p>
		<p>UR!KA was built and is run by <a href="http://www.red-root.com" title="Luke Williams" target="_blank">Red<strong>Root</strong></a></p>
	</div>
</div>
		   	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
	<script type='text/javascript' src='http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js'></script> 
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/urika.js"></script> 
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/libs/facebox.js"></script> 
	   <?= $_scripts ?>
	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try{
	var pageTracker = _gat._getTracker("UA-2325270-6");
	pageTracker._trackPageview();
	} catch(err) {}
	</script>
	 <!-- no script option to warn users with no JS that the site probably wont wort -->
	<noscript>
		<div class="no_js_is_bad">
			<p>You seem to have Javascript disabled. UR!KA makes use of many modern web technologies that require Javascript, so it is highly recommended
			that you enable Javascript in your browser in order to use the site.</p>
		</div>
	</noscript>

</body>

</html>