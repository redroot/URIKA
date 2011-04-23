<div id="feature_title">
	<h2>Tags <small>- View popular tags and search for tags</small></h2>
</div>
<div id="tags_list_page">
	<form action="<?php echo base_url(); ?>tags/" method="get" id="tags_search_form" >
		<fieldset class="simple">
			<input type="text" name="search_tag" id="search_tag" class="left"  value="<?php echo $search; ?>" />
		
			<input type="submit" name="tags_search" value="Search All Tags" class="left" />
			<div class="tags_sort_links">
				<?php echo $details_html; ?>
			</div>
		</fieldset>
		
	</form>
	<div class="kill">&nbsp;</div>
	<div>
	
	
			<?php echo $list_html; ?>

	</div>
</div>
<div class="clear">&nbsp;</div>
