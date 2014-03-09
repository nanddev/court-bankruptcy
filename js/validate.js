(function ($) {
	$(document).ready(function() {
		// Validate the form
		$('#form').validate({
			debug: true,
			rules: {
				courtId: {
					required: true
				},
				knowsCitation: {
					required: true
				},
				lastName: {
					required: { depends: doesntKnowCitation }
				},
				dob: {
					required: { depends: doesntKnowCitation }
				},
				citation: {
					required: { depends: knowsCitation }
				}
			},
			messages: {
				courtId: "Please select your court.",
				knowsCitation: "Please answer this question.",
				lastName: "Please enter your last name.",
				dob: "Please specify your date of birth.",
				citation: "Please specify your citation number."
			}
		}); 

		// Setup the show/hide functionality
		$('.show-hide').formShowHide();
	});

	var doesntKnowCitation = function() {
		return ($('#knowsCitation').val() == 'No');
	};
	var knowsCitation = function() {
		return ($('#knowsCitation').val() == 'Yes');
	};

})(jQuery);
