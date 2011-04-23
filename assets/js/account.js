/**
	AJAX Javascript Document for URIKA
	Big ajax document to handle most if not all ajax calls made on the site.
	@author Luke Williams
	@version 0.1
*/
/*
	Contents:
	
	- users -> addfollow and remove follow
			
*/



$(document).ready(function()
{
	$("#a_save_details").click(function(){  saveUserDetails(); });
	$("#a_save_password").click(function(){  saveUserPassword(); });
	$("#a_save_email").click(function(){  saveUserEmail(); });
	$("#a_save_notices").click(function(){  saveUserNotices(); });
	
	$("#a_img_upload_save").click(function(){  saveAvatarChoice("upload"); });
	$("#a_img_gravatar_save").click(function(){  saveAvatarChoice("gravatar") });
	
	$("#a_img_delete_avatar").click(function(){ deleteAvatar(); });
	
	
	if($("ul.notice_list").length > 0)
	{
		$("ul.notice_list li").hover(
			function()
			{
				$(this).find("p.notice_controls").css("display","block");
			},
			function()
			{
				$(this).find("p.notice_controls").css("display","none");
			}
		);
	}
	
	prepAvatarUpload();
});

// avatar upload
function prepAvatarUpload()
{
	if($('#a_img_gravatar_save').length == 1)
	{
		
		 var uploader = new qq.FileUploader({
                element: $('#avatar_upload_box')[0],
                action: base_url+"image/uploadAvatar/",
				allowedExtensions: ['jpg', 'jpeg', 'gif', 'png'],
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
					// hide progress bar
					$('.qq-upload-list').css("display","none");
					
					if(!responseJSON.error)
					{
						var img = $("<img />").attr("src",responseJSON.file_url);
						$("#avatar_upload_div .account_profile_img").html("").append(img);
						$("#avatar_url").text(responseJSON.file_url);
						
						$("#noavatar_info").addClass("hide");
						$("#hasavatar_info").removeClass("hide");
						
						if(responseJSON.update_to_avatar == "true")
						{
							$('#avatar_upload_div').addClass("avatar_choice").find("input#a_img_upload_save").val("Using Upload");
							$('#avatar_gravatar_div').removeClass("avatar_choice").find("input#a_img_gravatar_save").val("Use Gravatar");
						}
						
					}
			
					
				},
				showMessage: function(message){
					$.facebox(message);
					$('.qq-upload-button').css("display","block");
				} 
           });   
		
	}
}

///////// Static functions, called by elements but that dont really change

/**
	Saves Avatar choice
**/
function saveAvatarChoice(choice)
{
	var param = "";
	
	if(choice == "upload")
	{
		param = $('#avatar_url').text();
		if(param == "")
		{
			// no url
			param = "no_upload";
		}
	}
	else if(choice == "gravatar")
	{
		param = "use_gravatar";
	}
	
	// now work out if we need to do anything
	var do_anything = true;
	
	if(($("#avatar_upload_div").hasClass("avatar_choice") == true) && choice == "upload")
	{
		do_anything = false;
	}
	
	if(($("#avatar_gravatar_div").hasClass("avatar_choice") == true) && choice == "gravatar")
	{
		do_anything = false;
	}
	// now deal with the ajax side of things

	
	if(param == "no_upload")
	{
		$.facebox("You need to upload something first");
	}
	else if(do_anything == true)
	{
		
		if(choice == "upload")
		{
			$('#a_img_upload_save').addClass("disabled").attr("disabled","true").parent().find('span.loading').removeClass("hide");
		}
		else if(choice == "gravatar")
		{
			$('#a_img_gravatar_save').addClass("disabled").attr("disabled","true").parent().find('span.loading').removeClass("hide");
		}
		
		
	
		$.ajax({
			type: 'POST',
			url: base_url+"user/saveAvatar/",
			data: "param="+param,
			success: function(rtn_html){
				
				if(rtn_html == "false")
				{
					$.facebox('Something went wrong when saving. Please try again');
				}
				else if(rtn_html == "true")
				{
					
					if(choice == "upload")
					{
						// toggle classes
						$('#avatar_upload_div').addClass("avatar_choice").find("input#a_img_upload_save").val("Using Upload");
						$('#avatar_gravatar_div').removeClass("avatar_choice").find("input#a_img_gravatar_save").val("Use Gravatar");
						$('#a_img_upload_save').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
						
						$("#feature_title img").attr("src",$('#avatar_upload_div .account_profile_img img').attr("src"));
					}
					else if(choice == "gravatar")
					{
						// toggle classes
						$('#avatar_upload_div').removeClass("avatar_choice").find("input#a_img_upload_save").val("Use Upload");
						$('#avatar_gravatar_div').addClass("avatar_choice").find("input#a_img_gravatar_save").val("Using Gravatar");
						$('#a_img_gravatar_save').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
						
						$("#feature_title img").attr("src",$('#avatar_gravatar_div .account_profile_img img').attr("src"));
					}
				}
			}
		});
	}
}

/**
	Delete Avatar
**/
function deleteAvatar()
{
	var img_url = $("#avatar_url").text();
	
	// loading
	$('#a_img_delete_avatar').addClass("disabled").attr("disabled","true").parent().find('span.loading').removeClass("hide");
	
	// ajax call
	$.ajax({
			type: 'POST',
			url: base_url+"user/deleteAvatar/",
			data: "img_url="+img_url,
			success: function(rtn_html){
				
				if(rtn_html == "false")
				{
					$.facebox('Something went wrong when deleting. Please try again');
					$('#a_img_delete_avatar').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
				}
				else if(rtn_html == "nothingtodo")
				{
					$.facebox('Nothing to delete');
					$('#a_img_delete_avatar').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
				}
				else if(rtn_html == "true")
				{
					$('#a_img_delete_avatar').removeClass("disabled").removeAttr("disabled").parent().find('span.loading').addClass("hide");
					
					$("#avatar_upload_div .account_profile_img").html("No Upload");
					$("#avatar_url").text("");
					
					if($("#avatar_upload_div").hasClass("avatar_choice"))
					{
						$("#avatar_upload_div").removeClass("avatar_choice");
						$("#feature_title img").attr("src",base_url+"assets/images/layout/avatar_default.jpg");
					}
						
					$("#noavatar_info").removeClass("hide");
					$("#hasavatar_info").addClass("hide");
					$('.qq-upload-button').css("display","block");
				}
			}
		});
}

/**
	Deals with saving user details
**/
function saveUserDetails()
{

	// big chain function to set ball rolling
	$('#a_save_details').addClass("disabled").attr("disabled","true").parent().find("span").attr("class","").addClass("loading").html("Saving");
	
	var firstname = $("#a_fname").val();
	var surname = $('#a_sname').val();
	var twitter = $('#a_twitter').val();
	var website = $('#a_website').val();
	var location = $('#a_location').val();
	
	var message = new Array(null,null);
	
	if(website == "http://") { website = ""; }
	
	// validation
	
	if(firstname.indexOf("http://") != -1 || surname.indexOf("http://") != -1 || twitter.indexOf("http://") != -1 || location.indexOf("http://") != -1)
	{
		message[0] = "error";
		message[1] = "No links in fields where not appropriate please";
	}
	
	if(message[0] == null)
	{
		$.ajax({
			type: 'POST',
			url: base_url+"user/saveUserDetails/",
			data: "firstname="+firstname+"&surname="+surname+"&twitter="+twitter+"&website="+website+"&location="+location,
			success: function(rtn_html){
				
				if(rtn_html == "false")
				{
					message[0] = "error";
					message[1] = "Something went wrong with the save action. Please try again later";
				}
				else
				{
					message[0] = "success";
					message[1] = "User details saved! The details will now show up on your profile page.";
				}
				
				$('#a_save_details').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
		
			}
		});
	}
	else
	{
		$('#a_save_details').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
	}
}

/**
	Deals with saving user password
**/
function saveUserPassword()
{
	// big chain function to set ball rolling
	$('#a_save_password').addClass("disabled").attr("disabled","true").parent().find("span").attr("class","").addClass("loading").html("Saving");

	// grab values
	var cpass_a = $('#a_cpass_a').val();
	var cpass_b = $('#a_cpass_b').val();
	var npass_a = $('#a_npass_a').val();
	var npass_b = $('#a_npass_b').val();
	
	var message = new Array(null,null);

	// validation

	if(cpass_a == "" || cpass_b == "" || npass_a == "" || npass_b == "")
	{		
		message[0] = "error";
		message[1] = "Please ensure all fields are filled in";
	}
	else if(cpass_a != cpass_b || npass_a != npass_b)
	{
		message[0] = "error";
		message[1] = "Please ensure both pairs of passwords match";
	}
	else if(strongPass(npass_a) == false)
	{
		message[0] = "error";
		message[1] = "Please ensure your new password is at least 8 characters long and contains number AND letters";
	}
	if(cpass_a == npass_a)
	{
		message[0] = "error";
		message[1] = "Your new password must be different from your current password";
	}
	
	if(message[0] == null)
	{
		$.ajax({
			type: 'POST',
			url: base_url+"user/saveUserPassword/",
			data: "cpass="+cpass_a+"&npass="+npass_a,
			success: function(rtn_html){
				
				if(rtn_html == "false")
				{
					message[0] = "error";
					message[1] = "Something went wrong with the save action. Please try again later";
				}
				else if(rtn_html == "wrongpass")
				{
					message[0] = "error";
					message[1] = "The current password you entered does not match your own password";
				}
				else
				{
					message[0] = "success";
					message[1] = "New password saved!";
				}
				
				$('#a_save_password').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
		
			}
		});
	}
	else
	{
		$('#a_save_password').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
	}
	
}

/**
	Deals with saving user e-mail
**/
function saveUserEmail()
{
		// big chain function to set ball rolling
	$('#a_save_email').addClass("disabled").attr("disabled","true").parent().find("span").attr("class","").addClass("loading").html("Saving");

	// grab values
	var cemail = $('#a_cemail').val();
	var nemail = $('#a_nemail').val();
	
	var message = new Array(null,null);

	// validation

	if(cemail == "" || nemail == "")
	{		
		message[0] = "error";
		message[1] = "Please ensure all fields are filled in";
	}
	else if(cemail == nemail)
	{
		message[0] = "error";
		message[1] = "The emails entered are identical";
	}
	else if(validEmail(cemail) == false || validEmail(nemail) == false)
	{
		message[0] = "error";
		message[1] = "Please ensure both emails are valid";
	}
	
	if(message[0] == null)
	{
		$.ajax({
			type: 'POST',
			url: base_url+"user/saveUserEmail/",
			data: "current_email="+cemail+"&new_email="+nemail,
			success: function(rtn_html){
				
				if(rtn_html == "false")
				{
					message[0] = "error";
					message[1] = "Something went wrong with the save action. Please try again later";
				}
				else if(rtn_html == "wrongemail")
				{
					message[0] = "error";
					message[1] = "The current e-mail you entered does not match your own e-mail";
				}
				else if(rtn_html == "newemailexists")
				{
					message[0] = "error";
					message[1] = "The new e-mail you have entered already exists for another user.";
				}
				else
				{
					message[0] = "success";
					message[1] = "New e-mail saved! We have sent an e-mail to the address as a test.";
				}
				
				$('#a_save_email').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
		
			}
		});
	}
	else
	{
		$('#a_save_email').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
	}
}

/**
	Deals with saving user notice format
**/
function saveUserNotices()
{
		// big chain function to set ball rolling
	$('#a_save_notices').addClass("disabled").attr("disabled","true").parent().find("span").attr("class","").addClass("loading").html("Saving");

	// grab values
	var result = "";
	
	$('input[name="a_notice_format"]').each(function()
	{
		if($(this).is(":checked"))
		{
			result += $(this).val();
			result += '.';
		}
		
	});
	
	result = result.substring(0,result.length-1);
	
	var message = new Array(null,null);
	
	if(message[0] == null)
	{
		$.ajax({
		type: 'POST',
		url: base_url+"user/saveUserNotices/",
		data: "notice_format="+result,
		success: function(rtn_html){
			if(rtn_html == "false")
			{
				message[0] = "error";
				message[1] = "Something went wrong with the save action. Please try again later";
			}
			else
			{
				message[0] = "success";
				message[1] = "Notice settings saved!";
			}
			
			$('#a_save_notices').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
	
		}
	});
	}
	else
	{
		$('#a_save_notices').removeClass("disabled").removeAttr("disabled").parent().find("span").attr("class","").addClass(message[0]).html(message[1]);
	}
}

/** UTILITY **/

// test if a password has a mi length of 8 and contains letters and numbers
function strongPass(pass)
{
	var good = true;
	
	if(pass.length < 8)
	{
		good = false;
	}
	
	if(pass.match(/\d+/) == null)
	{
		good = false;
	}

	if(pass.match(/[a-z]/) == null)
	{
		good = false;
	}
	
	return good;
}

// valid email check
function validEmail(email)
{
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	
	if(reg.test(email) == false) 
	{
		return false;
	}
}