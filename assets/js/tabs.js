/**
	Tabs Javascript Document for URIKA
	@author Luke Williams
	@version 0.1
*/



	if($('.tabs').length > 0)
	{
		/*
		
			Dynamic tab generation
			
		*/
		
		var i = 1;
		$('.tabs').each(function()
		{
			var tabdiv = $(this);
			
			tabdiv.addClass("jsReady");
			
			//set default active
			if($('ul.tabNav li.active',tabdiv).length != 1)
			{
				$('ul.tabNav li:first',tabdiv).addClass("active");
			}
			
			if($('.tabContent.show',tabdiv).length != 1)
			{
				$('.tabContent:first',tabdiv).addClass("show");
			}
			
			var j = 1;
			//generate and set ids
			$('.tabContent',tabdiv).each(function(){
				
				var id = 'tabContent_'+i+'_'+j;
			
				$(this).attr("id",id);
				$('ul.tabNav li:nth-child('+j+') a',tabdiv).attr("data-tab",'#'+id);
				
				j++;
			});
			
			
			
			// set up active toggler
			$('ul.tabNav li a', tabdiv).click(function()
			{
				$('ul.tabNav li', tabdiv).removeClass("active");
				$(this).parent().addClass("active");
				
				$('.tabContent',tabdiv).removeClass("show");
				var show_id = $(this).attr("data-tab");
				$(show_id,tabdiv).addClass("show");
				
			});
			
			i++;
		});
	}
