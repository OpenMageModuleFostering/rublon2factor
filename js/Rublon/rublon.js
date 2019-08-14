document.observe("dom:loaded", function() {
	
	// API registration actions
	var registrationFormHidden = $('rublon-consumer-registration');	
	// Begin registration process if registration form was founded
	if (registrationFormHidden) registrationFormHidden.submit();
	
	var buttonActivate = $('rublon-button-activate');
//	buttonActivate.writeAttribute('disabled', true);	
	
	var apiTermsAgree = $('rublon-apireg-terms-agreed');
	
//	apiTermsAgree.observe('click', function() {				
//	
//		if (this.checked) {
//	
//			buttonActivate.writeAttribute('disabled', false);
			
			// Bind API registration click action
			buttonActivate.observe('click', function(e){
				e.preventDefault();
				if (apiTermsAgree.checked) {
					$('rublon-consumer-registration-init').submit();
				} else {
					alert('Please agree to the Rublon Terms of Service.');
					return false;
				}
			});
			
//		} else {
//			buttonActivate.writeAttribute('disabled', true);
//		}
	
//	});
	
});