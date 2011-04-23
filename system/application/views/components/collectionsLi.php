<li id="collection_<?php echo $collection_id; ?>" <?php echo $style; ?>>
	<a href="<?php echo $collection_url; ?>" title="view collection" style="display: block;">
		<span class="collection_left">
			<span class="collection_name"><?php echo $collection_name; ?></span>
			<span class="collection_data">A collection of <strong><?php echo $collection_image_count; ?></strong> by <strong><?php echo $collection_user; ?></strong></span>
			<em class="collection_update">Last Updated: <?php echo $collection_update; ?></em>
		</span>
		<span class="collection_right">
			<?php echo $collection_images; ?>
		</span>
		
		<span class="clear">&nbsp;</span>
	</a>
</li>