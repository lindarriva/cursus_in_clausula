<?php

class SingulariterController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        ini_set('session.gc_maxlifetime', '7800');
    }

    public function singulariterAction(){
        Zend_Session::start();
        Application_Model_Accessi::controlla();
        
        error_log("ESEGUO!");
        $db = new mysqli(   $_SESSION["host"],
                            $_SESSION["user"],
                            $_SESSION["password"],
                            $_SESSION["database"]);//localhost,user,pw,db
        $db->set_charset("utf8");
        error_log ('Success... ' . $db->host_info . "\n");

        $idSingulariter = 0;
        $singulariter =  new Application_Model_DbTable_Singulariter();
        $accessi = new Application_Model_Accessi();

//        if(!$accessi->hasIdentity()){
//            $this->_forward('index','index');
//            return;
//        }
        $idUtenti = $accessi->getIdUtente();
        $this->view->display = "block";
 
    }

     
    public function ajaxAction(){
        Zend_Session::start();
        $this->_helper->layout->disableLayout();
        if(!Application_Model_Accessi::controllaAjax()){
            echo "@sessionescaduta@";
            return;
        }
            
            
            $azione=$this->_getParam('azione');
            error_log("azione=" . $azione);
            
            $searchChiave=$this->_getParam('chiave');
            error_log("clausola: " . $searchChiave);

            if ($searchChiave==""){
                    error_log("Il campo è vuoto, inserire una clausola.");
                    $azione='annulla';
                    $this->view->messaggio="Il campo è vuoto, inserire una clausola.";
                }


            $db = new mysqli($_SESSION["host"] ,
                        $_SESSION["user"] ,
                        $_SESSION["password"] ,
                        $_SESSION["database"]);//localhost,user,pw,db
            $db->set_charset("utf8");
            error_log ('Success... ' . $db->host_info . "\n");

            $accessi = new Application_Model_Accessi();

            if(!$accessi->hasIdentity()){
                $this->_forward('index','index');
                return;
            }
                $idUtenti = $accessi->getIdUtente();
                $singulariter =  new Application_Model_DbTable_Singulariter();
                $stringa = array("clausola"=> $searchChiave, "idutenti"=>$idUtenti);
            if ($azione == "esegui"){
                 error_log("ESEGUO!");
// metti $clausola in DB!!!;
                $singulariter->insert($stringa);
                $idSingulariter = $singulariter->getAdapter()->lastInsertId();
                $fenomeno = true;
//                $stringa = array("clausola"=> $searchChiave, "idutenti"=>$idUtenti);
                
                $this->view->grid=$this->analisi($idSingulariter, $fenomeno);
//                return $grid;
            } else if ($azione=="annulla"){
                $searchChiave=$this->_getParam('chiave');
                error_log("NUOVA");
            }else {
                
            }
        
    }




    private function creaGrigliaRisultatiClausola($idSingulariter){
        Zend_Session::start();
        require_once("hidatagrid/HIdatagrid.php");
      $singulariter = new Application_Model_DbTable_Singulariter();
      $lista = $singulariter->getSingulariter($idSingulariter);
      
      $lista = $singulariter->getChiave($idSingulariter);
      $clausola = $lista['clausola'];
      $prosodia = $lista['quant_orlandi'];
      $ritmo1 = $lista['rit_pass'];
      $ritmo2 = $lista['rit_standard'];
      $ritmotab = $lista['tipo'];
      $cursus = $lista['cursus'];
      $parole = $lista['parole'];
      $quant_iato = $lista['quan_iato'];
//      error_log("lista: " . $lista['clausola'] . "   *    " . $lista['ritmo']);
      $grid = new HIdatagrid(
            "RISULTATI",
            "Analisi Clausola",
            array("","", "", "", "", ""),
            array(100,100,100,100,100,100),
            300);

            $grid->appendRow("row1", array("$parole"),
                    array("colsAlign" => array("left"))
            );
            $grid->appendRow("row2", array(""),
                    array("colsAlign" => array("left"))
            );
            $grid->appendRow("row3", array("$clausola"),
                    array("colsAlign" => array("left"))
            );
            if ($prosodia === "null"){
                $grid->appendRow("row4", array("Una delle parole in clausola non è risolvibile metricamente: usa la marcatura tipo Jesus(==++) per risolverla"),
                    array("colsAlign" => array("left"))
            );
            } else {
                $grid->appendRow("row4", array("prosodia (con sinalefe): $prosodia"),
                    array("colsAlign" => array("left"))
            );
            }
            if ($quant_iato === $prosodia){
                $grid->appendRow("row5", array("prosodia (senza sinalefe): $prosodia"),
                    array("colsAlign" => array("left"))
                 );
            } else if ($quant_iato === "vuota"){
                $grid->appendRow("row5", array(""),
                        array("colsAlign" => array("left"))
                );
            } else {
                $grid->appendRow("row5", array("prosodia (senza sinalefe): $quant_iato"),
                        array("colsAlign" => array("left"))
                );
            }
            $grid->appendRow("row6", array("ritmo: $ritmo1 -> $ritmo2"),
                    array("colsAlign" => array("left"))
            );
            $grid->appendRow("row7", array("cursus: $cursus, $ritmotab"),
                    array("colsAlign" => array("left"))
            );
//            $grid->appendRow("row8", array("?"),
//                    array("colsAlign" => array("left"))
//            );
    

        $grid->setWidth100(true);
        return $grid;
        
        }




    private function analisi($idSingulariter, $fenomeno){ //, $monosillabi, $sinalefe
        require_once 'Funzioni.php';
        require_once 'Utilities.php';
        require_once 'lemmata/Lemmata.php';
        Zend_Session::start();
        $path = "http://cursusinclausula.uniud.it/public/img/";
        $singulariter = new Application_Model_DbTable_Singulariter;
        $record= $singulariter->getChiave($idSingulariter);//[IdSingulariter]=> [IdUtenti]=> [clausola]=> ...
        $contenuto = $record['clausola'];
        $idUtenti = $record['IdUtenti'];
        error_log ("STRINGA: " . $contenuto);
        $nuova="";
        $array="";
        $accenti="";
        $quantita = "";
        $parole = explode(" ", $contenuto);
        $stringa="";
        //linking al vocabolario
        $db = new mysqli($_SESSION["host"] ,
                        $_SESSION["user"] ,
                        $_SESSION["password"] ,
                        $_SESSION["database"]);//localhost,user,pw,db
        $db->set_charset("utf8");
        error_log ('Success... ' . $db->host_info . "\n");


        $datiDb = array("host" => "localhost","username" => "cursusin_cla", "password" => "cursus", "dbname" => "cursusin_cla", "charset" => "utf8");
            $connection = new Zend_Db_Adapter_Pdo_Mysql($datiDb);
            $lemmata = new Lemmata_Lemmata();
            $lemmata->setConnection($connection);









        $vocabolario = new Application_Model_Vocabolario($db);//localhost,user,pw,db
//diventa oggetto segmento elaborato secondo la classe segmento : qui passo la connessione
        $segmento = new Application_Model_Segmento($contenuto, $db, $vocabolario);
    //richiamo il risultato in oggetto di classe parola
        $array = $segmento->getParole(); error_log("contenuto".$record['clausola']);
            $ritmi = "";
            $sommaSill = 0;
            $catena ="";
            $notazione="";
            $accenti = "";
            $orlandi="";
            $ictus_uno="";
            $ictus_due="";
            $ritmo_tab="";
            $calcoli="";
            $sinalefe = $fenomeno; //metto false di default?? o variabile $fenomeno
            $itemlog="";
            $quant_iato="";
            $ripeti=false;

            foreach ($array as $value){
                    error_log($value->getValore() . " $$$ " . $value->getQuantitaVoc());
                    $catena .= $value->getValore() . " "; //parola
                    $accenti .= $value->getRitmica() . " ";// 1 p 2p pp ...


                    $itemlog .= $value->getSingulae() . "</br>";
//error_log("ITEM: **  " . $itemlog);

                      //formare tipi clausole ritmiche
                     //in presenza di sinalefe salta la quantita dell'ultima!
                    $nuova = $value->getSillabe();
                    
                    foreach ($nuova as $sillaba){
                        error_log( "sing? " . $sillaba->getValore(). " ". $sillaba->getQuantita() . "<br/>");
                                                
                        if ($sinalefe && $sillaba->getFenomeno() === "S"){//echo "si annulla il valore prosodico di " . $value->getValore() . "<br/>";
                        error_log($sinalefe . "=sinalefe con Fenomeno");
                            $ripeti = true;
                        } else if ($sillaba->isUltima()){
                        error_log($sinalefe . "=sinalefe");
                            error_log ("che ORLANDI prendo qui? " . $sillaba->getQuantita() . "=" . $sillaba->getValore());
                            $orlandi .= $sillaba->getQuantita() . "|" ;
                            $notazione .= $sillaba->getQuantita();
                            $quant_iato = $orlandi;
                            error_log("VAR ORLANDI: " . $orlandi . " VAR IATO :  " . $quant_iato);
                        } else if ($sillaba->isAccento()){
                            $orlandi .= "A" . $sillaba->getQuantita(); //antepongo una "A" alla sillaba accentata
                            $notazione .= $sillaba->getQuantita();
                            $quant_iato = $orlandi;
                        } else {
                            $orlandi .= $sillaba->getQuantita();
                            $notazione .= $sillaba->getQuantita();
                            $quant_iato = $orlandi;
                        }
                    }
                    $numero_sillabe = count($nuova);
                    $sommaSill += $numero_sillabe;

            $porzione = $segmento->getPorzione();
            $porzione = str_replace("<", "[", $porzione);
            $porzione = str_replace(">", "]", $porzione);
            } //fine del ciclo che considera singole parole
            

          error_log($catena . " catena quantita " . $orlandi );//orlandi è clausola tipo A-+|-+-A+-
            
            
                $rit_pass = $this->standardizza($accenti);
            
           // error_log ("rit_pass==" . $rit_pass);
            $rit_standard = "";
            if ($rit_pass =="null"){
                $rit_pass = "null";
//                $log[] = "clausola scartata: troppo breve!" . $catena;
                error_log("clausola scartata: troppo breve!" . $catena);
                $rit_standard = "null";
            } else if ($rit_pass == "???") {
                $rit_pass = "???";
//                $log[] = "<p><img src='" .$path . "error_mini.png' align='bottom'/><b> !!ERRORE!!</b>: clausola non risolta ritmicamente: " . $catena . "</p>";
                error_log("clausola non risolta ritmicamente");
                $rit_standard = "da marcare";
            }else {
                if(Application_Model_Start::inStringa($rit_pass, "m")>-1){
                    $rit_standard = "monosillabi";
                }else {
                    $array = explode(" ", $rit_pass);
                    
                    if ($array[0]==="2"){
                            $array[0]= "p";

                        }else {
                            $array[0]=substr($array[0], 1);
                       }
                       if (count($array)==1){
                            $rit_standard = $array[0];
                        }   else {
                           $rit_standard = $array[0] . " " .  $array[1];
                        }

                    }





                    $ritmotab="";
//            $rit_standard=$analisi->rit_standard;

            if($rit_standard==="5p" ||
               $rit_standard==="1 4p" ||
               $rit_standard==="pp 2" ||
               $rit_standard==="p 3p"){

                $ritmotab.= "planus";


            } else if ($rit_standard==="6pp" ||
                    $rit_standard==="1 5p" ||
                    $rit_standard==="2 4pp" ||
                    $rit_standard==="p 4pp"||
                    $rit_standard==="pp 3pp"){

                $ritmotab.= "tardus";

            } else if ($rit_standard==="2 5p" ||
                    $rit_standard==="pp 4p" ||
                    $rit_standard==="p 5p" ){

                $ritmotab.= "velox";

            } else if ($rit_standard==="p 4p"
                    || $rit_standard==="pp 3p"){

                $ritmotab.= "trispondaicus";

            } else if ($rit_standard==="p 3pp"){

                $ritmotab.= "medius";

            } else if ($rit_standard==="p 2"){

                $ritmotab.= "dispondaicus";

            } else if ($rit_standard==="p 5pp" ||
                    $rit_standard==="pp 4pp"){

                $ritmotab.= "dispondeus-dattylicus";

            } else {

                $ritmotab.= "-";

            }

            






           }
               if (endsWith($rit_standard , "ppp")){
                   $rit_standard = str_replace("3ppp", "3pp", $rit_standard);
                   $rit_standard = str_replace("4ppp", "3pp", $rit_standard);
                   $rit_standard = str_replace("5ppp", "4pp", $rit_standard);
               }

           $orlandi = $this->orlandizza($orlandi); //passata clausola tipo A-+|-+-A+-
           $quant_iato = $this->orlandizza($quant_iato);
//           if (!$fenomeno){
//               $quant_iato = $this->orlandizza($quant_iato);
//           }
         //  error_log("dopo ORLANDIZZA: " . $orlandi);
         //  error_log("QUANT_IATO dopo ORLANDIZZA: " . $quant_iato);
           if ($orlandi != "null" || $quant_iato != "null"){
                $calcoli = substr_replace($orlandi, "*", -1, 1); //sostituisco l'ultimo segno con asterisco
                $calcoli = substr_replace($quant_iato, "*", -1, 1);
           } else if ($orlandi=="vuota" || $quant_iato ="vuota"){
//                $log[] = "<p>clausola scartata: troppo breve " . $catena . "</p>";
           } else {
//               $log[] = "<p><img src='" . $path . "error_mini.png' align='bottom'/><b><font color:#FF0000> !!ERRORE!!</font></b>: clausola non risolta metricamente: " . $catena . "</p>";
               error_log("clausola non risolta metricamente");
           }
           $ultima= "";
           $penultima = "";
           $array = explode("|", $calcoli);
           if (count($array)>1){
            $penultima = $array[0];
            $ultima = $array[1];
           } else if (count($array)==1){
               $ultima = $array[0];
           }
            $ritmi = $this->getClausola($rit_pass, $accenti);
            //error_log("clausola" . $ritmi);
            $cursus = $this->getCursus($ritmi);
            $cursus = ltrim($cursus);
            //error_log("cursus da ictus: " . $cursus);
            $cur_array = str_split($cursus);
            $ct= count($cur_array);
            for ($j=$ct-1; $j>=0; $j--){
                if ($cur_array[$j]==="o"){
                    $ictus_due = $cur_array[$j] . $ictus_due;
                } else {
                    $ictus_due = $cur_array[$j] . $ictus_due;
                    break;
                }
             $ictus_due = trim($ictus_due);
             $lun= strlen ($ictus_due)+1 ;
             $ictus_uno =  substr($cursus, 0, strlen($cursus)-$lun);

            }
            if ($fenomeno==true){

                  //error_log($ictus_uno ."ICTUS". $ictus_due);
                 if ($rit_pass != "null"){

                     $where = $singulariter->getAdapter()->quoteInto("IdSingulariter=?", $idSingulariter);
                     $data = array(
                    "clausola"=>$record['clausola'],
                    "quantita"=>$notazione,
                    "quant_orlandi"=>$orlandi,//visualizzato in tabella clausole generale
                    "quant_calcoli"=>$calcoli,
                    "penultima"=>$penultima,
                    "ultima"=>$ultima,
                    "accenti"=>$accenti,
                    "ritmi"=>$ritmi, //visualizzato in tabella clausole generale
                    "rit_standard"=>$rit_standard,//visualizzato in tabella Ritmo
                    "rit_pass"=>$rit_pass,
                    "tipo"=>$ritmotab,
                    "cursus"=>$cursus, //visualizzato in tabella Cursus
                    "ictus_uno"=>$ictus_uno,
                    "ictus_due"=>$ictus_due,
                    "sillabe"=>$sommaSill,
                    "parole"=>$itemlog, //visualizzato in prima riga tabella
                    "quan_iato"=>$orlandi
                         );
                $singulariter->update($data, $where);
                }
                 $fenomeno = 0;
                 if ($fenomeno==0 && $ripeti==true){
                 $this->analisi($idSingulariter, 0);
                 error_log("valore passato = " . $idSingulariter);
                 }

            } else {
                 
error_log ("SENZA SINALEFE!!!!!" . $quant_iato);

                 if ($rit_pass != "null"){

                     $where = $singulariter->getAdapter()->quoteInto("IdSingulariter=?", $idSingulariter);
                     $data = array(
                            "quan_iato"=>$quant_iato
                             );
                     $singulariter->update($data, $where);

                }
                return;
            }

 
         $vocabolario->close();
         $db->close();

         
         $this->view->grid = $this->creaGrigliaRisultatiClausola($idSingulariter, $idUtenti);
         $this->view->grid->render();
//         $log_return = $item_log;
//         return $log_return;
         return;
   }
   // riduce la clausola a 2 elementi: da considerare per calcoli frequenze
   private function  standardizza($clausola){ //arriva tipologia 1 1 1 4p oppure m m m 4p

        if ($clausola==="null"){
            return "null";
        }
	$clausola = trim($clausola);

        if (Application_Model_Start::inStringa($clausola, "?")>-1){
            return "???";
        }

        if ($clausola === "2 1" || $clausola === "1 1" || $clausola === "1 2" || $clausola === "2 m" || $clausola === "m 2" || $clausola === "1" || $clausola === "2" || $clausola === "3p" || $clausola ==="4p" || $clausola === "3pp" || $clausola === "4pp" || $clausola === "5p" || $clausola === "5pp" || $clausola === "6p" || $clausola === "6pp"){
                return $clausola;
        }

        $clausola = " " . $clausola . " |";//identificare con certezza la fine diventa " m m m 4p |"


          if ($clausola === " m 2 2 |" || $clausola===" m 2 |" || $clausola === " 2 m |" || $clausola=== " m m |"){
              //$clausola = " mono ";
              return $clausola;
        }
        error_log ("KKKK" . $clausola);
        $clausola = str_replace(" m 2 2 |", " p 2 |", $clausola); //contiamo dispondiaci ma perdiamo varianti velox e trispondiacus!!

        $clausola = str_replace(" 2 2 2 |", " 4p 2 |", $clausola);

        $clausola = str_replace(" 1 2 2 |", " 3p 2 |", $clausola);


        $clausola = str_replace(" 1 2 |", " 3p |", $clausola);


        $clausola = str_replace(" 2 1 |", " 3pp |", $clausola);

        $clausola = str_replace(" 1 1 |", " 2 ", $clausola);

        $clausola = str_replace(" 1 2 |", " 3p ", $clausola);

error_log ($clausola . "KKKK");


            $clausola = substr($clausola, 0, strlen($clausola)-1);
            $clausola = trim($clausola);
            $array = explode(" ", $clausola);

//            if (count($array)<2){
//                return "null";
//            }
            $n = count($array) - 1; //numero parole
            $errore = false;
            $clausola = "";
            $accenti = 0;
            for($i = $n; $i >= 0; $i --){
                    if (count($array)==1){
                        $clausola = $array[0];
                        
                    }

                    if($array[$i] === "1"){
                            if($i === $n){
                                    // se il monosillabo è finale, è enclitico (si attacca alla parola precedente, se c'è); poi si esce in ogni caso;
                                    if($i > 0){
                                            $clausola = $this->somma($array[$i - 1], 1) . " " . $clausola;
                                            $i = $i - 1;
                                    }else{
                                            // non c'è parola precedente
                                            return $clausola;
                                    }
                            }else{
                                    // se il monosillabo non è finale, è proclitico (si attacca alla parola che segue); poi si esce;
                                    $n1 = strpos($clausola, " ");
                                    $clausola = substr($clausola, $n1);	// toglie la parola seguente
                                    $clausola = $this->somma($array[$i + 1], -1) . " " . $clausola;
                            }
                            // si attacca la porzione precedente della clausola, se c'è
                            for($j = $i-1; $j >= 0; $j --){
                                    $clausola = $array[$j] . " " . $clausola;
                            }
                            break;
                    } else if ($array[$i]==="m"){
                        $clausola = $array[$i]. " " . $clausola;
                        return $clausola;
                    } else{
                            // plurisillabo: si acquisisce
                            $clausola = $array[$i] . " " . $clausola;
                            $accenti ++;
                    }
                    if($accenti > 1){
                            // se si sono raggiunte le due parole si esce
                            break;
                    }
            }
                $clausola = trim($clausola);
                $array = explode(" ", $clausola);

                if(count($array) === 2 || count($array) === 1){
                        // se si sono raggiunte le 2 parole si esce

                        return $clausola;

                }else{
                        // se le parole sono più di 2 si ripete il ciclo
                        return $this->standardizza($clausola);
                }


}

private function  orlandizza($clausola){//passo la clausola tipo +-A++|+A+-| >> ++|++-
//A segna la sillaba accentata; voglio le quantità solo dalla penultima sillaba accentata in giù!
//Problema con monosillabi 1, 2 o 3 prima dell'ultima parola accentata -
    // salta l'ultima posizione; controlla dove c'è una sillaba accentata, e considera la stringa da quel punto in poi
    //quindi cancella i segna-accento "A" e riposiziona la barra spaziatrice tra il penultimo e ultimo gruppo.
        $clausola = trim($clausola);
//        if (Application_Model_Start::inStringa($clausola, "*")>-1){ //scarta casi di soli 2 monosillabi o bisillabo + monosillabo!
//            return "null";
//        } else if (strlen($clausola)<5){
//            $clausola = "vuota";
//        }
            $clausola = substr($clausola, 0, strlen($clausola)-1);
            $array = explode ("|", $clausola); //[0]=>+-A++ [1]=>+A+-
            //error_log("** ". $clausola . " **"); //oretur pro impiis --> ** +A++|+|A+-+ **
            $filtrato = array_filter($array); //rimuove eventuali array con valore null, false, 0
            $array = array();
            foreach ($filtrato as $value){
                    $array[] = $value;
            }

            $n = count($array);//analizziamo a partire dall'ultima stringa
            for ($i=$n-1; $i>=0; $i--){ // dall'ultimo gruppo-prosodia; "m" indica il monosillabo!!
                if ($i===$n-1){//caso sillaba finale del gruppo || nel caso no_sinalefe non esiste problema!
                    if ($array[$i]==="-" || $array[$i]==="+" || $array[$i]==="A+" || $array[$i]==="A-"){ //A+ è il caso della sinalefe, ma A- non dovrebbe esistere, c'è però fiunt!!!
                    $array[$i] = $array[$i-1] ."m". $array[$i]; //il gruppo in penultima posizione prende il monosillabo successivo
                    $array[$i-1] = "";

                    }
                    $clausola = $array[$i];
                   // error_log("ultima pos: " . $clausola);
                } else if ($i!= 0) {
                            if (strlen($array[$i])=== 1){

                                $array [$i] = "m".$array[$i];// "m" indica il monosillabo con quantità annessa "m+" oppure "m-"
                                $clausola =   $array[$i] . "|" . $clausola;
                            } else if ($array[$i]==="m+" || $array[$i]==="m-") {

                                $clausola =  "|" . $array[$i] . $clausola; //caso doppi monosillabi?
                            }
                            else if ($array[$i]===""){
                                $clausola = $array[$i] . $clausola;
                                 error_log("posizione vuota:" . $clausola);
                            }
                            else if (($pos = Application_Model_Start::inStringa($array[$i], "A")) >-1){ //i casi accorpati di sinalefe che danno come risultato due A, non cercano ulteriori parole!!

                                $clausola =   substr($array[$i], $pos) . "|" . $clausola  ;
                                break;
                            }
                            error_log("intermedia: " . $clausola);
                } else if ($i===0){
                            if (strlen($array[$i])=== 1){
                                $clausola = $array[$i] . "|" . $clausola;
                            } else if (($pos = Application_Model_Start::inStringa($array[$i], "A")) >-1){ //i casi accorpati di sinalefe che danno come risultato due A, non cercano ulteriori parole!!
//                                if ($pos===0){
//                                    $clausola = $array[$i] . "|" . $clausola  ;
//                                }else{
                                $clausola = substr($array[$i], $pos) . "|" . $clausola  ;
//                                }

                            }
                            error_log("prima pos: " . $clausola);
                }
           }


           // controllo monosillabi; nota bene: il monosillabo in prima posizione resta staccato;
           // due o più monosillabi consecutivi tra seconda e penultmia posizione
           // si aggregano tra loro e NON con la parola seguente! (es. per se sunt manifesta vs effusa est nomen)

            //$clausola = str_replace ("|m", "", $clausola); //gruppo del monosillabo spostato in ultima posizione
            $clausola = str_replace ("|m+|", "|+", $clausola); //casi monosillabi tra parole vanno ad attaccarsi alla quantità seguente
            $clausola = str_replace ("|m-|", "|-", $clausola);
            $clausola = str_replace ("m", "", $clausola);

           // $clausola = str_replace ("||", "|", $clausola); //caso doppi monosillabi: tipo Ait --> impossibile
    //$clausola = str_replace ("", "", $clausola);
           /* if (startsWith($clausola, " |")){
                   $clausola = "***" ; //casi monosillabi da riconsiderare
            }*/

       error_log("prima del replace: " . $clausola);
            return str_replace("A", "", $clausola);

}


private function somma($parola, $posizione){//risolve la posizione del monosillabo atono

	$result = "";
        $somma = substr($parola, 1); //p o pp, se 2=null
        if (empty($somma)){
            $somma = "p";
        }
        if ($posizione === 1){ // 1 = enclitica  -1=proclitica
                $somma .= "p";}

        if($parola == "1"){ //cancella
                $result = "2"; //cancella
	}else if($parola == "2"){ //>2
                $result = "3" . $somma;
        }else { //parola npp o np
                $result = (substr($parola, 0, 1) + 1) . $somma;

        }
	return $result;

}
//propone il risultato di ritmi da presentare in tabella
private function getClausola ($rit_standard, $accenti){
        Zend_Session::start();
        $result="";

        $array = explode(" ", $rit_standard);
        $conta = 0;
        foreach ($array as $value){
            $conta += substr($value, 0, 1); //numero di sillabe da contare
    }


       $array = explode (" ", $accenti);
               $ct = 0;
                for ($i=count($array)-1; $i>=0; $i--){ //dall'ultima posizione
                    $result = $array[$i] . " " . $result;
                    $ct += substr($array[$i], 0, 1);
                    if ($ct === $conta){
                        break;
                    }

                }
                $result = trim($result);
                $prima = substr($result, 0, 1);
                if ($prima === "1"){
                }
                else if ($prima === "2"){
                    $result = substr_replace($result,"p", 0, 1);
                }
                else if ($prima === "m"){
                    $result = substr_replace($result,"m", 0, 1);
                } else {
                    $result = substr_replace($result,"", 0, 1);
                }

    return $result;
}

//trasforma ritmi nella notazione "cursus" dove si valutano solo le posizioni degli accenti
private function getCursus ($rit_pass){
    Zend_Session::start();
    $result = "";

    $ritmi = str_replace("2", "2p", $rit_pass);
    $array = explode(" ", $rit_pass);
    for ($i=0; $i<count($array); $i++){
        if ($i===0){
            if ($array[$i]==="p"){
                $array[$i]= " xo";
            } else if ($array[$i]==="pp"){
                $array[$i]= " xoo";
            } // non cambio il monosillabo, così resta 1

        } else {
            if ($array[$i]==="1"){
                $array[$i]="o";
            } else if ($array[$i]==="2"){
                $array[$i]="xo";
            } else if ($array[$i]==="3p"){
                $array[$i]="oxo";
            } else if ($array[$i]==="3pp"){
                $array[$i]="xoo";
            } else if ($array[$i]==="4p"){
                $array[$i]="ooxo";
            } else if ($array[$i]==="4pp"){
                $array[$i]="oxoo";
            } else if ($array[$i]==="5p"){
                $array[$i]="oooxo";
            } else if ($array[$i]==="5pp"){
                $array[$i]="ooxoo";
            } else if ($array[$i]==="6p"){
                $array[$i]="ooooxo";
            } else if ($array[$i]==="6pp"){
                $array[$i]="oooxoo";
           /*} else if ($array[$i]==="7p"){
                $array[$i]="oooooòo";
            } else if ($array[$i]==="7pp"){
                $array[$i]="ooooòoo";*/
            }  else {
                $array [$i]= ">7p";
            }
        }
        $result .= $array[$i];
    }
    return $result;
}


    }
