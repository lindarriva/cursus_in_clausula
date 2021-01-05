/* Funzioni per la creazione degli indici per autore nei risultati della query (createIndexes)
e per il puntamento al singolo autore (goToAuthor) */

function createIndexes(){
    waitOn();
    $.ajax({
        url: this_url_ajax,
        data: {azione: 'createIndexesQuery'
        },
        success : function (data, stato) {
        	waitOff();
            document.getElementById("divIndexes").innerHTML = data;
            document.getElementById("buttonIndexes").style.display = "none";
        },
        error : function (richiesta,stato,errori) {
            alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        }
    });    
}
