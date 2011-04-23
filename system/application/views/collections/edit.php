<div id="feature_title">
	<h2>Edit Collection: <?php echo $collection->col_name; ?></h2>
</div>
<div id="collection_edit">
	<?php echo form_open('collection/edit/',array("id"=>"collection_edit_form")); ?>
		<fieldset>
			<legend>Collection Details</legend>
			<ul>
				<li>
					<label for="col_name">Title for your upload:</label>
					<input type="text" name="col_name" id="col_name" class="required" value="<?php echo $collection->col_name; ?>"  />
				</li>
			</ul>
		</fieldset>
		<fieldset>
			<legend>Collection Images</legend>
			<?php
			
			if($images_html != "<p class='info'>No Images In Collection</p>")
			{
			
			echo '
			<p class="info">
			Click on an image to remove it from your collection. <strong>Remember to click  \'Save Collection\' at the end otherwise, the changes won\'t be saved</strong>
			</p><br class="clear"/>
			';
			}
			?>
			<div id="edit_col_images">
				<?php echo $images_html; ?>
				<div class="clear">&nbsp;</div>
			</div>
			<input type="hidden" id="col_string_values" name="col_string_values" value="<?php echo $collection->col_string; ?>" />
			<input type="hidden" id="col_edit_id" name="col_edit_id" value="<?php echo $collection->collection_id; ?>" />
			<ul>
				<li>
					<input type="submit" name="edit_save" id="edit_save" value="Save Collection" />
				</li>
			</ul>
			
		</fieldset>
	<?php echo form_close(); ?>
	
</div>
<div class="clear">&nbsp;</div>
