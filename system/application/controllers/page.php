<?php
/**
	content page controller
**/
class Page extends Controller {

	function Page()
	{
		parent::Controller();	
	}
	
	function index()
	{
	
	}
	
	/*
		Displays a page matching the slug given
	
		@param slug : a string to match a page record
	*/
	function p($slug = null)
	{
		
		if($slug == null)
		{
			show_404();
		}
		else
		{
		
			$this->load->model("page_model");
			$page = $this->page_model->getPage($slug,"p_slug");
			
			if($page != false)
			{
				$page = $page->row();
				
				$data = array(
					"title" => $page->p_name,
					"content" => str_replace("##base_url##",base_url(),$page->p_content),
					"updated" => date("F j, Y, G:i",strtotime($page->p_updated)),
					"id" => $page->page_id
				);
				
				//clean up title
				if(strpos($page->p_name,"-") !== FALSE)
				{
					$ex = explode("-",$page->p_name);
					
					$data["title"] = trim($ex[0])." - <small>".trim($ex[1])."</small>";
				}
				
				$this->template->add_js("assets/js/tabs.js");
			
				$this->template->write("title",$page->p_name);
				
				$this->template->write_view("content","general/page",$data,TRUE);
				
				//now render templates
				$this->template->render();
			}
			else
			{
				show_404();
			}
		}
	}
	
	
}

/* End of file page.php */
/* Location: ./system/application/controllers/page.php */