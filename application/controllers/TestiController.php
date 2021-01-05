<?php
/*
 * In questa procedura oltre a caricare i testi e riempire la tabella 'testi',
 * si procede all'analisi della suddivisione in sillabe e individuazione accenti
 * e quantità archiviando tutti i dati nella tabella 'analisi'.
 */

class TestiController extends Zend_Controller_Action
{

    public function init()
    {

        ini_set('session.gc_maxlifetime', '7800');//serve a non far scadere le variabili di sessione
    }

    public function pdfAction(){
        $this->_helper->layout->disableLayout();
        $idTesti=$this->_getParam('idTesti', 0);
        $testi = new Application_Model_DbTable_Testi();
        $testo = $testi->getTesto($idTesti);
        $this->view->nomepdf = $testo->sigla;
        $testo->log=str_replace("<img src='http://cursusinclausula.uniud.it/public/img/warning_viola_mini.png' align='bottom'/>", "!!", $testo->log);
        $testo->log=str_replace("<img src='http://cursusinclausula.uniud.it/public/img/warning_mini.png' align='bottom'/>", "**", $testo->log);
        $testo->log=str_replace("<img src='http://cursusinclausula.uniud.it/public/img/error_mini.png' align='bottom'/>", "##", $testo->log);
        $this->view->report = $testo->log;
        ;
    }

    public function testiAction(){
        Application_Model_Accessi::controlla();
        Zend_Session::start();
        $db = new mysqli(   $_SESSION["host"],
                            $_SESSION["user"],
                            $_SESSION["password"],
                            $_SESSION["database"]);//localhost,user,pw,db


        $db->set_charset("utf8");
        error_log ('Success... ' . $db->host_info . "\n");

        $this->view->attivaPulsanti = "falso";
        $idTesti = 0;
        $testi = new Application_Model_DbTable_Testi();
        $accessi = new Application_Model_Accessi();
        if(!$accessi->hasIdentity()){
            $this->_forward('index','index');
            return;
        }
        $idUtenti = $accessi->getIdUtente();
        $this->view->messaggio = "";
        $this->view->log = "";
        $this->view->mostraLog = "none";
        $this->view->arrayEnc = array("ISO-8859-1", "UTF-8", "UTF-16", "macintosh");

        $log_return="";

        if ($this->getRequest()->isPost()) {
            $azione = 'upload';
            foreach($_POST as $nome => $valore){
                // questo ciclo serve per distinguere quale pulsante di submit è stato cliccato; poiché $valore corrisponde
                // all'attributo value del button, facciamo la discriminazione sulla chiave, perché la coppia chiave=>valore
                // è name=>value
                if($nome == 'tx_rn'){
                    $azione = 'salva';
                }
            }
            error_log($azione);
            if ($azione=='upload'){
                     // importazione testo
                $fileName = basename($_FILES['uploader']['name']);
                 error_log("UPLOAD: ". $fileName);
                $uploadfile = $_SESSION["uploaddir"] . $idUtenti . "/" . $fileName;

                // eventuali controlli: dimensione, tipo
                // si verifica se esiste la directory dell'utente, altrimenti si crea
                $intDir = $_SESSION["uploaddir"] . $idUtenti . "/";
                if (!is_dir($intDir)) {
                    mkdir($intDir, 0777);
                }

                if(move_uploaded_file($_FILES['uploader']['tmp_name'], $uploadfile)){
                    // File is valid, and was successfully uploaded
                    // Registrazione su DB; prima si controlla che non ci sia già
                    $this->view->attivaPulsanti = "vero";
                    $stringa = file_get_contents($uploadfile);
                    $tmp = explode(" ", $stringa);
                    $tmp = array_filter($tmp);
                    $nparole = count($tmp);
                    if(!$testi->isFile($fileName, $idUtenti)){
                        // si controlla che la sigla del testo non sia già in uso; in caso contrario si aggiunge un indice
                        $sigla = $fileName;
                        $n = strrpos($sigla, ".");
                        if($n > 0){
                            $sigla = substr($sigla, 0, $n);
                        }
                        if($testi->isSigla($sigla, $idUtenti)){
                            $sigla .= "1";
                        }

                        $idTesti = $testi->getLastId()+1;
                        $data = array(
                            "idtesti" =>$idTesti,
                            "idutenti" => $idUtenti,
                            "nome" => $fileName,
                            "sigla" => $sigla,
                            "size" => $_FILES['uploader']['size'],
                            "nparole" => $nparole,
                            "encoding" => "UTF-8"
                            );
                        $testi->insert($data);
                        $this->eseguiAnalisi($idTesti);
                    }else{
                        $this->view->messaggio = "L'archivio contiene già un file con lo stesso nome; se si desidera sostituirlo, "
                                . "occorre rimuovere il testo precedente.";
                    }
                }
            }else if($azione == "salva"){
                $newName = $this->_getParam('nomeTesto');
                $sigla = $this->_getParam('siglaTesto');
                $testo_mod = $this->_getParam('tx_rn');
                $new_n = 0;
                $idTesti = $this->_getParam('idTesti', 0);
                
                $testo = $testi->getTesto($idTesti);
                $oldName = $testo->nome;
                $tmp = explode(" ", $testo_mod);
                $tmp = array_filter($tmp);
                $nparole = count($tmp);
                $new_n = count($tmp);
                $data = array("nome" => $newName, "sigla" => $sigla, "nparole"=>$new_n);
                $where = $testi->getAdapter()->quoteInto("idTesti = ?", $idTesti);
                $testi->update($data, $where);

                // rinomina il file
                $oldName = $_SESSION["uploaddir"] . $idUtenti . "/" . $oldName;
                $newName = $_SESSION["uploaddir"] . $idUtenti . "/" . $newName;
                rename($oldName, $newName);
                $f= fopen($newName,"w");
                fwrite($f, $testo_mod);
                
                $this->eseguiAnalisi($idTesti);
                $this->view->attivaPulsanti = "vero";
//                $this->view->grid = $this->creaGrigliaTesti($idUtenti);
//                $this->view->grid->render();
            }
        }
        $this->view->grid = $this->creaGrigliaTesti($idUtenti, $idTesti);
        error_log("abilita: " . $this->view->attivaPulsanti);
    }

    public function ajaxAction(){
        if(!Application_Model_Accessi::controllaAjax()){
            echo "@sessionescaduta@";
            return;
        }
        $this->_helper->layout->disableLayout();
        $azione = $this->_getParam('azione', 0);
        $accessi = new Application_Model_Accessi();
        $idUtenti = $accessi->getIdUtente();
        $idTesti = $this->_getParam('idTesti', 0);
        $testi = new Application_Model_DbTable_Testi();
        $testo = $testi->getTesto($idTesti);
        

        if ($this->getRequest()->isPost()) {
            if($azione == "mostraTesto"){
                $this->mostraTesto($idUtenti, $testo->nome, $testo->encoding);
            }else if($azione == "mostraReport"){
                $this->mostraReport($testo->log);
            }else if($azione == "cambiaEncoding"){

                $encoding = $this->_getParam('encoding', '');
                error_log("CODIFICA* " . $encoding);
//                $data = array("encoding" => $encoding);
//                $where = $testi->getAdapter()->quoteInto("idTesti = ?", $idTesti);
//                $testi->update($data, $where);
                $testo =$testi->getTesto($idTesti);
                $this->mostraTesto($idUtenti, $testo->nome, $encoding);
                
                
            }else if($azione == "salvaUTF"){
                $encoding = $this->_getParam('encoding', '');
//                $data = array("encoding" => $encoding);
//                $where = $testi->getAdapter()->quoteInto("idTesti = ?", $idTesti);
//                $testi->update($data, $where);
                $testo =$testi->getTesto($idTesti);
//                $this->mostraTesto($idUtenti, $testo->nome, $testo->encoding);
                $name=$testo->nome;

                $fileName = $_SESSION["uploaddir"] . $idUtenti . "/" . $name;
                $contenuto = file_get_contents($fileName);
                $contenuto = iconv($encoding, 'UTF-8', $contenuto);

                $f= fopen($fileName,"w");
                fwrite($f, $contenuto);
                $this->eseguiAnalisi($idTesti);


                
            }else if($azione == "eliminaTesto"){
                $fileName = $_SESSION["uploaddir"] . $idUtenti . "/"
                        . $testi->getTesto($idTesti)->nome;
                unlink ($fileName);
                $this->cancellaAnalisi ($idTesti);
                $where = $testi->getAdapter()->quoteInto('idtesti = ?', $idTesti);
                $testi->delete($where);
                
                $this->view->grid = $this->creaGrigliaTesti($idUtenti);
                $this->view->grid->render();
            }else if($azione == "mostraProprieta"){
                echo "<table>";
                echo "<tr><td align=\"left\">Nome del testo:</td><td align=\"left\"><b>" . $testo->sigla . "</b></td></tr>";
                echo "<tr><td align=\"left\">Numero di parole:</td><td align=\"left\"><b>" . $testo->nparole . "</b></td></tr>";
                echo "<tr><td align=\"left\">Nome del file:</td><td align=\"left\"><b>" . $testo->nome . "</b></td></tr>";
                echo "<tr><td align=\"left\">Dimensioni del file:</td><td align=\"left\"><b>" . $testo->size . "K</b></td></tr>";
                echo "<tr><td align=\"left\">Codifica dei caratteri:</td><td align=\"left\"><b>" . $testo->encoding . "</b></td></tr>";
                echo "</table>";

            }else if($azione == "eliminaTutto"){
                $lista = $testi->getTesti($idUtenti);
                $errore = "";
                foreach($lista as $testo){
                    $fileName = $_SESSION["uploaddir"] . $idUtenti . "/"
                        . $testo->nome;
                    unlink ($fileName);
                    $this->cancellaAnalisi ($testo->idtesti);
                    $where = $testi->getAdapter()->quoteInto('idtesti = ?', $testo->idtesti);
                    $testi->delete($where);
                }
                if(!empty($errore)){
                    echo "@@@@@@@@@@";
                }
                $this->view->grid = $this->creaGrigliaTesti($idUtenti);
                $this->view->grid->render();

            }else if($azione == "apriTesto"){
                 $this->mostraTesto($idUtenti, $testo->nome, $testo->encoding);
            }else if($azione == "modifica"){
                echo $testo->nome;
                echo "@@@@@@@@@@";
                echo $testo->sigla;
                error_log("cosa succedeeeeeeeeeeee?   " . $testo->nome);
            }else if ($azione == "cambiaImpostazioni"){
                if ($this->_getParam ('monosillabi')=='true'){
                    $monosillabi = 1;
                } else {
                    $monosillabi = 0;
                } 
                if ($this->_getParam ('sinalefe')=='true'){
                    $sinalefe = 1;
                } else {
                    $sinalefe = 0;
                }
                if ($testi->getCambiaImpostazioni($idTesti, $monosillabi, $sinalefe)){
                    $data = array("monosillabi" => $monosillabi, "sinalefe" => $sinalefe);
                    $where = $testi->getAdapter()->quoteInto("idTesti = ?", $idTesti);
                    $testi->update($data, $where);
                    $analisi = new Application_Model_DbTable_Analisi();
                    $where = $analisi->getAdapter()->quoteInto("idTesti = ?", $idTesti);
                    $analisi->delete($where);
                    $this->analisi($idTesti, $monosillabi, $sinalefe);
                    if ($azione == "mostraMonosillabi"){
                    $this->mostraMonosillabi($idTesti, $idUtenti, $monosillabi);
                    }
                }
            } else if ($azione== "salva"){
                $newName = $this->_getParam('nomeTesto');
                error_log("nuovo nome:" . $newName);
                $sigla = $this->_getParam('siglaTesto');
                error_log($newName . "  " . $sigla . "  " . $idTesti);
                // si controlla che il nome del file non sia già in uso;
                if($testi->isFileExcluding($newName, $idUtenti, $idTesti)){
                    echo "file";
                }
                
                // si controlla che la sigla del testo non sia già in uso;
                if($testi->isSiglaExcluding($sigla, $idUtenti, $idTesti)){
                    echo "sigla";
                }
            
            }
        }
    }
    
    private function eseguiAnalisi($idTesti){

        require_once("hidatagrid/HIdatagrid.php");
        $this->cancellaAnalisi($idTesti);
        $testi = new Application_Model_DbTable_Testi(); //riempie la tabella testi: indica le preferenze con/senza monosillabi/sinalefe
        $this->view->messaggio = "";
        $this->view->log = "";
        $this->view->mostraLog = "none";
        $this->log_return=$this->analisi($idTesti, true, true); //funzione analisi che di fatto analizza il testo e lo prepara per elaborazione
        $this->risolto_ritmo=$testi->isRisoltoRitmo($idTesti);
           if ($this->log_return=="null"){
                $this->view->mostraLog = "block";
                $this->view->log = "<p align='justify'><img src='" . $this->view->baseUrl() ."/img/tutto_ok_verde.png' align='bottom'/> TUTTO A POSTO! È POSSIBILE PROCEDERE CON L'ANALISI </p>";
            } else if($this->risolto_ritmo==true){
                $this->view->mostraLog = "block";
                $this->view->log = "<p align='justify'><img src='". $this->view->baseUrl() ."/img/tutto_ok_giallo.png' align='bottom'/> TUTTE LE PAROLE IN CLAUSOLA SONO STATE ACCENTATE: È POSSIBILE PROCEDERE CON L'ANALISI RITMICA
                    SENZA ULTERIORI CONTROLLI. <BR/><BR/></p>
                    <p align='justify'><img src='". $this->view->baseUrl() ."/img/warning_viola_mini.png' align='bottom'/> SE INVECE SI INTENDE UTILIZZARE ANCHE L'ANALISI PROSODICA, È BENE CONTROLLARE
                    IL REPORT PER CONTROLLARE LE SCELTE DI SCANSIONE OPERATE DAL PROGRAMMA. </p>";
            } else {
                $this->view->mostraLog = "block";
                $this->view->log = "<p ><font class: 'error'><b> ATTENZIONE: CONTROLLARE IL REPORT! <br/></b></font></p>
                    <p align='justify'>Consultando il report l'utente può capire in quali casi il programma
                    non è stato in grado di risolvere una clausola dal punto di vista prosodico (più spesso) o ritmico (raramente) e richiede
                    pertanto un intervento di marcatura.<br/> I casi più comuni riguardano l'incapacità di distinguere vocali brevi e vocali lunghe
                    in parole che di fatto ammettono entrambe le forme (per esempio rosă e rosā).<br/> Per risolvere le ambiguità è necessario
                    ricorrere ad una marcatura manuale delle parole interessate dall'avvertimento di Errore ed eventualmente Avviso elencate
                    nel report.<br/> La marca esplicativa dovrà:<br/><ol><li> seguire immediatamente (senza spazi) la parola incerta</li>
                    <li> essere delimitata dai segni <bold>(==</bold> e <bold>)</bold></li>
                    <li> utilizzare le convenzioni <bold>+</bold> (sillaba lunga) e <bold>-</bold> (sillaba breve)</li></ol></p>
                    <p align='justify'><img src='" .$this->view->baseUrl()."/img/error_mini.png' align='bottom'/><b>ERRORE</b> è l'unico caso dove la parola,
                    non trovata nel vocabolario e non risolta dal programma, potrebbe far escludere la clausola dall'analisi, sia ritmica che metrica.
                    Per evitare che la clausola sia scartata dall'elaborazione delle tabelle statistiche è necessario che l'utente intervenga apponendo
                    una marca esplicativa della sequenza prosodica immediatamente dopo la parola che ha prodotto il problema. </p>
                    <p align='justify'><img src='" .$this->view->baseUrl()."/img/warning_viola_mini.png' align='bottom'/>Questo tipo di <b>AVVISO</b> notifica che la clausola,
                    anche se risolta dal programma con un criterio casuale, presenta una parola con più possibilità di scansione quantitativa.</p>
                    <p align='justify'><img src='" .$this->view->baseUrl()."/img/warning_mini.png' align='bottom'/>Quest'altro <b>AVVISO</b> segnala invece la
                    presenza di una parola con quantità ambigua ma risolta con la soluzione statisticamente più probabile (esempio: mănĭbŭs, nome comune; solamente qualora il testo trattasse davvero degli dei Mani, l'utente dovrebbe intervenire
                    istruendo il programma a leggere la prosodia secondo la scansione del nome proprio: Mānĭbŭs).<br/><br/></p>
                    <p align='justify'>ESEMPIO DI RISOLUZIONE:<br/>
                    <img src='" .$this->view->baseUrl()."/img/error_mini.png' align='bottom'/><b>!!ERRORE!!</b> trovate più prosodie per la parola: VOLVERIT (volvĕrĭt, vŏlŭĕrĭt) in clausola: Horum orationibus, quae aperto scilicet
                    fiunt ostio, qui uoluerit se commendet </p>
                    <p> In testo è necessario aggiungere:  uoluerit(==----)</p>
                    <p align='justify'>Infine il report rende conto anche di tutte le clausole scartate perché considerate troppo brevi per essere incluse
                    significativamente nelle tabelle statistiche.</p>
                    <p><b>* NB.</b> prima di controllare il report, è necessario assicurarsi che la codifica di visualizzazione sia corretta!</p>";
            }
    }
    

    private function creaGrigliaTesti($idUtenti, $idTesti=0){
        // è diversa e più completa di quella che si trova in base.php
        require_once("hidatagrid/HIdatagrid.php");

        $testi = new Application_Model_DbTable_Testi();
        $lista = $testi->getTesti($idUtenti);
        $grid = new HIdatagrid(
            "testi",
            "Archivio testi",
            array("Nome file","Nome testo", "N° parole"),
            array(70,70,50),
            0,
            array ("dojo-help"=>'help_archivio')
           );
        $grid->setAction(array("onclick" => "abilitaTesto()"));
        $grid->setRowSelection(true);
        $grid->setHightLight(false);
        $grid->setSelectedId($idTesti);//evidenzia la riga e memorizza
        foreach($lista as $testo){
            $grid->appendRow($testo->idtesti, array(
                $testo->nome,
                $testo->sigla,
                $testo->nparole
                ),
                array("colsAlign" => array("left", "left", "center"))
            );
        }
        $grid->setWidth100(true);
        return $grid;
    }


    private function mostraTesto($idUtenti, $nome, $encoding){
        $fileName = $_SESSION["uploaddir"] . $idUtenti . "/" . $nome;
        $contenuto = file_get_contents($fileName);
error_log($encoding . " *CODiFICA");
        if($encoding == "UTF-8" && !mb_detect_encoding($contenuto)){
            // si tratta di testo nuovo (default encoding=UTF-8) e la codifica non viene riconosciuta
        }else if($encoding != "UTF-8"){
            $contenuto = iconv($encoding, 'UTF-8', $contenuto);
        }
//        $arrayEnc = array("ISO-8859-1", "UTF-8", "UTF-16", "macintosh");
//        $comboEncoding = "";
////        foreach ($arrayEnc as $value) {
////            $comboEncoding .= "<option value=\"" . $value . "\"";
////            if($encoding == $value){
////                $comboEncoding .= " selected=\"selected\"";
////            }
////            $comboEncoding .= ">" . $value . "</option>\n";
////        }
//        foreach ($arrayEnc as $value) {
//            $comboEncoding = $value;
//
//        }
        echo $contenuto . "@@@@@@@@@@" . $encoding;

    }

    private function mostraReport($log){
        if ($log=="null"){
            echo "Tutto a posto! è possibile procedere con l'analisi";
        } else {
            echo $log;
        }
    }


    //questa è la vera e propria funzione di elaborazione del testo: si occupa di tokenizzare la clausola e di
    //archiviarla nel db secondo la normalizzazione prosodica, ritmica, orlandi 

    private function analisi($idTesti, $monosillabi, $sinalefe){
        require_once 'Funzioni.php';
        $path = "http://cursusinclausula.uniud.it/public/img/";
        $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$testo->idutenti . "/" . $testo->nome;//percorso relativo del file
       
        $contenuto=file_get_contents($filename);//lettura del file

        if($testo->encoding != "UTF-8"){
            $contenuto = iconv($testo->encoding, 'UTF-8', $contenuto);
        }
        $log_return = "";
        $log = array();
        $nuova="";
        $array="";
        $accenti="";
        $quantita = "";
        $parole = explode(" ", $contenuto);
        $stringa="";
        foreach($parole as $parola){
            if (strlen($parola)===2 && endsWith ($parola, ".") && ctype_upper(substr($parola, 0, 1))){
                $parola = substr($parola, 0, 1) . "@";
            } else if  (strlen($parola)===3 && endsWith ($parola, ".")
                    && ctype_upper(substr($parola, 0, 1)) &&
                   (!Application_Model_Start::is_Vocale(substr($parola, 1, 1)) || !Application_Model_Start::is_Vocale(substr($parola, 0, 1)))){
                $parola = substr($parola, 0, 2) . "@";
            }
            $stringa.=$parola . " ";
        }
        $stringa = trim($stringa);
        $segmenti = preg_split("/[\.\?\!]+/", $stringa);
        $segmenti = array_filter($segmenti);

        $tmp= $segmenti;
        $segmenti = array();
        for ($i=0; $i<count($tmp); $i++){
           
            $tmp[$i]=str_replace("@",".", $tmp[$i]);//cerca @ sostituisci con .
            //controllo su caratteri >5
            //filtro per eliminare periodi troppo brevi
            if (strlen($tmp[$i])>5){
                $segmenti[] = $tmp[$i];
            }
           
        }
        
        $db = new mysqli($_SESSION["host"] ,
                        $_SESSION["user"] ,
                        $_SESSION["password"] ,
                        $_SESSION["database"]);//localhost,user,pw,db
        $db->set_charset("utf8");
        error_log ('Success... ' . $db->host_info . "\n");
        $datiDb = array("host" => "localhost","username" => $_SESSION["user"], "password" => $_SESSION["password"], "dbname" => $_SESSION["database"], "charset" => "utf8");
        $lemmiconnection = new Zend_Db_Adapter_Pdo_Mysql($datiDb);


        error_log ('Success... ' . $db->host_info . "\n");
        $vocabolario = new Application_Model_Vocabolario($db, $lemmiconnection);//localhost,user,pw,db
        

        

        $analisi = new Application_Model_DbTable_Analisi();//riempie tabella analisi
        $nfrasi = count($segmenti);
        $p_cons = 0;
        $p_quant = 0;
        $p_accent = 0;
        $p_trovate = 0;

        for ($i=0 ; $i<count($segmenti); $i++) {
//diventa oggetto segmento elaborato secondo la classe segmento : qui passo la connessione
            $segmento = new Application_Model_Segmento($segmenti[$i], $db, $vocabolario);
    //richiamo il risultato in oggetto di classe parola

            $array = $segmento->getParole();
            //$numero_parole += count($array);
               //echo "considero solo " . $numero_parole . "parole: <br/>";
            $ritmi = "";
            
            $p_cons += count($array);
            $sommaSill = 0;
            $catena ="";
            $marcate = "";
            $notazione="";
            $accenti = "";
            $orlandi="";
            $ictus_uno="";
            $ictus_due="";
            $calcoli="";
            $tipo = "";
            $n=count($array);
            $ultima = $array[$n-1]->getQuantitaVoc();
 
            foreach ($array as $value){
                
                    $catena .= $value->getValore() . " "; //parola
                    $accenti .= $value->getRitmica() . " ";// 1 p 2p pp ...
                    
                    //formare tipi clausole ritmiche
                     //in presenza di sinalefe salta la quantita dell'ultima!
                    $nuova = $value->getSillabe();
                    foreach ($nuova as $sillaba){
                        //echo $value->getValore(). " ". $value->getQuantita() . "<br/>";
                        // error_log($value->getValore(). " ". $sillaba->getQuantita() . "<br/>");
                        if ($sinalefe && $sillaba->getFenomeno() === "S"){//echo "si annulla il valore prosodico di " . $value->getValore() . "<br/>";
                        //error_log($sinalefe . "=sinalefe");

                        } else if ($sillaba->isUltima()){
                            $orlandi .= $sillaba->getQuantita() . "|" ;
                            $notazione .= $sillaba->getQuantita();
                        } else if ($sillaba->isAccento()){
                            $orlandi .= "A" . $sillaba->getQuantita(); //antepongo una "A" alla sillaba accentata
                            $notazione .= $sillaba->getQuantita();
                        } else {
                            $orlandi .= $sillaba->getQuantita();
                            $notazione .= $sillaba->getQuantita();
                        }
                    }
                    $numero_sillabe = count($nuova);
                    $sommaSill += $numero_sillabe;

                    if ($value->isProsodia() === true){
                        $p_quant ++;
                    }
                    if ($value->isAccentata() === true){
                        $p_accent ++;
                    }
                    if ($value->isTrovato() === true){
                        $p_trovate ++;
                    }
                    //cerco i log provenienti da Parola

                    


            $itemlog = $value->getLog();
            
            if (strpos($itemlog, $ultima)){
               $ultimaquantita = substr ($ultima, -1, 1);
               //conta le varianti in log!
               
               $temp = substr($itemlog, Application_Model_Start::inStringa($itemlog, "["));
               $contavarianti = substr_count($temp, "(");
               if ($contavarianti > 2){
                   
               } else {
                   $varianteinlog_01 = substr($temp, -3, 1);//la variabile $temp finisce sempre così: "*)]", quindi il terzultimo carattere è sempre l'ultima quantità
                   $var = explode(",",$temp);
                   error_log("var temporanea: " .$var[0]);
                   $varianteinlog_02 = substr ($var[0], -2, 1);
                   
                   error_log ("XXXX risultato per variantelog02: " . $varianteinlog_02 );
                   
               if ($ultimaquantita != $varianteinlog_01 || $ultimaquantita != $varianteinlog_02){
                   $itemlog="";
               }
                error_log($temp . "££" . $contavarianti. "##" .  "ultima della variante finale= " . $varianteinlog_01. " oppure ". $varianteinlog_02. "  ULTIMMMMMMMAAAAA  " . $ultima . "   @@@@   " );
                }
            }
            
            $porzione = $segmento->getPorzione(); // evidenziare in testo la parola porzione con $log ad un certo valore????
            $porzione = str_replace("<", "[", $porzione);
            $porzione = str_replace(">", "]", $porzione);
                if(!empty($itemlog) && !$value->isTaggata()){
                    
                    $log[] = "<p>" . $itemlog . " in clausola: " . $porzione . "</p>";
                }
            } //fine del ciclo che considera singole parole

          error_log($catena . " catena quantita " . $orlandi );//orlandi è clausola tipo A-+|-+-A+-
         // error_log("catena sillabica#" . $catena . "#");


          //con l'opzione escludi monosillabi attivata: non si riempiono i campi rit_standard e cursus con la parola "monosillabi!"
          if (!$monosillabi){
                    //error_log("monosillabi = " . $monosillabi);
                    if (Application_Model_Start::inStringa($accenti, "1")>-1){

                        $accenti = str_replace("1","m",$accenti);
                    }
            }
        



            $rit_pass = $this->standardizza($accenti);
           // error_log ("rit_pass==" . $rit_pass);
            $rit_standard = "";
            if ($rit_pass =="null"){
                $rit_pass = "null";
                $log[] = "clausola scartata: troppo breve!" . $catena;
                error_log("clausola scartata: troppo breve!" . $catena);
                $rit_standard = "null";
            } else if ($rit_pass == "???") {
                $rit_pass = "???";
                $log[] = "<p><img src='" .$path . "error_mini.png' align='bottom'/><b> !!ERRORE!!</b>: clausola non risolta ritmicamente: " . $catena . "</p>";
                error_log("clausola non risolta ritmicamente");
                $rit_standard = "da marcare";
            } else {
                if(Application_Model_Start::inStringa($rit_pass, "m")>-1){
                    $rit_standard = "monosillabi";
                } else {
                    $array = explode(" ", $rit_pass);
                    if ($array[0]==="2"){
                            $array[0]= "p";

                        } else {
                            $array[0]=substr($array[0], 1);
                       }
                       $rit_standard = $array[0] . " " .  $array[1];


                }

            }
               if (endsWith($rit_standard , "ppp")){
                   $rit_standard = str_replace("3ppp", "3pp", $rit_standard);
                   $rit_standard = str_replace("4ppp", "3pp", $rit_standard);
                   $rit_standard = str_replace("5ppp", "4pp", $rit_standard);
                   $rit_standard = str_replace("6ppp", "5pp", $rit_standard);
               }
            //    error_log ("rit_standard==" . $rit_standard);


           $orlandi = $this->orlandizza($orlandi); //passata clausola tipo A-+|-+-A+-
           error_log("dopo ORLANDIZZA: " . $orlandi);
           if ($orlandi != "null"){
                $calcoli = substr_replace($orlandi, "*", -1, 1); //sostituisco l'ultimo segno con asterisco
           } else if ($orlandi=="vuota"){
                $log[] = "<p>clausola scartata: troppo breve " . $catena . "</p>";
           } else {
               $log[] = "<p><img src='" . $path . "error_mini.png' align='bottom'/><b><font color:#FF0000> !!ERRORE!!</font></b>: clausola non risolta metricamente: " . $catena . "</p>";
               error_log("clausola non risolta metricamente");
           }
           $ultima= "";
           $penultima = "";
           if (Application_Model_Start::inStringa($calcoli, "|")-1){ //caso in cui non c'è divisione tra parole causa sinalefe
//               $ultima = $calcoli;
//               $penultima = "@@";
               // per evitare @@ e quindi la creazione di clausole inesistenti; conto le sillabe dell'ultima
               //parola e le incasello in ultima, in penultima restano le altre (ovvero mancherà sempre e solo la quantità finale dell'ultima sillaba)
               $n_sillabe =0;
               $n_pen = 0;
               if (strlen($rit_standard) === 4){
                   $n_sillabe = substr($rit_standard, 2, 1);
               }else if (strlen($rit_standard) === 5 || strlen($rit_standard)== 6){
                   $n_sillabe = substr($rit_standard, 3, 1);
               }
               if (is_numeric($n_sillabe)){
               		$ultima = substr($calcoli, -$n_sillabe);
               	} else {
               	error_log ("n_sillabe = " . $n_sillabe);
               	}
               error_log ("NUMERO SILLABE è dato dalla lunghezza di rit_standard " . $rit_standard . "cercando in posizione 3 o 2! = ". $n_sillabe . " quindi  in ultima vanno i seguenti: " . $ultima);
               $n_pen=strlen($calcoli)-strlen($ultima);
               $penultima = substr($calcoli, 0, $n_pen);
               error_log (" e questi in penultima: " . $penultima);
           }
           $array = explode("|", $calcoli);
           if (count($array)>1){
            $penultima = $array[0];
            $ultima = $array[1];
           }

            $ritmi = $this->getClausola($rit_pass, $accenti);
            //error_log("clausola" . $ritmi);
            



            if($rit_standard ==="5p" ||
               $rit_standard ==="1 4p" ||
               $rit_standard==="pp 2" ||
               $rit_standard==="p 3p"){

                    $tipo = "planus";
            } else if ($rit_standard ==="6pp" ||
               $rit_standard ==="1 5p" ||
               $rit_standard==="2 4pp" ||
               $rit_standard==="p 4pp" ||
               $rit_standard==="pp 3pp") {

                    $tipo= "tardus";
            } else if ($rit_standard ==="2 5p" ||
               $rit_standard ==="pp 4p" ||
               $rit_standard==="p 5p" ) {

                    $tipo= "velox";
           } else if ($rit_standard ==="p 4p" ||
               $rit_standard ==="pp 3p" ) {

                    $tipo= "trispondaicus";
           } else if ($rit_standard ==="p 3pp"  ) {

                    $tipo= "medius";
           } else if ($rit_standard ==="p 2"  ) {

                    $tipo= "dispondaicus";
           } else if ($rit_standard ==="p 5pp" ||
                       $rit_standard==="pp 4pp"){

                    $tipo= "dispondeus-dattylicus";
           } else {
               $tipo = "-";
           }

            $cursus = $this->getCursus($tipo, $rit_standard);

//            $cursus = $this->getCursus($ritmi);
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





             //error_log($ictus_uno ."ICTUS". $ictus_due);
             if ($rit_pass != "null"){

                 $data = array(
                "idtesti"=>$idTesti,
                "clausola"=>$catena,
                "quantita"=>$notazione, 
                "quant_orlandi"=>$orlandi, //visualizzato in tabella clausole generale
                "quant_calcoli"=>$calcoli,//visualizzato in tabella Orlandi
                "penultima"=>$penultima,
                "ultima"=>$ultima,
                "accenti"=>$accenti,
                "ritmi"=>$ritmi, //visualizzato in tabella clausole generale
                "rit_standard"=>$rit_standard,//visualizzato in tabella Ritmo
                "rit_pass"=>$rit_pass,
                "cursus"=>$cursus, //visualizzato in tabella Cursus
                "tipo"=>$tipo,
                "ictus_uno"=>$ictus_uno,
                "ictus_due"=>$ictus_due,
                "sillabe"=>$sommaSill
                     );
           $analisi->insert($data);

             }
        }//il segmento considerato comprende sempre almeno le ultime otto sillabe!
        $testi = new Application_Model_DbTable_Testi();
        
         $trovate = $p_trovate/$p_cons;
         $risolte_met = $p_quant/$p_cons;
         $risolte_rit =  $p_accent/$p_cons;





         if (count($log)>0){
             $item_log="";
             foreach ($log as $item){
                 $item_log .= $item;
                 
             }
            
         } else {
             $item_log = "null";
         }
         
         $where = $testi->getAdapter()->quoteInto("idtesti=?", $idTesti);
         $data = array(
                        "nfrasi" => $nfrasi,
                        "p_cons" => $p_cons,
                        "p_quant" => $p_quant,
                        "p_accent" => $p_accent,
                        "trovate" => $trovate,
                        "risolte_met" => $risolte_met,
                        "risolte_rit" => $risolte_rit,
                        "log" => $item_log
                        );
         $testi->update($data, $where);
         $vocabolario->close();
         $db->close();
         $log_return = $item_log;
         return $log_return;
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

        if ($clausola === "2 1" || $clausola === "1 1" || $clausola === "1 2" || $clausola === "2 m" || $clausola === "m 2"){
                return "null"; 

        }

        $clausola = " " . $clausola . " |";//identificare con certezza la fine diventa " m m m 4p |"


          if ($clausola === " m 2 2 |" || $clausola===" m 2 |" || $clausola === " 2 m |" || $clausola=== " m m |"){
              //$clausola = " mono ";
              return $clausola;
        }
        error_log ("KKKK" . $clausola);
        $clausola = str_replace(" m 2 2 |", " p 2 |", $clausola); //contiamo dispondaici ma perdiamo varianti velox e trispondiacus!!

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

            if (count($array)<2){
                return "null";
            }
            $n = count($array) - 1; //numero parole
            $errore = false;
            $clausola = "";
            $accenti = 0;
            for($i = $n; $i >= 0; $i --){
                    if($array[$i] === "1"){
                            if($i === $n){
                                    // se il monosillabo è finale, è enclitico (si attacca alla parola precedente, se c'è); poi si esce in ogni caso;
                                    if($i > 0){
                                            $clausola = $this->somma($array[$i - 1], 1) . " " . $clausola;
                                            $i = $i - 1;
                                    }else{
                                            // non c'è parola precedente
                                            return "null";
                                    }
                            }else{
                                    // se il monosillabo non è finale, è proclitico (si attacca alla parola che segue); poi si esce;
                                    $n1 = strpos($clausola, " ");
                                    $clausola = substr($clausola, $n1);	// toglie la parola seguente
                                    $clausola = $this->somma($array[$i + 1], -1) . " " . $clausola;
                            }
                            // si attaca la porzione precedente della clausola, se c'è
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

                if(count($array) === 2){
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
//            return "null"; //scarta a priori tutti i casi con asterisco...anche parole dove conta solo la quantità nota...
//        } else if (strlen($clausola)<5){
        if (strlen($clausola)<5){
            $clausola = "vuota";
        }
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
                    error_log("ultima pos: " . $clausola);
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
    $result="";
     
        $array = explode(" ", $rit_standard);
        $conta = 0;
        foreach ($array as $value){
        	$stringa = substr($value, 0, 1);
        	if (is_numeric ($stringa)) $conta += $stringa; //numero di sillabe da contare
    }
   

       $array = explode (" ", $accenti);
               $ct = 0;
                for ($i=count($array)-1; $i>=0; $i--){ //dall'ultima posizione
                    $result = $array[$i] . " " . $result;
                    $stringa = substr($array[$i], 0, 1);
        			if (is_numeric ($stringa)) $ct += $stringa;
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
//private function getCursus ($rit_pass){
//    $result = "";
//
//    $ritmi = str_replace("2", "2p", $rit_pass);
//    $array = explode(" ", $rit_pass);
//    for ($i=0; $i<count($array); $i++){
//        if ($i===0){
//            if ($array[$i]==="p"){
//                $array[$i]= " xo";
//            } else if ($array[$i]==="pp"){
//                $array[$i]= " xoo";
//            } // non cambio il monosillabo, così resta 1
//
//        } else {
//            if ($array[$i]==="1"){
//                $array[$i]="o";
//            } else if ($array[$i]==="2"){
//                $array[$i]="xo";
//            } else if ($array[$i]==="3p"){
//                $array[$i]="oxo";
//            } else if ($array[$i]==="3pp"){
//                $array[$i]="xoo";
//            } else if ($array[$i]==="4p"){
//                $array[$i]="ooxo";
//            } else if ($array[$i]==="4pp"){
//                $array[$i]="oxoo";
//            } else if ($array[$i]==="5p"){
//                $array[$i]="oooxo";
//            } else if ($array[$i]==="5pp"){
//                $array[$i]="ooxoo";
//            } else if ($array[$i]==="6p"){
//                $array[$i]="ooooxo";
//            } else if ($array[$i]==="6pp"){
//                $array[$i]="oooxoo";
//           } else if ($array[$i]==="7p"){
//                $array[$i]="oooooòo";
//            } else if ($array[$i]==="7pp"){
//                $array[$i]="ooooòoo";
//            }  else {
//                $array [$i]= ">8p";
//            }
//        }
//        $result .= $array[$i];
//    }
//    return $result;
//}


private function getCursus ($tipo, $rit_standard){
    $result= "";



    if ($tipo==="planus"){
        $result = "xooxo";
    } else if ($tipo==="velox"){
        $result = "xooooxo";
    } else if ($tipo==="tardus"){
        $result = "xooxoo";
    } else if ($tipo==="medius"){
        $result = "xoxoo";
    } else if ($tipo==="trispondaicus"){
        $result = "xoooxo";
    } else if ($tipo==="dispondaicus"){
        $result = "xoxo";
    } else if  ($tipo==="dispondeus-dattylicus"){
        $result = "xoooxoo";
    } else {
        $array = explode(" ", $rit_standard);
        for ($i=0; $i<count($array); $i++){
            if ($i===0){
                if ($array[$i]==="p"){
                    $array[$i]= " xo";
                } else if ($array[$i]==="pp"){
                    $array[$i]= " xoo";
                }
            } else {
                if ($array[$i]==="5p"){
                    $array[$i]="oooxo";
                } else if ($array[$i]==="5pp"){
                    $array[$i]="ooxoo";
                } else if ($array[$i]==="6p"){
                    $array[$i]="ooooxo";
                } else if ($array[$i]==="6pp"){
                    $array[$i]="oooxoo";
               } else if ($array[$i]==="7p"){
                    $array[$i]="oooooòo";
                } else if ($array[$i]==="7pp"){
                    $array[$i]="ooooòoo";
                } else if ($array[$i]==="8p"){
                $array[$i]="ooooooòo";
                } else if ($array[$i]==="8pp"){
                    $array[$i]="oooooòoo";
                }  else {
                    $array [$i]= ">9p";
                }
            }
    $result .=$array[$i];
        }
       
    }
    return $result;
}

//private function mostraMonosillabi ($idTesti, $idUtenti, $monosillabi){
//        $testi=new Application_Model_DbTable_Testi;
//        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
//        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
//        $contenuto=file_get_contents($filename);//lettura del file
//
//        $data=new Application_Model_DbTable_Analisi;
//
//
//        $lista=$data->getAnalisi($idTesti, 0);
//        $titoli = array("Clausola","Sill.", "Metrica","Ritmica", "Tipo");
//        require_once("hidatagrid/HIdatagrid.php");
//        $grid = new HIdatagrid(
//            "mostraMonosillabi",
//            "Clausole con monosillabi",
//            $titoli,
//            array(50,10,30,30, 30),
//            0
//            );
//
//        $grid->setNoWrap(true);
//
//
////        $rit_pass = $this->standardizza($accenti);
////           // error_log ("rit_pass==" . $rit_pass);
////            $rit_standard = "";
////            if ($rit_pass =="null"){
////                $rit_pass = "null";
////                $log[] = "clausola scartata: troppo breve!" . $catena;
////                error_log("clausola scartata: troppo breve!" . $catena);
////                $rit_standard = "null";
////            }
//
//
//
//        foreach($lista as $analisi){
//            if (($monosillabi = Application_Model_Start::inStringa($analisi->accenti, "m")) >-1){
//
//                error_log("MMMMMMMMMM" . $analisi->accenti);
//
//
//
//
//
//                $grid->appendRow($analisi->idanalisi, array(
//                $analisi->clausola,
//                $analisi->sillabe,
//                $analisi->quant_orlandi,
//                "",//$analisi->accenti,
//                ""//$analisi->tipo
//                ),
//                array("colsAlign" => array("left", "center", "left", "left", "left", "left"))
//                );
//            } else {
//
//            }
//
//
//        }
//        $grid->setWidth100(true);
//        return $grid;
//
//}



private function cancellaAnalisi($idTesti){
       $analisi = new Application_Model_DbTable_Analisi();
       $where = $analisi->getAdapter()->quoteInto('idtesti = ?', $idTesti);
                $analisi->delete($where);

   }

//private function mostraLog (){
//    $parola = new Application_Model_Parola($valore, $db, $vocabolario);
//    $report = $parola->getLog();
//}

}

