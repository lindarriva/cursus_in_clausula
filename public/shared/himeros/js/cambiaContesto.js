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
    dojo.xhrPost( {
        url: this_url_ajax,
        // passaggio dei parametri POST
        content: {azione: 'cambiaContesto',
            id: id,
            stato: stato
        },
        // funzione eseguita in risposta
        load: function(response) {
            waitOff();
            tdContesto.innerHTML = response;
            if(stato == "chiuso"){
                tdIcona.className = "contestoAperto";
                tdIcona.title = riduci;
            }else{
                tdIcona.className = "contestoChiuso";
                tdIcona.title = aumenta;
            }
        },
        // funzione eseguita in caso di errore
        error: function(error){
            alert(error);
        }
    });
}