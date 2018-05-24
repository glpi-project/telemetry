function actionProfileForm(ref){
	var modalElement = $('#modal_body_profile_update')[0];
	var selectCountry = $('#countries_select_modal_profile')[0];
	$('#ref_id_form_update').val(ref.id);
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
	$('#input_form_profile_delete_id').val(ref.id);
}

function actionProfileUser(user){
	$('#input_form_profile_user_id').val(user.id);
	$('#input_form_profile_user_name').val(user.username);
	$('#input_form_profile_user_mail').val(user.email);

	$('#input_form_profile_user_new_password').keyup(function(){
		var pass = $(this).val();
		isAtLeastOneSpecial(pass);

		var size = 8;
		var hasUpper = /[A-Z]/;
		var hasLower = /[a-z]/;
		var hasNumber = /\d/;

		var progress = 0;

		if(pass.length >= size){
			progress += 25;
		}
		if(hasUpper.test(pass)){
			progress += 25;
		}
		if(hasLower.test(pass)){
			progress += 25;
		}
		if(hasNumber.test(pass)){
			progress += 25;
		}

		divPass = $('#div_form_profile_user_new_password')[0];
		divBar = $('#div_form_profile_user_new_password_progress_bar')[0];

		divBar.style.width = progress + '%';

		if(progress == 100){
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
		Pass = $('#input_form_profile_user_new_password')[0];
		ConfirmPass = $('#input_form_profile_user_confirm_password')[0];
		divPass = $('#div_form_profile_user_new_password')[0];
		divConfirmPass = $('#div_form_profile_user_confirm_password')[0];

		if(Pass.value == ConfirmPass.value){
			divConfirmPass.classList.remove('has-warning');
			divConfirmPass.classList.add('has-success');
		} else {
			divConfirmPass.classList.add('has-warning');
			divConfirmPass.classList.remove('has-success');
		}
	});
}