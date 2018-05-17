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