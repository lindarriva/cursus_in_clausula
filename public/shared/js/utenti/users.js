// Devono essere definite, nella pagina chiamante, le seguenti variabili:
// ajax_url, ad esempio: '<?php echo $this->baseUrl()?>/backend/users/ajax'
// delete_return, ad esempio: '<?php echo $this->baseUrl()?>/backend/users/list'
// library_version: dojo | BS
// nel caso dojo occorre caricare jquery (nel caso BS è caricata nel layout)
// Se si desidera la versione inglese, dopo questa occorre caricare users_en.js

var this_messages = new Array();
this_messages[0] = "Annulla";
this_messages[1] = "Nome utente";
this_messages[2] = "Si invita a copiare questi dati e trasmetterli all'interessato: una volta chiusa questa finestra non saranno più visualizzati.";
this_messages[3] = "Vuoi veramente cancellare l'utente?";
this_messages[4] = "Cancella utente";
                            
function apriCambiaPassword(){
	document.getElementById("oldPassword").value = "";
	document.getElementById("newPassword").value = "";
	document.getElementById("messaggio_cambiaPassword").style.display = "none";
	if(library_version == "dojo"){
		cambiaPasswordDialog.show();
	}else{
		$('#cambiaPasswordDialog').modal();
	}
}

function apriNuovaPassword(){
	document.getElementById("newUsername").value = "";
	document.getElementById("messaggio_nuovaPassword").style.display = "none";
	document.getElementById("messaggio_erroreNuovaPassword").style.display = "none";
	if(library_version == "dojo"){
		nuovaPasswordDialog.show();
	}else{
		$('#nuovaPasswordDialog').modal();
	}
}
function apriElimina(){
	var testo = this_messages[3];
	if(library_version == "dojo"){
		apriConferma(this_messages[4], testo, "eliminaOk()", true, this_messages[0]);
	}else{	
		apriConferma(this_messages[4], testo, function(){eliminaOk();}, true, this_messages[0]);
	}
}

function eliminaOk(){
	waitOn();
	var idUsers = document.getElementById("idusers").value;
	$.ajax({
		url: ajax_url,
		data: {azione: "eliminaUtente",
			idUsers: idUsers
		},
		success: function(response, stato) {
			waitOff();
			location.href = delete_return;
		},
		error : function (richiesta,stato,errori) {
			alert(error);
		}
	});
}

function cambiaPassword(){
	waitOn();
	var idUsers = document.getElementById("idusers").value;
	var oldPassword = document.getElementById("oldPassword").value;
	var newPassword = document.getElementById("newPassword").value;
	$.ajax({
		url: ajax_url,
		// passaggio dei parametri POST
		data: {azione: "cambiaPassword",
			idUsers: idUsers,
			oldPassword: oldPassword,
			newPassword: newPassword
		},
		// funzione eseguita in risposta
		success: function(response, stato) {
			waitOff();
			var v = JSON.parse(response);
			if(typeof v["errore"] !== 'undefined'){
				document.getElementById("messaggioCambiaPassword").innerHTML = v["errore"];
				document.getElementById("messaggio_cambiaPassword").style.display = "block";
			}else{
				if(library_version == "dojo"){
					cambiaPasswordDialog.hide();
				}else{
					$('#cambiaPasswordDialog').modal('hide');
				}
			}
		},
		// funzione eseguita in caso di errore
		error : function (richiesta,stato,errori) {
			alert(error);
		}
	});
}
function nuovaPassword(){
	waitOn();
	document.getElementById("messaggio_erroreNuovaPassword").style.display = "none";
	document.getElementById("messaggio_nuovaPassword").style.display = "none";
	$.ajax({
		url: ajax_url,
		data: {azione: "nuovaPassword",
			idUsers: document.getElementById("idusers").value,
			username: document.getElementById("newUsername").value
		},
		success: function(response, stato) {
			waitOff();
			var v = JSON.parse(response);
			if(typeof v["errore"] !== 'undefined'){
				document.getElementById("messaggioErroreNuovaPassword").innerHTML = v["errore"];
				document.getElementById("messaggio_erroreNuovaPassword").style.display = "block";
			}else{
				var testo = "<ul><li>" + this_messages[1] + ": " + v["username"] + "</li><li>Password: " + v["password"] + "</li></ul>"
					+ "<div>" + this_messages[2] + "</div>";
				document.getElementById("messaggioNuovaPassword").innerHTML = testo;
				document.getElementById("messaggio_nuovaPassword").style.display = "block";
				document.getElementById("execute_button").style.display = "none";
			}
		},
		// funzione eseguita in caso di errore
		error : function (richiesta,stato,errori) {
			alert(error);
		}
	});

}

