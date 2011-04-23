<div id="feature_title">
	<a href="<?php echo base_url(); echo 'user/u/'.$username; ?>" title="link to <?php echo $username; ?>'s profile"><img src="<?php echo $image_url; ?>" id="profile_img" width="70" height="70" alt="user avatar" /></a>
	
	<h2><?php echo $username; ?> - Notices</h2>
		<p class="u_info_string">Manage your notices from this page, showing the last 50 notices</p>
	<div class="clear">&nbsp;</div>
</div>
<div id="user_notices" class="">
	<?php 
	if($show_buttons == true)
	{
	echo '
	<div id="notice_buttons">
		<input type="button" id="notice_all_read" name="notice_all_read" value="Mark all as Read" onclick="toggleAllNotices(0)" />
		<input type="button" id="notice_all_unread" name="notice_all_unread" value="Mark all as Unread" onclick="toggleAllNotices(1)" />
	</div>
	'; 
	}
	?>
	<?php echo $notices_html; ?>
</div>
<div class="clear">&nbsp;</div>
