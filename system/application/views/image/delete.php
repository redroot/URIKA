<div id="feature_title">
	<h2>Delete Image</h2>
</div>
<div id="image_delete">
	<div class="borderbox">
		<p>Are you sure you want to delete the image <a href="<?php echo $image_url; ?>"><strong><?php echo $image_title; ?></strong></a>?</p>
		<?php echo form_open("image/delete/",array("id"=>"image_delete_form")); ?>
			<input type="hidden" value="<?php echo $delete_id; ?>" name="delete_id" />
			<input type="submit" name="delete_sub" value="Yes, Delete '<?php echo $image_title; ?>'" />
			<input type="button" name="delete_back" value="No, take me back" onclick="javascript:history.go(-1);" />
		<?php echo form_close(); ?>
	</div>

	
</div>
<div class="clear">&nbsp;</div>
