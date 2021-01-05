// Importante: questo file deve essere chiamato DOPO l'inclusione degli overlays
this_messages[0] = "Annul";
this_messages[1] = "User name";
this_messages[2] = "Si invita a copiare questi dati e trasmetterli all'interessato: una volta chiusa questa finestra non saranno più visualizzati.*";
this_messages[3] = "Vuoi veramente cancellare l'utente?*";
this_messages[4] = "Cancel user";
if(library_version == null) library_version = "BS";
var message = "Change password";
if(library_version == "dojo"){
	document.getElementById("cambiaPasswordDialog").title = message;
}else{
	document.getElementById("cambiaPasswordDialog-title").innerHTML = message;
}

document.getElementById("oldPassword_label").innerHTML = "Old password";
document.getElementById("newPassword_label").innerHTML = "New password";
document.getElementById("confirm_button").value = "Confirm";
document.getElementById("annul_button").value = "Annul";

var message = "Create new password";
if(library_version == "dojo"){
	document.getElementById("nuovaPasswordDialog").title = message;
}else{
	document.getElementById("nuovaPasswordDialog-title").innerHTML = message;
}

document.getElementById("info_label").innerHTML = "Questa funzione serve a generare una nuova password per l'utente in caso di smarrimento*";
document.getElementById("username_label").innerHTML = "User name";
document.getElementById("execute_button").value = "DO";
document.getElementById("close_button").value = "Close";

