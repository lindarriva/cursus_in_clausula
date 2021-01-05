<?php


class Lemmata_Lemmata{

	private $connection = null;
    private $consonanti = "bcdfghjklmnpqrstvwxz";
    private $consonanti1 = "bcdfgjklmnpqrstvwxz";
    private $MUTA_CUM_LIQUIDA = array("BL","BR","CL","CR","CHR", "DL","DR","FR","FL","GL","GR","PL","PR",
                                                    "PHR","PHL","TL","TR","THR","THL");	
	
    public function setConnection($connection){
        $this->connection = $connection;
        $this->pstmtLemmi = $this->connection->prepare("Select * from lemmi where lemma=:param1");
        $this->pstmtLemmiPadri = $this->connection->prepare("Select * from lemmi where lemma=:param1 and modello=:param2");
        $this->pstmtDesinenze = $this->connection->prepare("Select * from desinenze where desinenza=:param1");
        $this->pstmtModelli = $this->connection->prepare("Select * from desinenze_modelli where iddesinenze=:param1");
        $this->pstmtTemi = $this->connection->prepare("Select * from temi where tema=:param1");
        $this->pstmtLemmiById = $this->connection->prepare("Select * from lemmi where idlemmi=:param1");
    }
    
    public function getProsodie($parola){
        // restituisce un array bidimensionale, perché ci sono più possibilità di prosodia;
        // per ciascuna prosodia fornisce:
        // [0] la parola con le quantità
        // [1] il lemma
        // [2] schema prosodico (+-)
        // [3] il superlemma
        // [4] il numero di sillabe
        // [5] il modello
        // [6] il punto
        // [7] il numero
        // [8] il lemma con le quantità
        mb_internal_encoding("UTF-8");
		$parola = $this->assimilaPrefisso($parola);        
        $parola = str_replace("-", "", $parola);
        $parola = str_replace("v", "u", $parola);
        $parola = str_replace("V", "U", $parola);
        $p = strtolower($parola);
        if(mb_strpos($parola, "issum") !== false){
            $stringa = strtolower($parola);
            if(mb_substr($stringa, -5) != "issum"){
                $parola = str_replace("issum", "issim", $stringa);
            }
        }
        
        $result = array(array(),array());
        $vector = array();
        
        // ricerca direttamente il lemma
        $this->pstmtLemmi->bindValue("param1", $parola);
        $this->pstmtLemmi->execute();
        $rows = $this->pstmtLemmi->fetchAll();
        
        
        
        foreach($rows as $row){
            $tmp = array();
            $tmp[0] = $row["prosodia"];
            if(!empty($row["lemmaPadre"])){
                $this->pstmtLemmiPadri->bindValue("param1", $row["lemmaPadre"]);
                $this->pstmtLemmiPadri->bindValue("param2", $row["modello"]);
                $this->pstmtLemmiPadri->execute();
                $rs = $this->pstmtLemmiPadri->fetchAll();
                $tmp[1] = $rs[0]["lemma"]; 
                $tmp[3] = $rs[0]["superlemma"]; 
                $tmp[4] = $rs[0]["sillabe"]; 
                $tmp[5] = $rs[0]["modello"];
                $tmp[6] = $rs[0]["punto"];
                $tmp[7] = $rs[0]["sub1"];
                $tmp[8] = $rs[0]["prosodia"];                
            }else{
                $tmp[1] = $row["lemma"]; 
                $tmp[3] = $row["superlemma"]; 
                $tmp[4] = $row["sillabe"]; 
                $tmp[5] = $row["modello"];
                $tmp[6] = $row["punto"];
                $tmp[7] = $row["sub1"];
                $tmp[8] = $row["prosodia"];                               
            }
            if($tmp[5] >= 17 && $tmp[5] <= 21){
                if(mb_substr($tmp[1], -2) == "or"){
                    $tmp[8] .= "/o";
                }
            }            
            $vector[] = $tmp;
        }
        
        // ricerca comunque le forme flesse
        $tmp = $this->getFormeFlesse($parola);
        
        $vector = array_merge($vector, $tmp);
        if(count($vector) > 0){
             $array = array();
             foreach($vector as $i => $value){
                 $array[] = Utilities::right("00" . $value[5], 2) . ";" . $value[1] . ";" . $this->getQuantita($value[0]) . ";" . $i;
             }            
             // eliminazione di doppioni (uguale modello + uguale lemma + uguale schema delle quantità; non necessariamente uguale rappresentazione, perché possono
             // esservi variazioni grafiche per la stessa prosodia)
             sort($array);
             $ct = 0;
             $len = count($array);
             foreach($array as $i => $value){
                if(empty($value)) continue;
                error_log("from lemmata " . $value);
                $n = mb_strrpos($value, ";");
                $stringa = mb_substr($value, 0, $n);
                $n = $i;
                for($j = $i + 1; $j < $len ; $j ++){
                    $n = $j;
                    $n1 = mb_strrpos($array[$j], ";");
                    if(mb_substr($array[$j], 0, $n1) == $stringa){
                        $array[$j] = "";
                    }else{
                        $n --;
                        break;
                    }
                }                 
             }

             $result = array();
             $ct = 0;
             foreach($array as $i => $value){
                 if(!empty($value)){
                     $tmp = explode(";", $value);
                     $n = $tmp[3];	// modello
                     $result[$ct] = $vector[$n];
                     // trasferisce schema prosodico
                     $result[$ct][2] = $tmp[2];
                     $ct ++;
                 }
             }             
        } else {
            $result = array();
        }
        return $result;
    }
    private function getFormeFlesse($parola){
        // restituisce un array bidimensionale, perché ci sono più possibilità di prosodia;
        // per ciascuna prosodia fornisce:
        // [0] la parola con le quantità
        // [1] il lemma
        // [2] schema prosodico (vuoto)
        // [3] il superlemma (vuoto)
        // [4] il numero di sillabe
        // [5] il modello
        // [6] il punto
        // [7] il numero
        // [8] il lemma con le quantità (vuoto)
        $result = array();
        // cerca le desinenze compatibili con la parola e le mette nel vector
        // ogni elemento del vector è un array che contiene:
        // [0] la desinenza con le quantità
        // [1] il modello
        // [2] il punto        
        // [3] il submodello
        // [4] il numero di sillabe
        // [5] il sub1
        // [6] 1 = completo
        mb_internal_encoding("UTF-8");
        $desinenze = $this->getDesinenze($parola);
        foreach($desinenze as $i => $desinenza){
            $modello = $desinenza[1];
            $punto = $desinenza[2];
            $sub = $desinenza[3];
            $sillabe = $desinenza[4];
            $sub1 = $desinenza[5];
            $completo = $desinenza[6];
          
            // cerca nei temi delle forme flesse:
            // stacca la desinenza dalla parola da cercare
            $tmp = array("","","","","","","","","");
            $stringa = str_replace("^", "", $desinenza[0]); 
            
            $len = mb_strlen($parola) - mb_strlen($stringa);
            // casi speciali:
            if($completo == 1){
                // sono le forme speciali dei verbi con punto: la desinenza, nel verbo semplice, corrisponde all'intera forma
                $array = $this->getLemmaVerbaleConPunto($modello, $punto);
                $prefisso = "";
                $prosodia = $array[1];
                $superlemma = "";
                $k = true;
                if($len > 0){
                    // verbi composti: si stacca il prefisso
                    $prefisso = mb_substr($parola, 0, $len);
                    // cerco la prosodia del lemma                    
                    $this->pstmtLemmi->bindValue("param1", $prefisso . $array[0]);
                    $this->pstmtLemmi->execute();
                    $rows = $this->pstmtLemmi->fetchAll();
                    $k = false;
                    foreach($rows as $row){
                        if($row["modello"] == $modello && $row["punto"] == $punto){
                            $prosodia = $row["prosodia"];  
                            $superlemma = $row["superlemma"]; 
                            $n = strrpos($superlemma, "*");
                            $sillabe += $n + 1;
                            $k = true;
                        }
                    }
                }
                if(!$k) continue;    // falso composto
                $tmp[0] = $prefisso . $desinenza[0];
                $tmp[1] = $prefisso . $array[0];
                $tmp[3] = $superlemma;
                $tmp[4] = $sillabe;
                $tmp[5] = $modello;
                $tmp[6] = $punto;
                $tmp[7] = "0";
                $tmp[8] = $prosodia;
                $result[] = $tmp;
                continue;
                
                
            }else if($modello == 22 && $sub == 1){
                // SUM
                if($len == 0){
                    // è SUM
                    $tmp[0] = $desinenza[0];
                    $tmp[1] = "sum";
                    $tmp[4] = $sillabe;
                    $tmp[5] = "22";
                    $tmp[6] = "0";
                    $tmp[7] = "0";
                    $tmp[8] = "sŭm";
                    $result[] = $tmp;
                    continue;
                }else{
                    // si tratta di un composto; occorre adattare i prefissi
                    $sum = $this->normalizzaVocali($desinenza[0]);
                    $len = mb_strlen($parola) - mb_strlen($desinenza[0]);
                    $tema = mb_substr($parola, 0, $len);
                    if(substr($sum, 0, 1) == "e"){
                        if(strtoupper($tema) == "PROD"){
                            // prosum
                            $desinenza[0] = "d" . $desinenza[0];
                            $tema = "pro";
                        }else if(strtoupper($tema) == "POT"){
                            // possum
                            $desinenza[0] = "t" . $desinenza[0];
                            $tema = "pos";
                        }else if(strtoupper($tema) == "AF"){
                            // assum
                            $tema = "as";
                        }
                    }else if(substr($sum, 0, 1) == "f"){  // participio futuro
                        if(strtoupper($tema) == "AF"){
                            // assum
                            $tema = "ad";
                        }
                    }
                }
            }else if($modello == 23 && $sub == 1){
                if($len == 0){
                    // è EO
                    $tmp[0] = $desinenza[0];
                    $tmp[1] = "eo";
                    $tmp[4] = $sillabe;
                    $tmp[5] = "23";
                    $tmp[6] = "0";
                    $tmp[7] = "0";
                    $tmp[8] = "ĕō";
                    $result[] = $tmp;
                    continue;
                }else{
                    $tema = mb_substr($parola, 0, $len); 
                }
            }else{
                if($len < 0){ 
                    continue;
                }            
                $tema = mb_substr($parola, 0, $len);                
            }
            // cerca il tema; 
            $this->pstmtTemi->bindValue("param1", $tema);
            $this->pstmtTemi->execute();
            $rows = $this->pstmtTemi->fetchAll();
            foreach($rows as $row){
                if($modello == $row["modello"] && $punto == $row["punto"] && $sub == $row["sub"]){
                    // se il tema è trovato lo accetta solo se modello, punto e sub corrispondono a quelli della desinenza                    
                    $tmp = array("","","","","","","","","");                    
                    $this->pstmtLemmiById->bindValue("param1", $row["idlemmi"]);
                    $this->pstmtLemmiById->execute();
                    $rs = $this->pstmtLemmiById->fetchAll();
                    $r = $rs[0];
                    // si scartano le forme passive per i verbi intransitivi (-1), le forme singolari per i pluralia tantum (2) e le forme plurali per i nomi propri (1)
                    $sub1Lemma = $r["sub1"];
                    if($sub1Lemma == 2 && $sub1 == 1){
                        continue;
                    }
                    if($sub1Lemma == 1 && $sub1 == 2){
                        continue;
                    }
                    if($sub1Lemma == -1 && $sub1 == 3){
                        continue;
                    }
                    $tmp[0] = $row["prosodia"] . $desinenza[0];
                    if($modello == 22 && $sub == 1){
                        $tmp[0] = str_replace("post", "pŏt", $tmp[0]);
                    }                    
                    // numero di sillabe
                    $tmp[1] = $r["lemma"];
                    $tmp[3] = $r["superlemma"];
                    $tmp[4] = $sillabe . $row["sillabe"];
                    $tmp[5] = $modello;
                    $tmp[6] = $punto;
                    $tmp[7] = $r["sub1"];
                    $tmp[8] = $r["prosodia"];
                    ////////////////////////////////////////////////////////////
                    // filtro perfetti sincopati: se la desinenza inizia per -s o -r si accetta solo se il tema è sincopato
                    if(($modello == 17 || $modello == 18 || $modello == 19 || $modello == 20 || $modello == 21 || $modello == 23) && $sub == 2){
                        if(mb_substr($desinenza[0], 0, 1) == "s" || mb_substr($desinenza[0], 0, 1) == "r"){
                            if($row["sincopato"] == 0){
                                continue;
                            }
                        }else{
                            if($row["sincopato"] == 1){
                                continue;
                            }                            
                        }
                    }
                    if($modello >= 17 && $modello <= 21){
                        if(mb_substr($tmp[1], -2) == "or"){
                            $tmp[8] .= "/o";
                        }
                    }
                    ////////////////////////////////////////////////////////////                    
                    $result[] = $tmp;
                }
            }
        }
        return $result;
    }
    private function getDesinenze($parola){
        $result = array();
        $len = mb_strlen($parola);
        for($i = $len - 1; $i >= 0; $i --){
            // cerca un segmento finale progressivamente più grande
            $this->pstmtDesinenze->bindValue("param1", substr($parola, $i));                
            $this->pstmtDesinenze->execute();
            $rows = $this->pstmtDesinenze->fetchAll();
            foreach($rows as $row){
                // per ogni desinenza trovata cerca i modelli che la prevedono
                $stringa = $row["prosodia"];
                $prosodie = explode(",", $stringa); // possibilità di due prosodie per la stessa desinenza, separate da virgola
                $this->pstmtModelli->bindValue("param1", $row["iddesinenze"]);
                $this->pstmtModelli->execute();
                $rs = $this->pstmtModelli->fetchAll();
                foreach($rs as $value){
                    // per ogni modello compatibile con la desinenza si associa al vector un array che contiene:
                    // [0] la desinenza con le quantità
                    // [1] il modello
                    // [2] il submodello   
                    // [3] il numero di sillabe
                    // [4] il sub1 (2=forma plurale; 3=forma passiva)
                    foreach($prosodie as $prosodia){
                        $tmp = array();
                        $tmp[0] = $prosodia;
                        $tmp[1] = $value["modello"];
                        $tmp[2] = $value["punto"];
                        $tmp[3] = $value["sub"];
                        $tmp[4] = $row["sillabe"];
                        $tmp[5] = $value["sub1"];
                        $tmp[6] = $value["completo"];
                        $result[] = $tmp;    
                    }
                }
            }
        }
        return $result;
    }
    private function getLemmaVerbaleConPunto($modello, $punto){
        $result = array("","");
        if($modello == 19){
            if($punto == 1){
                $result[0] = "volo";
                $result[1] = "vŏlo";
            }else if($punto == 2){
                $result[0] = "nolo";
                $result[1] = "nōlo";                
            }else if($punto == 3){
                $result[0] = "malo";
                $result[1] = "mālo";                
            }else if($punto == 4){
                $result[0] = "fero";
                $result[1] = "fĕro";                
            }else if($punto == 5){
                $result[0] = "edo";
                $result[1] = "ĕdo";                
            }
        }else if($modello == 20){
            if($punto == 1){
                $result[0] = "facio";
                $result[1] = "făcĭo";
            }
        }else if($modello == 21){
            if($punto == 1){
                $result[0] = "fio";
                $result[1] = "fīo";
            }
        }else if($modello == 22){
            if($punto == 1){
                $result[0] = "possum";
                $result[1] = "possum";
            }
        }
        return $result;
    }
    public function normalizzaVocali($stringa){
        // riporta le lettere con quantità alla lettera senza
        $stringa = str_replace("^", "", $stringa);
        $cc = Utilities::mb_str_split($stringa);
        $result = "";
        foreach($cc as $c){
            if($c == 'ă' || $c == 'ā'){
                $result .= "a";
            }else if($c == 'ĕ' || $c == 'ē'){
                $result .= "e";
            }else if($c == 'ĭ' || $c == 'ī' || $c == 'î'){
                $result .= "i";
            }else if($c == 'ŏ' || $c == 'ō'){
                $result .= "o";
            }else if($c == 'ŭ' || $c == 'ū' || $c == 'û'){
                $result .= "u";
            }else if($c == 'ў' || $c == 'ӯ' || $c == 'ȳ'){
                $result .= "y";
            }else if($c == 'Ā' || $c == 'Ă'){
               $result .= "A";
            }else if($c == 'Ē' || $c == 'Ĕ'){
               $result .= "E";
            }else if($c == 'Ī' || $c == 'Ĭ'){
               $result .= "I";
            }else if($c == 'Ō' || $c == 'Ŏ'){
               $result .= "O";
            }else if($c == 'Ū' || $c == 'Ŭ'){
               $result .= "U";
            }else if($c == 'Ȳ'){
                $result .= "Y";
                
            }else{
                $result .= $c;
            }
        }        
        return $result;
    }    
    
    public function getQuantita($parola){
        // restituisce le quantità di una forma flessa o di un lemma;
        // + lunga
        // - breve
        // x indifferens
        // * non definita
        $result = "";
        $parola = mb_strtolower($parola);
        
        // caso NGU + vocale
        if(($n = mb_strpos($parola, "ngu")) !== false){
            if(!$this->isConsonante(mb_substr($parola, $n + 3, 1))){
                $parola = substr($parola, 0, $n) . "ngv" . substr($parola, $n + 3);
            }
        }        
        // i intervocalica
        $cc = Utilities::mb_str_split($parola);
        $parola = "";
        $len = count($cc);
        for($i = 0; $i < $len; $i ++){
            if($cc[$i] == 'j' && $i > 0 && $i < $len - 1){
                if(!$this->isConsonante($cc[$i - 1]) && !$this->isConsonante($cc[$i + 1])){
                    $parola .= "j";
                }
            }
            $parola .= $cc[$i];
        }

        $parola = str_replace("qu", "c", $parola);
        
        // individuazione dei dittonghi: tutti i casi in cui la seconda vocale non abbia la quantità        
        $parola = str_replace("ae", "D", $parola);
        $parola = str_replace("āe", "D", $parola);
        $parola = str_replace("ăe", "D", $parola);
        $parola = str_replace("oe", "D", $parola);
        $parola = str_replace("ōe", "D", $parola);
        $parola = str_replace("ŏe", "D", $parola);
        $parola = str_replace("au", "D", $parola);
        $parola = str_replace("āu", "D", $parola);
        $parola = str_replace("ău", "D", $parola);
        
        
        //////////////
        $parola = str_replace("ēî", "D", $parola);
        $parola = str_replace("ĕî", "D", $parola);
        $parola = str_replace("eî", "D", $parola);
        $parola = str_replace("ūî", "D", $parola);
        $parola = str_replace("ŭî", "D", $parola);
        $parola = str_replace("uî", "D", $parola);
        ///////////////
        
        
        // i nessi si sostituiscono con w;
        // purtroppo nel L&S il trattamento dei nessi non sembra chiaro; l'ideale sembrerebbe: 
        // - togliere il segno di indifferens (dove c'è!)
        // - trattare il nesso come consonante singola
        // - accettare la quantità della vocale così com'è segnata (breve se non è segnata)
        // E così si è fatto qui: ma non sono assolutamente certo che i segni sulle vocali siano giusti
        foreach($this->MUTA_CUM_LIQUIDA as $value){
        	$value = strtolower($value);
            if(($n = mb_strpos($parola, $value)) !== false){
                $parola = substr($parola, 0, $n) . "w" . substr($parola, $n + strlen($value));
            }
        }
        $parola = str_replace("^w", "w", $parola);
        
        // risoluzione dei casi indifferens: si toglie la vocale precedente
        if(mb_strpos($parola, "^" > - 1)){
            $cc = Utilities::mb_str_split($parola);      
            $parola = "";
            foreach($cc as $c){
                if($c == '^'){
                    $parola = mb_substr($parola, 0, mb_strlen($parola) - 1);
                }
                $parola .= $c;
            }
        }
        
        // sostituzione consonanti doppie
        $parola = str_replace("z", "cc", $parola);
        $parola = str_replace("x", "cc", $parola);
        
        // J intervocalica = jj
        
        
        // si allungano le vocali seguite da 2 o più consonanti
        $cc = Utilities::mb_str_split($parola);          
        $parola = "";
        $len = count($cc);
        for($i = 0; $i < $len; $i ++){
            if($i < $len - 2 && $this->isVocale($cc[$i])){
                if($this->isConsonante($cc[$i + 1]) && $this->isConsonante($cc[$i + 2])){
                    $cc[$i] = $this->allunga($cc[$i]);
                }
            }
            $parola .= $cc[$i];
        }

        // dittongo eu
        if(mb_substr($parola, 2) == "eu" || mb_substr($parola, 2) == "ĕu" || mb_substr($parola, 2) == "ēu"){
            $parola = "D" . mb_substr($parola, 2);
        }
        // casi marcati come dittongo (es. Perseus)
        $parola = str_replace("eû", "D", $parola);
        $parola = str_replace("ĕû", "D", $parola);
        $parola = str_replace("ēû", "D", $parola);
        
        // tutti gli altri casi senza quantità sulla seconda
        $parola = str_replace("eu", "D", $parola);
        $parola = str_replace("ĕu", "D", $parola);
        $parola = str_replace("ēu", "D", $parola);
        
        
        // a questo punto si esaminano le vocali
        $cc = Utilities::mb_str_split($parola);
        foreach($cc as $c){
            if($c == '^'){
                $result .= "*";
            }else if($c == 'D'){
                $result .= "+";
            }else if($c == 'ā'
                    || $c == 'ē'
                    || $c == 'ī'
                    || $c == 'ō'
                    || $c == 'ū'
                    || $c == 'ӯ'){
                $result .= "+";
            }else if($c == 'ă'
                    || $c == 'ĕ'
                    || $c == 'ĭ'
                    || $c == 'ŏ'
                    || $c == 'ŭ'
                    || $c == 'ў'){
                $result .= "-";
                
            }else if($c == 'h'){
            }else if($this->isConsonante($c)){                
            }else{
                // vocale senza quantità
                $result .= "A";                
            }
        }
        // si assegna la quantità breve a vocale senza quantità non finale
        $cc = Utilities::mb_str_split($result);
        $result = "";
        $len = count($cc);
        for($i = 0; $i < $len; $i ++){
            if($cc[$i] == 'A'){
                if($i < $len - 1){
                    $result .= "-";
                }else{
                    $result .= "*";
                }
            }else{
                $result .= $cc[$i];
            }
        }        
        return $result;        
    }
    private function isVocale($carattere){
        $result = false;
        if(strpos($this->consonanti, $carattere) === false){
            $result = true;
        }
        return $result;
    }    
    public function isConsonante($carattere){
        $result = false;
        if(strpos($this->consonanti1, $carattere) !== false) $result = true;
        return $result;
    }    
    public function allunga($vocale){
        if($vocale == 'a' || $vocale == 'ă'){
            $vocale = 'ā';
        }else if($vocale == 'e' || $vocale == 'ĕ'){
            $vocale = 'ē';            
        }else if($vocale == 'i' || $vocale == 'ĭ'){
            $vocale = 'ī';            
        }else if($vocale == 'o' || $vocale == 'ŏ'){
            $vocale = 'ō';            
        }else if($vocale == 'u' || $vocale == 'ŭ'){
            $vocale = 'ū';            
        }else if($vocale == 'y' || $vocale == 'ў'){
            $vocale = 'ӯ';            
        }
        return $vocale;
    }	
    public function assimilaPrefisso($forma){
        $result = $forma;
        $forma = strtoupper($forma);
        $forma = str_replace("V", "U", $forma);
        $forma = str_replace("J", "I", $forma);
        $prefisso = "";
        $len = 0;
        if(Utilities::startsWith($forma, "ADC")){
            $prefisso = "AC";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADF")){
            $prefisso = "AF";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADGN")){
            $prefisso = "A";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADG")){
            $prefisso = "AG";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADL")){
            $prefisso = "AL";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADNASC")){
            $prefisso = "AG";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADNA")){
            $prefisso = "AN";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADNI")){
            $prefisso = "AN";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADNO")){
            $prefisso = "AN";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADNU") || Utilities::startsWith($forma, "ADNV")){
            $prefisso = "AN";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADP")){
            $prefisso = "AP";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADQUI")){
            $prefisso = "AC";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADSC")){
            $prefisso = "A";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADSP")){
            $prefisso = "A";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADST")){
            $prefisso = "A";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADS")){
            $prefisso = "AS";
            $len = 2;
        }else if(Utilities::startsWith($forma, "ADT")){
            $prefisso = "AT";
            $len = 2;
        }else if(Utilities::startsWith($forma, "APS")){
            // non è previsto dal dizionario, ma è nei testi; nel Calonghi ci sono solo 4 voci in aps-, tutte varianti di altre in abs-
            $prefisso = "AB";
            $len = 2;
        }else if(Utilities::startsWith($forma, "CONL")){
            $prefisso = "COL";
            $len = 3;
        }else if(Utilities::startsWith($forma, "CONM")){
            $prefisso = "COM";
            $len = 3;
        }else if(Utilities::startsWith($forma, "CONP")){
            $prefisso = "COM";
            $len = 3;
        }else if(Utilities::startsWith($forma, "CONR")){
            $prefisso = "COR";
            $len = 3;
        }else if(Utilities::startsWith($forma, "ECF")){
            $prefisso = "EF";
            $len = 2;
        }else if(Utilities::startsWith($forma, "INB")){
            $prefisso = "IM";
            $len = 2;
        }else if(Utilities::startsWith($forma, "INL")){
            $prefisso = "IL";
            $len = 2;
        }else if(Utilities::startsWith($forma, "INM")){
            $prefisso = "IM";
            $len = 2;
        }else if(Utilities::startsWith($forma, "INP")){
            $prefisso = "IM";
            $len = 2;
        }else if(Utilities::startsWith($forma, "INR")){
            $prefisso = "IR";
            $len = 2;
        }else if(Utilities::startsWith($forma, "OBC")){
            $prefisso = "OC";
            $len = 2;
        }else if(Utilities::startsWith($forma, "OBF")){
            $prefisso = "OF";
            $len = 2;
        }else if(Utilities::startsWith($forma, "OBG")){
            $prefisso = "OG";
            $len = 2;
        }else if(Utilities::startsWith($forma, "OBP")){
            $prefisso = "OP";
            $len = 2;
        }else if(Utilities::startsWith($forma, "OPS")){
            if(!forma.equalsIgnoreCase("OPS")){
                $prefisso = "OB";
                $len = 2;
            }
        }else if(Utilities::startsWith($forma, "OPTR")){
            $prefisso = "OB";
            $len = 2;
        }else if(Utilities::startsWith($forma, "OPTU")){
            if(!Utilities::startsWith($forma, "OPTUM")){
                $prefisso = "OB";
                $len = 2;
            }
        }else if(Utilities::startsWith($forma, "SUBF")){
            $prefisso = "SUF";
            $len = 3;
        }else if(Utilities::startsWith($forma, "SUBG")){
            $prefisso = "SUG";
            $len = 3;
        }else if(Utilities::startsWith($forma, "SUBR")){
            $prefisso = "SUR";
            $len = 3;
        }else if(Utilities::startsWith($forma, "SUPS")){
            $prefisso = "SUB";
            $len = 3;
        }else if(Utilities::startsWith($forma, "SUPT")){
            $prefisso = "SUB";
            $len = 3;
        }else if(Utilities::startsWith($forma, "SUSS")){
            $prefisso = "SUB";
            $len = 3;
            
        }
        if(!empty($prefisso)){
            if(ctype_lower(substr($result, 0, 1))){
                $prefisso = strtolower($prefisso);
            }
            $result = $prefisso . "-" . substr($result, $len);
        }
        return $result;
    }
	
	


}