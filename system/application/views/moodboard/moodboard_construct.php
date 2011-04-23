<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

   <title>Moodboard Concstruction - URIKA - Sharing Inspiration</title>


	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/layout.css" />
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/content.css" /> 
	<link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>assets/css/moodboard.css" />
   
   <?= $_styles ?>
  
</head>
<body id="mb">
<div id="header_wrapper">
	<div class="inner_wrap">
		<h1><a href="<?php echo base_url(); ?>" title="Return to Homepage" class="hidetext">URIKA - Homepage</a></h1>
		<div id="top_leftnav">
			<ul>
				<li class="save_button"><a href="#" onclick="saveMoodboard()">Save Moodboard</a></li>
				<li class="mb_dropdown menu_drop"  onclick="showColour()"><a href="#">Colour &amp; Grid</a></li>
				<li class="mb_dropdown menu_drop" onclick="showDetails()"><a href="#">Moodboard Details</a></li>
			</ul>
		</div>
		<a id="faqvid_mb" class="faqvid_link" href="<?php echo base_url(); ?>page/faq/" title="Stuck? Click here to read FAQs and see Video tutorials">Stuck? See our FAQs and Video Tutorials!</a>
	</div>
</div>
<div id="content_wrapper">
	<div id="moodboard">
		<div id="moodboard_area" style="width:700px;height:500px;" class="ui-droppable">
		
		</div>
	</div>
	<div id="side_panel">
		<div class="tabs ">
			<ul class="tabNav">
				<li><a href="#" title="Tab link">Images</a></li>
				<li><a href="#" title="Tab link">Layers</a></li>

			</ul>
			
			<div class="tabContentWrapper">
				<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<div id="objects">
						<p>Below is the list of images you can add to this moodboard. <br/><strong>Tip:</strong> Shift and drag the corner of the image to resize while maintaining aspect ratio.</p>
						<div id="objectsScroll">
						<?php
							echo $objects;
						?>
						</div>
						<div class="clear">&nbsp;</div>
					</div>
				</div>
				<div class="tabContent">
					<p>Use this tab to re-arrange and delete layers in the moodboard:</p>
					<ul id="layers">
				
					</ul>
					<div class="clear">&nbsp;</div>
				</div>
			</div>
		</div>
	</div>
 
		
		<div id="mb_colourgrid" class="menu_dropbox">
			<ul>
				<li>
					<p class="label">Background Colour</p>
					<div id="mb_colourwheel">
					
					</div>
				</li>
				<li>
					<input type="checkbox" name="toggleGrid" id="toggleGrid" value="1" checked="checked" /> Grid on/off
				</li>
				<li>
					<p class="label">Moodboard Width: <strong>700</strong></p>
					<div id="mbwidth"></div>

				</li>
				<li>
					<p class="label">Moodboard Height: <strong>500</strong></p>
					<div id="mbheight"></div>

				</li>
			</ul>
		</div>
		<div id="mb_details" class="menu_dropbox">
			<?php

				if($mb_id == false)
				{
					echo form_open('moodboard/save',array("id"=>"moodboard_form")); 
				}
				else
				{
					echo form_open('moodboard/editsave',array("id"=>"moodboard_form")); 
				}
			?>
			<fieldset class="simple">
			<ul>
				<li>
					<label for="mb_name">Moodboard Title * (required)</label>
					<input name="mb_name" id="mb_name" type="text" value="<?php echo $mb_title; ?>"  class="forceStyle" />
				</li>
				<li>
					<label for="mb_desc">Description * (required)</label>
					<textarea name="mb_desc" id="mb_desc" rows="5"  class="forceStyle"><?php echo $mb_desc; ?></textarea>
				</li>
				<li>
					<label for="mb_tags">Tags (comma separated)</label>
					<input name="mb_tags" id="mb_tags" type="text" class="forceStyle" value="<?php echo $mb_tags; ?>" />
				</li>
				<li class="hide">
					<input type="hidden" name="dataString" id="dataString" value='<?php echo $dataString; ?>' />
					<input type="hidden" name="mb_user_id" id="mb_user_id" value="<?php echo $mb_user_id; ?>" />
					<input type="hidden" name="mb_col_id" id="mb_col_id" value="<?php echo $mb_col_id; ?>" />
					<input type="hidden" name="mb_id" id="mb_id" value="<?php echo $mb_id; ?>" />
				</li>
			</ul>
			</fieldset>
			</form>
			</div>
		</div>
</div>
		   	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script> 
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/urika.js"></script> 
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/libs/facebox.js"></script> 
	 <?= $_scripts ?>
</body>

</html>