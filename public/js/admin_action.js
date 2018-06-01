function actionInfoToForm(ref_id, status, mails){
  $('#ref_id_input_id').val(ref_id);
  $('#status_input_id').val(status);
  $('#table_mails_action').prepend(createTable(mails[ref_id]));
  $('#form-admin-action-mail-checkbox').prop('checked', true);
  loadBodyActionForm();
}


 function renderSubmitButtonAdminActionForm(){
 	ck1 = $('#checkboxAdminActionForm1')[0] ;
	ck2 = $('#checkboxAdminActionForm2')[0];
	ck3 = $('#checkboxAdminActionForm3')[0];

	if (ck1!=null && ck1.checked || ck2!=null && ck2.checked || ck3!=null && ck3.checked) {
		$('#submitAdminActionForm')[0].disabled = false;
	}else{
		$('#submitAdminActionForm')[0].disabled = true;
	}

	if(ck3!=null && ck3.checked){
		$('#inputrow3col2id')[0].disabled = false;
	}else{
		$('#inputrow3col2id')[0].disabled = true;
	}
 }

function loadBodyActionForm() {
	if ($('#form-admin-action-mail-checkbox').prop('checked')){
		$('#form-admin-action-body')[0].classList.remove('d-none');
		$('#form-admin-action-mail-checkbox')[0].setAttribute('checked', 'checked');
	} else {
		$('#form-admin-action-body')[0].classList.add('d-none');
		$('#form-admin-action-mail-checkbox')[0].setAttribute('checked', '');
		$('#submitAdminActionForm')[0].disabled = false;
	}
}

function createTable(mails_obj) {

    // Create table.
    var el = $('#tableFormActionAdmin')[0];
    if(el != null){
    	el.remove();
    }
    var table = document.createElement('table');
    table.setAttribute('id', 'tableFormActionAdmin')
    table.className = "table table-striped glpi_references";

    if(mails_obj['user_mail']){
	    // Insert New Row for table at index '0'.
	    var row1 = table.insertRow();
	    // Insert New Column for Row1 at index '0'.
	    var row1col1 = row1.insertCell(0);
	    row1col1.innerHTML = 'User mail';
	    // Insert New Column for Row1 at index '1'.
	    var row1col2 = row1.insertCell(1);
	    row1col2.innerHTML = mails_obj['user_mail'];

	   	var row1col3 = row1.insertCell(2);
	    checkbox1 = document.createElement('input');
	    checkbox1.setAttribute('type', 'checkbox');
	    checkbox1.setAttribute('onchange', 'renderSubmitButtonAdminActionForm()');
	    checkbox1.setAttribute('id', 'checkboxAdminActionForm1');
	    checkbox1.setAttribute('name', 'checkboxAdminActionForm1');
	    checkbox1.setAttribute('value', mails_obj['user_mail'])
	    row1col3.prepend(checkbox1);
    }

    if(mails_obj['ref_mail']){
	    // Insert New Row for table at index '1'.
	    var row2 = table.insertRow();
	    // Insert New Column for Row2 at index '0'.
	    var row2col1 = row2.insertCell(0);
	    row2col1.innerHTML = 'Reference mail';
	    // Insert New Column for Row2 at index '1'.
	    var row2col2 = row2.insertCell(1);
	    row2col2.innerHTML = mails_obj['ref_mail'];

	    var row2col3 = row2.insertCell(2);
	    checkbox2 = document.createElement('input');
	    checkbox2.setAttribute('type', 'checkbox');
	   	checkbox2.setAttribute('onchange', 'renderSubmitButtonAdminActionForm()');
	    checkbox2.setAttribute('id', 'checkboxAdminActionForm2');
	    checkbox2.setAttribute('name', 'checkboxAdminActionForm2');
	    checkbox2.setAttribute('value', mails_obj['ref_mail'])
	    row2col3.prepend(checkbox2);
    }

	// Insert New Row for table at index '2'.
    var row3 = table.insertRow();
    // Insert New Column for Row3 at index '0'.
    var row3col1 = row3.insertCell(0);
    row3col1.innerHTML = 'Or send to';
    // Insert New Column for Row3 at index '1'.
    var row3col2 = row3.insertCell(1);
    var inputrow3col2 = document.createElement('input');
    inputrow3col2.setAttribute('type', 'email');
    inputrow3col2.setAttribute('id', 'inputrow3col2id');
    inputrow3col2.setAttribute('required', true);
    inputrow3col2.setAttribute('name', 'inputrow3col2');
    inputrow3col2.disabled = true;
    row3col2.prepend(inputrow3col2);

	var row3col3 = row3.insertCell(2);
    checkbox3 = document.createElement('input');
    checkbox3.setAttribute('type', 'checkbox');
   	checkbox3.setAttribute('onchange', 'renderSubmitButtonAdminActionForm()');
    checkbox3.setAttribute('id', 'checkboxAdminActionForm3');
    checkbox3.setAttribute('name', 'checkboxAdminActionForm3');
    row3col3.prepend(checkbox3);

    return table;
}



function validateAdminActionForm(){
	ck1 = $('#checkboxAdminActionForm1')[0];
	ck2 = $('#checkboxAdminActionForm2')[0];
	ck3 = $('#checkboxAdminActionForm3')[0];

	if (ck1!=null && !ck1.checked && ck2!=null && !ck2.checked && ck3!=null && !ck3.checked) {
		return false;
	}
	return true;
}

function usersManagementModal(user){
	$('#input_form_user_action_user_id').val(user.id);
}