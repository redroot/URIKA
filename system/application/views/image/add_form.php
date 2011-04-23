<div id="feature_title" class="relative">
	<h2>Upload an Image- <small> add to the inspiration</small></h2>
	<a id="faqvid_feature_right" class="faqvid_link" href="<?php echo base_url(); ?>page/faq/" title="Stuck? Click here to read FAQs and see Video tutorials">Stuck? See our FAQs and Video Tutorials!</a>
</div>
<div id="image_add">
	<?php echo form_open('image/add/',array("id"=>"image_add_form")); ?>
	
	<fieldset>
		<legend>1: Choose your image</legend>
		<div id="upload_box">
			
		</div>
		<p class="info">Once your image is uploaded you can choose to crop it. The maximum file size is 3mb. <span style="color: #f00;">Please wait a few seconds once the upload appears to be completed for it to actually appear, sometimes the server takes a while to respond.</span></p>
		
	
		
			<div id="crop_instructions" class="borderbox hide">
			<p><strong>Image Information:</strong><br/></p>
			<div class="new_image_controls">Current image dimensions are: <strong>H: <span class="h">900px</span>, W: <span class="w">900px</span></strong> | Resize Image: <strong id="size_val">100%</strong> <div id="size_slider"></div></div>
			<p>You can crop the image simply by clicking and dragging. Once you are happy with you crop, click "Save" to lock the crop</p>
			<p class="error hide">The image is currently too big or small to save. Please crop it to less than 900x900 pixels and bigger than 100x100 pixels.</p>
			
			<input type="button" name="add_save_crop" id="add_save_crop" value="Save Image" />
		</div>
		<div id="preview_box" class="hide">
			<p id="preview_msg">No Preview</p>
			<p id="crop_notice">Loading Image ...</p>
			<input type="button" name="add_save_crop_box" id="add_save_crop_box" value="Save Image" />
			<span id="crop_dims">1x1</span>
			<span id="crop_error">The image is currently too big or small to save, please crop it to less than 900x900 pixels and bigger than 100x100 pixels</span>
		</div>
		<div id="result_box" class="hide">
		
		</div>
		
		<input type="hidden" name="add_filename_temp" id="add_filename_temp" />
		<input type="hidden" name="add_filename_dims" id="add_filename_dims" />
		
		<div class="kill">&nbsp;</div>
	</fieldset>
	<fieldset style="display: none;" class="afterUpload">
		<legend>2: Details</legend>
		<ul>
			<li>
				<label for="add_title">Title for your upload * (required):</label>
				<input type="text" name="add_title" id="add_title" class="required"  />
			</li>
			<li>
				<label for="add_website">URL of source website * (required):</label>
				<input type="text" style="width: 250px;" name="add_website" id="add_website" class="required url" value="http://"  />
			</li>
			<li>
				<label for="add_desc">Description * (required):</label>
				<textarea name="add_desc" id="add_desc" class="required" cols="30" rows="5"></textarea>
			</li>
			<li>
				<label for="add_tags">Tags to describe your upload e.g. header, navigation, red. <strong>(press enter to select tag)</strong>:</label>
				<input type="text" name="add_tags" id="add_tags" style="width: 350px;"  />
			</li>
			<?php
				if($col_element != "")
				{
					echo $col_element;
				}
			?>
			<li>
				<input type="checkbox" name="add_terms" id="add_terms" value="1" class="required" />  By ticking this box I agree that this upload is an example of web design and is in agreement with the <a href="<?php echo base_url(); ?>page/terms/"><strong>terms and conditions</strong></a> of this website.
			</li>
		</ul>
		<input type="hidden" name="add_tags_list" id="add_tags_list" />
	</fieldset>
	<fieldset style="display: none;" class="afterUpload">
		<legend>3: Upload</legend>
		<ul>
			<li>
				<input type="submit" name="add_save" id="add_save" value="Save and Upload!" />
			</li>
		</ul>
	</fieldset>
	</form>
</div>
<div class="clear">&nbsp;</div>

