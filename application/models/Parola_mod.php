<?php

class Application_Model_Parola
{
    private $valore = "";
    private $standard = "";
    private $quantitavoc = "*";
    private $db;
    
    private $ritmica = ""; //1, 2, 3p o 3pp, 4p o 4pp ...: count($sillabe)
    private $rit_standard = "";
    private $trovato = false;
    private $sillabe = array();
    private $vocabolario;
    private $enclitica = "";
   
    private $accentata = false;
    private $prosodia = true;
    private $log = "";
    private $marcata = "";
    private $taggata = false;
    private $livellog = 0; // 1: non produce necessariamente errore; 2: risolto con criterio ; 3 risolto casualmente

    private $singulae = "";

    function __construct($valore, $db, $vocabolario){//singole parole
        $this->valore = $valore;
        $this->db = $db;
        $this->vocabolario = $vocabolario;
        $this->interroga();//richiama il metodo che interroga il DB su oggetto parola
        $this->sillabe[count($this->sillabe)-1]->setUltima(true);
    }

    public function getValore(){
        return $this->valore;
    }

    public function getQuantitaVoc(){
        return $this->quantitavoc;
    }

    public function setRitmica($value){
        $this->ritmica = $value;
    }

    public function getRitmica(){
        return $this->ritmica;
    }
    
    public function getRitStandard(){
        return $this->rit_standard;
    }
    public function setRitStandard($value){
        $this->rit_standard = $value;
    }

    public function getContaSill(){
        return count ($this->sillabe);
    }

    public function getSillabe(){
        return $this->sillabe;
    }

    public function isTrovato(){
        return $this->trovato;
    }

    public function isAccentata(){
        return $this->accentata;
    }

    public function isProsodia(){
        return $this->prosodia;
    }

    public function getPrima(){
        return $this->sillabe[0];
    }

    public function getUltima(){
        return $this->sillabe[count($this->sillabe)-1];
    }

    public function getPenultima(){
        return $this->sillabe[count($this->sillabe)-2];
    }

    public function getTerzultima(){
        return $this->sillabe[count($this->sillabe)-3];
    }

    public function getEnclitica(){
        return $this->enclitica;
    }

    public function getLog(){
        return $this->log;
    }

    public function getSingulae(){
        return $this->singulae;
    }

    public function getMarcata(){
        return $this->marcata;
    }

    public function isTaggata(){
        return $this->taggata;
    }

    public function getLivellog(){
        return $this->livellog;
    }


    private function interroga(){
       //prendo in entrata solo parole del segmento che mi conta almeno 8 sillabe
        $path = "https://cursusinclausula.uniud.it/public/img/";
        $value = $this->valore; //è la parola singola
        $value = trim($value);
        $n=-1;
        $scansione = "";
        $Num= 0;
        
        if (($n = Application_Model_Start::inStringa($value, "(=")) >-1){ //posizione
            $this->marcata = substr($value, $n+3, -1);
            $value = substr($value, 0, $n);
            $this->valore = $value;
            error_log("risultato= " . $value);
            error_log ("MARCATURA: ". $this->marcata);//tipo: +-+
            $this->taggata = true;
        }

        //if ($this->marcata != ""){
        $value = Application_Model_Start::cambiareU_V($value);
        $result = false;
        $tmp = "";
        //controllo ENCLITICHE
            for ($i=0; $i<count($value); $i++) {
                       if (endsWith($value, "QVE")){
                           if ($this->vocabolario->isEnclitica($value) === true){
                               $value = substr_replace ($value, "", -3, 3);
                              error_log( "#ENCLITICA!!! " . $value . "#");
                               $this->enclitica = "QVE";
                           }
                        }
            }
            for ($i=0; $i<count($value); $i++) {
                       if (endsWith($value, "VE")){
                           if ($this->vocabolario->isEnclitica($value) === true){
                               $value = substr_replace ($value, "", -2, 2);
                              // error_log( "#" . $value . "#");
                               $this->enclitica = "VE";
                           }
                        }
            }
            //analisi delle parole senza enclitica;
            $value = Application_Model_Start::cambiaV_U($value); //uniformo tutte le U/V in U
            ///controlla nella tabella semivocali se esiste la parola, sostituiscila con standard
           //error_log ("$$" . $value);
            $tmp = $this->vocabolario->semivocali($value);

                if (count($tmp)>0){ // Assegna a $value
                    $value = $tmp[0];
                    error_log($value . " ha semivocale anomala"); //di solito è risolta dal vocabolario
                    if  (count($tmp)>1){ // oppure assegna a $omografo
                        $omografo = $tmp[1];
                    error_log($value . " ha semivocale anomala e ha omografo= " . $omografo);//produce alert!
                    }
                 }  else if (count($tmp)===0) { ////se non è una parola con "semivocale riservata:
    //controlla se è perfetto
                        $tmp = Application_Model_Start::perfetti($value);
                        if (count($tmp)=== 0){
                            //se la parola non è un perfetto applico il cambia U/V e I/J
                            $value = Application_Model_Start::definisciSemivocali($value);
                        }else if (count($tmp) === 1){
                            $value = $tmp[0];
                            }else{//caso omografi tipo volvi/volui
                                $value = $tmp[0];
                            }
                 }
       //// cerco la parola nel vocabolario solo per controllo quantità

            $cerca = $value; //uso un alias
            $cerca = str_replace ("JJ", "I", $cerca);
            $cerca = str_replace ("J", "I", $cerca);
            $cerca = str_replace ("QV", "QU", $cerca);
            $cerca = str_replace ("GV", "GU", $cerca);
            $cerca = str_replace("SV", "SU", $cerca);


            $cercata = "";
            $pros_cercata = "";
              //$risultato= array() ;

            $risultato = $this->vocabolario->get($cerca);
           if ($risultato){
            error_log("cerca è uguale a " . $risultato[0][0] . "<br/>" . $risultato[0][1] ."<br/>". $risultato[0][2] . "<br/>");
            //echo "cerca è uguale a" . $risultato[0][0] . "<br/>" . $risultato[0][1] ."<br/>". $risultato[0][2] . "<br/>";
            $this->singulae = $cerca . " corrisponde a " . $risultato[0][0] . " (".  $risultato[0][2] . ") ";
//            $cercata = $risultato[0][0];
//            $pros_cercata = $risultato[0][2];

           }
            $copia_result = $risultato;

            //controllo di casi con maiuscola: ne prendiamo nota, ma li eliminiamo
            $pos= array(); //posizione
            if (count($risultato) > 1){
            //////////////////////////////////Riportare sul log///////////////////////


                for ($i=0; $i<count($risultato); $i++){
                   if (ctype_upper(mb_substr($risultato[$i][0], 0, 1))){
                       $pos[] = $i;
                   }
                }
                if ((count($risultato)-count($pos))== 1){
                    $array = array();

                    foreach ($pos as $item){
                        $risultato[$item][0]="";
                    }
                    foreach ($risultato as $item){
                        if ($item[0]!= ""){
                            $array[]=$item;
                        }
                    }
                    $risultato = $array;
                }
            }

            if (count($risultato) > 1){

                $this->log = "<img src='" . $path . "warning_viola_mini.png' align='bottom'/><b> ATTENZIONE!!</b> trovate più prosodie per la parola: " . $cerca . " (";
                $this->singulae = $cerca . " corrisponde a ";
                for($i=0; $i<count($risultato); $i++){
                    if ($i>0) $this->log .= ", ";
                    $this->log .=  $risultato[$i][0];
                    if ($i>0) $this->singulae .= " oppure a ";
                    $this->singulae .= $risultato[$i][0]. "(" . $risultato[$i][2] . ")" ;
                }
                $this->log .= ")";
                error_log("!!Warning! trovate più prosodie per la parola: " . $cerca );
                $this->livellog = 3; //risolto senza criterio
            } else if (count($pos)>0){
                    $this->log = "<img src='" . $path . "warning_mini.png' align='bottom'/><b> AVVISO!!</b> trovate più prosodie per la parola: " . $cerca . " (";
                    $this->singulae = $cerca . " corrisponde a ";
                    for($i=0; $i<count($copia_result); $i++){
                        if ($i>0) $this->log .= ", ";
                        $this->log .=  $copia_result[$i][0] ;
                        if ($i>0) $this->singulae .= " oppure a ";
                        $this->singulae .=  $copia_result[$i][0] . "(" . $copia_result[$i][2] .")";
                    }
                $this->log .= ")" . "  (accettato il nome comune)";
                $this->singulae .=  "  (accettato il nome comune)";
                error_log("WARNING! trovate più prosodie per la parola: " . $cerca );
                $this->livellog = 2; //risolto con criterio
            }
            $ct = 0;
            foreach ($risultato as $item){
               $this->quantitavoc = $item[2];
               $this->ritmica = '';
               $this->trovato = true;
                $ct++;
                break;
            }
            //error_log($value[0] . " @ " . $value[1] . " @ ". $value[2]);
            if ($ct === 0){
               error_log ("non trovo: " . $cerca . " nel vocabolario");
               $this->log = "Non trovata nel vocabolario: " .$cerca ;
               $this->singulae = $cerca . " non è stata trovata nel vocabolario";
               $this->livellog = 1;//non necessariamente produce un errore irrisolvibile
            }
                    // quindi applica a $value altri metodi utili
                    // per la seguente divisione in sillabe
                    //dittonghi deve restituire array e non stringa; se ha più array assegna omografo
                        $array = $this->vocabolario->dittonghi($value); //trova i falsi dittonghi tipo israel
                        $value = $array[0];
                            if (count($array)>1){
                                $omografo = $array[1];
                            error_log ($omografo . " è omografo di non dittongo!!");
                            }
                        $value = Application_Model_Start::cancellaH_init($value);
                        $value = Application_Model_Start::cons_doppie($value);
                        $value = Application_Model_Start::occl_liquida($value);
                        $value = trim($value);
                        
    /////divisione sillaba ////////////
                    $char = preg_split('//', $value, -1, PREG_SPLIT_NO_EMPTY);
                    $stringa = "";
                    $this->sillabe = array();
                    $conta = 0; //inizia il ciclo della divisione in sillabe
                    for ($i=0; $i<count($char); $i++){
                    $this->sillabe[$conta] = new Application_Model_Sillaba();
                    $vocale = false;
                    $valore_sillaba = "";
                    $chiudi = false;
                    for ($j=$i; $j<count($char); $j++){
                        //caso carattere finale
                        if ($j === count($char)-1){
                            $valore_sillaba .= $char[$j];
                            $chiudi= true;
                            $this->sillabe[$conta]->setUltima(true);
                            $i=$j;
                        } else if(Application_Model_Start::is_Vocale($char[$j])){     //caso vocale
                            if($vocale === false){
                                $valore_sillaba .= $char[$j];
                                $vocale = true;
                                if (Application_Model_Start::is_Vocale($char[$j+1])){
                                    $chiudi = true;
                                    $i=$j;
                                }
                           }else{
                                $chiudi = true;
                                $i = $j-1;
                           }
                          }else if($char[$j]==="."){        //casi dittongo e occl+liquida
                            if (Application_Model_Start::is_Vocale($char[$j-1])){
                                    $valore_sillaba .= $char[$j+1];
                                    $this->sillabe[$conta]->setQuantita("+");
                                    $j++;
                                    $i=$j;
                                    if ($j === count($char)-1){
                                        $chiudi = true;
                                    }

                            } else {
                                    $valore_sillaba .= $char[$j+1];
                                    //$this->sillabe[$conta]->setQuantita("+");//è lunga
                                    $j++;
                            }
                        }else if (!Application_Model_Start::is_Vocale($char[$j])){ //caso consonante
                            if ($vocale === false){
                                $valore_sillaba .= $char[$j]; //acquisisci consonante
                            }else {//vocale - consonante: controlla se fa parte di questa sillaba o della successiva
                                $stringa = $char[$j];
                                for ($y=$j+1; $y<count($char); $y++){ // controlli sul carattere successivo alla consonante
                                    if (Application_Model_Start::is_Vocale ($char[$y]) || ($char[$y]=== ".")){//se la successiva è una vocale o lettera doppia
                                        if(strlen($stringa) === 1){ //e se la stringa si ferma a 1
                                                $stringa = "";//esci
                                                $i = $j-1; // e torna all'analisi del carattere
                                        }else{//se la stringa ha già dei caratteri
                                            $stringa = substr($stringa, 0, 1); //prendi solo il primo
                                            $i = $j;
                                        }
                                        break;
                                    }else if ($y===(count($char)-1)){//se invece è consonante ed è l'ultima
                                            $stringa .= $char[$y];//acquisisci
                                            $i=$y;                //ed esci
                                    }else{ //se è consonante
                                        $stringa .= $char[$y]; //sillaba chiusa dentro a parola
                                        if ($char[$y]!= "H"){
                                            $this->sillabe[$conta]->setQuantita("+");
                                        }
                                    }
                                }
                               $valore_sillaba .= $stringa;
                               $chiudi = true;
                            }
                        }//finita la casistica
    //////operazioni di chiusura sillaba
                        if ($chiudi === true){
                        if(!empty($valore_sillaba)){//evita la sillaba vuota
                            $this->sillabe[$conta]->setValore($valore_sillaba);
                            $this->sillabe[$conta]->setPos($conta);
                            $conta++;
                        }
                            break;
                        }
                }//finito il ciclo for $j
            }//finito il ciclo for $i: completata la divisione in sillabe
    //}
/// assegno la quantita 'alternativa' del vocabolario alle sillabe
        if ($this->marcata!="" ){

            if(strlen($this->quantitavoc)==strlen($this->marcata)){
                $this->quantitavoc = $this->marcata;
                error_log ("la parola è marcata!!");
                if (count($this->sillabe)==strlen($this->quantitavoc)){
                    for ($i=0; $i<strlen($this->quantitavoc); $i++){
                    $this->sillabe[$i]->setQuantita(substr($this->quantitavoc, $i, 1));
                     error_log("WWW " . $this->sillabe[$i]->getQuantita(). " WWW");
                    }

                }else    {
                    $this->log = "!!ERRORE!! Diverso numero di sillabe";
                    error_log ("!!!ERRORE!!");
//                    for ($i=0; $i<strlen($this->quantitavoc); $i++){
//                    $this->sillabe[$i]->setQuantita(substr($this->quantitavoc, $i, 1));
//                     error_log("BBB " . $this->sillabe[$i]->getQuantita(). " BBB");
//                    }
                }
                error_log ("@@@ " . $this->quantitavoc . " @@");
            } else  {                     
                     error_log("diverso numero sillabe marcatura". $this->marcata . " e elaborazione/vocabolario:". $this->quantitavoc);
//                     if(strlen($this->quantitavoc)>strlen($this->marcata)){
//                        $this->quantitavoc = $this->marcata;
//
//                        error_log("SILLABE: " . count($this->sillabe));
//                        error_log("MARCATE: " . strlen($this->quantitavoc));
//
//                        //non posso assegnare un numero diverso di sillabe!! quindi????
//                       // count($this->sillabe)=strlen($this->quantitavoc);
//
//                        for ($i=0; $i<strlen($this->quantitavoc); $i++){
//                        $this->sillabe[$i]->setQuantita(substr($this->quantitavoc, $i, 1));
//                         error_log("HHH " . $this->sillabe[$i]->getQuantita(). " HHH");
//                        }
//                     }
//                     else if(strlen($this->quantitavoc)<strlen($this->marcata)){
//                        $this->quantitavoc = $this->marcata;
//                        error_log("SILLABE: " . count($this->sillabe));
//                        error_log("MARCATE: " . strlen($this->quantitavoc));
//                       // count($this->sillabe)=strlen($this->quantitavoc);
//
//                        for ($i=0; $i<strlen($this->quantitavoc); $i++){
//                        $this->sillabe[$i]->setQuantita(substr($this->quantitavoc, $i, 1));
//                         error_log("JJJ " . $this->sillabe[$i]->getQuantita(). " JJJ");
//                        }
//                     }


            }
                    
            


        }else if ($this->trovato === true){
            if (count($this->sillabe) != strlen($this->quantitavoc)){
                error_log("diverso numero di sillabe in :" . $this->valore . "(" . count($this->sillabe). " ma ".strlen($this->quantitavoc) . " in vocabolario)");
            }
            for ($i=0; $i<count($this->sillabe); $i++){
                $v= substr ($this->quantitavoc, $i, 1);
                if ($this->sillabe[$i]->getQuantita() != "*" && $this->sillabe[$i]->getQuantita()!= $v){
  error_log ("diversa quantità in :" . $this->valore . "= ". $v . " in vocabolario, ma nella nostra elaborazione: " . $this->sillabe[$i]->getQuantita());
                }
                $this->sillabe[$i]->setQuantita(substr ($this->quantitavoc, $i, 1));
                ///assegnamento quantità breve a sillabe finale con quantità * e non terminanti in 'S'
                $c=$this->sillabe[count($this->sillabe)-1]->getValore();
                $c = right ($c, 1);
                if ($c != "S" && $this->sillabe[count($this->sillabe)-1]->getQuantita() === "*"){
                    $this->sillabe[count($this->sillabe)-1]->setQuantita("-");
                }
                error_log ("quantità->> " . $this->sillabe[$i]->getQuantita());
            }
            
            
        }else{//se non ho quantità dal vocabolario!!!
//desinenze con quantità certe
           $x = Application_Model_Costanti::getCerte();
            foreach ($x as $key=>$value){
                if (endsWith($this->valore, $key) === true){
                //il numero di breve/lunghe in value corrisponde al numero di sillabe da 'ri-quantificare'!
                        if (strlen($value) === 2){
                           $this->sillabe[count($this->sillabe)-2] ->setQuantita(substr($value, 0, 1));
                           $this->sillabe[count($this->sillabe)-1] ->setQuantita(substr($value, 1, 1));
                        }
                        if (strlen($value) === 3){
                           $this->sillabe[count($this->sillabe)-3] ->setQuantita(substr($value, 0, 1));
                           $this->sillabe[count($this->sillabe)-2] ->setQuantita(substr($value, 1, 1));
                           $this->sillabe[count($this->sillabe)-1] ->setQuantita(substr($value, 2, 1));
                        }
            }
            
         }
     }
//     if($this->marcata !=""){
///si ricompone la parola che era interessata dall'enclitica
        if (!empty($this->enclitica)){
error_log("se è enclitica è:   " . $this->enclitica);
            $this->sillabe[$conta] = new Application_Model_Sillaba();
            $this->sillabe[$conta]->setValore($this->enclitica); //VE oppure QVE
            $this->sillabe[$conta]->setQuantita("-");
            $this->sillabe[$conta]->setPos($conta);
            $this->sillabe[$conta-1]->setAccento(true);
            $this->sillabe[$conta-1]->setUltima(false);
            $c=$this->sillabe[$conta -1]->getValore();
            $c = right ($c, 1);
            if (!Application_Model_Start::is_Vocale($c)){
                $this->sillabe[$conta-1]->setQuantita("+");

            }
        } 

        if (count($this->sillabe)>1 && $this->taggata==false && $this->trovato==false){//se non è monosillabo
////assegnamento quantità breve a sillaba che termina in vocale davanti a sillaba che inizia per vocale
            for ($conta=0; $conta<count($this->sillabe)-1; $conta++){
               $c= $this->sillabe[$conta]->getValore();
               $c = right ($c, 1);
               $s = $this->sillabe[$conta+1]->getValore();
               $s = substr($s, 0, 1);
                if (Application_Model_Start::is_Vocale($c) && Application_Model_Start::is_Vocale($s)){
                    $this->sillabe[$conta]->setQuantita("-");
                }
                error_log ("SSS " . $this->sillabe[$conta]->getQuantita() . " SS");
            }
///assegnamento quantità breve a sillabe finale con quantità * e non terminanti in 'S'
            $c=$this->sillabe[count($this->sillabe)-1]->getValore();
            $c = right ($c, 1);
            if ($c != "S" && $this->sillabe[count($this->sillabe)-1]->getQuantita() === "*"){
                $this->sillabe[count($this->sillabe)-1]->setQuantita("-");
            }
            
        }
//    }
////assegnamento ritmica (dopo aver ricomposto l'enclitica!!)
            if (count($this->sillabe) <= 2){ //casi monosillabi e bisillabi
                
                if (count($this->sillabe) === 1){
                   $this->ritmica = "1";
                   $this->rit_standard = "1";
                } else if (count($this->sillabe) === 2){
                    $penultima = $this->sillabe[count($this->sillabe)-2];
                    $this->ritmica = "2";
                    $this->rit_standard = "2";
                    $penultima->setAccento(true);
                }
            } else {
               $penultima = $this->sillabe[count($this->sillabe)-2];
               $terzultima = $this->sillabe[count($this->sillabe)-3];
               if ($penultima->getQuantita() === "+" || $penultima ->getQuantita()=== "x"){ //qui va fatto un controllo su enclitica!!
                   $penultima->setAccento(true);
                   $terzultima->setAccento(false);
                   $this->ritmica = count($this->sillabe) . "p";
                   $this->rit_standard = "p";
               } else if ($penultima ->getQuantita()=== "-"){
                   if ($penultima->isAccento(true)){
                       $terzultima->isAccento(false);
                   }
                   $terzultima->setAccento(true);
                   $this->ritmica = count($this->sillabe) . "pp";
                   $this->rit_standard = "pp";
               } else if ($penultima ->getQuantita()=== "*"){
                   $this->ritmica = " ? ";
                   $this->rit_standard = "?";
               }
               
           }
////////definizione parole risolte ritmicamente e prosodicamente
           if ($this->ritmica === " ? "){
               $this->accentata = false;
           } else {
               $this->accentata = true;
           }
           for ($conta=0; $conta<count($this->sillabe)-1; $conta++){
               if ($this->sillabe[$conta]->getQuantita()==="*"){
                   $this->prosodia = false;
               }
               
           }
           error_log ("XXX " . $this->quantitavoc . " XX");
           //if/else aggiunto 29 maggio!!!!!!
           if (count($this->sillabe)!=strlen($this->quantitavoc)) {
               error_log("assegno quello che ho! ma mi manca un oggetto SILLABA!!!");
               
                error_log("&&& ". $this->sillabe[$conta]->getQuantita() . " &&&" . $this->sillabe[$conta]->getValore());
           
               


           } else {
           for ($conta=0; $conta<count($this->sillabe); $conta++){
                error_log("## ". $this->sillabe[$conta]->getQuantita() . " ##" . $this->sillabe[$conta]->getValore());
           }
           }
        
     }//fine funzione interroga
}

