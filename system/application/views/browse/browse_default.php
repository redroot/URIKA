<div id="feature_title">
	<h2>Browse <small>- explore the inspiration</small></h2>
</div>
<div id="browse_list_page">
		<form action="<?php echo base_url(); ?>browse/" method="get" id="browse_form" >
		<fieldset class="simple">
			<ul>
			
				<li>
					<label for="search">Search Term</label>
					<input type="text" name="search" id="search"  value="<?php echo $search; ?>" />
				</li>
			
				<li>
					<label for="tag">Tag</label>
					<input type="text" name="tag" id="tag"  value="<?php echo $tag; ?>" />
				</li>
				
				<li>
					<label for="type">Type</label>
					<select class="forceStyle" name="type" id="type">
						<option <?php if($type == "both") { echo "selected='selected'"; } ?> value="both">Uploads &amp; Moodboards</option>
						<option <?php if($type == "uploads") { echo "selected='selected'"; } ?> value="uploads">Uploads</option>
						<option <?php if($type == "moodboards") { echo "selected='selected'"; } ?> value="moodboards">Moodboards</option>
					</select>
				</li>
				
				<li>
					<label for="sort">Sort By</label>
					<select class="forceStyle" name="sort" id="sort">
						<option <?php if($order_by == "datetime") { echo "selected='selected'";  } ?> value="datetime">Date Added</option>
						<option <?php if($order_by == "views") { echo "selected='selected'";  } ?> value="views">Views</option>
						<option <?php if($order_by == "favs") { echo "selected='selected'"; } ?> value="favs">Favourites</option>
					</select>
				</li>
			
				<li class="submit_li">
								
					<input type="submit" name="browse_search" value="Search"  />
				</li>
	
			
			</ul>
			
		</fieldset>
		
	</form>
	<div>
		
				<div class="results_info" style="margin-bottom: 15px;">
					<p><?php echo $results_info; ?></p>
				</div>
				<?php echo $list_html; ?>
	
	</div>

	

	
</div>
<div class="clear">&nbsp;</div>
