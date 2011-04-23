<div id="feature_title">
	<h2>Users <small>- View and search UR!KA users</small></h2>
</div>
<div id="tags_list_page">
	<form action="<?php echo base_url(); ?>user/ulist/" method="get" id="user_search_form">
		<fieldset class="simple">
				
			<input type="text" name="search" id="searchText" class="left"  value="<?php echo $search; ?>" />
			<?php echo $extra_params; ?>
			<input type="submit" name="user_search" class="left" value="<?php echo $search_text; ?>" />
			<?php echo $filters_html; ?>
					
		</fieldset>
	</form>
	<div>
		
		
				<?php echo $table_html; ?>
	
	</div>
	

	
</div>
<div class="clear">&nbsp;</div>
