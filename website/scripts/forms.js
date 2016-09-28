function formhash(form, password) {
    // Create a new element input, this will be our hashed password field. 
    var p = document.createElement("input");
 
    // Add the new element to our form. 
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);
 
    // Make sure the plaintext password doesn't get sent. 
    password.value = "";
 
    // Finally submit the form. 
    form.submit();
}
 
function regformhash(form, email, password, confirmpwd) {
     // Check each field has a value
    if (email.value == ''     || 
          password.value == ''  || 
          confirmpwd.value == '') {
 
        alert('You must provide all the requested details. Please try again');
        return false;
    }
 
    // Check that the password is sufficiently long (min 6 chars)
    // The check is duplicated below, but this is included to give more
    // specific guidance to the user
    if (password.value.length < 6) {
        alert('Passwords must be at least 6 characters long.  Please try again');
        form.password.focus();
        return false;
    }
 
    // At least one number, one lowercase and one uppercase letter 
    // At least six characters 
 
    var re = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}/; 
    if (!re.test(password.value)) {
        alert('Passwords must contain at least one number, one lowercase and one uppercase letter.  Please try again');
        return false;
    }
 
    // Check password and confirmation are the same
    if (password.value != confirmpwd.value) {
        alert('Your password and confirmation do not match. Please try again');
        form.password.focus();
        return false;
    }
 
    // Create a new element input, this will be our hashed password field. 
    var p = document.createElement("input");
 
    // Add the new element to our form. 
    form.appendChild(p);
    p.name = "p";
    p.type = "hidden";
    p.value = hex_sha512(password.value);
 
    // Make sure the plaintext password doesn't get sent. 
    password.value = "";
    confirmpwd.value = "";
 
    // Finally submit the form. 
    form.submit();
    return true;
}

function verifyNewAccountPasswords() {
    var re = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}/; 
    
	if ($("#confirmpwd").val().length > 0 && $("#password").val() != $("#confirmpwd").val()) {
		$("#confirmpwdSpan").show();
		$("#passwordSpan").show();
		$("#confirmpwdDiv").addClass("has-error");
		$("#passwordDiv").addClass("has-error");
	}
	else if ($("#password").val().length < 6 || !re.test($("#password").val())) {
		$("#passwordSpan").show();
		$("#passwordDiv").addClass("has-error");
		$("#confirmpwdSpan").hide();
		$("#confirmpwdDiv").removeClass("has-error");
	}
	else {		
		$("#confirmpwdSpan").hide();
		$("#passwordSpan").hide();
		$("#confirmpwdDiv").removeClass("has-error");
		$("#passwordDiv").removeClass("has-error");
	}

}
