<div id="feature_title">
	<h2>Login - <small> Join the growing community of creatives</small></h2>
</div>
<div id="signup_form">
		<?php echo form_open('user/validate',array("id"=>"loginForm")); 

		echo validation_errors('<p class="error">');
		
		echo $further_errors;
		
		?>
			<fieldset>
		<legend>Login</legend>
		<ul>
			<li>
				<label for="l_username">Username</label>
				<input type="text" name="l_username" id="l_username" placeholder="Username" class="required"  />
			</li>
			<li>
				<label for="l_password">Password</label>
				<input type="password" name="l_password" id="l_password" placeholder="Password"  class="required"   />
			
			</li>
			<li class="form_last">
				<input type="submit" name="l_submit" id="l_submit" value="Log In" id='loginButton' /> 
			</li>
		</ul>
		<input type="text" name="l_check" value="" class="hide"  />
		<input type="hidden" name="l_redirect" value="<?php if($redirect) { echo $redirect; } ?>" />
		</fieldset>
		<?php 
		echo form_close();
		?>

</div>
<div class="clear">&nbsp;</div>
