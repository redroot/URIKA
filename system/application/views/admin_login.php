<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

   <title>Batcave</title>


	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/layout.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/content.css" /> 
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/admin.css" /> 
   
   
  
</head>
<body id="admin_login">
<div id="content_wrapper">
	<div class="inner_wrap" id="admin_login_form">
		<?php echo $messages; ?>
		<?php echo form_open("batcave/entrance/"); ?>
			<fieldset>
				<legend>Entrance to the Batcave</legend>
				<input type="text" class="hide" name="batcave_code" />
				<ul>
					<li>
						<label>Your secret handle</label>
						<input type="text" name="batcave_name" />
					</li>
					<li>
						<label>Your secret saying</label>
						<input type="password" name="batcave_saying" />
					</li>
					<li>
						<input type="submit" name="batcave_submit" value="Enter!" />
					</li>
					
				</ul>
			</fieldset>
		<?php echo form_close(); ?>
	</div>
</div>

		   	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
	<script type='text/javascript' src='http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js'></script> 
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/urika.js"></script> 

</body>

</html>