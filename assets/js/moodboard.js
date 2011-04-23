/**
	Moodboard JS
	v0.5
	No cloning in this version, might be easier, dont really want replication
*/

/*** JSON Data ****/
var data = {
	"background" : {"colour" : "#eee", "width" : "700", "height" : "500", "offsetLeft": "", "offsetTop": "", "useRotation" : 0},
	"images": []
};

var colourwheel;
var counter = 0;
var loading_page = false;

$(document).ready(function()
{
	
	 
	/*** Moodboard TOOLS ****/
	var bg_image = $('#moodboard_area').css('background-image');
	$('#toggleGrid').change(function()
		{
			if($(this).is(":checked"))
			{
				$('#moodboard_area').css('background-image',bg_image);
			}
			else
			{
				$('#moodboard_area').css('background-image','none');
			}
		}
	);
	
	
	colourwheel = $.farbtastic($('#mb_colourwheel'),function(color)
	{
		$('#moodboard_area').css('background-color',color);
		data.background.colour = color;
	});

	$("#mbwidth").slider({
			value:parseInt(data.background.width),
			min: 500,
			max: 900,
			step: 50,
			slide: function(event, ui) {
				$("#moodboard_area").css("width",ui.value+'px');
				$(this).parent().find('p strong').html(ui.value);
				data.background.width = ui.value;
				data.background.offsetLeft = Math.round($("#moodboard_area").offset().left);
			}
	});
		
	$('#mbheight').slider({
			value:parseInt(data.background.height),
			min: 400,
			max: 900,
			step: 50,
			slide: function(event, ui) {
				$("#moodboard_area").css("height",ui.value+'px');
				$(this).parent().find('p strong').html(ui.value);
				data.background.height = ui.value;
				data.background.offsetTop = Math.round($("#moodboard_area").offset().top);
			}
	});

	
	/**** Moodboard Images ****/
	
	
	/*
		Moodboard offsets
	*/
	data.background.offsetLeft = $("#moodboard_area").offset().left;
	data.background.offsetTop = $("#moodboard_area").offset().top;

	
	//add hover state later
	$('#objects .obj_item').hover(
		function()
		{
			$(this).css("cursor","pointer");
			$('.addLink',$(this)).css("display","block");
		},
		function()
		{	
			$(this).css("cursor","");
			$('.addLink',$(this)).css("display","none");
		}
	).click(function()
	{
		var full_src = $('img',$(this)).attr("data-full-url"); // change this accordingly one there is a thumb and full
		var thumb_src = $('img',$(this)).attr("src");
		
		addImage(full_src,thumb_src,counter,null);
		
		if($.browser.msie == undefined)
		{
			sortLayers();
		}
		
		counter++;
	}); 
	
	//set up moodboard area
	$('#moodboard_area')
	.css("width",data.background.width)
	.css("height",data.background.height);
	
	$('#moodboard')
	.droppable(
	{
		accept : '#moodboard'
	}
	);
	
	// sortable layers
	$('#layers').sortable(
	{
		placeholder: 'sort_highlight',
		cursor: 'crosshair',
		stop : function(event, ui)
		{
			sortLayers();
		}
	});
	
	
	/***** Moodboard Save functions *****/
	$('#mbSave').click(function()
	{
		saveMoodboard();
	});
	
	/**
		Detect EDIT mode if DATA string is not empty
	**/
	if($("#dataString").val() != "")
	{
		rebuildMoodboard();
	}
	
	
});

//// FUNCTIONS

/*
	Adds an element the list of layours
*/
function addToList(thumb_src,icounter,values)
{
	var new_li = $('<li></li>');
	var thumb = $('<div class="thumb"></div>');
	var remove = $('<a></a>');
	var clear =$('<span class="clear">&nbsp;</span');
	var new_id = "droppedimg_"+icounter;
	
	var rotation = (values == null) ? 0 : values.rotation; 
	
	// update the counter if values exist so thing stay in the same place
	
	
	remove
		.addClass('remove')
		.click(function()
		{
			$('#'+new_id, '#moodboard').remove();
			
			$('#layer_'+icounter, '#layers').remove();
			delete data.images[icounter];
			
			// temp fix to remove null objects
			var temp_data = {"images":[]};
			
			for(var k = 0; k <= data.images.length; k++)
			{
				if(k == data.images.length)
				{
					data.images = temp_data.images;
					sortLayers(); 
					break;
				}
				
				if(typeof(data.images[k]) != "undefined")
				{
					temp_data.images.push(data.images[k]);
				}
				
			}

		});
	

	new_li.attr("id","layer_"+icounter);
	thumb.css("background-image",'url("'+thumb_src+'")');
	new_li.append(thumb).append(remove).append(clear);
	
	var layernum = parseInt(icounter)+1;
	
	if($.browser.msie == undefined)
	{
		// now added layer name and slider
		new_li.append($('<p></p>').html("Layer "+layernum+"<br/>Rotation: <strong>"+rotation+"</strong><sup>o</sup>"));
		new_li.append($('<div></div>').attr("id","rotateSlider_"+icounter));
	}
	else
	{	
		new_li.append($('<p></p>').html("Layer "+layernum));
	}
	

	$('#layers').prepend(new_li);

	
	// now do the slider
	if($.browser.msie == undefined)
	{
		data.background.useRotation = 1;
	
		$('#rotateSlider_'+icounter).slider({
				value:rotation,
				min: -180,
				max: 180,
				step: 4,
				slide: function(event, ui) {
					
					$("#"+new_id).parent().css({
							'-moz-transform':'rotate('+ui.value+'deg)',
							'-webkit-transform':'rotate('+ui.value+'deg)',
							'-o-transform':'rotate('+ui.value+'deg)'
						});

					
					$(this).parent().find('p strong').html(ui.value);
					var index = objectIndexFromId(icounter);
					data.images[index].rotation = ui.value;
				}
		});
		
		
		if(rotation != 0)
		{
			$("#"+new_id).parent().css({
				'-moz-transform':'rotate('+rotation+'deg)',
				'-webkit-transform':'rotate('+rotation+'deg)',
				'-o-transform':'rotate('+rotation+'deg)'
			});
		}
		
	}
	
	sortLayers();

	
		
}

/*
	Returns or sets up images original state
	takes a 4th parameter which updates the values if we are using edit
*/

function addImage(full_src,thumb_src,icounter, values)
{
	
	var new_id = 'droppedimg_'+icounter;
	var new_img = 
		$('<img/>')
			.attr("src",full_src)
			.attr("id",new_id)
			.addClass("droppedImage")
			.css("left","-9999px"); // hide under we can work out width
	
	
	
	if(loading_page == false)
	{
		$.facebox("Adding Image ....");
	}
	
	var test = $('#moodboard').append(new_img);
	

	$('#'+new_id).load(function()
	{
		$(this).css("left","0px");
		
		if(loading_page == false)
		{
			$(document).trigger("close.facebox");
		}
		
		$('#'+new_id).resizable(
		{
			handles: 'se',
			stop: function(event,ui)
			{	
				var index = objectIndexFromId(icounter);
				data.images[index].width = $(this).width();
				data.images[index].height = $(this).height();
				
			}
		})
		.parent('.ui-wrapper')
		.draggable(
		{
			containment: '#moodboard',
			stop: function(event, ui)
			{
				var index = objectIndexFromId(icounter);
				data.images[index].left = parseInt($(ui.helper).css("left"));
				data.images[index].top = parseInt($(ui.helper).css("top"));
			}
		});
		
		
		

		var img_w = $(this).width();
		var img_h = $(this).height();
		
		var div_x = ((data.background.width/2)-(img_w/2));
		var div_y = ((data.background.height/2)-(img_h/2));
		
		var rotation = 0;
	
		if(values != null)
		{
			var div_x = values.left;
			var div_y = values.top;
			
			$(this).width(values.width).parent().width(values.width);
			$(this).height(values.height).parent().height(values.height);
			
			img_w = values.width;
			img_h = values.height;
			
			rotation = values.rotation;
		}

		// set in the dead middle of the moodboard
		$('#'+new_id)
			.css("display","block")
			.parent('.ui-wrapper')
			.css("left",div_x+'px')
			.css("top",div_y+'px');

			
		
		
		
		// now to add the image to the json
		
		var newObject = {
			'id' : icounter,
			'src': full_src,
			'width': img_w,
			'height': img_h,
			'left': div_x,
			'top': div_y,
			'rotation': rotation,
			'order': null
		}
		
		
		
		data.images.push(newObject);
		
		addToList(thumb_src,icounter,values);

	});
}

/*
	sorts layers out z-index wise
*/
function sortLayers()
{
	// grab ids of all lis in order
	var ids_array = [];
	var j = 0;
	$('li','#layers').each(function()
	{
		ids_array[j] = $(this).attr("id").substr(6);
		j++;
	});
	
	var z;
	var index;
	for(var k = 0; k < ids_array.length; k++)
	{
		index = objectIndexFromId(ids_array[k]);
		
		if(typeof(data.images[index]) != "undefined")
		{
			z = 101 + (3*(ids_array.length - k)); // start from the top
			$('#droppedimg_'+ids_array[k]).css('z-index',z);
			$('#droppedimg_'+ids_array[k]).parent().css('z-index',z);
			// now dynamically update the z-index of the handles
			$('#droppedimg_'+ids_array[k]).parent().find(".ui-resizable-handle").css("z-index",z+1);

			
			data.images[index].order = k;
		}
	}
	
}

/***** MOODBOARD CONSTRUCTION FUNCTION *****/
// rebuilds moodboard from the ground up, assigning all values to the current data object
function rebuildMoodboard()
{
	

	var new_data = $.parseJSON($("#dataString").val());

	loading_page = true;
	
	// for some reason doesnt work if the facebox is showing
	if($.browser.msie == undefined)
	{
		$.facebox("<span class='loadingspan loading_block'>Loading Moodboard</span>");
	}
	
	// first background and dimensions
	$('#moodboard_area').css('background-color',new_data.background.colour);
	data.background.colour = new_data.background.colour;
	colourwheel.setColor(new_data.background.colour);
	
	data.background.width = new_data.background.width;
	data.background.height = new_data.background.height;
	
	$("#moodboard_area").css("height",data.background.height+'px');
	$("#mbheight").parent().find('p strong').html(data.background.height);
	$("#mbheight").slider("option","value", parseInt(data.background.height));
	data.background.offsetTop = $("#moodboard_area").offset().top;
	$("#moodboard_area").css("width",data.background.width+'px');
	$("#mbwidth").parent().find('p strong').html(data.background.width);
	$("#mbwidth").slider("option","value", parseInt(data.background.width));
	data.background.offsetLeft = $("#moodboard_area").offset().left;
	
	// now create images so they match
	// using set Interval since javascript is not sequential. Each loop doesnt make them work in order
	// since some images are faster to load. In slower engines (not WEbkit) order is not maintained.
	// enforcing an interval hopefull saves us this issue
	
	// first we need to sort by order the data by order so they are adde din the correct order layer
	
	var new_images = [];
	var temp_images = new_data.images;
	var order_index = temp_images.length - 1;
	
	

	for(var j = 0; j < new_data.images.length; j++)
	{
		$.each(temp_images,function(index,value)
		{
			if(typeof(value) != "undefined")
			{
				if(value.order == order_index)
				{
					new_images.push(value);
					delete temp_images[index];
					order_index--;
					return false;
				}
			}
		});

		if(j == new_data.images.length - 1)
		{			
			var imagesInt = window.setInterval(function()
			{
				var val = new_images[counter];
				var thumb_src = val.src.replace("images/","images/thumb_");
				addImage(val.src,thumb_src,counter,val);
			
				counter++;
				
				if(counter == new_data.images.length)
				{
					window.clearInterval(imagesInt);
					loading_page = false;
					window.setTimeout(function()
					{
						$(document).trigger("close.facebox");
					},900);
				}
			
			},900);
		}
		
	}
	
	
	
	
	

}



/*
	Save Moodboard function send all data to moodboard PHP
	part
*/
function saveMoodboard()
{
	if(validateForm() == true)
	{
		$.facebox('<span class="loadingspan loading_block">Saving Moodboard</span>');
		$('#dataString').val(JSON.stringify(data)); // from json2.js
		//alert($('#dataString').val());
		$('#moodboard_form').submit();
	}
}

function validateForm()
{
	if($("#mb_name").val() == "" || $("#mb_desc").val() == "")
	{
		$.facebox("Please fill in the moodboard information under the Moodboard Details dropdown");
		return false;
	}
	else if(data.images.length == 0)
	{
		$.facebox("Please add some images before saving the moodboard");
		return false;
	}
	else
	{
		return true;
	}

}

/**
	Menu Functions
**/
function showColour()
{
	if($("#mb_colourgrid").css("display") == "none")
	{
		$("#mb_colourgrid").css("display","block");
		$("#mb_details").css("display","none");
		$("li.menu_drop").first().removeClass("active");
		$("li.menu_drop").last().removeClass("active");
	}
	else
	{
		$("#mb_colourgrid").css("display","none");
		$("li.menu_drop").first().addClass("active");
		$("li.menu_drop").last().removeClass("active");
	}
}

function showDetails()
{
	if($("#mb_details").css("display") == "none")
	{
		$("#mb_details").css("display","block");
		$("#mb_colourgrid").css("display","none");

		$("li.menu_drop").last().removeClass("active");
		$("li.menu_drop").first().removeClass("active");

	}
	else
	{
		$("#mb_details").css("display","none");
		$("li.menu_drop").last().addClass("active");
		$("li.menu_drop").first().removeClass("active");
	}
}


/**
	Utility functions
**/
 /*  checks if an object was already registered */
function objectIndexFromId(id){
	for(var i = 0;i<data.images.length;++i){
		if(data.images[i].id == id)
			return i;
	}
	return -1;
}


