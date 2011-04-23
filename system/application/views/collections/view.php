<div id="feature_title">
	<img src="<?php echo $profile_url; ?>" id="profile_img" width="70" height="70" alt="user avatar" />
	
	<h2>Collection: <?php echo $col_title; ?> - <small><a href="<?php echo base_url(); ?>user/u/<?php echo $username; ?>/"><?php echo $username; ?></a></small></h2>
	<p class="u_info_string"><?php echo $col_images_string; ?></p>
	<div class="clear">&nbsp;</div>
</div>
<div id="col_view" class="bumpUp">
<?php 
$q_vars = get_url_vars();
if(isset($q_vars->col_saved))
{
	echo '<p class="success" style="margin-bottom: 10px;">Collection details saved successfully!</p>';
}
echo $controls_html;
echo $images_html;
?>
</div>
<div class="clear">&nbsp;</div>
