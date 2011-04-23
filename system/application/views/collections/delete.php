<div id="feature_title">
	<h2>Delete Collection</h2>
</div>
<div id="collection_delete">
	<div class="borderbox">
		<p>Are you sure you want to delete the collection <a href="<?php echo $collection_url; ?>"><strong><?php echo $collection_title; ?></strong></a>? This will also delete all associated moodboards!</p>
		<?php echo form_open("collection/delete/",array("id"=>"collection_delete_form")); ?>
			<input type="hidden" value="<?php echo $delete_id; ?>" name="delete_id" />
			<input type="submit" name="delete_sub" value="Yes, Delete '<?php echo $collection_title; ?>'" />
					<input type="button" name="delete_back" value="No, take me back" onclick="javascript:history.go(-1);" />
		<?php echo form_close(); ?>
	</div>

	
</div>
<div class="clear">&nbsp;</div>
