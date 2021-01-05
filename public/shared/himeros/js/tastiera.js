	// funzioni per la gestione della tastiera virtuale
	// richiede che:
	// 	  1) nella view sia definita la variabile JS this_urlAjax
	// 	  2) in RicercheController siano definite le funzioni ajax 'mostraTastiera' e 'nascondiTastiera';
	//    questa azione lato-server è necessaria per memorizzare lo stato della tastiera (visibile-non visibile) 
	//    tra una ricerca e l'altra
	//	  3) la classe Query memorizzi questa variabile booleana con setTastiera() e la restituisca con getTastiera()
	//	  4) che RicercheController definisca per la view la variabile $this->displayTastiera con valore 'block' o 'none'
    function apriChiudiTastiera(){
        var obj = document.getElementById("div_tastiera");
        var display = obj.style.display;
        var azione;
        if(display == "block"){
            obj.style.display = "none";
            azione = "nascondiTastiera";
        }else{
            obj.style.display = "block";
            azione = "mostraTastiera";
        }        
        $.ajax({        
            url : this_urlAjax,
            // passaggio dei parametri POST
            data: {azione: azione
            },
            success : function (data, stato) {
            },
            error : function (richiesta,stato,errori) {
                //alert("E' evvenuto un errore. Stato della chiamata: " + stato);
            }
        });
        campoFocus.focus();    
    }
    function getFocus(obj){
        campoFocus = obj;
    }
    /*
    function inserisci(carattere){
        var p = campoFocus.selectionStart;
        var stringa = campoFocus.value;
        stringa = stringa.substring(0, p) + carattere + stringa.substring(p);
        campoFocus.value = stringa;
        campoFocus.setSelectionRange(p + 1, p + 1);
        campoFocus.focus();
    }
    */
    function inserisci(carattere){
    	// Nel caso delle vocali minuscole con accento acuto il carattere inserito è quello con codice basso
		// vocali minuscole con accento acuto con codice 902 - 974: ά έ ή ί ό ύ ώ
		// vocali minuscole con accento acuto con codice 7936 - 8190: ά έ ή ί ό ύ ώ
        var p = campoFocus.selectionStart;
        var stringa = campoFocus.value;
        var prec = stringa.substring(p - 1, p);
        var isUpperCase = (prec == prec.toUpperCase()) ? true : false;
        prec = prec.toLowerCase();
        var n;
        var oldChars = "αἀἁὰάάᾶἂἃἄἅἆἇᾳᾀᾁᾲᾴᾷᾂᾃᾄᾅᾆᾇεἐἑὲέέἒἓἔἕηἠἡὴήήῆἢἣἤἥἦἧῃᾐᾑῂῄῇᾒᾓᾔᾕᾖᾗιἰἱὶίίῖἲἳἴἵἶἷοὀὁὸόόὂὃὄὅυὐὑὺύύῦὒὓὔὕὖὗωὠὡὼώώῶὢὣὤὥὦὧῳᾠᾡῲῴῷᾢᾣᾤᾥᾦᾧ";
        var newChars;
        var beta;
        var replace;
        if(carattere == "|"){
        	if(p == 0) return;
            oldChars = "αἀἁὰάάᾶἂἃἄἅἆἇηἠἡὴήήῆἢἣἤἥἦἧωὠὡὼώώῶὢὣὤὥὦὧ";       
            newChars = "ᾳᾀᾁᾲᾴᾴᾷᾂᾃᾄᾅᾆᾇῃᾐᾑῂῄῄῇᾒᾓᾔᾕᾖᾗῳᾠᾡῲῴῴῷᾢᾣᾤᾥᾦᾧ";
            beta = "ahw";
            if(beta.indexOf(prec) > -1){
                stringa = stringa.substring(0, p) + "|" + stringa.substring(p);
                p ++;
			}else if((n = oldChars.indexOf(prec)) > -1){
				replace = newChars.substring(n, n + 1);
				if(isUpperCase) replace = replace.toUpperCase();
                stringa = stringa.substring(0, p - 1) + replace + stringa.substring(p);
            }
        }else if(carattere == "᾽"){
            // spirito dolce
            if(p == 0) return;
        	newChars = "ἀἀἀἂἄἄἆἂἂἄἄἆἆᾀᾀᾀᾂᾄᾆᾂᾂᾄᾄᾆᾆἐἐἐἒἔἔἒἒἔἔἠἠἠἢἤἤἦἢἢἤἤἦἦᾐᾐᾐᾒᾔᾖᾒᾒᾔᾔᾖᾖἰἰἰἲἴἴἶἲἲἴἴἶἶὀὀὀὂὄὄὂὂὄὄὐὐὐὒὔὔὖὒὒὔὔὖὖὠὠὠὢὤὤὦὢὢὤὤὦὦᾠᾠᾠᾢᾤᾦᾢᾢᾤᾤᾦᾦ";
			if((n = oldChars.indexOf(prec)) > -1){
				replace = newChars.substring(n, n + 1);
				if(isUpperCase) replace = replace.toUpperCase();
                stringa = stringa.substring(0, p - 1) + replace + stringa.substring(p);
            }
        }else if(carattere == "῾"){
            // spirito aspro
            if(p == 0) return;
            newChars = "ἁἁἁἃἅἅἇἃἃἅἅἇἇᾁᾁᾁᾃᾅᾇᾃᾃᾅᾅᾇᾇἑἑἑἓἕἕἓἓἕἕἡἡἡἣἥἥἧἣἣἥἥἧἧᾑᾑᾑᾓᾕᾗᾓᾓᾕᾕᾗᾗἱἱἱἳἴἴἷἳἳἵἵἷἷὁὁὁὃὅὅὃὃὅὅὑὑὑὓὕὕὗὓὓὕὕὗὗὡὡὡὣὥὥὧὣὣὥὥὧὧᾡᾡᾡᾣᾥᾧᾣᾣᾥᾥᾧᾧ";
			if((n = oldChars.indexOf(prec)) > -1){
				replace = newChars.substring(n, n + 1);
				if(isUpperCase) replace = replace.toUpperCase();
                stringa = stringa.substring(0, p - 1) + replace + stringa.substring(p);
            }
        }else if(carattere == "΄"){
            // accento acuto
            if(p == 0) return;
        	newChars = "άἄἅάάάάἄἅἄἅἄἅᾴᾄᾅᾴᾴᾴᾄᾅᾄᾅᾄᾅέἔἕέέέἔἕἔἕήἤἥήήήήἤἥἤἥἤἥῄᾔᾕῄῄῄᾕᾕᾔᾕᾔᾕίἰἱίίίίἴἵἴἵἴἵόὄὁόόόὄὅὄὅύὔὕύύύύὔὕὔὕὔὕώὤὥώώώώὤὥὤὥὤὥῴᾤᾡῴῴῴᾤᾥᾤᾥᾤᾥ";            
			if((n = oldChars.indexOf(prec)) > -1){
				replace = newChars.substring(n, n + 1);
				if(isUpperCase) replace = replace.toUpperCase();
                stringa = stringa.substring(0, p - 1) + replace + stringa.substring(p);
            }
        }else if(carattere == "§"){
            // accento grave
            if(p == 0) return;
            newChars = "ὰἂἃὰὰὰὰἂἃἂἃἂἃᾲᾂᾃᾲᾲᾲᾂᾃᾂᾃᾂᾃὲἒἓὲὲὲἒἓἒἓὴἢἣὴὴὴὴἢἣἢἣἢἣῂᾒᾓῂῂῂᾒᾓᾒᾓᾒᾓὶἲἳὶὶὶὶἲἳἲἳἲἳὸὂὃὸὸὸὂὃὂὃὺὒὑὺὺὺὺὒὓὒὓὒὓὼὢὣὼὼὼὼὢὣὢὣὢὣῲᾢᾣῲῲῲᾢᾣᾢᾣᾢᾣ";
			if((n = oldChars.indexOf(prec)) > -1){
				replace = newChars.substring(n, n + 1);
				if(isUpperCase) replace = replace.toUpperCase();
                stringa = stringa.substring(0, p - 1) + replace + stringa.substring(p);
            }
        }else if(carattere == "῀"){
            // accento circonflesso
            if(p == 0) return;
        	newChars = "ᾶἆἇᾶᾶᾶᾶἆἇἆἇἆἇᾷᾆᾇᾷᾷᾷᾆᾇᾆᾇᾆᾇεἐἑὲέέἒἓἔἕῆἦἧῆῆῆῆἦἧἦἧἦἧῇᾐᾑῇῇῇᾖᾗᾖᾗᾖᾗῖἶἷῖῖῖῖἶἷἶἷἶἷοὀὁὸόόὂὃὄὅῦὖὗῦῦῦῦὖὗὖὗὖὗῶὦὧῶῶῶῶὦὧὦὧὦὧῷᾦᾧῷῷῷᾦᾧᾦᾧᾦᾧ";            
			if((n = oldChars.indexOf(prec)) > -1){
				replace = newChars.substring(n, n + 1);
				if(isUpperCase) replace = replace.toUpperCase();
                stringa = stringa.substring(0, p - 1) + replace + stringa.substring(p);
            }
        }else{
            stringa = stringa.substring(0, p) + carattere + stringa.substring(p);
            p ++;
        }
        campoFocus.value = stringa;
        campoFocus.setSelectionRange(p, p);
        campoFocus.focus();
    }
                    