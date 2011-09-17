<div id="feature_title">
	<h2>Edit Image - <?php echo $image->i_title; ?></h2>
</div>
<div id="image_edit">
	<?php echo form_open('image/edit/',array("id"=>"image_edit_form")); ?>
	
	<fieldset>
		<legend>Edit image details</legend>
		<p style="margin-bottom: 10px;">
			From here you can edit you image details, but at the moment the image and thumb cannot be modified
		
		</p>

		<ul>
		
			<li>
				<label for="edit_title">Title for your upload:</label>
				<input type="text" name="edit_title" id="edit_title" class="required" value="<?php echo $image->i_title; ?>"  />
			</li>
			<li>
				<label for="edit_website">URL of source website:</label>
				<input type="text" style="width: 250px;" name="edit_website" id="edit_website" class="required url" value="<?php echo $image->i_website; ?>" />
			</li>
			<li>
				<label for="edit_desc">Comments:</label>
				<textarea name="edit_desc" id="edit_desc" class="required" cols="30" rows="5"><?php echo html_entity_decode($image->i_description); ?></textarea>
			</li>
			<li>
				<label for="edit_tags">Tags:</label>
				<select type="text" name="edit_tags[]" multiple id="edit_tags" style="width: 350px;">
				<?php echo $tagsHTML; ?>
				</select>
			</li>
			<li>
				<input type="submit" name="edit_save" id="edit_save" value="Save Image" />
			</li>
		</ul>
		<input type="hidden" name="edit_tags_list" id="edit_tags_list" />
			<input type="hidden" name="edit_id" id="edit_id" value="<?php echo $image->image_id; ?>" />
	</fieldset>
	</form>
</div>
<div class="clear">&nbsp;</div>

