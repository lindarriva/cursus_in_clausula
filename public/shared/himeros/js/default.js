
function apriFinestra(file, larghezza, altezza){
    stringa = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=1,resizable=yes,left=0,top=0, width=" + larghezza + ",height=" + altezza;
    finestranuova = open(file, "_blank", stringa);
    finestranuova.focus();
}
function getParameter(parametro){
	// legge il valore del parametro <parametro> nella stringa di get
	// se il parametro non è definito restituisce una stringa vuota

	var i = 0;
	var n = 0;
	var ritorno = "";

	// legge i parametri trasmessi alla finestra
	var aa = unescape(self.location.search.substring(1)).split("&")
	.toString().split("=").toString().split(",");
	var n = aa.length;
	for (i = 0; i < n; i = i + 2){
		if (aa[i] == parametro) ritorno = aa[i + 1];
	}
	return ritorno;
}    
