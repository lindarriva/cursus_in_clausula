/* Funzioni per la gestione di una pagina di lessico:
- ricerca occorrenze per una voce
- cambio pagina per le occorrenze
- ricerca complanare (Officina Barezzi)
Richiede: 
- link e require per Dojo
- la definizione della variabile this_ajax_url

*/


function occorrenze(){
    var obj = document.getElementById("lessico");
    var numero = obj.selectedIndex;
    var chiave = obj.options[numero].value;
    waitOn();
   dojo.xhrPost( {
        url: this_ajax_url,
        // passaggio dei parametri POST
        content: {azione: 'occorrenze',
            chiave: chiave
        },
        // funzione eseguita in risposta
        load: function(response) {
            waitOff();
            document.getElementById("occorrenze").innerHTML = response;
        },
        // funzione eseguita in caso di errore
        error: function(error){
            alert(error);
        }
    });
}
function cambiaPagina(occfrom, occto, pagina){
    waitOn();
    dojo.xhrPost( {
        url: this_ajax_url,
        // passaggio dei parametri POST
        content: {azione: 'occorrenze',
            occfrom: occfrom,
            occto: occto,
            pagina: pagina
        },
        // funzione eseguita in risposta
        load: function(response) {
            waitOff();
            document.getElementById("occorrenze").innerHTML = response;
        },
        // funzione eseguita in caso di errore
        error: function(error){
            alert(error);
        }
    });
}

function ricercaComplanare(chiave, versione, ordinata){
    waitOn();
    dojo.xhrPost( {
        url: this_ajax_url,
        // passaggio dei parametri POST
        content: {azione: 'ricercaComplanare',
            chiave: chiave,
            versione: versione,
            ordinata: ordinata
        },
        // funzione eseguita in risposta
        load: function(response) {
            waitOff();
            var n = response.indexOf("###");
            response = response.substring(n + 3);
            document.getElementById("myContent").innerHTML = response;
            waitOff();
            showMyOverlay(chiave.replace("|", "'"));
        },
        // funzione eseguita in caso di errore
        error: function(error){
            alert(error);
        }
    });
}

function ricercaComplanare_BS(chiave, versione, ordinata){
    waitOn();
    $.ajax({
        url: this_ajax_url,
        // passaggio dei parametri POST
        data: {azione: 'ricercaComplanare',
            chiave: chiave,
            versione: versione,
            ordinata: ordinata
        },
        success : function (response, stato) {
            waitOff();
            var tmp = response.split("@");
            document.getElementById("sinotticaModal-title").innerHTML = tmp[0];
            document.getElementById("sinotticaModal-body-content").innerHTML = tmp[1];
            $('#sinotticaModal').modal();
            $("#sinotticaModal").animate({ scrollTop: 0 }, "slow");
        },
        error : function (richiesta,stato,errori) {
            alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        }
    });

}