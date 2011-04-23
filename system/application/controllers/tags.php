<?php
/**
	Tags controller
**/
class Tags extends Controller {

	function Tags()
	{
		parent::Controller();	
		$this->load->model("image_model");
	}
	
	function index()
	{
		$q_vars = get_url_vars();
		$sort = (isset($q_vars->sort)) ? $q_vars->sort : "";	
		$search = (isset($q_vars->search_tag)) ? $q_vars->search_tag : "";
		$base = base_url();
		
		if($search != "")
		{
			$cache = cacheFetch("tagsCache");
		}
		else
		{
			$cache = cacheFetch("tagsPopularCache");
		}
		
		if(isset($cache["data"]) !== false)
		{
			$tags = json_decode($cache["data"]);
			
			if(is_array($tags) || !empty($tags))
			{
				// start the query_string
				$q_str = "";
				if($search != "")
				{
					$q_str = '?search_tag='.$search.'&';
				}
				else
				{
					$q_str = '?';
				}
				
				// sort links
				$sort_alpha = '<a href="'.$base.'tags/'.$q_str.'sort=sort-alpha-';
				$sort_total = '<a href="'.$base.'tags/'.$q_str.'sort=sort-total-';
			
				if($sort == "sort-alpha-desc")
				{
					quicksort($tags,"value");
					$tags = array_reverse($tags);
					
					$sort_alpha .= 'asc" title="List Alphabetically descending"><strong>List Alphabetically</strong></a>';
					$sort_total .= 'desc" title="List By Total descending"><strong>List By Total</strong></a>';
				}
				else if($sort == "sort-total-asc")
				{
					quicksort($tags,"total");
					$sort_alpha .= 'desc" title="List Alphabetically ascending"><strong>List Alphabetically</strong></a>';
					$sort_total .= 'desc" title="List By Total descending"><strong>List By Total</strong></a>';
				}
				else if($sort == "sort-total-desc")
				{
					quicksort($tags,"total");
					$tags = array_reverse($tags);
					$sort_alpha .= 'desc" title="List Alphabetically ascending"><strong>List Alphabetically</strong></a>';
					$sort_total .= 'asc" title="List By Total ascending"><strong>List By Total</strong></a>';
				}
				else
				{
					quicksort($tags,"value");
					$sort_alpha .= 'desc" title="List Alphabetically ascending"><strong>List Alphabetically</strong></a>';
					$sort_total .= 'desc" title="List By Total descending"><strong>List By Total</strong></a>';
				}
				
				
				// grab heighest total value
				$temp_tags = $tags;
				quicksort($temp_tags,"total");
				$temp_tags = array_reverse($temp_tags);
				$highest = $temp_tags[0]->total;

				
				// now sort
				$tags_count = count($tags);
				
				
				$list_html = "";
				$details_html = "";
				
				$sort_html = 'Sort:  '.$sort_alpha.' <span class="sep">|</span> '.$sort_total.' <span class="sep">|</span> <a href="'.$base.'tags/'.$q_str.'tag_cloud=1" title="View popular tags as tag cloud"><strong>View as Tag Cloud</strong></a>';
				
				if($search != "")
				{
					
					$details_html = '
					<ul class="search_filters">
						<li><a href="'.$base.'tags/" title="Remove this filter" class="remove_filter"><span class="hide">Remove</span></a> Showing tags containing "<strong>'.$q_vars->search_tag.'</strong>"</li>
						<li>'.$sort_html.'</li>
					</ul>';
				}
				else
				{
					$details_html = '<ul class="search_filters">
						<li>Showing popular tags</li>
						<li>'.$sort_html.'</li>
					</ul>';
				}
				
				if(isset($q_vars->tag_cloud))
				{
				
					// tag cloud mode
					
					// we'll have 8 font-sizes, ranging from 10-18
					// and therefore six colours
					
					$list_html .= '<div style="text-align: center;"><ul class="tag_cloud">';
					$max = 30;
					$min = 10;
					$colours = array(
						"10" => "#aaa",
						"11" => "#909090",
						"12" => "#909090",
						"13" => "#999",
						"14" => "#808080",
						"15" => "#808080",
						"16" => "#888",
						"17" => "#707070",
						"18" => "#707070",
						"19" => "#777",
						"20" => "#606060",
						"21" => "#606060",
						"22" => "#666",
						"23" => "#505050",
						"24" => "#505050",
						"25" => "#555",
						"26" => "#404040",
						"27" => "#404040",
						"28" => "#444",
						"29" => "#303030",
						"30" => "#333",
						
					);
					
					for($i = 0; $i < $tags_count; $i++)
					{
						if($search != "")
						{
							if(strpos($tags[$i]->value,$search) === FALSE)
							{
								continue;
							}
						}
						
						
						$font_size = (string) $min + round(($tags[$i]->total/$highest)*($max-$min));
						
						$list_html .= '<li>
							<a style="font-size: '.$font_size.'px; color: '.$colours[$font_size].';" href="'.$base.'browse/?tag='.str_replace(" ","+",$tags[$i]->value).'" title="view uploads with this tag">
								'.$tags[$i]->value.'
								<em>'.$tags[$i]->total.'</em>
							</a>
						</li>';
					}
					
					$list_html .= '</ul><div class="clear">&nbsp;</div></div>';
					
				}
				else
				{
				
					$list_html .= '<ul class="tag_list">';
					
					for($i = 0; $i < $tags_count; $i++)
					{
						if($search != "")
						{
							if(strpos($tags[$i]->value,$q_vars->search_tag) === FALSE)
							{
								continue;
							}
						}
						
						$perc = round(($tags[$i]->total/$highest)*100);
						
						$list_html .= '<li>
							<a href="'.$base.'browse/?tag='.str_replace(" ","+",$tags[$i]->value).'" title="view uploads with this tag">
								'.$tags[$i]->value.'
								<em>'.$tags[$i]->total.'</em>
							</a>
							<span style="width:'.$perc.'%;" class="bar">'.$perc.'%</span>
						</li>';
					}
					
					$list_html .= '</ul>';
				
				}
			}
			else
			{
				$list_html = "<ul><li>No tags yet</li></ul>";
			}
				

				// template stuff
				$data = array (
					"list_html" => $list_html,
					"details_html" => $details_html,
					"search" => $search,
				);
				
				$this->template->write("title","Tags");
				
				$this->template->write_view("content","browse/tags",$data);
				
				$this->template->render();
		}
		else
		{
			redirect("","location");
		}
	}
	
	
}

/* End of file tags.php */
/* Location: ./system/application/controllers/tags.php */