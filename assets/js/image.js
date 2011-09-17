/**
	Images Javascript Document for URIKA
	Handles image functions such as upload, adding to favourites etc, editting
	@author Luke Williams
	@version 0.1
*/
/*

*/

$(document).ready(function()
{
	
	prepUpload();
	
	
	//comment hover links
	if($(".comment").length > 0)
	{
		$(".comment").hover(
			function()
			{
				if($(this).find(".delete_link").length > 0)
				{
					$(this).find(".delete_link").css("display","block");
				}
			},
			function()
			{
				if($(this).find(".delete_link").length > 0)
				{
					$(this).find(".delete_link").css("display","none");
				}
			}
		);
	}
	
	/*
	// tags stuff

	if($.browser.msie == true)
	{
		
		$("label[for='add_tags'],label[for='edit_tags']").html("Tags (Comma separate e.g banner,red,image");
	}
	else
	{
	//set up tags in non-ie browses
		if($("#add_tags,#edit_tags").length > 0 )
		{
			$("#add_tags,#edit_tags").fcbkcomplete({
				json_url: base_url+"assets/tags/tags.txt",
				//cache: true,
				filter_case: true,
				filter_hide: true,
				firstselected: true,
				//onremove: "testme",
				//onselect: "refocus",
				filter_selected: true,
				maxitems: 5,
				newel: true        
			  });
		
			// bind tags since onselect above isnt firing
			$("#add_save").click(function()
			{
				var values = "";
				
				$("select#add_tags option, select#edit_tags option").each(function()
				{
					values += $(this).val();
					values += ',';
					
				});
				
				$('#add_tags_list, #edit_tags_list').val(values.substring(0,values.length - 1));
				
			});
		}
	}
	*/
	$("#add_tags,#edit_tags").chosen();
	
	// collection stuff
	if($(".collectionEditList").length > 0)
	{
		var ids_string = "";
		$(".collectionEditList li").click(function()
		{
			$(this).fadeOut(300,function()
			{ 
				$(this).remove(); 
				// now redo string
				ids_string = "";
				$(".collectionEditList li").each(function()
				{
					ids_string += $(this).attr("data-imageid");
					ids_string += ',';

				});
				$('#col_string_values').val(ids_string.substring(0,ids_string.length - 1));
			});
		});
	}
	
	
});

///////// Image View functions


///////// Image upload + crop functions

function prepUpload()
{
	if($("#image_add").length == 1)
	{
		// dimensions array for uploaded object
		var dims = {
			orig_h: 0,
			orig_w: 0,
			new_h: null,
			new_w: null,
			offset_x: 0,
			offset_y: 0,
		};
		
		 var uploader = new qq.FileUploader({
                element: $('#upload_box')[0],
                action: base_url+"image/upload/",
				allowedExtensions: ['jpg', 'jpeg', 'png'],
                debug: false,
				multiple: false,
				onSubmit: function(id, fileName)
				{
					$('.qq-upload-button').css("display","none");
					
				},
				onProgress: function(id, fileName, loaded, total)
				{
					
					
				},
				onComplete: function(id, fileName, responseJSON)
				{
					if(responseJSON.filename)
					{
						$("#preview_box p#preview_msg").text("").remove();
						$("#preview_box").removeClass("hide").append("<img id='preview_img' src='"+responseJSON.fileURL+"' />");
						
						$('.qq-upload-button, .qq-upload-list').css("display","none");
						$('.qq-upload-drop-area').remove();
						
						// show preview box
						$("p.info").addClass("hide");
						$('#crop_instructions').removeClass("hide");
						$("#crop_notice").css("display","block");
						
						// fill in hidden value
						$("#add_filename_temp").val(responseJSON.filename);
						
						$('#preview_img').load(function()
						{
							var c_width = $('#preview_img').width();
							var c_height = $('#preview_img').height();
							
							$("#crop_instructions span.w").html(c_width+"px");
							$("#crop_instructions span.h").html(c_height+"px");
							
							if(c_height > 900 || c_width > 900 || c_height < 100 || c_width < 100 )
							{
								$('#crop_instructions p.error').removeClass("hide");
							}
							
							$("#crop_notice").html("Click and drag to crop the image to a desired size.");
							
							
							dims.new_h = c_height;
							dims.new_w = c_width;
							dims.orig_h = c_height;
							dims.orig_w = c_width;
							dims.resized_h = c_height;
							dims.resized_w = c_width;
							dims.offset_x = 0;
							dims.offset_y = 0;
							dims.cropped = 0;
							
							//set up slider
							//set up slider
							$("#size_slider").slider({
								value: 100,
								min: 70,
								max: 130,
								steps: 10,
								slide: function(event,ui)
								{
									// resize the image image b that factor 
									var factor = ui.value/100;
									var new_img_w = Math.round(factor*dims.orig_w);
									var new_img_h = Math.round(factor*dims.orig_h);
									
									dims.resized_w = new_img_w;
									dims.resized_h = new_img_h;
									
									if(dims.cropped == 1)
									{
										$("#crop_instructions span.w").html(dims.new_w+"px");
										$("#crop_instructions span.h").html(dims.new_h+"px");
									}
									else
									{
										$("#crop_instructions span.w").html(dims.resized_w+"px");
										$("#crop_instructions span.h").html(dims.resized_h+"px");
										dims.new_w = new_img_w;
										dims.new_h = new_img_h;
									}
									
									if(dims.new_w > 900 || dims.new_h > 900  || dims.new_h < 100 || dims.new_w < 100 )
									{
										$('#crop_instructions p.error').removeClass("hide");
									}
									else
									{
										$('#crop_instructions p.error').addClass("hide");
									}
									
									// now update size of the image
									$(".jcrop-holder, .jcrop-holder img, .jcrop-holder .jcrop-tracker").css("width",dims.resized_w+"px").css("height",dims.resized_h+"px");
									
									
									$("#size_val").html(ui.value+"%");
								}
							});
							
							// set up messagee
							
							$(this).Jcrop({
								onSelect: function(c)
								{
									dims.new_h = c.h;
									dims.new_w = c.w;
									dims.offset_x = c.x;
									dims.offset_y = c.y;
									
									$("#crop_notice").css("display","none");
									
									$("#crop_instructions span.w").html(c.w+"px");
									$("#crop_instructions span.h").html(c.h+"px");
									

									
									if(dims.new_w > 900 || dims.new_h > 900  || dims.new_h < 100 || dims.new_w < 100 )
									{
										$("#add_save_crop_box").css("display","none");
										
										$("#crop_error")
											.css("display","block")
											.css("position","absolute")
											.css("top", c.y + 5)
											.css("left", c.x + (c.w/2) - ($("#crop_error").width() / 2));
									}
									else
									{
										$("#crop_error").css("display","none");
									
										// show save button
										$("#add_save_crop_box")
										.css("display","block")
										.css("position","absolute")
										.css("top", c.y + (c.h/2) - ($("#add_save_crop_box").height() / 2))
										.css("left", c.x + (c.w/2) - ($("#add_save_crop_box").width() / 2));
										
										$('#crop_instructions p.error').addClass("hide");
										
										
									}
								},
								onChange: function(c)
								{
									$("#add_save_crop_box,#crop_error").css("display","none");
									$("#crop_dims")
										.css("display","block")
										.css("position","absolute")
										.css("top", c.y + c.h - 25)
										.css("left", c.x + 20)
										.html(c.w+' x '+c.h)
										
									dims.cropped = 1;

								}
							});
						});
						
					}
					else
					{
						// didnt work
					}
					
				},
           });   
	
		$("#add_save_crop, #add_save_crop_box").click(function()
		{
			var c_width = $('#preview_img').width();
			var c_height = $('#preview_img').height();
			
			if(dims.new_w > 900 || dims.new_h > 900 || dims.new_h < 100 || dims.new_w < 100)
			{
				$('#crop_instructions p.error').removeClass("hide");
			}
			else
			{
				$('#crop_instructions p.error').addClass("hide");
				
				
				// update dims info
				dims.orig_w = c_width;
				dims.orig_h = c_height;
				
				$("#add_filename_dims").val(JSON.stringify(dims));
				
				// now create div inside preview_box showing new image
				
				var src = $("#preview_img").attr("src");
			
				var new_div = $("<div />");
				var new_img = $("<img />");
				
				new_div.attr("id","crop_result_div");
				
				new_div.attr("style",
				"overflow: hidden; width: "+dims.new_w+"px; height: "+dims.new_h+"px;  -"+dims.offset_x+"px -"+dims.offset_y+"px  "
				);
				new_img.attr("src",src).attr("style",
				"position: relative; top: -"+dims.offset_y+"px; left: -"+dims.offset_x+"px; width: "+dims.resized_w+"px; height: "+dims.resized_h+"px; "
				);
				
				new_div.append(new_img);
				
				var edit_button = $("<input type='button' onclick='showEditImage();' name='showEdit' value='Edit Image' />");
				
				
				$('#preview_img').hide();
				$('#crop_instructions').hide();
				$('#preview_box').addClass("hide");
				
				$('#result_box').show().html("").append(new_div).append(edit_button);

				// now show the fieldsets below
				$(".afterUpload").slideDown(600,function()
				{
					$("html,body").animate({scrollTop: $(this).offset().top},1200);
				});
				
			}
		});

		window.onbeforeunload = function(e)
		{
			if($("p.info").hasClass("hide") && $("#add_terms").attr("checked") == false)
			{
				var e = e || window.event;
				var msg = 'Are you sure you want to leave? You have not finished uploading your image yet.';

				// For IE and Firefox
				if (e) {
					e.returnValue = msg;
				}

				// For Safari / chrome
				return msg;
			}
			
		}
			
	}
}

function showEditImage()
{
	$("#crop_result_div").remove();
	$("#result_box").hide();
	$('#crop_instructions').show();
	$('#preview_box').removeClass("hide");
}

// adds an image to a collection
function addToCollectionPopUp(image_id)
{
	var html = "<div>";

	
	
	if($("#userColsJSON").val() != "empty")
	{
		var colsJSON = $.parseJSON($("#userColsJSON").val());
		// cant get length so jQuery each
		
		html += "<p>Choose a collection to add to. Listed below are your collections which do not already contain this image </p>";
		
		var k = 0;
		if( colsJSON == null || typeof(colsJSON) != "object" || typeof(colsJSON[0]) != "object" )
		{
			html += '<p class="error">All your collections have this image already</p>';
		}
		else
		{
			
			html += "<select class='forceStyle' id='collections_add_select'>";
		
			$.each(colsJSON,function()
			{
				html += '<option value="'+this.id+'">'+this.name+'</option>';
				k++;
			});
		
			html += "</select><br/><input type='button' name='collection_add_button' id='collection_add_button' onclick='addToCollection()' value='Add to Collection' />";
		}
		
		
	}
	else
	{
		html += "<p class='error'>You currently don't have any collections. You can create one using the form below or on your profile page.</p>";
	}
	
	html += "<p style='border-top: 1px solid #ccc;padding-top: 10px; margin-top: 10px;'>&nbsp;</p><input type='hidden' id='addColImageId' value='"+image_id+"' />";
	// now add to new collectin
	
	html += "<p>Create a new collection containing this image</p>";
	html += "<ul style='margin-top: 10px;'>";
	html += '	<li><label for="newcol_name">New Collection Name:</label><input type="text" name="newcol_name" class="newcol_name" id="newcol_name" /></li>';
	html += '	<li><input type="button" name="addImageNewCollection" id="addImageNewCollection" onclick="addToNewCollection()" value="Create Collection" /><span class="loading hide">&nbsp;</span></li>';
	html += '</ul>';
	
		
	
	
	
	
	html += '</div>';
	

	
	
	$.facebox(html);
}

// adds an image to a collection
function addFlagPopUp(image_id)
{
	var html = "<div><p>Are you sure you want to flag this image for inappropriate content? Misuse could result in blacklisting.</p>";
	html += '<input type="hidden" name="addFlagImageId" id="addFlagImageId" value="'+image_id+'" />';
	html += '<input type="button" name="flagYes" id="flagYes" onclick="addFlag()" value="Yes" /> <input type="button" name="flagNo" id="flagNo" onclick="closeFacebox();" value="No" />';
	html += '</div>';
	
	$.facebox(html);
}

// closes facebox
function closeFacebox()
{
	$(document).trigger("close.facebox");
}
