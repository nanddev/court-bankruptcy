$(document).ready(function() {
	toggleButton();

	$('.paying').change(function() {
		toggleButton();
	});
});

function toggleButton() {
	if ($('.paying').is(":checked")) {
		$('#checkout').show();
	}
	else {
		$('#checkout').hide();
	}
}
