<div id="feature_title">
	<h2>Sign Up - <small> Join the growing community of creatives</small></h2>
</div>
<div id="signup_form">
	<p>
		Joining UR!KA is a simple process, simply fill out the form below and click the link in the confirmation e-mail
		that gets sent to you and you'll be on your way!
	</p>
		<?php echo form_open('user/create_user',array("id"=>"signupForm")); 

		echo validation_errors('<p class="error">');
			
		echo $further_errors;
		?>
			<fieldset>
		<legend>Pick a username</legend>
		<ul>
			<li>
				<label for="s_username">Username</label>
				<input type="text" name="s_username" id="s_username" placeholder="Enter a username" minlength="4" class="required"  />
			</li>
			<li>
				<label for="s_email">E-mail Address</label>
				<input type="text" name="s_email" id="s_email" placeholder="Your E-mail Address" class="required email"  />
			</li>
			<li>
				<label for="s_password_a">Enter a Password (8 characters or more with both numbers and letters)</label>
				<input type="password" name="s_password_a" id="s_password_a"  class="required"   />
			
			</li>
			<li>
				<label for="s_password_b">Repeat the password</label>
				<input type="password" name="s_password_b" id="s_password_b" equalTo="#s_password_a" minlength="8" class="required"   />
			
			</li>
			<li>
				<label for="s_human">Human Check: 8 + 3 = ?</label>
				<input type="text" name="s_human" id="s_human" class="required"  />
			</li>
			<li class="form_last">
				<input type="submit" name="s_submit" id="s_submit" value="Sign Up" id='signupButton' /> 
			</li>
		</ul>

		 
		</fieldset>
		<?php 
		echo form_close();
		?>
</div>
<div class="clear">&nbsp;</div>
