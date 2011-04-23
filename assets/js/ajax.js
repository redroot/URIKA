/**
	AJAX Javascript Document for URIKA
	Big ajax document to handle most if not all ajax calls made on the site.
	@author Luke Williams
	@version 0.1
*/
/*
	Contents:
	
	- users -> addfollow and remove follow
	- image -> add favourites
	- comments -> add and delete
	- collections -> adding
	- flags
	- pagination
			
*/



$(document).ready(function()
{
		
});

///////// Static functions, called by elements but that dont really change

function addFollow(subject_id)
{
	$.ajax({
		type: 'POST',
		url: base_url+"user/addfollow/",
		data: "subject="+subject_id,
		success: function(rtn_json){
			rtn_json = JSON.parse(rtn_json);
			if(rtn_json.msg == "false")
			{
				$.facebox('Unfortunately the add didnt work. If you believe this is a bug, please get in touch');
			}
			else
			{
				$('#profile_follow').remove();
				
				// now add unfollow button we've got in rtn_html after the profile image
				$('#feature_title img').after(rtn_json.newunfollow);
				
				//incremement followers list
				
				//long grab
				var followers_count = $('#user_profile .col-4.right .tabs ul.tabNav li:first a strong');
				var fol_count = parseInt(followers_count.html());
				followers_count.html(fol_count+1);
				
				// now append li if less than 10 users
				
				// first are there any users already there
				
				if($("#followersList").length > 0 && $("#followersList li").length < 10)
				{
					$("#followersList").append(rtn_json.newfollowli);
				}
				else
				{
					// have to create the list
					var ul_followers = $('<ul></ul>');
					$(ul_followers).attr("id","followersyList").addClass("smallUserList").append(rtn_json.newfollowli);
					 $('#user_profile .col-4.right .tabs .tabContent:first').html("").append(ul_followers);
				}
				
			}
		}
	});
}

function deleteFollow(subject_id,user_id)
{
	$.ajax({
		type: 'POST',
		url: base_url+"user/deletefollow/",
		data: "subject="+subject_id,
		success: function(rtn_json){
			rtn_json = JSON.parse(rtn_json);
			if(rtn_json.msg == "false")
			{
				$.facebox('Unfortunately the delete didnt work. If you believe this is a bug, please get in touch');
			}
			else
			{
				$('#profile_unfollow').remove();
				
				
				// now add unfollow button we've got in rtn_html after the profile image
				$('#feature_title img').after(rtn_json.newfollow);
				
				//incremement followers list
				
				//long grab
				var followers_count = $('#user_profile .col-4.right .tabs ul.tabNav li:first a strong');
				var fol_count = parseInt(followers_count.html());
				followers_count.html(fol_count-1);
				
				//hide lsit from followers
				$('#user_profile .col-4.right .tabs .tabContent:first ul.smallUserList li.sul_'+user_id).css("display","none");
			}
		}
	});
}

function addFavourite(image_id)
{
	var type = $("#subject_type").val();
	
	$.ajax({
		type: 'POST',
		url: base_url+"favourite/add/",
		data: "subject_id="+image_id+"&object_type="+type,
		success: function(rtn_json){
			rtn_json = JSON.parse(rtn_json);
			if(rtn_json.msg == "false")
			{
				$.facebox("Unfortunately the favourite add didn't work. If you believe this is a bug please get in touch");
			}
			else
			{
				$("#ajax_fav").removeAttr("onclick").removeAttr("id").removeAttr("href").text("Favourited!");
				
				// first are there any users already there
				
				//long grab
				var favs_count = $('.col-12.left .tabs ul.tabNav li:last a strong');
				var favs = parseInt(favs_count.html());
				favs_count.html(favs+1);
				
				if($("#imageFavsList").length > 0)
				{
					$("#imageFavsList").append(rtn_json.newfavli);
				}
				else
				{
					// have to create the list
					var ul_favs = $('<ul></ul>');
					$(ul_favs).attr("id","imageFavsList").addClass("wideUserList").append(rtn_json.newfavli);
					 $('.col-12.left .tabs .tabContent:last').html("").append(ul_favs);
				}
			}
		}
	});
}

//comment function

function addComment()
{

	var user_id = parseInt($("#comment_user_id").val());
	var subject_id = parseInt($("#comment_subject_id").val());
	var subject_user_id = parseInt($("#subject_user_id").val());
	var subject_name = $("#subject_name").val();
	
	var type = $("#subject_type").val();
	
	var text = $("#comment_text").val();
	text = text.replace(/(<([^>]+)>)/ig,"");
	
	// ajax here
	$("#comment_save").val("Posting Comment...").attr("disabled","disabled").parent().prepend($("<span class='loading'>&nbsp;</span>"));
	
	$("#comment_form p.toRemove").remove();
	
	if(text == "")
	{
		$("#comment_save").val("Post Comment").removeAttr("disabled").parent().find("span").remove();
					
		// message append
		var message = $('<p></p>').addClass("error").text("Please write a comment before pressing the post comment button!");
		
		$('#comment_form').prepend(message);
	}
	else
	{
		$.ajax({
			type: 'POST',
			url: base_url+"comment/add/",
			data: "user_id="+user_id+"&subject_id="+subject_id+"&sub_user_id="+subject_user_id+"&subject_name="+subject_name+"&type="+type+"&text="+text,
			dataType: "json",
			success: function(rtn_json){
				if(rtn_json.result == "false")
				{
					
					$.facebox("Unfortunately the comment add didn't work. If you believe this is a bug please get in touch");
				}
				else if(rtn_json.result == "toosoon")
				{
					$("#comment_save").val("Post Comment").removeAttr("disabled").parent().find("span").remove();
					
					// message append
					$("#comment_form p.error").remove();
					var message = $('<p></p>').addClass("error").text("You can only write one comment every two minutes, for security reasons. Please wait before posting again.");
					
					$('#comment_form').prepend(message);
				}
				else if(rtn_json.result == "true")
				{	
					
					$("#comment_form p.error").remove();
					
					// now construct comment to show
					
					var new_comment = $("<div></div>").addClass("comment").attr("id","comment_"+rtn_json.comment_id).css("display","none");
					
					// add classes where needed
					if(rtn_json.is_uploader == "true")
					{
						new_comment.addClass("uploader_comment");
					}
					
					var comments_count = $(".comments .comment");
					
					if(comments_count % 2 == 0)
					{
						new_comment.addClass("even");
					}
					else
					{
						new_comment.addClass("odd");
					}
				
					// now create img
					
					var img = $("<img src='"+rtn_json.profile_url+"' width='70' height='70' />"); 
					var img_div = $("<div></div>").addClass("comment_img").append(img);
					
					// create data info
					var info_div = $("<div></div>").addClass("comment_info").html("by <strong><a href='"+rtn_json.user_url+"'>"+rtn_json.username+"</a></strong> on "+rtn_json.datetime);
					if(rtn_json.is_uploader == "true")
					{
						var new_html = info_div.html() + "<span> - uploader comment</span>";
						info_div.html(new_html);
					}
					
					
					// add text
					var text_div = $("<div></div>").addClass("comment_content").html(text.replace( /\n/g, '<br \\>' ));
					
					// combine everything
					new_comment.append(img_div);
					new_comment.append(info_div);
					new_comment.append(text_div);
					new_comment.append($('<div class="kill">&nbsp;</div>'));
					
					// sort out comment box
					$("#comment_save").val("Comment Posted!").attr("disabled","disabled").parent().find("span").remove();
					$("#comment_text").val("");
					
					$("#comment_form p.error").remove();
					var message = $('<p></p>').addClass("success").text("Comment posted successfully. Please wait 2 minutes before posting another comment");
					
					$('#comment_form').prepend(message);
					
					
					// remove no comments box if there is any
					$("p.nocomments").remove();
					
					$('.comments').prepend(new_comment);
					new_comment.fadeIn(500);
					
					
					
					// now update total at the top
					var comment_count = parseInt($("ul.tabNav li:first a strong").text()) + 1;
					if(comments_count == 1)
					{
						$("ul.tabNav li:first a").html("<strong>1</strong> Comment");
					}
					else
					{
						$("ul.tabNav li:first a").html("<strong>"+comment_count+"</strong> Comments");
					}
					
					
				}
			}
		});
	}
				
}

// delete comment function

function deleteComment(id)
{
	// remove onclick attr and changing text
	$("#comment_"+id+" .comment_info .delete_link").html("Deleting Comment...").removeAttr("onclick");
	
	//now ajax bit
	$.ajax({
		type: 'POST',
		url: base_url+"comment/delete/",
		data: "delete_id="+id,
		success: function(rtn_html){
			if(rtn_html == "false")
			{
				$.facebox("The comment could not be deleted. Please refresh and try again. If the problem persists please contact us");
			}
			else
			{
				$("#comment_"+id).fadeOut(300,function()
				{
					$(this).remove();
					
							var comment_count = parseInt($("ul.tabNav li:first a strong").text()) - 1;
					
					if(comment_count < 1)
					{
						
						$(".comments").html("<p class='nocomments'>No Comments</p>");
					}
					// now update total at the top
			
					if(comment_count == 1)
					{
						$("ul.tabNav li:first a").html("<strong>1</strong> Comment");
					}
					else
					{
						$("ul.tabNav li:first a").html("<strong>"+comment_count+"</strong> Comments");
					}
					
				});
			}
		}
	});
	
}

// toggle read function
function toggleNoticeRead(id)
{
	
	var notice = $("#notice_"+id);
	var loading = $("<span class='loading'>&nbsp;</span>");
	
	notice.find("p.notice_controls").prepend(loading);
	
	//now ajax bit
	$.ajax({
		type: 'POST',
		url: base_url+"user/toggleNoticeRead/",
		data: "notice_id="+id,
		success: function(rtn_html){
			if(rtn_html == "false")
			{
				notice.find("p.notice_controls span.loading").remove();
				$.facebox("The notice could not be modified. Please refresh and try again. If the problem persists please contact us");
			}
			else if(rtn_html == "true")
			{
				
				if(notice.hasClass("notice_new"))
				{
					notice.removeClass("notice_new").find("a.toggle_read_link").html("Mark as Unread");
					
				}
				else
				{
					notice.addClass("notice_new").find("a.toggle_read_link").html("Mark as Read");
				}
				
				// now update span at the top
				if($("#notices_li a span").length == 1)
				{
					if($(".notice_new").length == 0)
					{
						$("#notices_li a span").remove();
					}
					else
					{
						$("#notices_li a span").html($(".notice_new").length);
					}
				}
				else
				{
					var span = $("<span></span>").html($(".notice_new").length);
					$("#notices_li a").append(span);
					
				}
				
				
				
				notice.find("p.notice_controls span.loading").remove();
			}
		}
	});
}

// toggle all as read or unread
var button_press = 1; // dont want people abusing the system
function toggleAllNotices(value)
{
	if(value == 1 || value == 0)
	{
		var ids_list = $("#notice_ids_list").val();
		
		button_press++;
		
		if(button_press > 10)
		{
			$.facebox("Please refrain from trying to overload the system like that");
		}
		else
		{
			var loading = $("<span class='loading'>&nbsp;</span>");
			
			$("#notice_buttons").append(loading);
			
			
			//now ajax bit
			$.ajax({
				type: 'POST',
				url: base_url+"user/multipleToggleRead/",
				data: "ids_list="+ids_list+"&value="+value,
				success: function(rtn_html){
					if(rtn_html == "false")
					{
						$("#notice_buttons .loading").remove();
						$.facebox("The notices could not be modified. Please refresh and try again. If the problem persists please contact us");
					}
					else if(rtn_html == "true")
					{
						
						if(value == 1)
						{
							$(".notice_list li").addClass("notice_new").find("a.toggle_read_link").html("Mark as Read");
						}
						else if(value == 0)
						{
							$(".notice_list li").removeClass("notice_new").find("a.toggle_read_link").html("Mark as Unread");
						}
						
						// now update span at the top
						if($("#notices_li a span").length == 1)
						{
							if($(".notice_new").length == 0)
							{
								$("#notices_li a span").remove();
							}
							else
							{
								$("#notices_li a span").html($(".notice_new").length);
							}
						}
						else
						{
							if($(".notice_new").length > 0)
							{
								var span = $("<span></span>").html($(".notice_new").length);
								$("#notices_li a").append(span);
							}
							
						}
						
						
						$("#notice_buttons .loading").remove();
					}
				}
			});
			
		}
		
	}
}

// delete notice function
function deleteNotice(id)
{	
	var notice = $("#notice_"+id);
	var loading = $("<span class='loading'>&nbsp;</span>");
	
	notice.find("p.notice_controls").prepend(loading);
	
	
	//now ajax bit
	$.ajax({
		type: 'POST',
		url: base_url+"user/deleteUserNotice/",
		data: "notice_id="+id,
		success: function(rtn_html){
			if(rtn_html == "false")
			{
				notice.find("p.notice_controls span.loading").remove();
				$.facebox("The notice could not be deleted. Please refresh and try again. If the problem persists please contact us");
			}
			else if(rtn_html == "true")
			{		
					// now update span at the top
				
			
			
				notice.fadeOut(300,function()
				{
					notice.remove()
					
					var count = $(".notice_new").length;
					
					if($(".notice_new").length == 0)
					{
						$("#notices_li a span").remove();
					}
					else
					{
						$("#notices_li a span").html(count);
					}
				
				});
			}
		}
	});
	
	
}

//
function addCollection()
{
	// grab values
	var col_name = $("#facebox .newcol_name").val();	
	
	if(col_name != "")
	{
		$('#facebox p.error').remove();
		
		// loading
		$('#saveNewCollection').addClass("disabled").attr("disabled","true").parent().find('span.loading').removeClass("hide");
		
		// ajax request
		$.ajax({
			type: 'POST',
			url: base_url+"collection/addAJAX/",
			data: "col_name="+col_name,
			success: function(rtn_html){
				if(rtn_html == "false")
				{
					// hide facebox
					$('#saveNewcollection').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
					$.facebox("Something went wrong when saving the new collection. Please refresh and try again");
				}
				else if(rtn_html.indexOf("collection_left") != -1)
				{		
					// create new element on page
					// hide facebox
					
					// hide intro message
					$('#collectionIntro').fadeOut(300,function()
					{ 
						$(this).remove();
					});
					
					
					var new_html = $('ul.bigCollectionsList').html();
					new_html = new_html + "" + rtn_html;
					
					$('ul.bigCollectionsList').html(new_html);
					$('ul.bigCollectionsList li:last').fadeIn(300);
					$('#saveNewCollection').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
					
					// update total 
					var count = parseInt($('#userImagesTabs ul.tabNav li:nth-child(4) a strong').html());
					var new_html = "";
					
					count = count+1;
					
					if(count == 1)
					{
						new_html = '<strong>'+count+'</strong> Collection';
					}
					else
					{
						new_html = '<strong>'+count+'</strong> Collections';
					}
		
					$('#userImagesTabs ul.tabNav li:nth-child(4) a').html(new_html)
					
					$(document).trigger('close.facebox');
				}
			}
		});
	}
	else
	{	
		$('#facebox p.error').remove();
		var error_p = $("<p class='error'>Please enter a collection name before saving</p>");
		$('#facebox .content').append(error_p);
	}
	
}

// adds an image to a collection
function addToNewCollection()
{
	// grab values
	var image_id = parseInt($("#addColImageId").val());
	var col_name = $("#newcol_name").val()
	
	if(col_name == "")
	{
		var error_p = $("<p class='error'>Please enter a collection name before saving</p>");
		$('#facebox .content').append(error_p);
	}
	else
	{	
		$("#facebox p.error:last").remove();
		
		// use ajax to add to collection
		$("#addImageNewCollection").val("Creating Collection...");
		
		// ajax request
			$.ajax({
				type: 'POST',
				url: base_url+"collection/addImageToNew/",
				data: "newcol_name="+col_name+"&image_id="+image_id,
				success: function(rtn_html){
					if(rtn_html == "false")
					{
						// hide facebox
						$("#addImageNewCollection").val("Create Collection");
						$.facebox("Something went wrong when saving to the new collection. Please refresh and try again");
					}
					else if(rtn_html == "colfail")
					{
						// hide facebox
						$("#addImageNewCollection").val("Create Collection");
						$.facebox("Something went wrong when creating the new collection. Please refresh and try again");
					}
					else if(rtn_html == "true")
					{		
						
						$.facebox("Image added to new collection successfully");
						
						window.setTimeout(function()
						{
							$(document).trigger('close.facebox');
						}, 800);
					}
				}
			});
	}
	
	
}

// adds an image to a collection
function addToCollection()
{
	// grab values
	var image_id = parseInt($("#addColImageId").val());
	var col_id = parseInt($("#collections_add_select").val());
	
	
	// use ajax to add to collection
	$("#collection_add_button").val("Saving to Collection...");
	
	// ajax request
		$.ajax({
			type: 'POST',
			url: base_url+"collection/addImage/",
			data: "col_id="+col_id+"&image_id="+image_id,
			success: function(rtn_html){
				if(rtn_html == "false")
				{
					// hide facebox
					$("#collection_add_button").val("Add to Collection");
					$.facebox("Something went wrong when saving the new collection. Please refresh and try again");
				}
				else if(rtn_html == "true")
				{		
					$("#collection_add_button").val("Add to Collection");
					// update colsJSON
					var colsJSON = $.parseJSON($("#userColsJSON").val());
					
					var k = 0;
					var delete_index = -1;
					while(typeof(colsJSON[k]) == "object")
					{
						if(colsJSON[k].id == col_id)
						{
							delete_index = k;
							break;
						}
						k++;
					}
					
					if(k != -1)
					{
						delete colsJSON[k];
					}
					
					$("#userColsJSON").val(JSON.stringify(colsJSON));
					
					$.facebox("Image added to collection successfully");
					
					window.setTimeout(function()
					{
						$(document).trigger('close.facebox');
					}, 800);
				}
			}
		});
	
	
}

// adds an image to flag lsit
function addFlag()
{
	// grab values
	var image_id = parseInt($("#addFlagImageId").val());
	
	
	// use ajax to add to collection
	$("#flagYes").val("Flagging...");
	$("#flagNo").hide();
	// ajax request
	$.ajax({
		type: 'POST',
		url: base_url+"image/flag/",
		data: "image_id="+image_id,
		success: function(rtn_html){
			if(rtn_html == "false")
			{
				$.facebox("Something went wrong when saving the new collection. Please refresh and try again");
			}
			else if(rtn_html == "true")
			{		
				var flag_li = $("span.flag_link").parent();
				
				$("span.flag_link").remove();
				
				flag_li.html('<li><span class="flag_link">Flagged!</span></li>');
				
				$.facebox("Image flagged. Moderators have been informed.");
				
				window.setTimeout(function()
						{
							$(document).trigger('close.facebox');
						}, 800);
			}
		}
	});
	
	
}

//////////////////// PAGINATION Functions

// user uploads
// @param user_id : id of the user in question
// @param change : 1 or -1, which way to go
// @param total : overall total so we know when to stop
function userUploadsNav(user_id,change, total)
{
	var current_page = parseInt($("#current_uploads_page").text());
	var new_page = change + current_page;
	
	var new_total;
	var per_page = 20;
	
	
	if(new_page > 0)
	{
		// add loading state
		$("div.uploads_pagination").find('span.loading').removeClass("hide");
		$.ajax({
			type: 'POST',
			url: base_url+"browse/ajaxresults/images/",
			data: "user_id="+user_id+"&page="+new_page,
			success: function(rtn_html){
				if(rtn_html == "no_more")
				{
					$("#uploads_next").hide();
					$("div.uploads_pagination").find('span.loading').addClass("hide");
				}
				else if(rtn_html == "false")
				{
					alert("Something went wrong when paginating the page, please refresh and try again");
					$("div.uploads_pagination").find('span.loading').addClass("hide");
				}
				else
				{
					// fade out original then append new results
					$("ul#userUploads li").fadeOut(200,function()
					{
						$(this).remove();
						if($("ul#userUploads li").length == 0)
						{
							$("ul#userUploads").html(rtn_html);
							$("div.uploads_pagination").find('span.loading').addClass("hide");
							new_total = $("ul#userUploads li").length;
							
							if(new_total == per_page) // full page
							{
								// only show previous if the page is full and this is not page 1
								if(new_page > 1)
								{
									$("#uploads_prev").show();
									$("#uploads_next").show();
								}
								else
								{
									$("#uploads_prev").hide();
									$("#uploads_next").show();
								}
								
								// one last check to check if this current page holds the last info
								if((new_page * per_page) == total)
								{
									$("#uploads_next").hide();
								}
							}
							else
							{	
								$("#uploads_prev").show();
								$("#uploads_next").hide();								
							}
							
							$("#current_uploads_page").text(new_page);
							
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
					});

					
				
				}
			}
		});
	}

}


// moodboard
// @param user_id : id of the image in question
// @param change : 1 or -1, which way to go
// @param total : overall total so we know when to stop
function userMBsNav(user_id,change, total)
{
	var current_page = parseInt($("#current_mbs_page").text());
	var new_page = change + current_page;
	
	var new_total;
	var per_page = 20;
	
	
	if(new_page > 0)
	{
		// add loading state
		$("div.mbs_pagination").find('span.loading').removeClass("hide");
		$.ajax({
			type: 'POST',
			url: base_url+"browse/ajaxresults/moodboards/",
			data: "user_id="+user_id+"&page="+new_page,
			success: function(rtn_html){
				if(rtn_html == "no_more")
				{
					$("#mbs_next").hide();
					$("div.mbs_pagination").find('span.loading').addClass("hide");
				}
				else if(rtn_html == "false")
				{
					alert("Something went wrong when paginating the page, please refresh and try again");
					$("div.mbs_pagination").find('span.loading').addClass("hide");
				}
				else
				{
					// fade out original then append new results
					$("ul#userMoodboards li").fadeOut(200,function()
					{
						$(this).remove();
						if($("ul#userMoodboards li").length == 0)
						{
							$("ul#userMoodboards").html(rtn_html);
							$("div.mbs_pagination").find('span.loading').addClass("hide");
							new_total = $("ul#userMoodboards li").length;
							
							if(new_total == per_page) // full page
							{
								// only show previous if the page is full and this is not page 1
								if(new_page > 1)
								{
									$("#mbs_prev").show();
									$("#mbs_next").show();
								}
								else
								{
									$("#mbs_prev").hide();
									$("#mbs_next").show();
								}

								// one last check to check if this current page holds the last info
								if((new_page * per_page) == total)
								{
									
									$("#mbs_next").hide();
								}
							}
							else
							{	
								$("#mbs_prev").show();
								$("#mbs_next").hide();								
							}
							
							$("#current_mbs_page").text(new_page);
							
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
					});

					
				
				}
			}
		});
	}

}

// user favourites
// @param subject_id : id of the image in question
// @param change : 1 or -1, which way to go
// @param total : overall total so we know when to stop
function userFavsNav(user_id,change, total)
{
	var current_page = parseInt($("#current_favourites_page").text());
	var new_page = change + current_page;
	
	var new_total;
	var per_page = 20;
	
	
	if(new_page > 0)
	{
		// add loading state
		$("div.favourites_pagination").find('span.loading').removeClass("hide");
		$.ajax({
			type: 'POST',
			url: base_url+"browse/ajaxresults/images/",
			data: "user_id="+user_id+"&user_favs=1&page="+new_page,
			success: function(rtn_html){
				if(rtn_html == "no_more")
				{
					$("#favs_next").hide();
					$("div.favourites_pagination").find('span.loading').addClass("hide");
				}
				else if(rtn_html == "false")
				{
					alert("Something went wrong when paginating the favourites, please refresh and try again");
					$("div.favourites_pagination").find('span.loading').addClass("hide");
				}
				else
				{
					// fade out original then append new results
					$("ul#userFavs li").fadeOut(200,function()
					{
						$(this).remove();
						if($("ul#userFavs li").length == 0)
						{
							$("ul#userFavs").html(rtn_html);
							$("div.favourites_pagination").find('span.loading').addClass("hide");
							new_total = $("ul#userFavs li").length;
							
							if(new_total == per_page) // full page
							{
								// only show previous if the page is full and this is not page 1
								if(new_page > 1)
								{
									$("#favs_prev").show();
									$("#favs_next").show();
								}
								else
								{
									$("#favs_prev").hide();
									$("#favs_next").show();
								}
								
								// one last check to check if this current page holds the last info
								if((new_page * per_page) == total)
								{
									$("#favs_next").hide();
								}
							}
							else
							{	
								$("#favs_prev").show();
								$("#favs_next").hide();								
							}
							
							$("#current_favourites_page").text(new_page);
							
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
					});

					
				
				}
			}
		});
	}

}

// comments
// @param type : image or moodboard
// @param subject_id : id of the image in question
// @param change : 1 or -1, which way to go
// @param total : overall total so we know when to stop
function commentsNav(type,subject_id,change, total)
{
	var current_page = parseInt($("#current_comment_page").text());
	var new_page = change + current_page;
	
	var new_total;
	var per_page = 10;
	
	
	if(new_page > 0)
	{
		// add loading state
		$("div.comments_buttons").find('span.loading').removeClass("hide");
		$.ajax({
			type: 'POST',
			url: base_url+"browse/ajaxresults/comments/",
			data: "subject_id="+subject_id+"&page="+new_page+"&comment_type="+type,
			success: function(rtn_html){
				if(rtn_html == "no_more")
				{
					$("#comments_next").hide();
					$("div.comments_buttons").find('span.loading').addClass("hide");
				}
				else if(rtn_html == "false")
				{
					alert("Something went wrong when paginating. please reload and try again.");
					$("div.comments_buttons").find('span.loading').addClass("hide");
				}
				else
				{
					// fade out original then append new results
					$("div.comments div.comment").fadeOut(200,function()
					{
						$(this).remove();
						if($("div.comments div.comment").length == 0)
						{
							$("div.comments").html(rtn_html);
							$("div.comments_buttons").find('span.loading').addClass("hide");
							new_total = $("div.comments div.comment").length;
							
							if(new_total == per_page) // full page
							{
								// only show previous if the page is full and this is not page 1
								if(new_page > 1)
								{
									$("#comments_prev").show();
									$("#comments_next").show();
								}
								else
								{
									$("#comments_prev").hide();
									$("#comments_next").show();
								}
								
								// one last check to check if this current page holds the last info
								if((new_page * per_page) == total)
								{
									$("#comments_next").hide();
								}
							}
							else
							{	
								$("#comments_prev").show();
								$("#comments_next").hide();								
							}
							
							$("#current_comment_page").text(new_page);
							
							// re-do stylings
							if($(".comment").length > 0)
							{
								
								$(".comment").unbind("hover").hover(
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
						}
					});

					
				
				}
			}
		});
	}

}