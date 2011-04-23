<div id="feature_title" class="relative">
	<h2><?php echo $title; ?></h2>
	<?php 
	$base_url = base_url(); 
	if($id == 10 || $id == 3)
	{
		echo '<a id="faqvid_feature_right" class="faqvid_link" href="'.$base_url.'page/faq/" title="Stuck? Click here to read FAQs and see Video tutorials">Stuck? See our FAQs and Video Tutorials!</a>';
	}
	else if($id == 7)
	{
		echo '<a id="faqvid_feature_right" class="faqvid_link" href="'.$base_url.'page/faq/" title="See out FAQ section">Looking for our FAQ section?</a>';
	}
	else if($id == 4)
	{
		echo '<a id="faqvid_feature_right" class="vid_link" href="'.$base_url.'page/tutorials/" title="Sees Video tutorials">Looking for our video tutorials?</a>';
	
	}
	?>
</div>
<div id="content_page">
	
		<?php echo $content; ?>
	

	
</div>
<div class="clear page_updated">Last Updated: <em><?php echo $updated; ?></em></div>
