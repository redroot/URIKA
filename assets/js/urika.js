/**
	Main Javascript Document for urika.com
	@author Luke Williams
	@version 0.1
*/

$(document).ready(function()
	{
		js_init();
	}
);

// global variables
var base_url = 'http://localhost/urika/ci/';

/**
	Function to run once the page has loaded, gets everything ready
**/
function js_init()
{
	menu_dropdowns();
	miscStuff();
	
	// rollovers on uploadlist
	$(".uploadList li a").hover(
		function()
		{
			$(this).find("span,em").stop().fadeTo('fast',1);
		},
		function()
		{
			$(this).find("span,em").stop().fadeTo('fast',0);
		}
	);
	
	
	
}

/**
	Sets up login dropdown
**/
function menu_dropdowns()
{
	$('li.menu_drop').click(function(e)
		{
			e.preventDefault();
			
			var box = $(this).attr("data-menu");
			
			if($(this).hasClass('active'))
			{
				$(this).removeClass('active');
				$(box).removeClass('show');
				
			}
			else
			{		
			
				$("li.menu_drop").each(function(){
				
					$(this).removeClass('active');
					$($(this).attr("data-menu")).removeClass('show');
				
				});
				
				//dynamic lef tposition
				if($(box).attr("id") == "account_drop")
				{
					$(box).css("right",11 + $(this).width() + 'px');
				}
			
				$(this).addClass('active');
				$(box).addClass('show');
				$('#lform #l_username').focus();
				
				
				
				
			}
			
		}
	);
}

/**
	Misc function for small, universal things
	or jsut one page things
**/
function miscStuff()
{
	//hide default search value when user clicks on it
	$('#main_search').focus(function()
	{
		if($(this).val() == "Search") $(this).val("");
	});
	
	// collection add button
	$('#addCollection').click(function()
	{
		$.facebox({ div: '#createCollectionForm'});
	});
	
	// ie warning
	if($.browser.msie && parseInt($.browser.version) < 9)
	{
		$("body").append(
			$("<div></div>")
				.addClass("ie_is_poor")
				.html("<p>Unfortunately, your version of Internet Explorer does not support the cool functionality on UR!KA. We recommend you upgrade to a more modern browser, such as <a href='http://www.google.com/chrome' style='color: #2d5;' target='_blank'> Google Chrome </a>, <a href='http://www.getfirefox.com' style='color: #f60;' target='_blank'>Mozilla Firefox</a> or <a target='_blank' style='color: #06f;' href='http://www.apple.com/safari/'>Safari</a> in order to get the maximum experience.</p>")
				.click(function()
				{
					$(this).fadeOut(600,function()
					{
						$(this).remove();
					});
				})
		)
	}
	
	//homepage flair
	if($("#home_flair").length == 1 && !$.browser.msie)
	{
		$("#home_flair").hover(
		function()
		{
			$("#home_flair").stop().fadeTo(500,1,"easeInQuad");
		},
		function(){
			$("#home_flair").stop().fadeTo(500,0,"easeInQuad");
		}
		);
	}
	
	// faq stuff
	if($('.faq_facebox').length > 0)
	{
		$.each($('.faq_facebox'),function()
		{
			var href = $(this).attr("href");
			$(this).attr("href","javascript:return false;");
			$(this).click(function()
			{
				$.facebox('<img src="'+href+'" alt="'+$(this).attr("title")+'" />');
				return false;
			});
			
		});
	}

}

///// Global Functions

var JSON = JSON || {};
// implement JSON.stringify serialization
JSON.stringify = JSON.stringify || function (obj) {
	var t = typeof (obj);
	if (t != "object" || obj === null) {
		// simple data type
		if (t == "string") obj = '"'+obj+'"';
		return String(obj);
	}
	else {
		// recurse array or object
		var n, v, json = [], arr = (obj && obj.constructor == Array);
		for (n in obj) {
			v = obj[n]; t = typeof(v);
			if (t == "string") v = '"'+v+'"';
			else if (t == "object" && v !== null) v = JSON.stringify(v);
			json.push((arr ? "" : '"' + n + '":') + String(v));
		}
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
	}
};
