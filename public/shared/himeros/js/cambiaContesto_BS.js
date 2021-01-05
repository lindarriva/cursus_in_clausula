/* usa jQuery anziché Dojo */
function cambiaContesto(tdIcona, language){
    waitOn();
    var aumenta;
    var riduci;
    if(language == "it"){
        aumenta = "Aumenta contesto";
        riduci = "Riduci contesto";
    }else{
        aumenta = "Enlarge context";
        riduci = "Reduce context";
    }
    var id = tdIcona.id;
    var stato = "chiuso";
    if(tdIcona.className == "contestoAperto"){
        stato = "aperto";
    }
    var tdContesto = document.getElementById("td_" + id);
    
    
    $.ajax({
        url: this_url_ajax,
        // passaggio dei parametri POST
        data: {azione: 'cambiaContesto',
            id: id,
            stato: stato
        },
        success: function(response) {
            waitOff();
            if(response.indexOf("@errore@") > -1){
            }else{
            	tdContesto.innerHTML = response;
            	if(stato == "chiuso"){
                	tdIcona.className = "contestoAperto";
                	tdIcona.title = riduci;
            	}else{
                	tdIcona.className = "contestoChiuso";
                	tdIcona.title = aumenta;
            	}
            }
        },
        error : function (richiesta,stato,errori) {
            alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        }
    });
    
}