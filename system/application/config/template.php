<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| Active template
|--------------------------------------------------------------------------
|
| The $template['active_template'] setting lets you choose which template 
| group to make active.  By default there is only one group (the 
| "default" group).
|
*/
$template['active_template'] = 'urika';

/*
|--------------------------------------------------------------------------
| Explaination of template group variables
|--------------------------------------------------------------------------
|
| ['template'] The filename of your master template file in the Views folder.
|   Typically this file will contain a full XHTML skeleton that outputs your
|   full template or region per region. Include the file extension if other
|   than ".php"
| ['regions'] Places within the template where your content may land. 
|   You may also include default markup, wrappers and attributes here 
|   (though not recommended). Region keys must be translatable into variables 
|   (no spaces or dashes, etc)
| ['parser'] The parser class/library to use for the parse_view() method
|   NOTE: See http://codeigniter.com/forums/viewthread/60050/P0/ for a good
|   Smarty Parser that works perfectly with Template
| ['parse_template'] FALSE (default) to treat master template as a View. TRUE
|   to user parser (see above) on the master template
|
| Region information can be extended by setting the following variables:
| ['content'] Must be an array! Use to set default region content
| ['name'] A string to identify the region beyond what it is defined by its key.
| ['wrapper'] An HTML element to wrap the region contents in. (We 
|   recommend doing this in your template file.)
| ['attributes'] Multidimensional array defining HTML attributes of the 
|   wrapper. (We recommend doing this in your template file.)
|
| Example:
| $template['default']['regions'] = array(
|    'header' => array(
|       'content' => array('<h1>Welcome</h1>','<p>Hello World</p>'),
|       'name' => 'Page Header',
|       'wrapper' => '<div>',
|       'attributes' => array('id' => 'header', 'class' => 'clearfix')
|    )
| );
|
*/

/*
|--------------------------------------------------------------------------
| Default Template Configuration (adjust this or create your own)
|--------------------------------------------------------------------------
*/

$template['urika']['template'] = 'template.php';
$template['urika']['regions'] = array(
   'title', // dynamic title for the page
   'content', //main content in content area
   'body_id' =>array(
    'content' => array('default')
  )
);
$template['urika']['parser'] = 'parser';
$template['urika']['parser_method'] = 'parse';
$template['urika']['parse_template'] = FALSE;

/*
|--------------------------------------------------------------------------
| Admin template
|--------------------------------------------------------------------------
*/
$template['urika_admin']['template'] = 'admin_template.php';
$template['urika_admin']['regions'] = array(
   'content', //main content in content area
   'feature' => array(
	'content' => array('')
   ),
   'menu_lis' => array(
	'content' => array('')
   )
);
$template['urika_admin']['parser'] = 'parser';
$template['urika_admin']['parser_method'] = 'parse';
$template['urika_admin']['parse_template'] = FALSE;

/*
|--------------------------------------------------------------------------
| Moodboard template
|--------------------------------------------------------------------------
*/
$template['urika_moodboard']['template'] = 'moodboard/moodboard_construct.php';
$template['urika_moodboard']['regions'] = array(
   'content', //main content in content area
   'objects',
   'layers',
   'dataString',
   'mb_user_id',
   'mb_col_id',
   'mb_title',
   'mb_id',
   'mb_desc',
   'mb_tags'
);
$template['urika_moodboard']['parser'] = 'parser';
$template['urika_moodboard']['parser_method'] = 'parse';
$template['urika_moodboard']['parse_template'] = FALSE;

/* End of file template.php */
/* Location: ./system/application/config/template.php */