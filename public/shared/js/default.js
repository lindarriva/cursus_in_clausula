function copyText(id) {
	// copia nel clipboard il contenuto della div id
	window.getSelection().selectAllChildren(document.getElementById(id));
	document.execCommand("copy");
}
function controllaCheck(checkBox, idHidden){
    // Serve ad ovviare al fatto che se il checkbox non è spuntato, il parametro non passa via post,
    // così si trasferisce il valore 0 o 1 sull'elemento hidden, che ha come name il nome del campo
    var hidden = document.getElementById(idHidden);
    if(checkBox.checked == true){
        hidden.value = 1;
    }else{
        hidden.value = 0;
    }
}
