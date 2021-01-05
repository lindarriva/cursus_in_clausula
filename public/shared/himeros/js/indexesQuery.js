/* Funzioni per la creazione degli indici per autore nei risultati della query (createIndexes)
e per il puntamento al singolo autore (goToAuthor) */

function createIndexes(){
    waitOn(100);
    dojo.xhrPost( {
        url: this_url_ajax,
        content: {
            azione: 'createIndexesQuery'
        },
        load: function(response) {
            waitOff();
            document.getElementById("divIndexes").innerHTML = response;
            document.getElementById("buttonIndexes").style.display = "none";
        },
        error: function(error){
            alert(error);
        }
    });
}

function goToAuthor(obj, linkQuery){
    var numOccorrenza = obj.value;
    if(numOccorrenza == -1){
        return;
    }else{    
        waitOn();
        location.href = linkQuery + "/check/indexes/numOccorrenza/" + numOccorrenza + "#mark";
    }        
}