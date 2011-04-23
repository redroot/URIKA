<?php

class page_model extends Model {


/////////// GET Functions
	

	/**
		This function returns a page record
	
		@param value : the value to use in the search
		@param field : the field to use
	**/
	function getPage($value,$field = "page_id")
	{
		$this->db->where(trim($field), trim($value));
		$query = $this->db->get('pages');

		
		if($query->num_rows == 1)
		{
			return $query;
		}
		else
		{
			return false;
		}
	}
	

	
////////////// INSERT FUNCTIONS

////////////// UPDATE FUNCTIONS

////////////// DELETE FUNCTIONS



	
}

/* End of file page_model.php */
/* Location: ./system/application/models/page_model.php */