/* funzioni per la griglia di ricerca */    

    function controlloInput(i){
    	// attiva o disattiva il select "tutte le parole ecc." e gli elementi del pannello distanza
    	// a seconda del numero di parole nel campo di input
        var obj = document.getElementById("chiave_" + i);
        var text = obj.value;
        text = text.trim();
		if(text.indexOf("*") > -1 || text.indexOf("?") > -1){
			apriPannello('_lessico', '');
		}        
        var tmp = text.split(" ");
        var distanza = true;
        if(tmp.length > 1){
            document.getElementById("chiaveBoolean_" + i).disabled = false;
            apriPannello('_distanza', '');
            distanza = false;
        }else{
            document.getElementById("chiaveBoolean_" + i).disabled = true;
            var ct = 0;
            for(var j = 0; j <= 8; j ++){
                if(document.getElementById("chiave_" + j).value != ""){
                    ct ++;
                }
                if(ct > 1){
                	distanza = false;
                	break;
                }
            }
        }
        document.getElementById("distanza").disabled = distanza;
        if(document.getElementById("distanzaPrecisa") != null){
            document.getElementById("distanzaPrecisa").disabled = distanza;
        }
        document.getElementById("ordine").disabled = false;
        if(distanza == false){
        	document.getElementById("labelOrdine").className = "attiva";
        }else{
        	document.getElementById("labelOrdine").className = "inattiva";
        }
    }
    
    function mostraCampo(campo){
        campo ++;
        var obj = document.getElementById("campo_" + campo);
        obj.style.display = "block";
        document.getElementById("chiave_" + campo).focus();
    }
    function apriChiudiPannello(id, campoFocus, chiudiSempre){
        if(chiudiSempre == null) chiudiSempre = false;
        var obj = document.getElementById(id);
        var className = obj.className;
        if(className == "ric_labelPanel ric_aperto" || chiudiSempre){
            obj.className = "ric_labelPanel ric_chiuso"
            document.getElementById("div" + id).style.display = "none";
        }else{
            obj.className = "ric_labelPanel ric_aperto"
            document.getElementById("div" + id).style.display = "block";
            if(campoFocus != "") document.getElementById(campoFocus).focus();
        }
    }    
    function apriPannello(id){
        var obj = document.getElementById(id);
        obj.className = "ric_labelPanel ric_aperto"
        document.getElementById("div" + id).style.display = "block";
    }    
    
    function annullaChiave(){
        for(var i = 0; i < 9; i ++){
            document.getElementById("chiave_" + i).value = "";
            document.getElementById("chiaveBoolean_" + i).selectedIndex = 0;
            document.getElementById("chiaveBoolean_" + i).disabled = true;
            if(document.getElementById("posizioniVerso_" + i) != null){
            	document.getElementById("posizioniVerso_" + i).selectedIndex = 0;
            }
            if(i > 0){
                document.getElementById("chiaviBoolean_" + i).selectedIndex = 0;
                document.getElementById("campo_" + i).style.display = "none";
            }
        }
        document.toServer.chiave_0.focus();
    }
    function annullaDistanza(){
        document.getElementById("distanza").selectedIndex = 0;
        document.getElementById("distanza").disabled = true;
        if(document.getElementById("distanzaPrecisa") != null){
        	document.getElementById("distanzaPrecisa").selectedIndex = 0;
        	document.getElementById("distanzaPrecisa").disabled = true;
        }
        document.getElementById("ordine").checked = false;
        document.getElementById("ordine").disabled = true;
        document.getElementById("labelOrdine").className = "inattiva"; 
        apriChiudiPannello("_distanza", "", true);
    }
    function annullaOpere(){
        var obj = document.getElementById("autori")
		var n = obj.options.length;
        var y;
		for(y=0; y<n; y++){
            document.toServer.autori.options[y].selected=false;
		}
        apriChiudiPannello("_opere", "", true);
        if(document.getElementById('headingOpere') != null) openClose(document.getElementById('headingOpere'));
    }
    function apriChiudiTastieraRemoto(path){
        var obj = document.getElementById("div_tastiera");
        var display = obj.style.display;
        var label = document.getElementById("label_tastiera");
        var azione;
        if(display == "block"){
            obj.style.display = "none";
            label.className = "tastiera k_apri";
            azione = "nascondiTastiera";
        }else{
            obj.style.display = "block";
            label.className = "tastiera k_chiudi";
            azione = "mostraTastiera";
        }
        dojo.xhrPost( {
            url: path,
            content: {azione: azione
            },
            load: function(response) {
            },
            error: function(error){
                alert(error);
            }
        });
        campoFocus.focus();

    }
  	function controllaSezioni(obj, totale){
        var id = obj.id;
        var ct = 0;
        if(id == "m0"){
            if(obj.checked == true){
                for(var i = 1; i <= totale; i ++){
                    document.getElementById("m" + i).checked = false;
                }
            }else{
                for(var i = 1; i <= totale; i ++){
                    if(document.getElementById("m" + i).checked == true) ct ++;
                }
                if(ct == 0){
                    document.getElementById("m0").checked = true;
                }
            }
        }else{
            for(var i = 1; i <= totale; i ++){
                if(document.getElementById("m" + i).checked == true) ct ++;
            }
            if(ct == 0){
                document.getElementById("m0").checked = true;
            }else if(ct == totale){
                document.getElementById("m0").checked = true;
                controllaSezioni(document.getElementById("m0"), totale);
            }else{
                document.getElementById("m0").checked = false;
            }
        }
    }