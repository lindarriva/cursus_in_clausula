
    /* Funzioni che operano in sinergia con gli overlay di dialogo; ovviamente la pagina che ospita le funzioni e gli overlay
     * deve avere alcune caratteristiche:
     * - le div che ospitano le grid e label che contengono le voci (rispettivamente nella relazione molti-molti e uno-molti)
     *   devono avere ID="div_tabella";
     * - all'ingresso della pagina occorre definire this_controller: ad esempio: this_controller = "articolo";
     * - all'ingresso della pagina occorre definire this_idScheda: ad esempio: IDArticolo, IDEvento ecc.
     * - all'ingresso della pagina occorre definire this_nomeScheda: ad esempio: 'articolo', 'evento' ecc.
     */


    var this_pagina = "";   // 'articolo', 'evento' ecc.
    var this_tabella = "";
    var this_chiave = "";
    var this_pathAjax = "ajax";	// può essere cambiato in caso di view con nome diverso da 'ajax'
    var this_idScheda = 0;  // denominazione generica dell'ID del record su cui opera la pagina (IDArticolo, IDEvento ecc.)
    var this_nomeScheda = ""    // nome della tabella del record su cui opera la pagina ('articolo', 'evento' ecc.)
    var this_idItem = 0;    // denominazione generica dell'ID in una tabella (chiave esterna)
    
    // queste tre variabili impostano le caratteristiche dell'overlay per l'inserimento di una nuova voce (inserisciItemDialog)
    // o per la modifica di una voce; devono essere ridefinite al momento di lanciare cercaItem();
    var this_itemDialog_numeroCampi = 1;
    var this_itemDialog_nomeTabella = "";
    var this_itemDialog_width = 400;
    
    var this_permettiNuovaVoce = true;
    var this_cognomeTuttoMaiuscolo = false;
    var this_abilitaPulsanti = false;   // se true, in uscita i pulsanti della griglia vengono abilitati,
    									// se se false, vengono disabilitati
    									// di norma si sceglie true nella relazione uno-molti (i pulsanti sono sempre abilitati)
    									// e false nella relazione molti-molti, perché il risultato è una griglia;
    									// si dovrà porre true in una griglia che si presenterà con record evidenziato
    
    var this_filtro = "";	// eventuale filtro nella ricerca, da gestire nelle singole circostanze

    var this_uscita = 0;    // 0 = salva solo, senza indirizzare ad altra pagina
                            // 1 = salva e chiude tutta la scheda, ritornando alla ricerca
                            // 2 = salva e torna indietro alla pagina chiamante
                            // 3 = la scheda è stata cancellata

	var this_modo = 1;		// numero di caratteri a partire da cui si avvia la ricerca

	var this_nomeCampoAggiornato = "";	// può essere necessario aggiornare un campo non standard

	var this_labelCampoCognome = "Cognome";	// nome dell'eventuale primo campo
	var this_labelCampoNome = "Nome";	// nome del secondo o unico campo
	
    var this_esci = false;  // se true non viene chiesta l'autorizzazione a lasciare la pagina

	var this_custom = "";
	var this_customModifica = "";
	var this_campiFormattati = 0;
	
	var this_messages = new Array();
	this_messages[0] = "Cognome";
	this_messages[1] = "Cerca voce";
	this_messages[2] = "Cerca";
	this_messages[3] = "Inserire almeno";
	this_messages[4] = "caratteri";
	this_messages[5] = "Nuovo inserimento nella tabella";	
	this_messages[6] = "Nuovo inserimento in tabella";
	this_messages[7] = "L'operatore non ha diritto di modifica su questa voce.";
	this_messages[8] = "Modifica voce nella tabella ";
	this_messages[9] = "Modifica voce in tabella";
	this_messages[10] = "Sostituisci";
	this_messages[11] = "Inserisci";
	
	document.getElementById("span_buttonMostraTutte").innerHTML = "Mostra&nbsp;tutte";
	document.getElementById("labelModificaItemNome_").innerHTML = "Nome";
	document.getElementById("labelModificaItemCognome_").innerHTML = "Cognome";
	document.getElementById("div_messaggio").innerHTML = "La voce è già presente nella tabella";
	//document.getElementById("span_buttonMostraTutte").innerHTML[4] = "&Egrave; stato rimosso il collegamento alla voce selezionata. Poiché questa non ha altri "
	//	+ "legami, potrebbe essere cancellata dalla corrispondente tabella.<br/>Eseguire la cancellazione?";
	document.getElementById("button_chiudi_dialog").innerHTML = "Annulla";
	document.getElementById("span_nuovoItem_").innerHTML = "Nuova voce";
	//document.getElementById("span_buttonMostraTutte").innerHTML[7] = "Inserisci";
	//document.getElementById("span_buttonMostraTutte").innerHTML[8] = "Eliminazione da tabella";
	document.getElementById("modificaItemDialog-title").innerHTML = "Modifica voce";
	

    function apriCercaItem(tabella, numeroCampi, width, nomeTabella, modo, abilitaPulsanti, creaNuovaVoce, titolo, noMostraTutte, labelCampoCognome, filtro){
    	// argomenti:
    	// tabella: tabella in cui cercare
    	// numeroCampi: 2 nel caso di cognome-nome, altrimenti 1
    	// width: larghezza della finestra di dialogo; se = 0 la larghezza è quella di default  
    	// nomeTabella: come deve comparire nel titolo
    	// modo: numero di caratteri a partire da cui si avvia la ricerca:
    	// 		default=1; 
    	//		> 1 numero di caratteri a partire da cui si avvia la ricerca
    	// 		0 tutte le voci compaiono all'apertura della finestra
    	// 		-1 è saltata del tutto la ricerca, si passa direttamente alla creazione della nuova coce
    	// abilitaPulsanti:   // se true, in uscita i pulsanti della griglia vengono abilitati,
    	// 		se se false, vengono disabilitati
    	// 		di norma si sceglie true nella relazione uno-molti (i pulsanti sono sempre abilitati)
    	// 		e false nella relazione molti-molti, perché il risultato è una griglia;
    	// 		si dovrà porre true in una griglia che si presenterà con record evidenziato
    	// creaNuovaVoce: deve comparire il pulsante 'Crea nuova voce'?
    	// FACOLTATIVE
    	// titolo: titolo dell'overlay
    	// noMostraTutte: non deve comparire il pulsante mostraTutte
    	// labelCampoCognome: etichetta per il campo cognome (default: "Cognome")
    	// filtro: eventuale filtro per la ricerca, da gestire nelle singole circostanze
    	var k = false;
    	this_chiave = "";
    	if(labelCampoCognome != null){
    		if(labelCampoCognome != ""){
    			k = true;
    		}
    	}
    	if(k){
    		this_labelCampoCognome = labelCampoCognome;
    	}else{
    		this_labelCampoCognome = this_messages[0]; //"Cognome";    	
    	}
    	if(typeof this_labelCampo2 !== 'undefined'){
    		this_labelCampoNome = this_labelCampo2;
    	}
    	
        if(creaNuovaVoce == null){
            creaNuovaVoce = true;
        }
        if(modo == null){
            modo = 1;
        } 
        if(titolo == null || titolo == ""){
            titolo = this_messages[1]; // 'Cerca voce';
        }
        if(noMostraTutte == null){
            noMostraTutte = false;
        }
        
        if(filtro == null){
        	filtro = "";
        }
        
        document.getElementById("cercaItemDialog-title").innerHTML = titolo;
        this_modo = modo;
        this_tabella = tabella;
        this_itemDialog_numeroCampi = numeroCampi;
        this_filtro = filtro;
        this_itemDialog_nomeTabella = nomeTabella;
        
        // ignoriamo la larghezza personalizzata, altrimenti non c'è posto per i pulsanti
        this_itemDialog_width = width;
        if(this_itemDialog_width == 0){
        	this_itemDialog_width = 640;
        }
        
        document.getElementById("cercaItemDialog-main").style.width = this_itemDialog_width + "px";
        //document.getElementById("chiaveItem_").style.width = (this_itemDialog_width - 200) + "px";
        //document.getElementById("chiaveItem_").style.width = (this_itemDialog_width - 450) + "px";
        this_abilitaPulsanti = abilitaPulsanti;
        document.getElementById("trovatiItem_").innerHTML = "";
        document.getElementById("chiaveItem_").value = "";
        
        if(modo == 0 || noMostraTutte){
            // presenta tutte le voci all'apertura'
            document.getElementById("buttonMostraTutte").style.display = "none";            
        }else{
            document.getElementById("buttonMostraTutte").style.display = "inline";
        }
        
        if(creaNuovaVoce){
            this_permettiNuovaVoce = true;
            document.getElementById("nuovoItem_").style.display = "inline";
        }else{
            // l'utente non può creare una nuova voce
            this_permettiNuovaVoce = false;
            document.getElementById("nuovoItem_").style.display = "none";
        }
        if(modo < 2){
        	document.getElementById("labelCercaItem").innerHTML = this_messages[2]; //"Cerca";
        	if(modo == 0) cercaItem(0);
		}else{
        	document.getElementById("labelCercaItem").innerHTML = this_messages[3] + " " + modo + " " + this_messages[4]; // "Inserire almeno " + modo + " caratteri";
		}
		if(modo == -1){
			// nel modo = -1 non si passa per la fase di ricerca, perché non avrebbe senso (ad esempio i link associati a una scheda)
			nuovoItemInTabella();
		}else{
            $("#cercaItemDialog").on('shown.bs.modal', function(){
                $(this).find('#chiaveItem_').focus();
            });		
			$('#cercaItemDialog').modal();
        }
    }
    function chiudiCercaItem(){
        // chiude l'overlay e ripulisce i campi
        document.getElementById("trovatiItem_").innerHTML = "";
        document.getElementById("chiaveItem_").value = "";
        $('#cercaItemDialog').modal('hide');
    }

    function cercaItem(modo){
        // modo = numero di caratteri a partire da cui si fa la ricerca: 0 = tutte
        var tabella = this_tabella;
        var obj = document.getElementById("trovatiItem_");
        var chiave = document.getElementById("chiaveItem_").value;
        if(modo == null){
            modo = this_modo;
        }
        if(chiave.length < modo){
            obj.innerHTML = "";
            return;
        }
        if(modo == 0){
            chiave = "";
        }
        waitDialogOn("cercaItemDialog");   
        $.ajax({
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            // passaggio dei parametri POST
            data: {azione: "cerca_",
                tabella: tabella,
                nomeScheda: this_nomeScheda,
                idScheda: this_idScheda,
                chiave: chiave,
                filtro: this_filtro
            },
            // funzione eseguita in risposta
            success: function(response, stato) {
                obj.innerHTML = response;
                waitDialogOff("cercaItemDialog");
            },
            // funzione eseguita in caso di errore
            error : function (richiesta,stato,errori) {
            	waitDialogOff("cercaItemDialog");
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
    }
    function importaItem(tabella, idItem){
    	waitDialogOn("cercaItemDialog");    	
        $.ajax({
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            sync: true,
            // passaggio dei parametri POST
            data: {azione: "importa_",
                tabella: tabella,
                nomeScheda: this_nomeScheda,
                idScheda: this_idScheda,
                idItem: idItem,
                filtro: this_filtro
            },
            // funzione eseguita in risposta
            success: function(response, stato) {
            	var n = response.indexOf("@errore@");
            	if(n > -1){
            		alert(response.substring(n + 8));
            		//aggiornaPagina(tabella, response, this_abilitaPulsanti, true);
            	}else{
                	chiudiCercaItem();       
                	aggiornaPagina(tabella, response, this_abilitaPulsanti, true);
              	
           	        eventoPost(tabella, 'importa', response);	// evento personalizzato alla fine del processo: la funzione deve essere implementata nella pagina; 
           	        								// se non lo è non dovrebbe dare problemi
                }
                waitDialogOff("cercaItemDialog");
            },
            // funzione eseguita in caso di errore
            error : function (richiesta,stato,errori) {
                waitDialogOff("cercaItemDialog");
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
	 }
     function cancellaLegameItem(tabella, idDialog){
        // CANCELLA IL COLLEGAMENTO AL RECORD ESTERNO, sia 1-molti, sia molti-molti
        // idCollegamento è l'id della tabella di collegamento in relazione molti-molti
        // idDialog va definito solo nel caso in cui per la domanda post cancellazione (cancellare anche nella tabella?)
        // non si usi l'overlay standard
        // Ritorna solo un'eventuale azione personalizzata
        this_tabella = tabella;
        var azione = "cancellaLegame_";
        var idCollegamento = 0;
        if(idDialog == null){
            idDialog = "eliminaItemDialog";
        }
        // se la relazione è molti-molti c'è una grid con un oggetto EVItabella che contiene l'id del record di collegamento
        var obj = document.getElementById("EVI" + tabella);
        if(obj != null){
            idCollegamento = getSelectedId(tabella);
        }
        var result = new Array();
        var ritorno = "";
        $.ajax( {
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            // passaggio dei parametri POST
            //
            //////////////
            sync: true, // credo che qui ci debba stare
            //////////////

            data: {azione: azione,
                idCollegamento: idCollegamento,
                nomeScheda: this_nomeScheda,
                idScheda: this_idScheda,
                tabella: tabella
            },
            // funzione eseguita in risposta
            success: function(response, stato) {
                // result[0] è l'innerHTML aggiornato (grid o label)
                // result[1] è (se non vuoto) la richiesta autorizzazione alla cancellazione del record nella tabella esterna
                // result[2] è una risposta personalizzabile
                var result = new Array();
                result[1] = "";
                result[2] = "";
                // errore per cui non si procede all'eliminazione
                var n = response.indexOf("@errore@");
                if(n > -1){
                	alert(response.substring(n + 8));
                	return;
                }
                
                var n2 = response.lastIndexOf("@@@");
                if(n2 > -1){
                    result[2] = response.substring(n2 + 3);
                    response = response.substring(0, n2);
                }
                var n1 = response.lastIndexOf("###");
                if(n1 > -1){
                    result[1] = response.substring(n1 + 3);
                    response = response.substring(0, n1);
                }
                result[0] = response;
//alert(response);
                // si fa in tutti i casi
                aggiornaPagina(tabella, result[0], false, false);

                if(result[2] != ""){
                    // azione personalizzata, si restituisce
                    ritorno = result[2];
                }
          	  	eventoPost(tabella, 'cancella', response);	// evento personalizzato alla fine del processo

                
            },
            // funzione eseguita in caso di errore
            error : function (richiesta,stato,errori) {
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
        return ritorno;
    }

    function cancellaItemInTabella(){
        // E' STATA AUTORIZZATA LA CANCELLAZIONE DI UN RECORD DALLA TABELLA: VIENE ESEGUITA
        var tabella = this_tabella;
        var azione = "cancellaItemInTabella_";
        $.ajax( {
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            // passaggio dei parametri POST
            data: {azione: azione,
                idItem: this_idItem,
                tabella: tabella
            },
            // funzione eseguita in risposta
            success: function(response,stato) {
            },
            // funzione eseguita in caso di errore
            error : function (richiesta,stato,errori) {
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
    }


    function nuovoItemInTabella(){
        // PREPARA L'OVERLAY PER L'INSERIMENTO DEL NUOVO ITEM E LO APRE
        // se numeroCampi = 2 l'inserimento è per nome e cognome
        chiudiCercaItem();
        if(this_custom != null){ 
        	if(this_custom != ""){
        		// l'immissione della nuova voce avviene mediante una finestra personalizzata:
        		// la variabile this_custom deve essere inizializzata e non essere vuota;
        		// la view deve contenere la funzione nuovoItemInTabellaCustom('finestra')
        		if(this_custom == 1){
        			// ho trovato questa soluzione, non troppo elegante, per risolvere 
        			// i casi con più tabella custom nella stessa pagina
        			nuovoItemInTabellaCustom_1();
        		}else if(this_custom == 2){
        			nuovoItemInTabellaCustom_2();
        		}else if(this_custom == 3){
        			nuovoItemInTabellaCustom_3();
        		}else{
        			nuovoItemInTabellaCustom(this_custom);
        		}
        		return;
        	}
        }
        this_custom = "";

        // Fa la stessa funzione del precedente, ma con modalità più flessibili;
        // il precedente è mantenuto per compatibilità;
        // la variabile this_customModifica contiene la chiamata alla procedura personalizzata
        if(this_customModifica != null){ 
        	if(this_customModifica != ""){
        		document.getElementById("customModifica").onclick = function(){eval(this_customModifica)};
        		document.getElementById("customModifica").click();
        		this_customModifica = "";
        		return;
        	}
        }
        var obj = document.getElementById("chiaveItem_");
        if(obj != null){
            // importa il valore già inserito in una chiave di ricerca
            this_chiave = obj.value;
        }
        modificaItem(0);
    }
    function apriModificaItem(tabella, numeroCampi, width, nomeTabella, abilitaPulsanti, nomeCampoAggiornato){
    	// nomeCampo, se definito, è il campo da aggiornare non standard
    	if(nomeCampoAggiornato == null){
    		nomeCampoAggiornato = "";
    	}   	
    	this_nomeCampoAggiornato = nomeCampoAggiornato;
        this_tabella = tabella;
        this_itemDialog_numeroCampi = numeroCampi;
        this_itemDialog_nomeTabella = nomeTabella;
        this_itemDialog_width = width;
        this_abilitaPulsanti = abilitaPulsanti;
        modificaItem(1);
    }
    
    function modificaItem(edit){
        // IMPOSTA L'OVERLAY PER LA MODIFICA DI UNA VOCE NELLA TABELLA; APRE L'OVERLAY
        // numeroCampi = 1 -> voce singola
        // numeroCampi = 2 -> cognome nome
        var azione = "modificaItem_";
        var idCollegamento = 0;
        document.getElementById("messaggio_modificaItem_").style.display = "none";
		var obj = document.getElementById("EVI" + this_tabella);        
        if(edit == 1){ 
        	// se non si tratta di nuovo, idCollegamento è sempre > 0;
        	// tuttavia, nel caso 1 - molti non c'è la grid, perciò esso ha il valore di idScheda
        	// Il nuovo si riconosce per idCollegamento = 0
        	if(obj != null){
            	idCollegamento = getSelectedId(this_tabella);
            }else{
            	idCollegamento = this_idScheda;            
            }
        }
        // serve a riconoscere il nuovo        
        document.getElementById("idModificaItem").value = idCollegamento;
        
        if(this_itemDialog_width == '') this_itemDialog_width = 400;  // dimensione standard
        
        var campoCognome = document.getElementById("modificaItemCognome_");
        var campoNome = document.getElementById("modificaItemNome_");
        
        if(this_itemDialog_numeroCampi == 2){
        	if(edit == 0){
            	document.getElementById("labelModificaItemCognome_").style.display = "inline";
            	document.getElementById("labelModificaItemCognome_").innerHTML = this_labelCampoCognome;
            	document.getElementById("labelModificaItemNome_").innerHTML = this_labelCampoNome;
            	document.getElementById("labelModificaItemNome_").style.display = "inline";        
            }else{
            	document.getElementById("labelModificaItemCognome_").style.display = "none";
            	document.getElementById("labelModificaItemNome_").style.display = "none";            
            }
            document.getElementById("div-cognome").style.display = "inline";
            campoNome.style.width = (this_itemDialog_width - 280) + "px";
        }else{
            document.getElementById("div-cognome").style.display = "none";
            campoNome.style.width = (this_itemDialog_width - 50) + "px";
            document.getElementById("labelModificaItemCognome_").style.display = "none";
            document.getElementById("labelModificaItemNome_").style.display = "none";            
        }                
        if(this_campiFormattati == 1){
        	if (typeof editorModificaItemNome_ === 'undefined'){
        		editorModificaItemNome_ = new wysihtml5.Editor("modificaItemNome_", {
            		parserRules: wysihtml5ParserRules
        		});
        		editorModificaItemCognome_ = new wysihtml5.Editor("modificaItemCognome_", {
            		parserRules: wysihtml5ParserRules
        		});
        	}
        }        
        
        $.ajax({
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            // passaggio dei parametri POST
            data: {azione: azione,
                idScheda: this_idScheda,
                nomeScheda: this_nomeScheda,
                idCollegamento: idCollegamento,
                tabella: this_tabella
            },
            // funzione eseguita in risposta
            success: function(response, stato) {           
                var array = response.split("@");
                if(edit == 0) array[2] = this_chiave;
                if(this_campiFormattati == 1){
                	editorModificaItemCognome_.setValue(array[1], true);
                	editorModificaItemNome_.setValue(array[2], true);
                }else{
                	document.getElementById("modificaItemCognome_").value = array[1];
                	document.getElementById("modificaItemNome_").value = array[2];
                }
                if(array[3] == 0){
                    // modifiche non consentite
                    campoCognome.disabled = true;
                    campoNome.disabled = true;
                }else{
                    // modifiche consentite
                    campoCognome.disabled = false;
                    campoNome.disabled = false;
                }
                if(array[3] == 0){
                    alert(this_messages[7]); // "L'operatore non ha diritto di modifica su questa voce."
                    return;
                }
                var titolo = "";
                if(this_itemDialog_nomeTabella != ""){
                	if(edit == 0){
                		titolo = this_messages[5];
                	}else{
                		titolo = this_messages[8];
                	}
                    titolo += " " + this_itemDialog_nomeTabella; // "Modifica voce nella tabella "
                }else{
                    titolo = this_messages[9];	//"Modifica voce in tabella";
                }
                document.getElementById("modificaItemDialog-title").innerHTML = titolo;
                document.getElementById("modificaItemDialog-main").style.width = this_itemDialog_width + "px";
                $("#modificaItemDialog").on('shown.bs.modal', function(){
                    $(this).find('#modificaItemNome_').focus();
                });                
                $('#modificaItemDialog').modal();
            },
            error : function (richiesta,stato,errori) {
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
    }
    function modificaItemOk(){
        // SALVA LA MODIFICA DI UNA VOCE NELLA TABELLA)
        var azione = "modificaItemOk_";
        // Nuovo: idCollegamento = 0
        var idCollegamento = document.getElementById("idModificaItem").value;
        if(idCollegamento == 0) azione = "inserisciItemInTabella_";
        var cognome = "";
        var obj = document.getElementById("modificaItemCognome_");
        if(obj != null){ 
        	cognome = document.getElementById("modificaItemCognome_").value;
        }
        var nome = document.getElementById("modificaItemNome_").value;
        $('#cercaItemDialog').modal('hide');
        $.ajax({
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            // passaggio dei parametri POST
            data: {azione: azione,
                idCollegamento: idCollegamento,
                cognome: cognome,
                nome: nome,
                idScheda: this_idScheda,
                nomeScheda: this_nomeScheda,
                tabella: this_tabella,
                filtro: this_filtro
            },
            // funzione eseguita in risposta
            success: function(response, stato) {
                var n = response.indexOf("@errore@");
                if(n > -1){
                    document.getElementById("messaggio_modificaItem_").style.display = "block";
                    return;
                }else{
                    $('#modificaItemDialog').modal('hide');
                    chiudiCercaItem();
                    aggiornaPagina(this_tabella, response, true, true);
                    this_nomeCampoAggiornato = "";   
                    eventoPost(this_tabella, 'modificaItemOk', response);                 
                }
                this_campiFormattati == 0;
            },
            // funzione eseguita in caso di errore
            error : function (richiesta,stato,errori) {
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
    }

   function muoviItem(tabella, verso){
        // porta una voce verso l'alto o il basso
        var idCollegamento = getSelectedId(tabella);
        waitOn();
        $.ajax( {
            url: this_baseUrl + "/" + this_controller + '/' + this_pathAjax,
            // passaggio dei parametri POST
            data: {azione: "muoviItem_",
                idCollegamento: idCollegamento,
                nomeScheda: this_nomeScheda,
                idScheda: this_idScheda,
                tabella: tabella,
                verso: verso
            },
            // funzione eseguita in risposta
            success: function(response) {
            	waitOff();
                abilitaPulsanti(tabella, true);
                document.getElementById("div_" + tabella).innerHTML = response;
                eventoPost(tabella, 'muoviItem', response);	// evento personalizzato alla fine del processo
            },
            // funzione eseguita in caso di errore
            error : function (richiesta,stato,errori) {
                alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
    }
    function aggiornaPagina(tabella, response, abilita, attivaLabel){    
        // AGGIORNA LA PAGINA DOPO UN'ESECUZIONE AJAX
        // attivaLabel: si conferisce aspetto evidenziato all'etichetta del campo
        var nomeCampo = "div_" + tabella;
        
        if(this_nomeCampoAggiornato != ""){
        	nomeCampo = this_nomeCampoAggiornato;
        }
        
        obj = document.getElementById("label_" + tabella);
        if(obj != null){
        	if(attivaLabel){
        		obj.className = "exe";
        	}else{
        		obj.className = "nonexe";
        	}
        	controllaPannello();	// la funzione va implementata nella view
        }        
//alert(nomeCampo);            	
        
        if(document.getElementById(nomeCampo) != null) document.getElementById(nomeCampo).innerHTML = response;
        abilitaPulsanti(tabella, abilita);
    }
    function abilitaPulsanti(tabella, abilita){
        // ABILITA/DISABILITA I PULSANTI MODIFICA E CANCELLA E, SE PRESENTI, I PULSANTI SU E GIU'
        var disabled = !abilita;
        abilitaPulsante("modifica", tabella, disabled);
        abilitaPulsante("cancella", tabella, disabled);
        abilitaPulsante("anteprima", tabella, disabled);
		abilitaPulsante("giu", tabella, disabled);
		abilitaPulsante("su", tabella, disabled);
        obj = document.getElementById("labelPulsante_" + tabella);
        if(obj != null){
            // è tabella del tipo uno-molti: etichetta dal pulsante 'Inserisci/Sostituisci'
            if(abilita){
                obj.innerHTML = this_messages[10]; //"Sostituisci";
            }else{
                obj.innerHTML = this_messages[11];	//"Inserisci";
            }
        }else{
        	obj = document.getElementById("inserisci_" . tabella);
        	if(obj != null){
            	if(abilita){
                	obj.setAttribute("title", this_messages[10]); //"Sostituisci";
            	}else{
                	obj.setAttribute("title", this_messages[11]);	//"Inserisci";
            	}
            }
        }
        /*
        obj = document.getElementById("giu_" + tabella);
        if(obj != null){
            var disabledSuGiu = disabled;
            // occorre controllare se c'è un solo record, nel qual caso non si abilitano i pulsanti su e giù
            $.ajax( {
                url: '<?php echo $this->baseUrl() ?>/' + this_controller + '/' + this_pathAjax,
                // passaggio dei parametri POST
                data: {azione: "contaItem_",
                    tabella: tabella,
                    idScheda: this_idScheda
                },
                // funzione eseguita in risposta
                success: function(response, stato) {
                    if(response == 1){
                        disabledSuGiu = true;
                    }
                    objSu = document.getElementById("img_su_" + tabella);
                    objGiu = document.getElementById("img_giu_" + tabella);
                    if(objSu != null){
                    	if(disabledSuGiu){
        					objSu.className = "img_button img_alto_i";
        					objGiu.className = "img_button img_basso_i";
        				}else{
        					objSu.className = "img_button img_alto";
        					objGiu.className = "img_button img_basso";
        				}
        			}
                    document.getElementById("su_" + tabella).disabled = disabledSuGiu;
                    document.getElementById("giu_" + tabella).disabled = disabledSuGiu;
                },
                // funzione eseguita in caso di errore
            	error : function (richiesta,stato,errori) {
                	alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            	}
            });
        }
        */
    }
    function abilitaPulsante(nome, tabella, disabled){    
        var obj = document.getElementById(nome + "_" + tabella);
        if(obj == null) return;
        obj.disabled = disabled;
    }
	function waitDialogOn(id){
		var cach = document.getElementById(id + "Wait");
    	cach.style.visibility = "visible";
	}
	function waitDialogOff(id){
		var cach = document.getElementById(id + "Wait");
    	cach.style.visibility = "hidden";
	}
	
	/* I seguenti tre metodi gestiscono il caso in cui non si desidera aprire una finestra modal per la ricerca
	   (in genere per evitare problemi con le finestre modal sovrapposte), per cui la ricerca è gestita
	   direttamente entro la pagina, includendo dialogInt.php. E' indispensabile: 
	   
	   1) gestire nel controller l'azione: "dialogIntCercaItem"
	   
	   2) gestire in eventoPost l'importazione dell'item, con chiusura della div di ricerca
	   e attivazione dei pulsanti:
	   
	       function eventoPost(tabella, funzione){
        		if(tabella == "xxxxxxxxxxx"){
            		if(funzione == "importa"){
            			dialogIntAnnulla(tabella);
                		abilitaPulsanti(tabella, true);
            		}
            	}
    		}
    	
    	3) prevedere la ripulitura all'apertura della finestra modal contenitrice:
    	
            dialogIntAnnulla("xxxxxxxxxxx");

	   	 
	*/
	function dialogIntCerca(scheda, idScheda, tabella){
    	this_nomeScheda = scheda;
    	this_idScheda = idScheda;	
    	document.getElementById("dialog-int-cerca_" + tabella).style.display = "block";
    	document.getElementById("dialog-int-cercaItem_" + tabella).value = "";
    	document.getElementById("dialog-int-cercaItem_" + tabella).focus();
	}
	
	function dialogIntCercaItem(obj, ajaxPath, tabella){
    	$.ajax({
        	url: ajaxPath,
        	data: {azione: "dialogIntCercaItem",
            	tabella: tabella,
            	nomeScheda: this_nomeScheda,
            	idScheda: this_idScheda,
            	chiave: obj.value
        	},
        	success: function(response, stato) {
            	document.getElementById("dialog-int-trovati_" + tabella).innerHTML = response;
        	},
        	error : function (richiesta,stato,errori) {
            	alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        	}
    	});
	}
	function dialogIntNuovo(ajaxPath, scheda, idScheda, tabella){
    	this_nomeScheda = scheda;
    	this_idScheda = idScheda;
    	var nome = document.getElementById("dialog-int-cercaItem_" + tabella).value;
    	$.ajax({
        	url: ajaxPath,
        	// passaggio dei parametri POST
        	data: {azione: "inserisciItemInTabella_",
            	idCollegamento: 0,
            	nome: nome,
            	idScheda: this_idScheda,
            	nomeScheda: this_nomeScheda,
            	tabella: tabella
        	},
        	// funzione eseguita in risposta
        	success: function(response, stato) {
            	var n = response.indexOf("@errore@");
            	if(n > -1){
                	document.getElementById("error_message").innerHTML = "La voce è già presente nella tabella.";
                	$('#errorDialog').modal();
                	return;
            	}else{
                	document.getElementById("div_" + tabella).innerHTML = response;
                	document.getElementById("dialog-int-cerca_" + tabella).style.display = "none";
                	abilitaPulsanti("autoribibliografia", true);
            	}
        	},
        	// funzione eseguita in caso di errore
        	error : function (richiesta,stato,errori) {
            	alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        	}
    	});
	}	
	function dialogIntAnnulla(tabella){
		document.getElementById("dialog-int-cercaItem_" + tabella).value = "";
		document.getElementById("dialog-int-trovati_" + tabella).innerHTML = "";
		document.getElementById("dialog-int-cerca_" + tabella).style.display = "none";
	}
	
	
	
	
	// Queste funzioni sono indipendenti dal sistema e gestiscono l'inserimento multiplo di voci da un combo
	function aggiungiVoce(obj, tabella, nomeScheda, idScheda){
    	// aggiunge una voce molti-molti da un combo
    	$.ajax( {
        	url: this_baseUrl + '/' + this_controller + '/ajax',
        	data: {azione: "aggiungiVoce_",
            	idScheda: idScheda,
            	nomeScheda: nomeScheda,
            	idItem: obj.value,
            	tabella: tabella
        	},
        	success: function(response, stato) {
            	document.getElementById("div_" + tabella).innerHTML = response;
            	obj.selectedIndex = 0;
        	},
        	error : function (richiesta,stato,errori) {
            	alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        	}
    	});
	}
	function cancellaVoce(tabella, nomeScheda, idScheda){
    	$.ajax( {
        	url: this_baseUrl + '/' + this_controller + '/ajax',
        	data: {azione: "cancellaVoce_",
            	idScheda: idScheda,
            	nomeScheda: nomeScheda,
            	idCollegamento: getSelectedId(tabella),
            	tabella: tabella
        	},
        	success: function(response, stato) {
            	document.getElementById("div_" + tabella).innerHTML = response;
            	abilitaPulsanti(tabella, false);
        	},
        	error : function (richiesta,stato,errori) {
            	alert("E' evvenuto un errore. Stato della chiamata: " + stato);
        	}
    	});
	}
