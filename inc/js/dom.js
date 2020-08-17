/*
	WebBackup
*/

$(function(){ 

	// Form style with uniform
	$("select, input, button").uniform(); 

	// Show new Schedule Form
	$(".new-schedule-button").click(function(){
		$("#my-acount").hide();
		$("#new-schedule").slideToggle("slow");
	})

	// Show My Acount Form
	$(".my-acount-button").click(function(){
		$("#new-schedule").hide();
		$("#my-acount").slideToggle("slow");
	})

	// Hide Schedule Form
	$("#cancel").click(function(){
		$("#new-schedule").slideToggle("slow");
		formSchedule.resetForm();
		return false;
	})

	// Show/Hide File backup options
	$("#files").click(function(){
		if($(this).is(':checked'))
			$("#field-files").show(200);
		else
			$("#field-files").hide(200);
	})

	// Show/Hide DB backup options
	$("#db").click(function(){
		if($(this).is(':checked'))
			$("#field-db").show(200);
		else
			$("#field-db").hide(200);
	})

	$(".report-button").click(function(){
		window.open("log.php", "report", "width=800,height=600");
	})

	// Validate Schedule Form
	var formSchedule = $("#new-schedule-form").validate({
						rules: {
							name: {required: true, minlength: 2},
							'backup[]': {required: true, minlength: 1},
							folder: {required: "#files:checked"},
							host: {required: "#db:checked"},
							user: {required: "#db:checked"},
							dbname: {required: "#db:checked"},
							ftphost: "required",
							ftpuser: "required",
							ftppass: "required",
							ftpfolder: "required"
						},
						messages: {
							'backup[]': {
								minlength: "Please select at least two topics you'd like to receive."
							}
						}
					});

	// Validate My Acount Form
	$("#my-acount-form").validate({
		rules: {"user-name": "required","user-email": "required","user-login": "required"}
	});	

});