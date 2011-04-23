<div id="feature_title">
	<img src="<?php echo $image_url; ?>" id="profile_img" width="70" height="70" alt="user avatar" />
	<?php 
	echo $follow_section;
	?>
	<h2><a href="<?php echo base_url(); echo 'user/u/'.$username; ?>" title="link for this profile"><?php echo $username; ?></a></h2>
	<p class="u_info_string"><?php echo $info_string; ?></p>
	<div class="clear">&nbsp;</div>
</div>
<div id="user_profile" class="bumpUp">
	<div class="left col-12">
	<?php
	$q_vars = get_url_vars();

if(isset($q_vars->image_deleted))
{
	echo '<p class="success" style="margin-bottom: 10px;">Image deleted successfully!</p>';
}

if(isset($q_vars->collection_deleted))
{
	echo '<p class="success" style="margin-bottom: 10px;">Collection deleted successfully!</p>';
}

if(isset($q_vars->moodboard_deleted))
{
	echo '<p class="success" style="margin-bottom: 10px;">Moodboard deleted successfully!</p>';
}

	?>
		<div id="userImagesTabs" class="tabs fullWidthTabs">
			<ul class="tabNav">
				<li><a href="#" title="Tab link"><strong><?php echo $user_count["images"]; ?></strong> Upload<?php if($user_count["images"] != 1){ echo 's'; } ?></a></li>
				<li><a href="#" title="Tab link"><strong><?php echo $user_count["mbs"]; ?></strong> Moodboard<?php if($user_count["mbs"] != 1){ echo 's'; } ?></a></li>
				<li><a href="#" title="Tab link"><strong><?php echo $user_count["favs"]; ?></strong> Favourite<?php if($user_count["favs"] != 1){ echo 's'; } ?></a></li>
				<li><a href="#" title="Tab link"><strong><?php echo $user_count["collections"]; ?></strong> Collection<?php if($user_count["collections"] != 1){ echo 's'; } ?></a></li>
				<li class="clear">&nbsp;</li>
			</ul>
			
			<div class="tabContentWrapper">
			<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<?php
						echo $output["images"];
					?>
				</div>
				<div class="tabContent">
					<?php
						echo $output["moodboards"];
					?>
				</div>
				<div class="tabContent">
					<?php echo $output["favourites"]; ?>
				</div>
				<div class="tabContent">
					<?php
						echo $output["collections"]; 
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-4 right">
		<div class="tabs fullWidthTabs">
			<ul class="tabNav">
				<li><a href="#" title="Tab link"><strong><?php echo $user_count["followedby"]; ?></strong> Follower<?php if($user_count["followedby"] != 1){ echo 's'; } ?></a></li>
				<li><a href="#" title="Tab link">Following <strong><?php echo $user_count["follows"]; ?></strong></a></li>
				<li class="clear">&nbsp;</li>
			</ul>
			<div class="tabContentWrapper">
				<div class="clear">&nbsp;</div>
				<div class="tabContent">
					<?php echo $output["followedby"]; ?>
				</div>
				<div class="tabContent">
					<?php echo $output["follows"]; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="clear">&nbsp;</div>
