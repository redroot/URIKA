<div id="feature_title">
	<h2>Forgotten Password - <small> Join the growing community of creatives</small></h2>
</div>
<div id="fpasswordForm">
	<?php
	echo validation_errors('<p class="error">');
	
	echo $message;
	?>
	<div class="borderbox">
	<p>
		We can't actually give your password due to our security protocols but we can e-mail you a new one, provided you know your <strong>username</strong>. Once you have logged in with your new password, you can change it from your account page.
	</p>
	<?php echo form_open('user/fpassword',array("id"=>"fpasswordForm")); 

		
		
		?>
	<p>
		Username: <input type="text" name="fp_username" id="fp_username"  placeholder="Your Username" class="required"  /> 
		<input type="submit" name="fp_submit" id="fp_submit" value="Send New Password" id='fpButton' /> 
	</p>
	<?php
		echo form_close();
	?>
	</div>
</div>
<div class="clear">&nbsp;</div>

