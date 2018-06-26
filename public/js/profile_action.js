function actionProfileDelete(ref){
	$('#input_form_profile_delete_id').val(ref.id);
}

function actionProfileUser(user){
	$('#input_form_profile_user_new_password').keyup(function(){
		var pass = $(this).val();

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