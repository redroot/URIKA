<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

   <title>Admin - URIKA - Sharing Inspiration</title>


	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/layout.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/content.css" /> 
   
   <?= $_styles ?>
  
</head>
<body id="admin">
<div id="header_wrapper">
	<div class="inner_wrap">
		<h1><a href="<?php echo base_url(); ?>" title="Return to Homepage" class="hidetext">URIKA - Homepage</a></h1>
		<div id="top_leftnav">
			<ul>
				<li class="highlight"><a href="<?php echo base_url(); ?>batcave" title="Admin home">Admin Home</a></li>
				<li class="admin_dropdown"><a href="#">Tables</a>
					<ul>
					<?php
						echo $menu_lis;
					?>	
					<div class="clear">&nbsp;</div>
					</ul>
					
				</li>
				<li> <a href="<?php echo base_url(); ?>batcave/tempfiles/" title="manage temp folder">Temp Files</a>
				<li> <a href="<?php echo base_url(); ?>batcave/blacklistadd/" title="ADd to blacklist">Blacklist Add</a>
				<li> <a href="<?php echo base_url(); ?>batcave/invites/" title="Invites">Invites</a>
				<li> <a href="<?php echo base_url(); ?>batcave/analytics/" title="Analytics">Analytics</a>
				<li class="highlight"> <a href="<?php echo base_url(); ?>batcave/logout/" title="Admin Logout">Logout</a>
			</ul>
		</div>
		</div>
	</div>
</div>
<div id="feature_wrapper">
	<?php print $feature; ?>
</div>
<div id="content_wrapper">
	<div class="inner_wrap clear">
   
 
      <?php print $content ?>
 
		<div class="clear">&nbsp;</div>
	</div>
</div>
<div id="footer_wrapper">
	<div class="inner_wrap">
		UR!KA Admin System - &copy; <?php echo date("Y"); ?>
	</div>
</div>
		   	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
	<script type='text/javascript' src='http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js'></script> 
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/urika.js"></script> 
	  
	 <script type='text/javascript'>
		$(document).ready(function()
		{
			
			$("#showSearch").click(function()
			{
				$(this).hide();
				$('#admin_search_form').show();
			});
		});
			
		
	 </script>
</body>

</html>