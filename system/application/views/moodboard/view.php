<div id="feature_title">
	<img src="<?php echo $profile_url; ?>" id="profile_img" width="70" height="70" alt="user avatar" />
	
	<h2><?php echo $mb_title; ?> - <small><a href="<?php echo $base_url; ?>user/u/<?php echo $username; ?>/"><?php echo $username; ?></a></small></h2>
	<p class="u_info_string"><?php echo $mb_datetime; ?> <span class="sep">|</span>  <?php echo $mb_views; ?> views <span class="sep">|</span> <?php echo $favs_count; ?> favourites</p>
	<div class="clear">&nbsp;</div>
</div>
<div id="moodboard_view" class="bumpUp">
<?php 
$q_vars = get_url_vars();
		
if(isset($q_vars->message) && $q_vars->message == "saved")
{
	echo '<p class="success" style="margin-bottom: 10px;">Moodboard details saved successfully!</p>';
}


?>
	<div id="view_image">
			<img  src="<?php echo $mb_url; ?>" alt="<?php echo $mb_title; ?>" />
		</div>
	<div class="left col-12">
		
		<div id="imageTabs" class="tabs fullWidthTabs">
			<ul class="tabNav">
				<li><a href="#" title="Tab link"><strong><?php echo $comments_count ?></strong> Comment<?php if($comments_count != 1){ echo 's'; } ?></a></li>
				<li><a href="#" title="Tab link"><strong><?php echo $favs_count ?></strong> Favourite<?php if($favs_count != 1){ echo 's'; } ?></a></li>

			</ul>
			
			<div class="tabContentWrapper">
				<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<?php if($this->session->userdata("is_logged_in") == 1)
					{
					 echo '
					<div id="comment_form">
						<p class="toRemove">Leave a comment:</p>
						<img src="'.$signedin_profile_url.'" id="profile_img" width="70" height="70" alt="user avatar" />
							<textarea name="comment_text" id="comment_text" rows="6" ></textarea>
							<input type="hidden" id="comment_user_id" name="comment_user_id" value="'.$this->session->userdata("user_id").'" />
							<input type="hidden" id="comment_subject_id" name="comment_subject_id" value="'.$mb_id.'" />
							<input type="hidden" id="subject_user_id" name="subject_user_id" value="'.$mb_user_id.'" />
							<input type="hidden" id="subject_name" name="subject_name" value="'.$mb_title.'" />
							<input type="hidden" id="subject_type" name="subject_type" value="moodboard" />
							<div id="button_div"><input type="button" onclick="addComment();" id="comment_save" name="comment_save" value="Post Comment" /></div>
							<div class="clear">&nbsp;</div>
					</div>
					';
					
					}
					
					?>
					<div class="comments">
						
					<?php
						echo $comments_html;
						
					?>
						
					</div>
					<?php
						echo $comments_buttons;
					?>
					<div class="clear">&nbsp;</div>
					
				</div>
				<div class="tabContent">
					<?php
						echo $favs_html; 
					?>
				</div>
				
			</div>
		</div>
	</div>
	<div class="col-4 right">
		<div id="imageDescTabs" class="tabs fullWidthTabs">
			<ul class="tabNav">
				<li><a href="#" title="Information">Information</a><li>
			</ul>
			<div class="tabContentWrapper">
				<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<p class="desc_title"><strong>Description:</strong></p>
					<div><?php echo str_replace(array("<?php","?>"),array("[removed]","[removed]"),html_entity_decode($mb_desc)); ?></div>
					<p class="tags_p"><?php echo $mb_tags_html; ?></p>
					
					<ul class="image_controls">
						
						
						<?php if(isset($controlsHTML["editLink"])) { ?>
						<li><a class="edit_link" href="<?php echo $controlsHTML["editLink"]; ?>">Edit your Moodboard</a></li>
						<li><a class="delete_link" href="<?php echo $controlsHTML["deleteLink"]; ?>">Delete your Moodboard</a></li>
						<?php }else if(isset($controlsHTML["favlink"])){
						
							echo $controlsHTML["favlink"];
						
						 } 
						 
						 
						 ?>
					</ul>
					<?php
					if(isset($controlsHTML["flagLink"]))
					{
						echo '<ul class="image_controls">';
						echo $controlsHTML["flagLink"];
						echo '</ul>';
					}
					?>
				</div>
		</div>
	</div>
</div>
<div class="clear">&nbsp;</div>
