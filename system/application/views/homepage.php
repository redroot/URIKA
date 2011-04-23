<div id="feature_title">
	<div class="homepage_left">
		
		<?php echo $feature_content; ?>
	
	</div>
		<?php if($this->session->userdata("is_logged_in") === FALSE)
		{
			echo '	<div id="home_flair"></div>';
		}
		?>
</div>
<div id="homepage_content">
		<div class="col-12 left">
			<div  class="tabs fullWidthTabs">
				<ul class="tabNav">
					<?php if($this->session->userdata("is_logged_in") == 1 && $following_list != "")
					{
						echo '	<li><a href="#" title="View uploads by those you follow"><strong>Following Feed</strong></a></li>';
					}
					?>

					<li><a href="#" title="View latest uploads and moodboards"><strong>Latest Inspiration</strong></a></li>
					<li><a href="#" title="View all time most popular uploads"><strong>Most Popular</strong></a></li>
				
				</ul>
				<div class="tabContentWrapper">
					<div class="clear">&nbsp;</div>
					<?php if($this->session->userdata("is_logged_in") == 1 && $following_list != "")
						{
							echo '<div class="tabContent">';
							echo $following_list;
							echo '</div>';
						}
					?>
					<div class="tabContent">
						<?php echo $latest_list; ?>
					</div>
					<div class="tabContent">
							<?php echo $views_list; ?>
					</div>
						
				</div>
			</div>	
		
		</div>
		<div class="col-4 right">
			<div  class="tabs fullWidthTabs">
				<ul class="tabNav">
				<?php 
					if($this->session->userdata("is_logged_in") == 1 && $following_list != "")
					{
						echo '<li><a href="#" title="Tab link"><strong>Following Activity</strong></a></li>';
					}
					else
					{
						echo '<li><a href="#" title="Tab link"><strong>Latest Activity</strong></a></li>';
					}
				?>
					
				</ul>
				<div class="tabContentWrapper">
					<div class="clear">&nbsp;</div>
					<div class="tabContent">
			<?php
				echo $activity_list;
			?>
				</div>
			</div>
		</div>
	</div>

	
</div>
<div class="clear">&nbsp;</div>
