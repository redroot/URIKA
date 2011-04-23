<div id="feature_title">
	<a href="<?php echo base_url(); echo 'user/u/'.$username; ?>" title="link for this profile"><img src="<?php echo $image_url; ?>" id="profile_img" width="70" height="70" alt="user avatar" /></a>
	<h2><a href="<?php echo base_url(); echo 'user/u/'.$username; ?>" title="link for this profile"><?php echo $username; ?></a>: <small>Account Management</small></h2>
	<p class="u_info_string">Manage all your settings and public details from this page</p>
	<div class="clear">&nbsp;</div>
</div>
<div id="user_account" class="bumpUp">
	<div class="left col-12">
		<div class="tabs fullWidthTabs">
			<ul class="tabNav">
				<li><a href="#" title="Tab link">Profile Image</a></li>
				<li><a href="#" title="Tab link">Public Details</a></li>
				<li><a href="#" title="Tab link">Change Password</a></li>
				<li><a href="#" title="Tab link">Change E-mail</a></li>
				<li><a href="#" title="Tab link">Notices</a></li>
				<li><a href="#" title="Tab link">API Key</a></li>
			</ul>
			
			<div class="tabContentWrapper">
			<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<p>
					 For your profile picture you can:
					</p>
					<div id="avatar_upload_div" class="left col-6 borderbox <?php echo $profilevalues["upload_class"]; ?>">
						<div class="right">
						<span class="loading hide">&nbsp;</span>
							<input type="button" name="a_img_upload_save" id="a_img_upload_save" value="<?php echo $profilevalues["upload_text"]; ?>" />
							<span id="avatar_url" class="hide"><?php echo $profilevalues["upload_url"]; ?></span>
							
						</div>
						<p><strong>Use your own:</strong></p>
						
						<div class="account_profile_img">
							<?php
								if($profilevalues["upload_url"] == "")
								{
									echo 'No Upload';
								}
								else
								{
									echo '<img src="'.$profilevalues["upload_url"].'" width="70" height="70" alt="currently uploaded avatar" />';
								}
							?>
						</div>
						<div id="noavatar_info" <?php if($profilevalues["upload_url"] != "") { echo 'class="hide"'; } ?>>
							<p class="avatar_info">Your avatar must be no more than 200x200 pixels in size, but we will resize it to 70x70 pixels on upload</p>
						
							<div id="avatar_upload_box">
						
							</div>
						</div>
						<div id="hasavatar_info" <?php if($profilevalues["upload_url"] == "") { echo 'class="hide"'; } ?>>
							<p>To upload a new avatar, you first need to delete your current one:</p>
							<div>
								<input type="button" name="a_img_delete_avatar" id="a_img_delete_avatar" value="Delete Avatar" />
								<span class="loading hide">&nbsp;</span>
							</div>
						</div>
						
					</div>
					<div id="avatar_gravatar_div" class="right col-6 borderbox <?php echo $profilevalues["gravatar_class"]; ?>">
						<div class="right">
							<span class="loading hide">&nbsp;</span>
							<input type="button" name="a_img_gravatar_save" id="a_img_gravatar_save" value="<?php echo $profilevalues["gravatar_text"]; ?>" />
					
						</div>
						<p><strong>Use your <a href="http://www.gravatar.com" target="_blank">Gravatar</a></strong></p>
						<div class="account_profile_img">
							<img width="70px" height="70px" src="<?php echo $profilevalues["gravatar"]; ?>" alt="Your Gravatar Image if any" />
						</div>
						<p> <a href="http://www.gravatar.com" target="_blank"><strong>Gravatar</strong></a> is a free service that thats you use a globally recognised avatar based on your e-mail address.</p>
					</div>

				</div>
				<div class="tabContent">
					<p>You can change your personal details from here:</p>
					<fieldset>
					
						<ul>
							<li>
								<label for="a_fname">First Name</label>
								<input type="text" name="a_fname" id="a_fname"  value="<?php echo $profilevalues["firstname"]; ?>"  />
							</li>
							<li>
								<label for="a_sname">Surname</label>
								<input type="text" name="a_sname" id="a_sname"  value="<?php echo $profilevalues["surname"]; ?>"   />
							</li>
							<li>
								<label for="a_website">Website URL</label>
								<input type="text" name="a_website" id="a_website" value="<?php echo ($profilevalues["website"] != "") ? $profilevalues["website"] : "http://"; ?>"   />
							</li>
							<li>
								<label for="a_twitter">Twitter Username</label>
								<input type="text" name="a_twitter" id="a_twitter" value="<?php echo $profilevalues["twitter"]; ?>"    />
							</li>
							<li>
								<label for="a_location">Location</label>
								<input type="text" name="a_location" id="a_location" value="<?php echo $profilevalues["location"]; ?>"    />
							</li>
						<li>
						<input type="button" name="a_save_details" id="a_save_details" value="Save Details" />
						<span class="loading hide">Saving</span>
						</li>
						</ul>
					
					</fieldset>
				</div>
				<div class="tabContent">
					<p>You can change your password on this tab. Same rules apply, the password need to be at least 8 characters in length</p>
					<fieldset>
						<ul>
							<li>
								<label for="a_cpass_a">Current Password</label>
								<input type="password" name="a_cpass_a" id="a_cpass_a"   />
							</li>
							<li>
								<label for="a_cpass_b">Repeat your current password</label>
								<input type="password" name="a_cpass_b" id="a_cpass_b"   />
							</li>
							<li>
								<label for="a_npass_a">Your new Password</label>
								<input type="password" name="a_npass_a" id="a_npass_a"   />
							</li>
							<li>
								<label for="a_npass_b">Repeat your new password</label>
								<input type="password" name="a_npass_b" id="a_npass_b"   />
							</li>
							
						<li>
						
						<input type="button" name="a_save_password" id="a_save_password" value="Save Password" />
							<span class="loading hide">Saving</span>
						</li>
						</ul>
					
					</fieldset>
				</div>
				<div class="tabContent">

					<p>You change your e-mail from here. We will send a test e-mail to check you can recieve mail from us once you have changed it.</p>
				<fieldset>
				<ul>
							<li>
								<label for="a_cemail">Current E-mail Address</label>
								<input type="text" name="a_cemail" id="a_cemail"   />
							</li>
							<li>
								<label for="a_nemail">Your New E-mail Address</label>
								<input type="text" name="a_nemail" id="a_nemail"   />
							</li>
							
						<li>
						
						<input type="button" name="a_save_email" id="a_save_email" value="Save E-mail" />
						<span class="loading hide">Saving</span>
						</li>
						</ul>
					</fieldset>
			</div>
				<div class="tabContent">
				<p>Use this panel to save what activity you want to be notified about:</p>
					<fieldset>
					
					<ul>
						<li>
							<input type="checkbox" name="a_notice_format" value="upload_favs" <?php if(in_array("upload_favs",$profilevalues["notice_format"])) { echo "checked"; } ?> />  Upload Favourites
						</li>
						<li>
							<input type="checkbox" name="a_notice_format" value="upload_comments" <?php if(in_array("upload_comments",$profilevalues["notice_format"])) { echo "checked"; } ?> /> Upload Comments
						</li>
						<li>
							<input type="checkbox" name="a_notice_format" value="mb_favs" <?php if(in_array("mb_favs",$profilevalues["notice_format"])) { echo "checked"; } ?> /> Moodboard Favourites
						</li>
						<li>
							<input type="checkbox" name="a_notice_format" value="mb_comments" <?php if(in_array("mb_comments",$profilevalues["notice_format"])) { echo "checked"; } ?> /> Moodboard Comments
						</li>
						<li>
							<input type="checkbox" name="a_notice_format" value="collection_add" <?php if(in_array("collection_add",$profilevalues["notice_format"])) { echo "checked"; } ?> /> Upload added to a collection
						</li>
						<li>
						
						<input type="button" name="a_save_notices" id="a_save_notices" value="Save Notices" />
						<span class="loading hide">Saving</span>
						</li>
					</ul>
					
					</fieldset>
				</div>
				<div class="tabContent">
					<p>
					Below is your personal <abbr title="Application Protocol Interface">API</abbr> key, which is used when making requests to our site though our <a href="<?php echo base_url(); ?>page/tools/"><strong>API</strong> and Browser Extensions</a>.
					</p>
					<p class="borderbox" style="font-size: 16px; margin-top: 10px; ">
						Your API Key: <strong style="color: #930;"><?php
						 echo $apikey;
						?></strong>
					</p>
				</div>
			</div>
				
		</div>
	</div>
	<div class="col-4 right">
		<div class="tabs fullWidthTabs">
			<ul class="tabNav">
				<li><a href="#" title="Information">Statistics</a><li>
			</ul>
			<div class="tabContentWrapper">
				<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<p><strong><?php echo $username; ?></strong>, you have:</p>
					<ul>
						<li>uploaded <strong><?php echo $stats["images"]; ?></strong> image(s)</li>
						<li>created <strong><?php echo $stats["mbs"]; ?></strong> moodboard(s)</li>
						<li>added <strong><?php echo $stats["favourites"]; ?></strong> favourite(s)</li>
						<li>made <strong><?php echo $stats["collections"]; ?></strong> collection(s)</li>
						<li>commented <strong><?php echo $stats["comments"]; ?></strong> times</li>
					</ul>
					<p>Others have:
					<ul>
						<li>favourited your uploads and moodboards <strong><?php echo $stats["other_favs"]; ?></strong> times</li>
						<li>made <strong><?php echo $stats["other_comments"]; ?></strong> comments on your uploads and moodboards</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="clear">&nbsp;</div>
