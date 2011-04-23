<?php

class batcave extends Controller {

	function batcave()
	{
		parent::Controller();	
		$this->load->model("general_model");
	}
	
	function index()
	{
	
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		// list table fields
		$this->template->set_template("urika_admin");
		
		$out = "<strong>Welcome to the batcave!</strong>";
		
		$out .= $this->_tableSummaryHTML();
		
		
		$this->template->add_css("assets/css/admin.css");
			
		$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
			
		$this->template->write("feature","<h2>Admin Dashboard</h2>");
		$this->template->write("content",$out);
		$this->template->render();
			
			// set back to defaut
		$this->template->set_template("urika");
	}
	
	/*
		lists all rows in a table, 20 at a time
	*/
	function table($table_name)
	{
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		/// set template dynamically
		$this->template->set_template("urika_admin");
		
		
		$out = "";
		$base = base_url();
		
		if($table_name == "" || $table_name == null)
		{
			$out = "No table selected";
		}
		else
		{
		
			
			/*
				Sort query string from server styling			
			*/
			$q_vars = get_url_vars();
			
			$out = "";
			
			//succes messsage
			if(isset($q_vars->saved) == true)
			{
				$out .= '<p class="success">Record id #'.$q_vars->saved_id.' saved!</p>';
			}
			
			//delete message
			if(isset($q_vars->delete) == true)
			{
				$out .= '<p class="success">Record id #'.$q_vars->delete_id.' deleted!</p>';
			}
			
			
			
			// grab details + results
			
			$fields = $this->db->field_data($table_name);
			
			$order_by = (isset($q_vars->sort)) ? $q_vars->sort : "";
			$order_dir = (isset($q_vars->sort_dir)) ? strtoupper($q_vars->sort_dir) : "";
			$per_page = 20;
			$offset = (isset($q_vars->page)) ? ($q_vars->page-1)*$per_page : 0;
			$current_page = (isset($q_vars->page)) ? ($q_vars->page) : 1;
			$search_field = (isset($q_vars->search_field)) ? $q_vars->search_field : null;
			$search_val = (isset($q_vars->search_val)) ? ($q_vars->search_val) : null;
			
			
			
			$result = $this->general_model->getRecords($table_name,$offset,$per_page,$order_by,$order_dir,$search_field,$search_val);
			
			$total_count = $this->general_model->countRecords($table_name,$search_field,$search_val); 
			
	
			
			if($total_count < $per_page)
			{
				$limit_start = 1;
				$limit_end = $total_count; // if less than per page records in this table, show total count
			}
			else // otherwise show current total
			{
				$limit_start = ($current_page == 1) ? 1 : (($current_page-1) * $per_page)+1;
				
				if((($current_page)*$per_page) < $total_count)
				{
					$limit_end = ($current_page)*$per_page;
				}
				else
				{
					$limit_end = $total_count;
				}
			}
			
			/*
				Generate search form
			*/
			$current_url = $base.'batcave/table/'.$table_name.'/';
			$search_form = '
				<div>
				<input type="button" name="showSearch" id="showSearch" value="Show Search Form" />
				<form id="admin_search_form" action="'.$current_url.'" method="get">
					<fieldset>
						<legend>Search Form</legend>
						<ul>
							<li>
								<label>Field to search:</label>
								<select name="search_field">
				';
				
			// list fields
			foreach($fields as $field)
			{
				$search_form .= '
								<option value="'.$field->name.'">'.$field->name.'</option>
				';
			}
				
			$search_form .= '
								</select>
								<input type="text" name="search_val" />
							</li>
							<li>
								<input type="submit" name="search_submit" value="Search '.$table_name.'" />
							</li>
						</ul>
					</fieldset>
				</form>
				</div>
			';
			
			$search_text = "";
			if($search_field != null)
			{
				$search_text = 'Searched <strong>'.$q_vars->search_field.'</strong> for <strong>'.$q_vars->search_val.'</strong>';
			}
			
			/*
				pagination fun
			*/
			$pagination = "";
			
			if($total_count > $per_page)
			{
				$pagination = '<div class="admin_pagination right">';
				
				$base_link = $base.'batcave/table/'.$table_name.'/';
				
				// compile other params
				$extra_params = "";
				if($search_field != null)
				{
					$extra_params .= '&search_field='.$q_vars->search_field.'&search_val='.$q_vars->search_val;
				}
				
				if($order_by != "")
				{
					$extra_params .= '&sort='.$q_vars->sort.'&sort_dir='.$q_vars->sort_dir;
				}
				
				$total_pages = ceil($total_count/$per_page);
				
				// previous page
				if($current_page != 1)
				{
					$p_page = $current_page - 1;
					$pagination .= '<a title="previous page" href="'.$base_link.'?page='.$p_page.$extra_params.'">Back</a> | ';
				}
				
				$pagination .= 'Page <strong>'.$current_page.'</strong> of <strong>'.$total_pages.'</strong>';
				
				// next page link
				if($current_page != $total_pages)
				{
					$n_page = $current_page + 1;
					$pagination .= ' | <a title="previous page" href="'.$base_link.'?page='.$n_page.$extra_params.'">Next</a> ';
				}
				
				$pagination .= '</div>';
			}
			
			
			$out .= '<div class="borderbox">
						'.$pagination.'
						<p>Showing <strong>'.$limit_start.'-'.$limit_end.'</strong> of <strong>'.$total_count.'</strong> records in this table. '.$search_text.'</p>
						'.$search_form.'
						
					</div>';
			
			
			
			$out .= '<table class="admin_table">
						<tr>';
						
			foreach($fields as $field)
			{
				$sort_dir = "asc";
				$class = '';
				
				$field_name = $field->name;
				
				if(isset($q_vars->sort_dir))
				{
					if($q_vars->sort == $field_name  && $q_vars->sort_dir == "asc")
					{
						$sort_dir = "desc";
					}
					
					if($q_vars->sort == $field_name)
					{
						$class = "sorting";
					}
				}
				
				// extra params
				// need to retain search parameters
				$extra_params = "";
				if($search_field != null)
				{
					$extra_params .= '&search_field='.$q_vars->search_field.'&search_val='.$q_vars->search_val;
				}
				
				$out .= '<th class="sort '.$class.' sort_'.$sort_dir.'"><a href="'.$base.'batcave/table/'.$table_name.'/?sort='.$field_name.'&sort_dir='.$sort_dir.$extra_params.'" title="Sort by this field">'.$field_name.'</a></th>';
			}
			
			// edit/delete buttons
			$out .= '<th>Record Controls</th>';
			
			$out .= '</tr>';
			
	
			
			
			
			if($result != false)
			{
				// now loop through, chucking out fields
				$record_count = count($result);
				
				for($i = 0; $i < $record_count; $i++)
				{
					if($i%2 == 1)
						$class = "odd";
					else
						$class = "even";
					
					
					$out .= '<tr class="'.$class.'">';
					
					$id = 0;
					
					foreach($fields as $field)
					{
						$field_name = $field->name;
						
						if($field->primary_key == 1)
						{
							$id_field = $field->name;
							$id = $result[$i]->$id_field;
						}
						
						if($field->type == "blob")
						{
							$result[$i]->$field_name = ''.substr(htmlentities($result[$i]->$field_name),0,500).'';
						}
						
						$out .= '<td>'.$result[$i]->$field_name.'</td>';
					}
					
					$out .= '<td><a class="edit_link" href="'.$base.'batcave/edit/'.$table_name.'/'.$id.'/">Edit Record</a><a class="delete_link" href="'.$base.'batcave/delete/'.$table_name.'/'.$id.'/">Delete Record</a> </td>';
					
					$out .= '</tr>';
				}
			}
			else
			{
				$out = '<td>No records</td>';
			}
			
			// end db query stuff
			
			$out .= '</table>';
			
			$this->template->add_css("assets/css/admin.css");
			
			$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
			
			$this->template->write("feature","<h2>Table - ".$table_name."</h2>");
			$this->template->write("content",$out);
			$this->template->render();
			
			// set back to defaut
			$this->template->set_template("urika");
		}
	}
	
	/*
		Lets you edit a record from a specified table
		
		@param table_name : name of the table to access
		@param id : id to search for in primary field
	*/
	function edit($table_name = null,$id = null)
	{
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		if($table_name == null && $id == null)
		{
			if($this->input->post("record_id") !== FALSE)
			{
				$post_array = $_POST;
				$record_info = array(
					"field" => $this->input->post("record_field"),
					"id" => $this->input->post("record_id"),
					"table" => $this->input->post("record_table")
				);
				
				unset($post_array["record_id"]);
				unset($post_array["record_field"]);
				unset($post_array["record_table"]);
				unset($post_array["submit"]);
				
				// now set data
				
				$update_data = array();
				
				foreach($post_array as $name => $value)
				{
					$f_name = str_replace("edit_","",$name);
					
					if($f_name != "submit")
					{
						$update_data[$f_name] = trim($this->input->post($name));
					}
				}
				
				
				
				$update = $this->general_model->updateRecord($record_info["table"],$record_info["field"],$record_info["id"],$update_data);
				
				if($update == false)
				{
					echo "Something went wrong with the update";
				}
				else
				{
					redirect('batcave/table/'.$record_info["table"].'/?saved=1&saved_id='.$record_info["id"],'location');
				}
			}
			else
			{
				redirect('batcave','location');
			}
		}
		else
		{
			
			$this->template->set_template("urika_admin");
			
			$fields = $this->db->field_data($table_name);
			
			// grab record
			$record = $this->general_model->getRecord($table_name,$id);
			$base = base_url();

			// loop through fields and fill in details accordingly
			
			$out = '<form action="'.$base.'batcave/edit/"  method="post">
					<fieldset>
						<legend>Edit \''.$table_name.'\' record</legend>
							<ul>
						';
			foreach($fields as $field)
			{
				$field_name = $field->name;
				if($field->primary_key == 1)
				{
					$out .= '
					<input type="hidden" name="record_id" id="record_id" value="'.$record->$field_name.'" />
					<input type="hidden" name="record_field" id="record_field" value="'.$field_name.'" />
					';
				}
				else
				{
					$out .= '
							<li>
								<label for="edit_'.$field_name.'">'.$field_name.' (type: '.$field->type.')</label>
								';
					//deal with different types
					if($field->type == "blob") // text area
					{
						$out .= '
							<textarea rows="10" cols="50" name="edit_'.$field_name.'">'.$record->$field_name.'</textarea>
						';
					}
					else // normal input for now
					{
						$out .= '
							<input type="text" name="edit_'.$field_name.'" value="'.$record->$field_name.'" />
						';
					}
					
					$out .= '
						</li>
						';
				}
			}
			
			
			$out .= '
			<li><input type="submit" name="edit_submit" value="Save Record" /></li>
			<input type="hidden" name="record_table" value="'.$table_name.'" />
			</ul></fieldset></form>';
			
			
			$this->template->add_css("assets/css/admin.css");
				
			$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
				
			$this->template->write("feature","<h2>Edit '".$table_name."' Record #".$id." </h2>");
			$this->template->write("content",$out);
			$this->template->render();
				
				// set back to defaut
			$this->template->set_template("urika");
		}
	}
	
	/*
		var models
		
		contains the model to run the delete from if iits set
		other delete the record straight
	*/
	
	var $delete_models = array(
		"images" => array(
			"model" => "image_model",
			"function" => "deleteImage"
		),
		"comments" => array(
			"model" => "comment_model",
			"function" => "deleteComment"
		),
		"users" => array(
			"model" => "user_model",
			"function" => "deleteUser"
		),
		"moodboard" => array(
			"model" => "moodboard_model",
			"function" => "deleteMoodboard"
		)
	);
	
	/*
		Delete function which simply deletes the records specified
		
		@param table_name : table to delete from
		@param record_id : id to delete
	*/
	function delete($table_name = null,$id = null)
	{
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		if($table_name == null)
		{
			if($this->input->post("record_table") !== FALSE)
			{
				$delete = false;
				
				if(isset($this->delete_models[$this->input->post("record_table")])) // delete 
				{
					$model = $this->delete_models[$this->input->post("record_table")];
					
					// run delete function as specified
					$this->load->model($model["model"]);
					
					$rtn = $this->$model["model"]->$model["function"]($this->input->post("record_id"));
					$delete = $rtn;
				}
				else // delete as normal
				{
				
					$delete = $this->general_model->deleteRecord($this->input->post("record_table"),$this->input->post("record_field"),$this->input->post("record_id"));
				
				}
				
				if($delete == false)
				{
					echo "Something went wrong with the delete";
				}
				else
				{
					redirect('batcave/table/'.$this->input->post("record_table").'/?delete=1&delete_id='.$this->input->post("record_id"),'location');
				}
			}
			else
			{
				redirect('batcave/','location');
			}
		}
		else
		{
			// display page
			$this->template->set_template("urika_admin");
			$base = base_url();
			$fields = $this->db->field_data($table_name);
			
			$record_field = "";
			foreach($fields as $field)
			{
				if($field->primary_key == 1)
				{
					$record_field = $field->name;
				}
			}
			
			$out = '
						<form action="'.$base.'batcave/delete/"  method="post">
						<fieldset>
							<legend>Delete \''.$table_name.'\' record</legend>
							<input type="hidden" name="record_table" id="record_field" value="'.$table_name.'" />
							<input type="hidden" name="record_id" id="record_id" value="'.$id.'" />
							<input type="hidden" name="record_field" id="record_field" value="'.$record_field.'" />
								<ul>
									<li><p>Are you sure you want to delete record <strong>#'.$id.'</strong> from table <strong>'.$table_name.'</strong></p></li>
									<li><input type="submit" name="edit_submit" value="Delete!" /></li>
								</ul>
							</legend>
						</fieldset>
						</form>
				
						';
			$this->template->add_css("assets/css/admin.css");
				
			$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
				
			$this->template->write("feature","<h2>Delete '".$table_name."' Record #".$id." </h2>");
			$this->template->write("content",$out);
			$this->template->render();
				
				// set back to defaut
			$this->template->set_template("urika");
						
		}
	}
	
	/**
		List
	**/
	function tempfiles()
	{
	
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		if($this->input->post("tempClear") !== FALSE)
		{
			if($this->input->post("tempSafe") == "safe")
			{
				$this->load->helper("file");
			
				$files = get_dir_file_info(UPLOAD_TEMP_PATH);
				$now = time();
				
				if(count($files) > 0)
				{
					foreach($files as $filename => $props)
					{
						$diff = $now - $props["date"];
						
						if($diff > 600)
						{
							unlink($props["server_path"]);
						}
					}
					
					redirect('batcave/tempfiles/?cleared=1','location');
				}
			}
		}
		else
		{
			// list table fields
			$this->template->set_template("urika_admin");
			
			
			
			$out = "<p><strong>List files inside the temp folder</strong></p>";
			
			$q_vars = get_url_vars();
			
			if(isset($q_vars->cleared))
			{
				$out .= '<p class="success">Temp folder cleared of safe files</p>';
			}
			
			$this->load->helper("file");
			
			$files = get_dir_file_info(UPLOAD_TEMP_PATH);
			$now = time();
			
			if(count($files) > 0)
			{
				$out .= '<ul class="filelist">';
				
				foreach($files as $filename => $props)
				{
					$diff = $now - $props["date"];
					$time = "";
					
					if($diff < 600)
					{
						$time = "less than 10 minutes ago";
					}
					else
					{
						$time = "over 10 minutes ago - safe";
					}
				
					$out .= '
						<li>
							<strong>'.$filename.'</strong> - 
							<em style="color: #0d0;">'.$time.'</em>
						</li>
					';
				}
				
				$out .= '
					</ul>
					<form action="" method="post" style="width: 150px">
						<fieldset width="150">
							<input type="hidden" value="safe" name="tempSafe" />
							<input type="submit" value="Clear Temp Folder" name="tempClear" />
						</fieldset>
					</form>
				';
			}
			else
			{
				$out .= "<p><em>No files in the temp folder</em></p>";
			}
			
			
			$this->template->add_css("assets/css/admin.css");
				
			$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
				
			$this->template->write("feature","<h2>Manage Temp Images Folder</h2>");
			$this->template->write("content",$out);
			$this->template->render();
				
				// set back to defaut
			$this->template->set_template("urika");
		}
	}
	
	/**
		Allow admin to add an e-mail to the blacklist
	**/
	function blacklistadd()
	{
	
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		if($this->input->post("blEmail") !== FALSE)
		{
			// Emailvalidation
			$this->load->library('form_validation');
			
			$this->form_validation->set_rules('blEmail', 'Email Address', 'trim|required|valid_email');
			
			if($this->form_validation->run() == FALSE)
			{
				redirect('batcave/blacklistadd/?invalid=1','location');
			}
			
			// check the blacklist doesnt already exists
			$this->load->model("user_model");
			
			if($this->user_model->checkBlacklist($this->input->post("blEmail")) == true)
			{
				redirect('batcave/blacklistadd/?exists=1','location');
			}
			else
			{
				// do the insert
				$values = array(
					"bl_email" => trim($this->input->post("blEmail"))
				);
				
				if($this->general_model->addRecord($values,"blacklist") !== FALSE)
				{
					redirect('batcave/blacklistadd/?saved='.$this->input->post("blEmail"),'location');
				}			
			}
		}
		else
		{
			// list table fields
			$this->template->set_template("urika_admin");
			
			$out = "<p><strong>Use the form below to add an e-mail to the blacklist</strong></p>";
			
			$q_vars = get_url_vars();
			
			if(isset($q_vars->saved))
			{
				$out .= '<p class="success">E-mail <strong>'.$q_vars->saved.'</strong> added to blacklist</p>';
			}
			else if(isset($q_vars->exists))
			{
				$out .= '<p class="error">E-mail already in the blacklist</p>';
			}
			else if(isset($q_vars->invalid))
			{
				$out .= '<p class="error">E-mail entered not valid</p>';
			}
			
				
				$out .= '
					</ul>
					<form action="" method="post" style="width: 250px">
						<fieldset width="250">
							<label for="blEmail">E-mail</label>
							<input type="text" name="blEmail" />
							<input type="submit" value="Add to Blacklist" name="blAdd" />
						</fieldset>
					</form>
				';
			
			
			
			$this->template->add_css("assets/css/admin.css");
				
			$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
				
			$this->template->write("feature","<h2>Add to Blacklist</h2>");
			$this->template->write("content",$out);
			$this->template->render();
				
				// set back to defaut
			$this->template->set_template("urika");
		}
	}
	
	/**
		Simple login page, with a form
	**/
	function entrance()
	{
		
		if(isAdmin() == true)
		{
			redirect('batcave/','location');
		}
		
		if($this->input->post("batcave_code") !== FALSE)
		{
			$user = trim($this->input->post("batcave_name"));
			$pass = trim($this->input->post("batcave_saying"));
			$code = trim($this->input->post("batcave_code"));
			
			$message = "";
			if($code != "" || $user == "" || $pass == "")
			{
				$message = '<p class="error">Something went wrong. You are probably a bot</p>';
			}
			else	// validate
			{
				$validate = $this->_validate($user,$pass);
				
				if($validate == true)
				{
					redirect('batcave/','location');
				}
				else
				{
					$message = '<p class="error">Wrong credentials Bruce!</p>'; 
				}
			}
			
			if($message != "")
			{
				$view_data = array (
					"messages" => $message,
				);
			
				$this->load->view("admin_login",$view_data);
			}
		}
		else
		{
			$view_data = array (
				"messages" => "",
			);
			
			$this->load->view("admin_login",$view_data);
		}
	}
	
	/*
		Exit function logs out and destroys the session
	*/
	function logout()
	{
		if(isAdmin() == true)
		{
			$this->session->destroy();
		}
		
		redirect('','location');
	}
	
	/**
		private function to grab table links
	**/
	function _tableMenuLinksHTML()
	{
		$tables = $this->db->list_tables();
		$base = base_url();
		
		$out = "";
		
		foreach($tables as $table)
		{
			$out .= '<li><a href="'.$base.'batcave/table/'.$table.'/">'.$table.'</a></li>';
		}
		
		return $out;
	}
	/**
		private function to grab table links
	**/
	function _tableSummaryHTML()
	{
		$tables = $this->db->list_tables();
		$base = base_url();
		
		$out = '<ul class="admin_table_sum">';
		
		foreach($tables as $table)
		{
			$count = $this->general_model->countRecords($table);
			
			$out .= '
				<li>
					<a style="background-image: url('.$base.'assets/images/layout/admin/icon_'.$table.'.png);" href="'.$base.'batcave/table/'.$table.'/"><strong>'.$count.'</strong> '.$table.'</a>
				</li>
				';
		}
		
		$out .= '</ul>';
		
		return $out;
	}
	
	/**
		Validates the user log in and sets up
		
		@param user : username to check
		@param pass : password o check
	**/
	function _validate($user,$pass)
	{
		
		$string = $user.'-urika-'.$pass;
		
		$crypt = md5($string);
		
		if($crypt == "3070a4a87dd3d4ec273bd80e0b3b760b")
		{
			
			// we have a winner, now generate admin session vars
			$rand_a = rand(7,19);
			$string = "urika_admin";
			$num = 923 * $rand_a;
			$hash = md5(strrev($string)."".$num);
			
			$final_string = $num.'.'.$string.'.'.$hash.'.'.$rand_a;
			
			
			$data = array(
					'admin' => 1,
					'admin_check' => $final_string,
			);
				
			$this->session->set_userdata($data);
			

			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	/**
		Analytics page, uses the analytics class to grab information from
		Google analytics API
	*/
	function analytics()
	{
	
		if(isAdmin() == false)
		{
			redirect('','location');
		}
		
		
		
		// list table fields
		$this->template->set_template("urika_admin");
		
		$out = "<p><strong>Analyics for urika-app.com:</strong> An general overview of recent activity on the site for the last<em> 30 days</em>.<br/><br/></p>";
		
		/*
			Start analytics data grab
		*/
		$this->load->helper("analytics_api_helper");
		
		$login = "luke.redroot@googlemail.com";
		$password = "asdf1234";
		//$profile_id = "4301220";
		$profile_id = "41180304";

		
		$start_date = time()-(60*60*24*30);
		$end_date = time()-(60*60*24*1);
		
		// authenticate
		$ga = gapiClientLogin::authenticate($login,$password);

		
		// general data
	
		$ga->requestReportData($profile_id,null,array('pageviews','visits','visitors','timeOnSite'),null,null,$start_date,$end_date);
		$data = $ga->getResults();
		
		// visits
		
		
		$ga->requestReportData($profile_id,array('date'),array('visits'),array('date'),null,$start_date,$end_date,1,30);
		$visits_data = $ga->getResults();
		
		// traffic sources
		
		$ga->requestReportData($profile_id,array('medium'),array('visits'),null,null,$start_date,$end_date);
		$traffic_data = $ga->getResults();
		
		// pages
		
		$ga->requestReportData($profile_id,array('pagePath'),array('visits'),array('-visits'),null,$start_date,$end_date,1,10);
		$page_data = $ga->getResults();
		
	
		
		/*
			Start Google charts bit and sort output
		*/
		
		// 1) General info
		
		$general = '
			<table style="font-size: 12px;">
				<tr><th style="text-align:left; font-size: 12px">Pageviews:</th><td> '.$data[0]->getPageviews().'</td></tr> 
				<tr><th style="text-align:left; font-size: 12px">Visits:</th><td> '.$data[0]->getVisits().'</td></tr> 
				<tr><th style="text-align:left; font-size: 12px">Pages per Visit:</th><td> '.round($data[0]->getPageViews()/$data[0]->getVisits()).'</td></tr>
				<tr><th style="text-align:left; font-size: 12px">Visitors:</th><td> '.$data[0]->getVisitors().'</td></tr> 	
				<tr><th style="text-align:left; font-size: 12px">Average time on site:</th><td> '.round($data[0]->getTimeonsite()/$data[0]->getVisits()).'s</td></tr>			
			</table>
		';
		
		
		// 2) get visits data
		$visits_chd = "";
		$visits_chl = "";
		
		$total = 0;
		$min = 100000;
		$max = 0;
		
		$k = 0;
		foreach($visits_data as $result)
		{
			$visits_chd .= $result->getVisits().',';
			
			if($k == 0)
			{
				$visits_chl .= date("jS M",strtotime($result->getDate())).'|';
				$k++;
			}
			else
			{
				$k++;
				if($k == 4)
				{
					$k = 0;
				}
			}
			
			// numeric values
			$total += $result->getVisits();
			
			if($result->getVisits() < $min)
			{
				$min = $result->getVisits();
			}
			
			if($result->getVisits() > $max)
			{
				$max = $result->getVisits();
			}
			
		}
		$visits_chd = substr($visits_chd,0,-1);
		$visits_chl = substr($visits_chl,0,-1);
		
		$avg = round($total/30);
		
		
		$visits_chart_src = 'https://chart.googleapis.com/chart?cht=lc&chs=500x300&chxt=x,y,r&chxr=1,0,'.$max.'|2,0,'.$max.'&chds=0,'.$max.'&chxl=2:|min|average|max&chxs=2,0000dd,13,-1,t,FF0000&chxp=2,'.$min.','.$avg.','.$max.'&chxtc=2,-500&chd=t:'.$visits_chd.'&chl='.$visits_chl;
		
		// 3) traffic sources
		$traffic_chd = "";
		$traffic_chl = "";
		
		foreach($traffic_data as $result)
		{
			$traffic_chd .= $result->getVisits().',';
			$traffic_chl .= $result->getMedium().' - '.$result->getVisits().'|';
		}
		$traffic_chd = substr($traffic_chd,0,-1);
		$traffic_chl = substr($traffic_chl,0,-1);
		
		$traffic_chart_src = 'https://chart.googleapis.com/chart?cht=p&chs=400x250&chd=t:'.$traffic_chd.'&chl='.$traffic_chl;
		
		// 4) Page views
		
		$pages = '<table style="font-size: 12px;">';
		
		foreach($page_data as $result)
		{
			$pages .= '
				<tr>
					<th style="text-align: left; font-size:12px;">'.$result->getPagepath().'</th>
					<td>'.$result->getVisits().'</td>
				</tr>
			';
		}
		
		$pages .= '</table>';
		
		/*
			End all API stuff
		*/
		
		$chart_html = '
			<div class="left col-8">
				<h3 style="margin-bottom: 10px;">General Data</h3>
				'.$general.'
			</div>
			<div class="left col-8" style="margin-left: 20px;">
				<h3 style="margin-bottom: 10px;">Visits</h3>
				<img src="'.$visits_chart_src.'" />
			</div>
			<div class="clear">&nbsp;</div>
			<div class="left col-8">
				<h3 style="margin-bottom: 10px;">Traffic Sources</h3>
				<img src="'.$traffic_chart_src.'" />
			</div>
			<div class="left col-8" style="margin-left: 20px;">
				<h3 style="margin-bottom: 10px;">Pages</h3>
				'.$pages.'
			</div>
		';
		
		$out .= $chart_html;
		
		
		$this->template->add_css("assets/css/admin.css");
			
		$this->template->write("menu_lis",$this->_tableMenuLinksHTML());
			
		$this->template->write("feature","<h2>Admin Dashboard</h2>");
		$this->template->write("content",$out);
		$this->template->render();
			
			// set back to defaut
		$this->template->set_template("urika");
	}
	
	
	
}

/* End of file batcave.php */
/* Location: ./system/application/controllers/batcave.php */