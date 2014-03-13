(function ($) {
	$(document).ready(function() {
		// Validate the form
		$('#form').validate({
			focusCleanup: true,
			focusInvalid: false,
			rules: {
				courtId: {
					required: true,
					digits: true
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
		$('body').showhide({ 'inline': true });
	});

	var doesntKnowCitation = function() {
		return ($('#knowsCitation').val() == 'No');
	};
	var knowsCitation = function() {
		return ($('#knowsCitation').val() == 'Yes');
	};

})(jQuery);
