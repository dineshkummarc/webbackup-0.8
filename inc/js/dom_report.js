
$(function(){ 
	
	$("#select-report").change(function(){
		window.location = 'log.php?'+$("#select-report").val();
	})

});