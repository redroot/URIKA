/**
	Inline Form Validation
**/
$(document).ready(function(){

	// Signup Form (user/signup)
	var validator = $('form#signupForm').validate({
		messages:{
			s_password_b: {
				equalTo: "Please ensure the passwords match."
			}
		},
		success: function(label)
		{
			label.html("OK").addClass("validfield");
		},
		submitHandler: function(form)
		{
			form.submit();
		}
	});
	
	// Login page form (user/login)
	var validator2 = $('form#loginForm').validate({
		success: function(label)
		{
			label.html("OK").addClass("validfield");
		},
		submitHandler: function(form)
		{
			form.submit();
		}
	});
	
	// Login page form (user/fpassword)
	var validator2 = $('form#fpasswordForm').validate({
		success: function(label)
		{
			label.html("OK").addClass("validfield");
		},
		submitHandler: function(form)
		{
			form.submit();
		}
	});
	
		// Image add form (image/add)
	var validator4 = $('form#image_add_form').validate({
		success: function(label)
		{
			label.html("OK").addClass("validfield");
		},
		submitHandler: function(form)
		{
			//validation on file uploads etc
			if($('#add_filename_dims').val() == "" || $('#add_filename_temp').val() == "")
			{
				$.facebox("Please upload a file before submitting the form.");
			}
			else
			{
				form.submit();
			}
		}
	});
	
	// Image edit form (image/edit)
	var validator5 = $('form#image_edit_form').validate({
		success: function(label)
		{
			label.html("OK").addClass("validfield");
		},
		submitHandler: function(form)
		{
			
				form.submit();
			
		}
	});
	
});