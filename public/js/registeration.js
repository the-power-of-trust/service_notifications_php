$(function() {
	$("#registrationform").submit(function(e) {
		e.preventDefault(); // prevent default submit behaviour
		// get values from FORM
          
		var password = $("#password").val();
		var password_confirm = $("#password_confirmation").val();
            
		if (password != password_confirm) {
			$('#registrationerrormessagetext').text($("#password_confirmation").attr('data-validation-required-message'));
				$('#registrationerrormessage').show();
				return false;
		}
            
		if ($('#t_and_c:checked').length == 0) {
			$('#registrationerrormessagetext').text($("#t_and_c").attr('data-validation-required-message'));
			$('#registrationerrormessage').show();
    			var div = $("#igreearea");
			$('html,body').animate({scrollTop: div.offset().top},'slow');
			//div.effect("highlight", {}, 3000);
			return false;
		}
		$('#registrationerrormessage').hide();
            
		$('#responseformat').val('json');
            
		$.ajax({
			url: $('#registrationform').attr('action'),
			type: "POST",
			dataType: "json",
			data: $('#registrationform').serialize(),
			cache: false,
			success: function(data) {
			
				if (data.status == 'ok') {
					$('#registrationsuccess').show();
					$('#newregisteredemail').val($("#email").val());
					$('#registrationform').trigger("reset");
					$('#registrationformdiv').hide();
				} else {
					var errormessage = data.message;
					
					if (data.context !== undefined && data.context.input != '') {
						if ($('#'+data.context.input) !== undefined) {
							$('#'+data.context.input).next('p.help-block').text(data.message);
							$('#'+data.context.input).next('p.help-block').show();
							
							errormessage = $('#generalerrormessage').text();
						}
					}
					
					$('#registrationerrormessagetext').text(errormessage);
					$('#registrationerrormessage').show();
				}
                 
			},
			error: function() {
			// Fail message
				$('#registrationerrormessagetext').text("Sorry , it seems that the server is not responding. Please try again later!");
				$('#registrationerrormessage').show();
				
			},
		});
	});
    /*When clicking on Full hide fail/success boxes */
	$('#login,#name,#email,#company,#password,#password_confirmation').focus(function() {
		$('#registrationerrormessage').hide();
	});
});





