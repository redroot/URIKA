<div class="comment <?php echo $classes; ?>" id="comment_<?php echo $comment_id; ?>">
	<div class="comment_img">
		<img src="<?php echo $profile_url; ?>" width="70" height="70" alt="user avatar" />
	</div>
	<div class="comment_info">
		<a class="comment_link" href="#comment_<?php echo $comment_id; ?>"><strong>#</strong></a> - by <a href="<?php echo $user_url; ?>"><strong><?php echo $username; ?></strong></a> on <?php echo $date_uploader; ?>
		<?php 
		
		if($showdelete == "true")
		{
		
		echo '<a class="delete_link" href="#comment_'.$comment_id.'" onclick="deleteComment('.$comment_id.')">Delete Comment</a>';
		
		}?>
	</div>
	<div class="comment_content">
	<?php echo $comment_text; ?>
	</div>
	<div class="kill">&nbsp;</div>
</div>