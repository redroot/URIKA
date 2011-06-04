<div id="feature_title">
	<a href="<?php echo $base_url; ?>user/u/<?php echo $username; ?>/" title="link to <?php echo $username; ?>'s profile"><img src="<?php echo $profile_url; ?>" id="profile_img" width="70" height="70" alt="user avatar" /></a>
	
	<h2><?php echo $image_title; ?> - <small><a href="<?php echo $base_url; ?>user/u/<?php echo $username; ?>/"><?php echo $username; ?></a></small></h2>
	<p class="u_info_string"><?php echo $image_datetime; ?>  <span class="sep">|</span>  <?php echo $image_dims; ?>  <span class="sep">|</span>  <?php echo $image_views; ?> views <span class="sep">|</span> <?php echo $favs_count; ?> favourites</p>
	<div class="clear">&nbsp;</div>
</div>
<div id="image_view" class="bumpUp">
<?php 

$q_vars = get_url_vars();

if(isset($q_vars->saved))
{
	echo '<p class="success" style="margin-bottom: 10px;">Image details saved successfully!</p>';
}


?>
	<div id="view_image">
			<img  src="<?php echo $image_url; ?>" alt="<?php echo $image_title; ?>" />
			<a id="imageSourceLink" title="view original webpage" href="<?php echo $image_website; ?>"><?php echo $image_website; ?></a>
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
							<input type="hidden" id="comment_subject_id" name="comment_subject_id" value="'.$image_id.'" />
							<input type="hidden" id="subject_user_id" name="subject_user_id" value="'.$image_user_id.'" />
							<input type="hidden" id="subject_name" name="subject_user_id" value="'.$image_title.'" />
							<input type="hidden" id="subject_type" name="subject_type" value="image" />
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
				<li><a href="#" title="Information">Information</a></li>
			</ul>
			<div class="tabContentWrapper">
				<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<p class="image_link"><strong>Source:</strong><br/><a href="<?php echo $image_website; ?>" title="View website for this image"><?php echo $image_website; ?></a></p>
					<p class="desc_title"><strong>Description:</strong></p>
					<div><?php echo str_replace(array("<?php","?>"),"[removed]",html_entity_decode($image_desc)); ?></div>
					<p class="tags_p"><?php echo $image_tags_html; ?></p>
					
					<?php if(isset($controlsHTML["editLink"]) || isset($controlsHTML["favlink"])) { ?>
					<ul class="image_controls">
					<?php } ?>
						
						
							<?php if(isset($controlsHTML["editLink"])) { ?>
							<li><a class="edit_link" href="<?php echo $controlsHTML["editLink"]; ?>">Edit your image</a></li>
							<li><a class="delete_link" href="<?php echo $controlsHTML["deleteLink"]; ?>">Delete your image</a></li>
							<?php }else if(isset($controlsHTML["favlink"])){
							
								echo $controlsHTML["favlink"];
							
							 } 
							 if(isset($controlsHTML["collectionsLink"]))
							 {
								echo $controlsHTML["collectionsLink"];
							 }
							 
							 
							 ?>
					<?php if(isset($controlsHTML["editLink"]) || isset($controlsHTML["favlink"])) { ?>
					</ul>
					<?php } ?>
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
</div>
<div class="clear">&nbsp;</div>
