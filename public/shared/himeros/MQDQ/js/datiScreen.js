function vai(){
    document.toServer.submit();
}

function setDatiScreen(path){
    // Viene chiamata dalla home per ottenere e registrare la dimensione dello schermo, per effettuare la centratura di contesto.jsp
    // via css, evitando l'effetto di 'slittamento' che si genera se la centratura si effettua via Javascript (vedi sotto).
    // Richiede la disponibilita di Dojo, nonché il file ajax_datiScreen.jsp
    var leftGap = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    leftGap = Math.round((leftGap - 1010) /2);
    var screenHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight; 
    var url = path + 'ajax_datiScreen.jsp'; 
    dojo.xhrPost( {
        url: url,
        content: {leftGap: leftGap,
            screenHeight: screenHeight
        },
        load: function(response) {
        },
        // funzione eseguita in caso di errore
        error: function(error){
            alert(error);
        }
    });    	
}


function centra(leftGap){
    // Esegue la centratura della pagina con pseudo-frames: via css sembra impossibile;
    // deve essere chiamata con <body onload="centra(leftGap)">
    // Di fatto, nel normale flusso del programma questa funzione non viene eseguita, perché i css già dispongono
    // della dimensione dello schermo, ottenuta con la funzione precedente (setDatiScreen) passando per la home.
    // Se invece NON si è passati per la home, allora è necessaria questa funzione
    if(leftGap > 0){
        // la centratura è già eseguita via css
        return;
    }
    var larghezza = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    larghezza = Math.round((larghezza - 1010) /2);
    if(document.getElementById("frameBanner") != null) document.getElementById("frameBanner").style.left = larghezza + "px";
    document.getElementById("frameTop").style.left = larghezza + "px";
    document.getElementById("frameLeft").style.left = larghezza + "px";
    var left = 551;
    if(document.getElementById("frameRight") != null) document.getElementById("frameRight").style.left = (larghezza + left) + "px";
    if(document.getElementById("frameRightApparato") != null) document.getElementById("frameRightApparato").style.left = (larghezza + left) + "px";
    if(document.getElementById("frameRightApparatoTop") != null) document.getElementById("frameRightApparatoTop").style.left = (larghezza + left) + "px";
    if(document.getElementById("frameRightApparatoBottom") != null) document.getElementById("frameRightApparatoBottom").style.left = (larghezza + left) + "px";
    if(document.getElementById("frameRight") != null) document.getElementById("frameRight").style.left = (larghezza + left) + "px";
}


