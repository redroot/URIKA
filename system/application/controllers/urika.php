<?php
/**
	Homepage controller
**/
class Urika extends Controller {

	function Urika()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$this->load->model("user_model");
		$this->load->model("image_model");
		$this->load->model("general_model");
		$this->load->model("activity_model");
		$per_page = 20;
		$base = base_url();
		
		$is_signed_in = ($this->session->userdata("is_logged_in") == 1) ? true : false;
		
		$user = false;
		if($is_signed_in)
		{
			$user = $this->user_model->getUser($this->session->userdata("user_id"))->row();
		}
		// header content up here
		
		if(!$is_signed_in)
		{
			$feature_content = '
				<div class="guestinfo">
		<h2>Share. Create. Inspire.</h2>
		<p>The best place on the web for creatives to share inspiration. Upload and crop images, tag with categories, create moodboards and much more.</p>
		<div class="buttons">
			<a href="'.$base.'page/about/" id="home_about_link" title="Learn more about UR!KA"><span class="hide">Learn More</span></a>
			<a href="'.$base.'user/signup/" id="home_signup_link" title="Sign up for UR!KA"><span class="hide">Join UR!KA</span></a>
		</div>
			</div>';
		}
		else
		{
			$welcome = array(
				"Welcome",
				"Hi there",
				"Ahoy,",
				"Hello",
				"Ah,"
			);
		
			$feature_content = '
			<div class="userinfo">
				<h2>'.$welcome[array_rand($welcome)].' <a href="'.$base.'user/u/'.$user->u_username.'/" title="Your profile">'.$user->u_username.'</a>!</h2>
				<p>
				No doubt you\'ve either found something stunning to share, or you\'re looking for that little something special. Good to have you back!</p>
				</p>
				<div class="buttons">
					<a href="'.$base.'page/about/" id="home_about_link" title="Learn more about UR!KA"><span class="hide">Learn More</span></a>
					<a href="'.$base.'page/faq/" id="home_faq_link" title="Learn how to use the site"><span class="hide">View FAQS and Tutorials</span></a>
				</div>
			</div>
			';
			
		}
		
		// body content here
		$following = null;
		//get following
			$followed_string = "";
		
		if($is_signed_in)
		{
			// get latest images by date, view, following
			// function needed for follow
			
			$latest = $this->general_model->searchAll("","","both",0,$per_page,"datetime","DESC"); 
			$by_views = $this->general_model->searchAll("","","both",0,$per_page,"views","DESC");
			
			
			//get following
			$followed_string = "";
			
			// grab records id
			$res = $this->user_model->getUserFollows($user->user_id);
			
			if($res["count"] > 0)
			{
				for($i = 0; $i < $res["count"]; $i++)
				{
					if($res["result"][$i]->f_followed != "")
						$followed_string .= $res["result"][$i]->f_followed.',';
				}
				$followed_string = substr($followed_string,0,-1);
			}
			
			if($followed_string != "")
			{
				$followed_string = 'followIN#'.$followed_string.'';
				$following = $this->general_model->searchAll("",$followed_string,"both",0,$per_page,"datetime","DESC");
				
				$following = $following["results"];
			}
		
			
			// latest activity - folllowed			
			$activity = $this->activity_model->getLatestActivity(0,10,$this->session->userdata("user_id"));
		
		}
		else
		{
			// get images by date, view
			
			$latest = $this->general_model->searchAll("","","both",0,$per_page,"datetime","DESC"); 
			$by_views = $this->general_model->searchAll("","","both",0,$per_page,"views","DESC"); 
			
			
			// get latest activity
			$activity = $this->activity_model->getLatestActivity(0,10);
			
		}
		
		$latest_html = "";
		$views_html = "";
		$following_list = "";
		$activity_list = "";
		$count = count($latest["results"]);
		// sort latest_html
		if($count > 0 && isset($latest["results"][0]))
		{
			$out = '<ul class="uploadList">';
			
					
			for($i = 0; $i < $count; $i++)
			{
				
					if($latest["results"][$i]->type == "image")
					{
						$row_url = $base.'image/view/'.$latest["results"][$i]->row_id.'/';
						$overlay = $base.'assets/images/layout/uploadListOverlay.gif';
						$view = "components/uploadListLi";
					}
					else
					{
						$row_url = $base.'moodboard/view/'.$latest["results"][$i]->row_id.'/';
						$overlay = $base.'assets/images/layout/mbListOverlay.gif';
						$view = "components/mbListLi";
					}
					
					// parse object
					// parse Moodboard
					$data = array(
						"thumb_url" => $latest["results"][$i]->row_thumb_url,
						"title" => $latest["results"][$i]->row_title,
						"url" => $row_url,
						"user_url" => $base.'user/u/'.$latest["results"][$i]->u_username.'/',
						"username" => $latest["results"][$i]->u_username,
						"views" => $latest["results"][$i]->views,
						"overlay" => $overlay
					);
					
					$out .= $this->load->view($view,$data,true);

			}
			
			$out .= '</ul>';
			
			if((int) $latest["total"] > 20)
			{
				$out .= '
				<div class="pagination_buttons clear"><a class="view_more" href="'.$base.'browse?sort=datetime&page=2" title="View more by upload date">View More \'Latest\'</a></div>';
			}
			
			
		}
		else
		{
			$out = "No results";
		}
		$latest_html = $out;
		
		// sort views_html
		$count = count($by_views["results"]);
		if($by_views["total"] > 0 && isset($by_views["results"][0]))
		{
			$out = '<ul class="uploadList">';
			
					
			for($i = 0; $i < $count; $i++)
			{
				
				if($by_views["results"][$i]->type == "image")
				{
					$row_url = $base.'image/view/'.$by_views["results"][$i]->row_id.'/';
					$overlay = $base.'assets/images/layout/uploadListOverlay.gif';
					$view = "components/uploadListLi";
				}
				else
				{
					$row_url = $base.'moodboard/view/'.$by_views["results"][$i]->row_id.'/';
					$overlay = $base.'assets/images/layout/mbListOverlay.gif';
					$view = "components/mbListLi";
				}
				
				// parse object
				// parse Moodboard
				$data = array(
					"thumb_url" => $by_views["results"][$i]->row_thumb_url,
					"title" => $by_views["results"][$i]->row_title,
					"url" => $row_url,
					"user_url" => $base.'user/u/'.$by_views["results"][$i]->u_username.'/',
					"username" => $by_views["results"][$i]->u_username,
					"views" => $by_views["results"][$i]->views,
					"overlay" => $overlay
				);
				
				$out .= $this->load->view($view,$data,true);
				
			
				
			}
			
			$out .= '</ul>';
			
			if((int) $by_views["total"] > 20)
			{
				$out .= '<div class="pagination_buttons clear"><a class="view_more" href="'.$base.'browse?sort=views&page=2" title="View more by views">View More \'Popular\'</a></div>';
			}
			
		}
		else
		{
			$out = "No results";
		}
		$views_html = $out;
		
		// sort following_html
		if($following != null || $followed_string != "")
		{
			if(count($following) > 0 && isset($following[0]))
			{
				$out = '<ul class="uploadList">';
				$count = count($following);
						
				for($i = 0; $i < $count; $i++)
				{
					
					if($following[$i]->type == "image")
					{
						$row_url = $base.'image/view/'.$following[$i]->row_id.'/';
						$overlay = $base.'assets/images/layout/uploadListOverlay.gif';
						$view = "components/uploadListLi";
					}
					else
					{
						$row_url = $base.'moodboard/view/'.$following[$i]->row_id.'/';
						$overlay = $base.'assets/images/layout/mbListOverlay.gif';
						$view = "components/mbListLi";
					}
					
					// parse object
					// parse Moodboard
					$data = array(
						"thumb_url" => $following[$i]->row_thumb_url,
						"title" => $following[$i]->row_title,
						"url" => $row_url,
						"user_url" => $base.'user/u/'.$following[$i]->u_username.'/',
						"username" => $following[$i]->u_username,
						"views" => $following[$i]->views,
						"overlay" => $overlay
					);
					
					$out .= $this->load->view($view,$data,true);
					
				}
				
				$out .= '</ul>';
				
			}
			else
			{
				$out = "No results. You need to follow other users first.";
			}
		}
		else
		{
			$out = "";
		}
		$following_html = $out;
		
		// activity list
		// sort activity html
		if(count($activity) > 0 && isset($activity[0]))
		{
			$out = '<ul class="activity_list">';
			$count = count($activity);
					
			for($i = 0; $i < $count; $i++)
			{
				
				
				$out .= '<li class="activity_'.$activity[$i]->n_type.'">'.$activity[$i]->n_html.'</li>';
				
			
				
			}
			
			$out .= '</ul>';
			
		}
		else
		{
			$out = "No results";
		}
		$activity_html = $out;
		
		$body_id = "homepage";
		if($is_signed_in) $body_id = "user_home";
		
		$data = array(
			"latest_list" => $latest_html,
			"views_list" => $views_html,
			"following_list" => $following_html,
			"activity_list" => $activity_html,
			"feature_content" => $feature_content
		);
		
		$this->template->add_js("assets/js/tabs.js");
		
		$this->template->write("title","Homepage");
		$this->template->write("body_id",$body_id,TRUE);
		
		$this->template->write_view("content","homepage",$data,TRUE);
		
		//now render templates
		$this->template->render();
	}

	
	
}

/* End of file urika.php */
/* Location: ./system/application/controllers/urika.php */