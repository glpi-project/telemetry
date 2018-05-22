function actionProfileForm(ref){
	console.log(ref);
	var modalElement = document.getElementById('modal_body_profile_update');
	var selectCountry = document.getElementById('countries_select_modal_profile');
	document.getElementById('ref_id_form_update').value = ref.id;
	for( var i=0 ; i<selectCountry.options.length ; i++){
		if(selectCountry.item(i).value == ref.country.toUpperCase()){
			selectCountry.options[i].selected = true;
		      $("#warning-country").remove();
		      $("#countries_select_modal_profile")
		         .val(selectCountry.item(i).value)
		         .trigger("change");
		}
	}

	modalElement.children[0].children[1].value = ref.name;
	modalElement.children[1].children[1].value = ref.url;
	modalElement.children[3].children[1].value = ref.phone;
	modalElement.children[4].children[1].value = ref.email;
	modalElement.children[5].children[1].value = ref.referent;
	modalElement.children[6].children[1].value = ref.num_assets;
	modalElement.children[7].children[1].value = ref.num_helpdesk;
	modalElement.children[8].children[1].value = ref.comment;
}

function actionProfileDelete(ref){
	document.getElementById('input_form_profile_delete_id').value = ref.id;
}

function actionProfileUser(user){
	document.getElementById('input_form_profile_user_id').value = user.id;
	document.getElementById('input_form_profile_user_name').value = user.username;
	document.getElementById('input_form_profile_user_mail').value = user.email;

	$('#input_form_profile_user_new_password').keyup(function(){
		var pass = $(this).val();
		isAtLeastOneSpecial(pass);

		var lengthPass = isAtLeastLength(8,pass);
		var uppercasePass = isOneUpper(pass);
		var lowercasePass = isOneLower(pass);
		var digitPass = isAtLeastOneDigit(pass);

		var progress = 0;

		if(lengthPass){
			progress += 25;
		}
		if(uppercasePass){
			progress += 25;
		}
		if(lowercasePass){
			progress += 25;
		}
		if(digitPass){
			progress += 25;
		}

		divPass = document.getElementById('div_form_profile_user_new_password');
		divBar = document.getElementById('div_form_profile_user_new_password_progress_bar');

		divBar.style.width = progress + '%';

		if(lengthPass && uppercasePass && lowercasePass && digitPass){
			divPass.classList.remove('has-warning');
			divPass.classList.add('has-success');
		} else {
			if(pass.length == 0){
				divBar.classList.add('d-none');
				divPass.classList.remove('has-warning');
				divPass.classList.remove('has-success');
			} else {
				divBar.classList.remove('d-none');
				divPass.classList.add('has-warning');
				divPass.classList.remove('has-success');
			}
		}
	});

	$('#input_form_profile_user_confirm_password').keyup(function(){
		Pass = document.getElementById('input_form_profile_user_new_password');
		ConfirmPass = document.getElementById('input_form_profile_user_confirm_password');
		divPass = document.getElementById('div_form_profile_user_new_password');
		divConfirmPass = document.getElementById('div_form_profile_user_confirm_password');

		if(Pass.value == ConfirmPass.value){
			divConfirmPass.classList.remove('has-warning');
			divConfirmPass.classList.add('has-success');
			//document.getElementById('submit_profile_user_update').disabled = false;
		} else {
			divConfirmPass.classList.add('has-warning');
			divConfirmPass.classList.remove('has-success');
		}
	});
}

function isAtLeastLength(size, pass) {
	if(pass.length >= size){
		return true;
	} else {
		return false;
	}
}


function isOneUpper(pass) {
	for (var i=0 ; i < pass.length; i++) {
	    if (pass.charAt(i) === pass.charAt(i).toUpperCase() && !isAtLeastOneDigit(pass.charAt(i)) && !isAtLeastOneSpecial(pass.charAt(i))) {
	    	console.log(pass.charAt(i));
	        return true;
	    }
	}
	return false;
}

function isOneLower(pass){
	for (var i=0 ; i < pass.length; i++) {
	    if (pass.charAt(i) === pass.charAt(i).toLowerCase()) {
	        return true;
	    }
	}
	return false;
}

function isAtLeastOneDigit(pass) {
	var hasNumber = /\d/;
	if(hasNumber.test(pass)){
		return true;
	}
	return false;
}

function isAtLeastOneSpecial(pass) {
	var hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;
	if(hasSpecial.test(pass)){
		return true;
	}
	return false;
}