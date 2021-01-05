<?php

class AnalisiController extends Zend_Controller_Action
{

public function init()
    {
        /* Initialize action controller here */
    ini_set('session.gc_maxlifetime', '7800');
    ini_set('display_errors', 1);
    }

    public function pdfAction(){
        set_time_limit (240);
        $this->_helper->layout->disableLayout();
        $idTesti=$this->_getParam('idTesti', 0);
        $accessi = new Application_Model_Accessi();
        $idUtenti = $accessi->getIdUtente();
        $tabella = $this->_getParam('tabella',0);
        $ordineScelto=$this->_getParam('ordine',0);
        if ($tabella=="analisiClausole"){
            $this->view->nomefile="analisiClausole";
            $this->view->grid=$this->analisiClausole($idTesti, $idUtenti, $ordineScelto, false, true);
        } else if ($tabella=="analisiOrlandiAltre"){
                $this->view->nomefile="analisiOrlandiAltre";
            $this->view->grid=$this->analisiOrlandi($idTesti, $idUtenti, $ordineScelto, true, true);
        } else if ($tabella=="analisiCursusAltre"){
                $this->view->nomefile="analisiCursusAltre";
            $this->view->grid=$this->analisiCursus($idTesti, $idUtenti, $ordineScelto, true, true);
        } else if ($tabella=="analisiRitmoAltre"){
                $this->view->nomefile="analisiRitmoAltre";
            $this->view->grid=$this->analisiRitmo($idTesti, $idUtenti, $ordineScelto, true, true);
        } else if ($tabella=="analisiOrlandi"){
                $this->view->nomefile="analisiOrlandi";
            $this->view->grid=$this->analisiOrlandi($idTesti, $idUtenti, $ordineScelto, false, true);
        } else if ($tabella=="analisiRitmo"){
            $this->view->nomefile="analisiRitmo";
            $this->view->grid=$this->analisiRitmo($idTesti, $idUtenti, $ordineScelto, false, true);
        } else if ($tabella=="analisiCursus"){
            $this->view->nomefile="analisiCursus";
            $this->view->grid=$this->analisiCursus($idTesti, $idUtenti, $ordineScelto, false, true);
        }
    }// action body


    public function analisiAction(){
        Application_Model_Accessi::controlla();
        Zend_Session::start();
        $testi = new Application_Model_DbTable_Testi();
        $accessi = new Application_Model_Accessi();
//        if(!$accessi->hasIdentity()){
//            $this->_forward('index','index');
//            return;
//        }
        $idUtenti = $accessi->getIdUtente();
        $this->view->messaggio = "";
        $this->view->grid = $this->creaGrigliaTesti($idUtenti);
    }// action body

    public function ajaxAction(){
        if(!Application_Model_Accessi::controllaAjax()){
            echo "@sessionescaduta@";
            return;
        }
        Zend_Session::start();
       $this->_helper->layout->disableLayout();
       $azione=$this->_getParam('azione',0);
       $ordineScelto=$this->_getParam('ordine',0);
       $accessi=new Application_Model_Accessi();
       $idUtenti=$accessi->getIdUtente();
       $idTesti=$this->_getParam('idTesti', 0);
       $testi = new Application_Model_DbTable_Testi();
       $testo =$testi->getTesto($idTesti);
       $schema = $this->_getParam('schema',"");
       error_log("QUI è già sbagliato: " . $schema);
       $campo = $this->_getParam('campo',"");
       $monosillabi = $testi->isMonosillabi($idTesti);
       //$nome = $testi->getTesto($idTesti)->nome;
       if ($this->getRequest()->isPost()){
           if($azione==="mostraTitolo"){
               echo strtoupper($testo->sigla);
           }
           else if ($azione==="mostraDati"){
                echo $this->parser($idTesti, $idUtenti);
           }
           else if ($azione==="apriTesto"){
               echo $this->apriTesto($idTesti, $idUtenti);
           }
           else if($azione==="confronto"){
               echo "scegli un altro testo da analizzare";
           }else if($azione==="analisiClausole"){
               $grid=$this->analisiClausole($idTesti, $idUtenti, $ordineScelto, $monosillabi, false);
               $grid->render();
           }else if($azione==="mostraMonosillabi"){
               $grid=$this->mostraMonosillabi($idTesti, $idUtenti, $ordineScelto);
               $grid->render();
           }else if($azione ==="analisiAili"){
               $this->analisiAili($idTesti, $idUtenti, $ordineScelto);
           }else if($azione ==="analisiOrlandi"){
               $grid=$this->analisiOrlandi($idTesti, $idUtenti, $ordineScelto, false);
               $grid->render();
           }else if($azione ==="analisiRitmo"){
               $grid=$this->analisiRitmo($idTesti, $idUtenti, $ordineScelto, false);
               $grid->render();
           }else if($azione ==="analisiCursus"){
               $grid=$this->analisiCursus($idTesti, $idUtenti, $ordineScelto, false);
               $grid->render();
           }else if ($azione=="mostraNascostiOrlandi"){
               $grid=$this->analisiOrlandi($idTesti, $idUtenti, $ordineScelto, true);
               $grid->render();
           }else if($azione==="mostraNascostiRit"){
                $grid=$this->analisiRitmo($idTesti, $idUtenti, $ordineScelto, true);
               $grid->render();
           }else if($azione==="mostraNascostiCur"){
                $grid=$this->analisiCursus($idTesti, $idUtenti, $ordineScelto, true);
               $grid->render();
           }else if ($azione=="mostraClausole"){
               $grid=$this->mostraClausole($idTesti, $schema, $campo);
               return $grid;
           }
        }
    }


    private function apriTesto($idTesti, $idUtenti){
        require_once 'Funzioni.php';
        Zend_Session::start();
        $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file
        if($testo->encoding != "UTF-8"){
            $contenuto = iconv($testo->encoding, 'UTF-8', $contenuto);
        }
        $contenuto = str_replace('<', '&lt;', $contenuto);
        $contenuto = str_replace('>', '&gt;', $contenuto);
        $contenuto = preg_replace('/\r\n/', '<br>', $contenuto);
        $contenuto = preg_replace('/\r|\n/', '<br>', $contenuto);
        return $contenuto;
    }

    private function creaGrigliaTesti($idUtenti){
        // è diversa e più completa di quella che si trova in base.php
        Zend_Session::start();
        require_once("hidatagrid/HIdatagrid.php");
        $testi = new Application_Model_DbTable_Testi();
        $lista = $testi->getTesti($idUtenti);

        $grid = new HIdatagrid(
            "testi",
            "Archivio testi",
            array("Nome file","Nome testo", "N° parole"),
            array(70,70,50),
            0);
        $grid->setAction(array("onclick" => "abilitaPulsanti()"));
        $grid->setRowSelection(true);
        $grid->setHightLight(false);

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

//    private function analisiAili($idTesti, $idUtenti, $ordineScelto){
//        require_once 'Funzioni.php';
//
////calcolare/eseguire tutte le tabelle!!
//
//        $testi=new Application_Model_DbTable_Testi;
//        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
//        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
//        $contenuto=file_get_contents($filename);//lettura del file
//
//        $dati=new Application_Model_DbTable_Analisi;
//        $analisi=$dati->getAnalisi($idTesti);
//
//       require_once("hidatagrid/HIdatagrid.php");
//
//        $grid = new HIdatagrid(
//            "analisiAili",
//            "",
//            array("Clausole","o", "e", "χ", "%"),
//            array(50,40,40,40,40),
//            0);
//
//
//            $grid->appendRow(0, array(
//                "clausole",
//                "valore o",
//                "valore e",
//                "indice Pearson",
//                "percentuale"
//                ),
//                array("colsAlign" => array("left", "center", "center", "center", "center"))
//            );
//
//        $grid->setWidth100(true);
//        $grid->render();
//   }

   private function analisiOrlandi($idTesti, $idUtenti, $ordineScelto, $soloAltre, $isPdf=false){
       require_once 'Funzioni.php';
       Zend_Session::start();
       $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        error_log("*" . $_SESSION["uploaddir"]);
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file

        $data=new Application_Model_DbTable_Analisi;
        $array_ordine = Application_Model_Start::getArrayOrdine("AXAXXX", $ordineScelto);

        $quantita = $data->getQuantOrl($idTesti); //+-|+-*
        //contare le clausole con monosillabi da scartare!!

        $tot = $data->getnClausole($idTesti);
//        if ($tot_mono!= 0){
//            $tot = $tot-$tot_mono;
//        }
        $pstmt = $data->getPSTMTosservata($idTesti, 'quant_calcoli');
        $pstmt_e = $data->getPSTMTosservata($idTesti, 'penultima');//+- oppure @ se non c'è divisione causa sinalefe
        $pstmt_ex = $data->getPSTMTosservata($idTesti, 'ultima');//+-*
        $conta_tipi = 0;
        $tot_chi = 0;
        $tot_o= 0;
        $tot_e= 0;
        $somma_attese= 0;
        $somma_chi=0;


        $titoli = array("Clausole"," ", "o", "e", "x<sup>2</sup>", "%");

        if($isPdf){
            require_once("hidatagrid/HIdatagridPDF.php");
            $grid = new HIdatagridPDF(
            "Analisi Orlandi",
            $titoli,
            array(140,120,80,80,80,80)
            );
        } else {
            require_once("hidatagrid/HIdatagrid.php");
            $buttons =array();
            if ($soloAltre) {
                $nome = 'analisiOrlandi';
                $buttons[]=array('label'=>"mostra standard",'action'=>"analisiOrlandi('2D')");
            } else {
                $nome = 'analisiOrlandi';
                $buttons[]=array('label'=>"mostra altre",'action'=>"mostraNascostiOrlandi('2D')");
            }
            $opzioni = array("colsIndex"=>$array_ordine,
                    "dojo-help"=>'help_orlandi',
                    "buttons"=> $buttons);

            $grid = new HIdatagrid(
            $nome,
            "",
            $titoli,
            array(150,130,130,130,130,110),
            150,
            $opzioni
            );
            $grid->setPDF(true);
            $grid->setRowSelection(true);
            $grid->setAction(array("onclick" => "mostraClausoleOrlandi()"));
        }

        $grid->setNoWrap(true);

        $altre = array("canone"=>'',"osservata"=>0, "attesa"=> 0, "chi"=> 0, "percentuale"=>0) ;
        //creazioni array per ordinamento in base "osservate"


       $array_orlandi = array();//array multidimensionale


       foreach($quantita as $analisi){ //per ogni record di tabella $analisi
            $record = array();
            //...restituisce il tipo quant_calcoli "+-+|+*"
            $query = $pstmt->execute(array($idTesti, $analisi->quant_calcoli));
            $query = $pstmt->fetch();//è il record con distinct
            $osservata = $query['conta']; //è un intero
            $record['clausola'] = $analisi->quant_calcoli; //+-|+-*

            $query_e = $pstmt->execute(array($idTesti, $analisi->penultima));
            $query_e = $pstmt->fetch();//è il record con distinct
            $record['penultima'] = $analisi->penultima;

            $query_ex = $pstmt->execute(array($idTesti, $analisi->ultima));
            $query_ex = $pstmt->fetch();//è il record con distinct
            $record['ultima'] = $analisi->ultima;
                error_log ("ULTIMA!!! = " . $record['ultima']);
                error_log ("PENULTIMA!!! = " . $record['penultima']);
                

                $record['clausolatab'] = "";
                if ($record['clausola']=== "+-+|+*"){
                    $record['clausolatab'] .= "(cretico-spondeo I)";
                } else if ($record['clausola']=== "+-|++*") {
                    $record['clausolatab'] .= "(cretico-spondeo II )";
                } else if ($record['clausola']=== "+-+|+-*") {
                    $record['clausolatab'] .= "(dicretico I)";
                } else if ($record['clausola']==="+-|++-*") {
                    $record['clausolatab'] .= "(dicretico II)";
                } else if ($record['clausola']==="+-+|--*") {
                    $record['clausolatab'] .= "(cretico-tribraco I)";
                } else if ($record['clausola']==="+-|+--*") {
                    $record['clausolatab'] .= "(cretico-tribraco II)";
                } else if ($record['clausola']=== "+-|+*") {
                    $record['clausolatab'] .= "(dicoreo)";
                } else if ($record['clausola']=== "+-+|+-+*") {
                    $record['clausolatab'] .= "(cretico-dicoreo)";
                } else if ($record['clausola']==="+-|--+*") {
                    $record['clausolatab'] .= "(peoneI-spondeo I)";
                } else if ($record['clausola']==="+--|-+*") {
                    $record['clausolatab'] .= "(peoneI-spondeo II)";
                } else if ($record['clausola']==="+-|+-*") {
                    $record['clausolatab'] .= "(trocheo-cretico I)";
                } else if ($record['clausola']==="+-+|-*") {
                    $record['clausolatab'] .= "(trocheo-cretico II)";
                } else if ($record['clausola']==="+-|--+-*") {
                    $record['clausolatab'] .= "(peoneI-cretico I)";
                } else if ($record['clausola']==="+--|-+-*") {
                    $record['clausolatab'] .= "(peoneI-cretico II)";
                } else if ($record['clausola']==="+-|----*") {
                    $record['clausolatab'] .= "(peoneI-tribraco)";
                } else if ($record['clausola']==="+--|---*") {
                    $record['clausolatab'] .= "(peoneI-tribraco)";
                } else {
                    $record['clausolatab'] .= "( - )";
                }
                $record['osservata'] = $osservata; //numero di occorrenze
                if ($record['osservata']){
                $tot_o += $record['osservata'];
                }
                error_log ("O=" . $tot_o);


                $array_orlandi[] = $record; //+-, n
            }
             $indice = array();
             $record = array();
             $colonna = substr($ordineScelto, 0, 1);
             $verso = substr($ordineScelto, 1);

             $gridAltre = array();

             foreach ($array_orlandi as $value){
             //rinominiamo clausola _calc per l'analisi, ma lasciamo visualizzata la clausola originale
               $value['clausola_calc'] = $value['clausola'];


            //calcola frequenza attesa
                 if (Application_Model_Start::inStringa($value['clausola_calc'], "|")=== -1) {
                     $value['clausola_calc']= $value['penultima']. "|" . $value['ultima'];

// $value['clausola']= "@|". $value['clausola'];

                 }//trucco per fare in modo che vengano contate clausole che causa sinalefe non presentano divisione!!
                 error_log ("clausola: " . $value['clausola_calc']);
                $array = explode ("|", $value['clausola_calc']);//+-
                foreach ($array as &$valore){
                    $valore = trim($valore);//+-
                }
                //conto valori penultima
                $query = $pstmt_e->execute(array($idTesti, $array[0]));
                $query = $pstmt_e->fetch();
                $n_penultima = $query['conta'];

           //conto valori ultima
                $query = $pstmt_ex->execute(array($idTesti, $array[1]));
                $query = $pstmt_ex->fetch();
                $n_ultima = $query['conta'];

              //  error_log($array[1]. "ultima = " . $n_ultima);

                $attesa = (($n_penultima*$n_ultima )/ $tot);
              //  error_log("ATTESA" . $array[0] . "= penultima:" . $n_penultima . "*" . $array[1] . "ultima = " . $n_ultima . "/ Totale= " . $tot_o);

                if ($attesa){
                    $tot_e += $attesa;
//                    $tot_e = 0;



                $chi = pow(($value['osservata']-$attesa), 2)/$attesa;
//                if ($chi){
//                    $tot_chi += $chi;
//                }
                $percentuale = ($value['osservata']/$tot)*100;

                }


                 if ($soloAltre) {
                     if ($attesa<5){
                        $conta_tipi ++;
                        $record[] = array("clausola"=>$value['clausola'], "clausolatab"=>$value['clausolatab'],
                           "osservata"=>$value['osservata'], "attesa"=>$attesa, "chi"=>$chi, "percentuale"=>$percentuale) ;
                       if ($colonna==0){
                           $indice[] = $value['clausola'];
                        } else if ($colonna==2){
                           $indice[] = $value['osservata'];
                        }
                   }
                } else {
                     if ($attesa<5){
//                           $attesa += $attesa; //somma di altre: mancano tutti casi non comparsi, ma esistenti per arrivare al totale
//                           $attesa = $tot_e+$attesa;
                           $altre["osservata"] += $value['osservata'];
//                           $altre["attesa"] += $attesa;
                           $altre["attesa"] = $tot_o - $somma_attese;
//                           bcpow(($value['osservata']-$attesa), 2, 4)/$attesa;
                           $altre["chi"] = pow(($altre["osservata"]-$altre["attesa"]), 2)/$altre["attesa"];
                           $altre["percentuale"] += $percentuale;
                     } else {
                        $conta_tipi ++;
                       $record[] = array("clausola"=>$value['clausola'], "clausolatab"=>$value['clausolatab'],
                           "osservata"=>$value['osservata'], "attesa"=>$attesa, "chi"=>$chi, "percentuale"=>$percentuale) ;
                       $somma_attese += $attesa;
                       $somma_chi += $chi;
                       error_log("ATTESA = " . $attesa);
                       error_log($somma_attese . " è la somma attese");

                       if ($colonna==0){
                           $indice[] = $value['clausola'];
                       } else if ($colonna==2){
                           $indice[] = $value['osservata'];
                       }
                   }

                 }

            }

            if ($verso=="A"){
                asort($indice);
            } else {
                arsort($indice);
            }
            $tot_e = round($tot_e + ($tot_o-$tot_e), 2);
            $tot_chi += $somma_chi +  $altre["chi"];
            error_log("nuovo chi = ". $tot_chi);

            foreach ($indice as $key=>$value){

                $grid->appendRow($record[$key]['clausola'], array(
                    $record[$key]['clausola'],
                    $record[$key]['clausolatab'],
                    $record[$key]['osservata'],
                    round ($record[$key]["attesa"], 2),
                    round ($record[$key]["chi"], 2),
                    round ($record[$key]["percentuale"],2)
                    ),
                    array("colsAlign" => array("left", "left","center", "center", "center", "center"))
                );
            }


            if (!$soloAltre){
                $grid->appendRow(-1, array(
                        "altre",
                        " ",
                        $altre["osservata"] ,
                        round ($altre["attesa"], 2) ,
                        round ($altre["chi"], 2) ,
                        round ($altre["percentuale"], 2)

                        ),
                array("colsAlign" => array("left", "left","center", "center", "center", "center"))
                     );



                $grid->appendRow(0, array(
                       "<b>totali</b>",
                        " ",
                        " ",
                        " ",
                        " ",
                        " "
                ),
                array("disabled" => true,"colsAlign" => array("left", "left", "center", "center", "center", "center"))
                        );



                $grid->appendRow(0, array(

                ($conta_tipi) . " tipi + altre", //in metrica escludi i "null", quindi il totale deve essere uguale alle osservate: tot_o!!
                 " ",
                 $tot_o,
                 round($tot_e, 2), //
                 round ($tot_chi, 2),//
                "-"),
                array("disabled" => true,"colsAlign" => array("left", "left", "center", "center", "center", "center"))
                        );

            }
               $val_critico = new Application_Model_DbTable_Valorecritico();
               $cerca_critico = $val_critico->getValoreCritico($conta_tipi);

                //echo "<div> se " . (round($tot_chi, 2)) . " e > del valore critico di riferimento ". $cerca_critico . ", calcolato per ". $conta_tipi . " gradi liberta,
                //allora ce una possibilita di oltre il 95 che la ricerca delle clausole non sia casuale. </div>"; //rimando a tabella, o calcolo...??

            $grid->setWidth100(true);
            return $grid;
    }

    private function mostraClausole($idTesti, $schema, $campo){
    error_log("schema: " . $schema . " campo: " . $campo);
        $data=new Application_Model_DbTable_Analisi;

        $clausole = $data->getTab($idTesti, $schema, $campo);
        require_once("hidatagrid/HIdatagrid.php");
                    $grid = new HIdatagrid(
                    "mostraClausole",
                    $schema,
                    array(""),
                    array(600),
                    0
                    );
       foreach ($clausole as $analisi){

            $grid->appendRow(0, array(
                $analisi->clausola));
        }

        $grid->setNoWrap(true);
        $grid->setWidth100(true);
        $grid->render();
    }

   private function analisiRitmo($idTesti, $idUtenti, $ordineScelto, $soloAltre, $isPdf=false){
       require_once 'Funzioni.php';
       Zend_Session::start();
        $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file

        $data=new Application_Model_DbTable_Analisi;

        $array_ordine = Application_Model_Start::getArrayOrdine("AXAXXX", $ordineScelto);

        $ritmi = $data->getRitmo($idTesti);
        $tot = $data->getnClausole($idTesti);

        $pstmt = $data->getPSTMTosservata($idTesti, 'rit_standard');
        $pstmt_e = $data->getPSTMTattese($idTesti, 'rit_standard');
        $conta_tipi = 0;
        $tot_chi = 0;
        $tot_o= 0;
        $tot_e= 0;
        $somma_attese= 0;
        $somma_chi=0;

        $titoli = array("Clausole","","o", "e", "x<sup>2</sup>", "%");
        if($isPdf){
            require_once("hidatagrid/HIdatagridPDF.php");
            $grid = new HIdatagridPDF(
            "Analisi Ritmo",
            $titoli,
            array(140,120,80,80,80,80)
            );
        } else {
        require_once("hidatagrid/HIdatagrid.php");
        $buttons = array();
        if ($soloAltre){
            $nome = 'analisiRitmo';
            $buttons[] = array ('label'=>"mostra standard", 'action'=>"analisiRitmo('2D')");
        } else {
            $nome = 'analisiRitmo';
            $buttons[]=array('label'=>"mostra altre",'action'=>"mostraNascostiRit('2D')");
            }
        $opzioni = array("colsIndex"=>$array_ordine,
                    "dojo-help"=>'help_ritmo',
                    "buttons"=>$buttons);
        $grid = new HIdatagrid(
            $nome,
            "",
            $titoli,
            array(150,130,130,130,130,110),
            150,
            $opzioni
        );

        $grid->setPDF(true);
        $grid->setRowSelection(true);
        $grid->setAction(array("onclick" => "mostraClausoleRitmo()"));
        }
        $grid->setNoWrap(true);

        $altre = array("canone"=>"","osservata"=>0, "attesa"=> 0, "chi"=> 0, "percentuale"=>0) ;
         //creazioni array per ordinamento in base "osservate"


        $array_ritmi = array();//array multidimensionale

        foreach($ritmi as $analisi){
            error_log("##" . $analisi['rit_standard']);
            $query = $pstmt->execute(array($idTesti, $analisi->rit_standard));
            $query = $pstmt->fetch();//è il record

            $osservata = $query['conta'];
            $record=array();
            if ($osservata){
                $tot_o += $osservata;
            }


            $record['ritmotab']="";
            $record['rit_standard']=$analisi->rit_standard;

            if($record['rit_standard']==="5p" ||
               $record['rit_standard']==="1 4p" ||
               $record['rit_standard']==="pp 2" ||
               $record['rit_standard']==="p 3p"){

                $record['ritmotab'].= "planus";


            } else if ($record['rit_standard']==="6pp" ||
                    $record['rit_standard']==="1 5p" ||
                    $record['rit_standard']==="2 4pp" ||
                    $record['rit_standard']==="p 4pp"||
                    $record['rit_standard']==="pp 3pp"){

                $record['ritmotab'].= "tardus";

            } else if ($record['rit_standard']==="2 5p" ||
                    $record['rit_standard']==="pp 4p" ||
                    $record['rit_standard']==="p 5p" ){

                $record['ritmotab'].= "velox";

            } else if ($record['rit_standard']==="p 4p"
                    || $record['rit_standard']==="pp 3p"){

                $record['ritmotab'].= "trispondaicus";

            } else if ($record['rit_standard']==="p 3pp"){

                $record['ritmotab'].= "medius";

            } else if ($record['rit_standard']==="p 2"){

                $record['ritmotab'].= "dispondaicus";

            } else if ($record['rit_standard']==="p 5pp" ||
                    $record['rit_standard']==="pp 4pp"){

                $record['ritmotab'].= "dispondeus-dattylicus";

            } else {

                $record['ritmotab'].= "-";

            }

            $record['osservata']=$osservata;//numero di occorrenze
            error_log ("OSSERVATA" . $record['osservata']);
            $array_ritmi[]=$record;

        }

        $indice = array();
        $record = array();
        $colonna = substr($ordineScelto, 0, 1);
        $verso = substr($ordineScelto, 1);
        $gridAltre = array();
        $array_result = array();
        foreach ($array_ritmi as $value){
//             error_log ("R=" . $tot_o);
//            $ritmo= $analisi->rit_standard;
            $ritmo = $value['rit_standard'];
            $tipo = $value['ritmotab'];

            //calcolo attese
            $array = $ritmo;
            $array = explode (" ", $array);
            $query = $pstmt_e->execute(array($idTesti, $array[0]. " %" ));
            $query = $pstmt_e->fetch();
            $n_penultima = $query['conta'];
            $query = $pstmt_e->execute(array($idTesti, "% " . $array[1]));
            $query = $pstmt_e->fetch();
            $n_ultima = $query['conta'];

            $attesa = (($n_penultima* $n_ultima )/ $tot);

            if ($attesa){
                $tot_e += $attesa;
            }
            $chi = pow(($value['osservata']-$attesa), 2)/$attesa;
//            if ($chi){
//                $tot_chi += $chi;
//            }

             $array_result[] =   array ("rit_standard"=>$ritmo, "ritmotab"=>$tipo, "attesa" => round ($attesa, 2), "chi" => round($chi, 2));



            $percentuale = ($value['osservata']/$tot)*100;

            if($soloAltre){
                if ($attesa<5){
                       $conta_tipi ++;
                       $record[] = array("clausola"=>$value['rit_standard'],"ritmotab"=>$value['ritmotab'],
                           "osservata"=>$value['osservata'], "attesa"=>$attesa, "chi"=>$chi, "percentuale"=>$percentuale) ;
                       if ($colonna==0){
                           $indice[] = $value['rit_standard'];
                       } else if ($colonna==2){
                           $indice[] = $value['osservata'];
                       }
                }
            } else {
                if($attesa<5){
                    $altre["osservata"] += $value["osservata"];
//                    $altre["attesa"] += $attesa;
                    $altre["attesa"] = $tot_o - $somma_attese;
                     $altre["chi"] = pow(($altre["osservata"]-$altre["attesa"]), 2)/$altre["attesa"];
                    $altre["percentuale"] += $percentuale;
                } else {
                    $conta_tipi ++;
                           $record[] = array("clausola"=>$ritmo, "ritmotab"=>$value['ritmotab'],
                               "osservata"=>$value['osservata'], "attesa"=>$attesa, "chi"=>$chi, "percentuale"=>$percentuale) ;
                           $somma_attese += $attesa;
                           $somma_chi += $chi;
                           if ($colonna==0){
                               $indice[] = $ritmo;
                           } else if ($colonna==2){
                               $indice[] = $value['osservata'];
                           }
                 }
              }
            }

            $_SESSION['result'] = $array_result;





            if ($verso=="A"){
                asort($indice);
            } else {
                arsort($indice);
            }
            $tot_e = round($tot_e + ($tot_o-$tot_e), 2);
            $tot_chi += $somma_chi +  $altre["chi"];
            error_log("nuovo chi = ". $tot_chi);

            foreach ($indice as $key=>$value){
                        $grid->appendRow($record[$key]["clausola"], array(
                            $record[$key]["clausola"],
                            $record[$key]["ritmotab"],
                            $record[$key]["osservata"], //dovremmo mettere la tabella in ordine decrescente rispetto a $o
                            round ($record[$key]["attesa"], 2),
                            round ($record[$key]["chi"],2),
                            round ($record[$key]["percentuale"], 2)
                            ),
                            array("colsAlign" => array("left", "left", "center", "center", "center", "center"))
                        );
            }
            if (!$soloAltre){
                $grid->appendRow(-1, array(
                    "altre",
                    "tipo",
                    $altre["osservata"] ,
                    round ($altre["attesa"], 2) ,
                    round ($altre["chi"], 2) ,
                    round ($altre["percentuale"], 2)
                        ),
                array("colsAlign" => array("left", "left","center", "center", "center", "center"))
                     );


                $grid->appendRow(0, array(
                       "<b>totali</b>",
                        " ",
                        " ",
                        " ",
                        " ",
                        " "
                ),
                array("disabled" => true, "colsAlign" => array("left", "left", "center", "center", "center", "center"))
                        );




                $grid->appendRow(0, array(

                 ($conta_tipi) . " tipi + altre", //per i gradi di libertà si conta il totale dei tipi (qui manca "altre") - 1: non aggiungo e non tolgo!!
                    " ",
                    $tot_o,
                 round($tot_e, 2), //è normale che il totale non coincida esattamente con somma clausole!!
                 round ($tot_chi, 2),//
                "-"),
                array("disabled" => true, "colsAlign" => array("left", "left","center", "center", "center", "center"))
                        );
            }

               $val_critico = new Application_Model_DbTable_Valorecritico();
               $cerca_critico = $val_critico->getValoreCritico($conta_tipi);

              // echo " se l'indice di Pearson,  " . (round($tot_chi, 2)) . " è maggiore del valore critico di riferimento ". $cerca_critico . ", calcolato per ". $conta_tipi . " gradi libertà,
                //allora c'è una possibilità di oltre il 95% che la ricerca delle clausole non sia casuale. </br></br>"; //rimando a tabella, o calcolo...??

        $grid->setWidth100(true);
        return $grid;
   }


   private function presentaRitmo($stringa){ //non più usato...vedere se servirà ancora
       $stringa = explode (" ", $stringa);
       if ($stringa[0] === "2"){
           $stringa[0] = "p";
       } else {
           $stringa [0] = substr($stringa[0], 1);
       }
       if (endsWith($stringa[1], "ppp")){
           $stringa[1] = ((substr($stringa[1], 0, 1))-1) .  "pp+1";
       }

       return $stringa[0] . " " . $stringa[1];
   }


   private function analisiCursus($idTesti, $idUtenti, $ordineScelto, $soloAltre, $isPdf=false){
        require_once 'Funzioni.php';
        Zend_Session::start();
        $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file

        $data=new Application_Model_DbTable_Analisi;

        $array_ordine = Application_Model_Start::getArrayOrdine("AXAXXX", $ordineScelto);

        $cursus = $data->getCursus($idTesti);
        $tot = $data->getnClausole($idTesti);

       $pstmt = $data->getPSTMTosservata($idTesti, 'cursus');
//       $pstmt_e = $data->getPSTMTosservata($idTesti, 'ictus_uno');
//       $pstmt_ex = $data->getPSTMTosservata($idTesti, 'ictus_due');
//       $pstmt_tipo = $data->getPSTMTosservata($idTesti, 'tipo');
        $conta_tipi = 0;
        $tot_chi = 0;
        $tot_o= 0;
        $tot_e= 0;
        $somma_attese= 0;
        $somma_chi=0;

//come calcolare osservate in cursus???? prendo i valori canonici di ritmo, così in Ricci; uso variabile globale $_SESSION['result']
        $array_result = array();
        $array_result = $_SESSION['result'];

        $planus_attesa = 0;
        $planus_chi = 0;
        $tardus_attesa = 0;
        $tardus_chi = 0;
        $velox_attesa = 0;
        $velox_chi = 0;
        $trispondaicus_attesa = 0;
        $trispondaicus_chi = 0;
        $medius_attesa = 0;
        $medius_chi = 0;
        $dispondaicus_attesa = 0;
        $dispondaicus_chi = 0;
        $dispondeus_attesa = 0;
        $dispondeus_chi = 0;
        $var1_attesa = 0;
        $var1_chi = 0;
        $var2_attesa = 0;
        $var2_chi = 0;
        $var3_attesa = 0;
        $var3_chi = 0;

        foreach ($array_result as $value){

            if ($value["ritmotab"]=="planus"){
                $planus_attesa += $value['attesa'];
                $planus_chi += $value['chi'];
            } else if ($value["ritmotab"]=="tardus"){
                $tardus_attesa += $value['attesa'];
                $tardus_chi += $value['chi'];
            } else if ($value["ritmotab"]=="velox"){
                $velox_attesa += $value['attesa'];
                $velox_chi += $value['chi'];
            } else if ($value["ritmotab"]=="trispondaicus"){
                $trispondaicus_attesa += $value['attesa'];
                $trispondaicus_chi += $value['chi'];
            } else if ($value["ritmotab"]=="dispondaicus"){
                $dispondaicus_attesa += $value['attesa'];
                $dispondaicus_chi += $value['chi'];
            } else if ($value["ritmotab"]=="medius"){
                $medius_attesa += $value['attesa'];
                $medius_chi += $value['chi'];
            } else if ($value["ritmotab"]=="dispondeus-dattylicus"){
                $dispondeus_attesa += $value['attesa'];
                $dispondeus_chi += $value['chi'];
            } else {
                if ($value["rit_standard"]=="p 6p" ||$value["rit_standard"]=="pp 5p"){
                    $var1_attesa += $value['attesa'];
                    $var1_chi += $value['chi'];
                } else if ($value["rit_standard"]=="pp 6pp"){
                    $var2_attesa += $value['attesa'];
                    $var2_chi += $value['chi'];
                } else if ($value["rit_standard"]=="p 6pp" || $value["rit_standard"]=="pp 5pp"){
                    $var3_attesa += $value['attesa'];
                    $var3_chi += $value['chi'];
                }
           }
       }

        $titoli=array("Clausole", " ", "o","e","x<sup>2</sup>", "perc.");
        if($isPdf){
            require_once("hidatagrid/HIdatagridPDF.php");
            $grid = new HIdatagridPDF(
            "Analisi Cursus",
            $titoli,
            array(140,120,80,80,80,80)
            );
        } else {
        require_once("hidatagrid/HIdatagrid.php");
        $buttons = array();
        if ($soloAltre){
            $nome = 'analisiCursus';
            $buttons[] = array ('label'=>"mostra standard", 'action'=>"analisiCursus('2D')");
        } else {
            $nome = 'analisiCursus';
            $buttons[]=array('label'=>"mostra altre",'action'=>"mostraNascostiCur('2D')");
            }
        $opzioni = array("colsIndex"=>$array_ordine,
                    "dojo-help"=>'help_cursus',
                    "buttons"=>$buttons);
        $grid = new HIdatagrid(
            $nome,
            "",
            $titoli,
            array(150,130,130,130,130,110),
            150,
            $opzioni
        );

        $grid->setPDF(true);
        $grid->setRowSelection(true);
        $grid->setAction(array("onclick" => "mostraClausoleCursus()"));
        }

        $grid->setNoWrap(true);

    $altre = array("canone"=>'', "osservata"=>0, "attesa"=> 0, "chi"=> 0, "percentuale"=>0) ;
        //creazioni array per ordinamento in base "osservate"

    $array_cursus = array();//array multidimensionale

            foreach($cursus as $analisi){
                $query = $pstmt->execute(array($idTesti, $analisi->cursus));
                $query = $pstmt->fetch();//è il record con distinct

                $osservata = $query['conta'];
                $record = array();
                $tot_o += $osservata;
                $record['clausola'] = $analisi->cursus;
                $record['clausolatab'] = " "; //tipo
                $record['attesa'] = 0; //prelevo il valore dalla tabella ritmi, vedi sopra
                $record['chi'] = 0; //prelevo il valore dalla tabella ritmi, vedi sopra
                $record['osservata'] = 0; // conto con distinct
//                $record['percentuale'] = 0; //calcolo

                if ($record['clausola']==="xooxo"){
                    $record['clausolatab'] .= "planus";
                    $record['attesa'] = $planus_attesa;
                    $record ['chi'] = $planus_chi;
                } else if ($record['clausola']==="xooooxo"){
                    $record['clausolatab'] .=  "velox";
                    $record['attesa'] = $velox_attesa;
                    $record ['chi'] = $velox_chi;
                } else if ($record['clausola']==="xooxoo"){
                    $record['clausolatab'] .=  "tardus";
                    $record['attesa'] = $tardus_attesa;
                    $record ['chi'] = $tardus_chi;
                } else if ($record['clausola'] === "xoooxo"){
                    $record['clausolatab'] .=  "trispondaicus";
                    $record['attesa'] = $trispondaicus_attesa;
                    $record ['chi'] = $trispondaicus_chi;
                } else if ($record['clausola'] === "xoxoo"){
                    $record['clausolatab'] .= "medius";
                    $record['attesa'] = $medius_attesa;
                    $record ['chi'] = $medius_chi;
                } else if ($record['clausola'] === "xoooxoo"){
                    $record['clausolatab'] .=  "dispondeus-dattylicus";
                    $record['attesa'] = $dispondeus_attesa;
                    $record ['chi'] = $dispondeus_chi;
                } else if ($record['clausola'] ==="xoxo"){
                    $record['clausolatab'] .=  "dispondaicus";
                    $record['attesa'] = $dispondaicus_attesa;
                    $record ['chi'] = $dispondaicus_chi;
                }else if ($record['clausola'] ==="xoooooxo"){
                    $record['clausolatab'] .=   "-" ;
                    $record['attesa'] = $var1_attesa;
                    $record ['chi'] = $var1_chi;
                }else if ($record['clausola'] ==="xoooooxoo"){
                    $record['clausolatab'] .=   "-" ;
                    $record['attesa'] = $var2_attesa;
                    $record ['chi'] = $var2_chi;
                }else if ($record['clausola'] ==="xooooxoo"){
                    $record['clausolatab'] .=   "-" ;
                    $record['attesa'] = $var3_attesa;
                    $record ['chi'] = $var3_chi;
                }

                $record['osservata'] = $osservata; //numero di occorrenze
                 error_log ("OSSERVATA" . $record['osservata']);
                $array_cursus[] = $record;

//                $record['percentuale'] = ($record['osservata']/$tot)*100;
            }




            $indice = array();
            $record = array();
            $colonna = substr($ordineScelto, 0, 1);
            $verso = substr($ordineScelto, 1);
            $gridAltre = array();
//            $attesa = 0;
//            $chi = 0;
            //calcolo di o, e, % e X in base ai valori della tabella ritmo!!! quindi non serve calcolo, ma basta riprendere gli stessi valori

            //calcolo con valori di frequenza attesa in base a calcolo su cursus quanti "xoo" in prima posizione incontrano "xoo" in seconda etc...
             foreach ($array_cursus as $value){
             //calcola frequenza attesa
 //calcolo con valori di frequenza attesa in base a calcolo su cursus quanti "xoo" in prima posizione incontrano "xoo" in seconda etc...
//                $array[0] = "";
//                $array[1] = "";
//                $sequenza= str_split($value['clausola']);
//                $ct= count($sequenza);
//            for ($j=$ct-1; $j>=0; $j--){
//                if ($sequenza[$j]==="o"){
//                    $array[1] = $sequenza[$j] . $array[1];
//                } else {
//                    $array[1] = $sequenza[$j] . $array[1];
//                    break;
//                }
//             $array[1] = trim($array[1]);
//             $lun= strlen ($array[1])+1 ;
//             $array[0] =  substr($value['clausola'], 0, strlen($value['clausola'])-$lun);
//
//            }
//             //conto valori primo ictus
//                $query = $pstmt_e->execute(array($idTesti, $array[0]));
//                $query = $pstmt_e->fetch();
//                $n_penultima = $query['conta'];
//
//               error_log($array[0] . "penultima = " . $n_penultima);
//           //conto valori secondo ictus
//                $query = $pstmt_ex->execute(array($idTesti, $array[1]));
//                $query = $pstmt_ex->fetch();
//                $n_ultima = $query['conta'];
//                error_log($array[1]. "ultima = " . $n_ultima);
//
//                $attesa = (($n_penultima*$n_ultima )/ $tot_o);
//                //error_log("ATTESA: " . $array[0] . "= n. " . $n_penultima . "*" . $array[1] . "= n. " . $n_ultima . "/ Totale= " . $tot_o);
//
//                if ($attesa){
//                    $tot_e += $attesa;
//                }
//                $chi = bcpow(($value['osservata']-$attesa), 2, 4)/$attesa;

//                if ($chi){
//                    $tot_chi += $chi;
//                }

                $percentuale = ($value['osservata']/$tot)*100;


            if ($soloAltre) {
                     if ($value['attesa']<5){
                        $conta_tipi ++;
                        $record[] = array("clausola"=>$value['clausola'], "clausolatab"=>$value['clausolatab'],
                           "osservata"=>$value['osservata'], "attesa"=>$value['attesa'], "chi"=>$value['chi'], "percentuale"=>$percentuale) ;
                        //informazioni sull'ordinamento
                        if ($colonna==0){
                           $indice[] = $value['clausola'];
                        } else if ($colonna==2){
                           $indice[] = $value['osservata'];
                        }
                   }
                } else {
                     if ($value['attesa']<5){
                           $altre["osservata"] += $value['osservata'];
                           $altre["attesa"] += $value['attesa'];
                           $altre["chi"] += $value['chi'];
//                           $altre["attesa"] = $tot_o - $somma_attese;
//                           $altre["chi"] = bcpow(($altre["osservata"]-$altre["attesa"]), 2, 4)/$altre["attesa"];
                           $altre["percentuale"] += $percentuale;
                     } else {
                        $conta_tipi ++;
                       $record[] = array("clausola"=>$value['clausola'], "clausolatab"=>$value['clausolatab'],
                           "osservata"=>$value['osservata'], "attesa"=>$value['attesa'], "chi"=>$value['chi'], "percentuale"=>$percentuale) ;
                       $somma_attese += $value['attesa'];
                       $somma_chi += $value['chi'];
                       if ($colonna==0){
                           $indice[] = $value['clausola'];
                       } else if ($colonna==2){
                           $indice[] = $value['osservata'];
                       }
                   }

                 }
            }
            if ($verso=="A"){
                asort($indice);
            } else {
                arsort($indice);
            }
            $tot_e += round($tot_e + ($tot_o-$tot_e), 2);
            $tot_chi += $somma_chi +  $altre["chi"];


            error_log("nuovo chi = ". $tot_chi);

            foreach ($indice as $key=>$value){

                $grid->appendRow($record[$key]['clausola'], array(
                    $record[$key]['clausola'],
                    $record[$key]['clausolatab'],
                    $record[$key]['osservata'],
                    round ($record[$key]["attesa"], 2),
                    round ($record[$key]["chi"], 2),
                    round ($record[$key]["percentuale"],2)
                    ),
                    array("colsAlign" => array("left", "left","center", "center", "center", "center"))
                );
            }


            if (!$soloAltre){
                $grid->appendRow(-1, array(
                        "altre",
                        " ",
                        $altre["osservata"] ,
                        round ($altre["attesa"], 2) ,
                        round ($altre["chi"], 2) ,
                        round ($altre["percentuale"], 2)

                        ),
                array("colsAlign" => array("left", "left","center", "center", "center", "center"))
                     );



                $grid->appendRow(0, array(
                       "<b>totali</b>",
                        " ",
                        " ",
                        " ",
                        " ",
                        " "
                ),
                array("disabled" => true, "colsAlign" => array("left", "left", "center", "center", "center", "center"))
                        );

                $grid->appendRow(0, array(

                ($conta_tipi) . " tipi + altre", //per i gradi di libertà si conta il totale dei tipi (qui manca "altre") - 1: non aggiungo e non tolgo!!
                 " ",
                 $tot_o,
                 round($tot_e, 2), //è normale che il totale non coincida esattamente con somma clausole!!
                 round ($tot_chi, 2),//
                "100%"),
                array("disabled" => true, "colsAlign" => array("left", "left", "center", "center", "center", "center"))
                        );

            }
               $val_critico = new Application_Model_DbTable_Valorecritico();
               $cerca_critico = $val_critico->getValoreCritico($conta_tipi);

                //echo "<div> se " . (round($tot_chi, 2)) . " e > del valore critico di riferimento ". $cerca_critico . ", calcolato per ". $conta_tipi . " gradi liberta,
                //allora ce una possibilita di oltre il 95 che la ricerca delle clausole non sia casuale. </div>"; //rimando a tabella, o calcolo...??

            $grid->setWidth100(true);
            return $grid;
    }








   private function analisiClausole($idTesti, $idUtenti, $ordineScelto, $monosillabi, $isPdf=false){
       Zend_Session::start();
       $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file

        $data=new Application_Model_DbTable_Analisi;

        switch ($ordineScelto) {
            case "0A" :
                $order="idanalisi";
                break;
            case "0D" :
                $order="idanalisi DESC";
                break;
            case "2A" :
                $order="quant_orlandi";
                break;
             case "2D" :
                $order="quant_orlandi DESC";
                break;
            case "3A" :
                $order="accenti";
                break;
            case "3D" :
                $order="accenti DESC";
                break;
            case "4A" :
                $order="tipo";
                break;
            case "4D" :
                $order="tipo DESC";
                break;
            default:
                $order="";
                break;
        }
        $array_ordine = Application_Model_Start::getArrayOrdine("AXAAA", $ordineScelto);
        $lista=$data->getAnalisi($idTesti, $order);
        $titoli = array("Clausola","Sill.", "Metrica","Ritmica", "Tipo");
        if ($isPdf){
            require_once("hidatagrid/HIdatagridPDF.php");
            $grid = new HIdatagridPDF(
            "Controllo sistematico clausole",
            $titoli,
            array(150,110,130,130, 130)
            );
        }else{
            require_once("hidatagrid/HIdatagrid.php");
            $nome = 'analisiClausole';
            $buttons = array();
            if ($monosillabi){

                //$buttons[]=array('label'=>"mostra clausole", 'action'=>"analisiClausole('0A')");
            } else {

                $buttons[]=array('label'=>"mostra monosillabi", 'action'=>"mostraMono('0A')");
            }
            $opzioni = array ("colsIndex"=>$array_ordine,
                "dojo-help"=>'help_clausole',
                "buttons"=>$buttons);
            $grid = new HIdatagrid(
            $nome,
            "Controllo sistematico clausole",
            $titoli,
            array(50,10,30,30, 30),
            0,
            $opzioni
            );
        $grid->setPDF(true);//chiamata a javascript HIPDF_name
        }

        $grid->setNoWrap(true);
        foreach($lista as $analisi){
            if (Application_Model_Start::inStringa($analisi->rit_pass, "m")===-1){ //verifica che non vengano escluse le clausole con monosillabi non calcolati in tabelle!!
            $grid->appendRow($analisi->idanalisi, array(
                $analisi->clausola,
                $analisi->sillabe,
                $analisi->quant_orlandi,
                $analisi->rit_standard, //sono indecisa se mettere accenti...
                $analisi->tipo
                ),
                array("colsAlign" => array("left", "center", "left", "left", "left", "left"))
            );
            //il segmento considerato comprende sempre almeno le ultime otto sillabe!
            }
       }
        $grid->setWidth100(true);
            return $grid;
    }


    private function mostraMonosillabi($idTesti, $idUtenti, $ordineScelto){
        $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file
        $data=new Application_Model_DbTable_Analisi;
        switch ($ordineScelto) {
            case "0A" :
                $order="idanalisi";
                break;
            case "0D" :
                $order="idanalisi DESC";
                break;
            case "2A" :
                $order="quant_orlandi";
                break;
             case "2D" :
                $order="quant_orlandi DESC";
                break;
            case "3A" :
                $order="accenti";
                break;
            case "3A" :
                $order="accenti DESC";
                break;

            default:
                $order="";
                break;
        }
        $array_ordine = Application_Model_Start::getArrayOrdine("AXAA", $ordineScelto);
        $lista=$data->getAnalisi($idTesti, $order);
        $titoli = array("Clausola","Sill.", "Metrica","Ritmica");

            require_once("hidatagrid/HIdatagrid.php");
            $nome = 'mostraMonosillabi';
            $opzioni = array ("colsIndex"=>$array_ordine);
            $grid = new HIdatagrid(
            $nome,
            "Clausole escluse dai calcoli",
            $titoli,
            array(50,10,30,30),
            0,
            $opzioni
            );
        $grid->setNoWrap(true);
        foreach($lista as $analisi){
            if (Application_Model_Start::inStringa($analisi->rit_pass, "m")>-1){ //verifica che non vengano escluse le clausole con monosillabi non calcolati in tabelle!!
            $grid->appendRow($analisi->idanalisi, array(
                $analisi->clausola,
                $analisi->sillabe,
                $analisi->quant_orlandi,
                $analisi->accenti
                ),
                array("colsAlign" => array("left", "center", "left", "left", "left"))
            );
            //il segmento considerato comprende sempre almeno le ultime otto sillabe!
            }
       }
        $grid->setWidth100(true);
            return $grid;

    }




    private function parser($idTesti, $idUtenti){
        require_once 'Funzioni.php';
        Zend_Session::start();
        $testi=new Application_Model_DbTable_Testi;
        $testo=$testi->getTesto($idTesti);//riga tabella del testo che si vuole caricare
        $filename=$_SESSION["uploaddir"].$idUtenti . "/" . $testo->nome;//percorso relativo del file
        $contenuto=file_get_contents($filename);//lettura del file
        //echo "testo: " . $testo->nome . "</br>" . $contenuto;
        $dati=new Application_Model_DbTable_Analisi;
        $analisi=$dati->getAnalisi($idTesti);

        $nfrasi = count($analisi);

        $nmonosillabi = 0;


        $p_quant = 0;
        $p_accent = 0;

        $risolte_v=round($testo->trovate, 3) * 100;
        $risolte_m=round($testo->risolte_met, 3) * 100;
        $risolte_r=round ($testo->risolte_rit, 3) * 100;

        require_once("hidatagrid/HIdatagrid.php");
        $grid = new HIdatagrid(
                "parser",
                "Informazioni Generali",
                array("", "", ""),
                array(60, 60, 60),
                300,
                array ("dojo-help"=>'help_info')
                );
        $grid->appendRow("row1", array("numero totale di frasi: $nfrasi", "numero totale di parole: $testo->nparole", "frasi escluse per monosillabi: $nmonosillabi"),
                          array('colsAlign'=>array('left', "left", "left"))
                );
        $grid->appendRow("row2", array("parole effettivamente considerate:  $testo->p_cons", "parole con prosodia risolta: $testo->p_quant", "parole con ritmica definita: $testo->p_accent"),
                            array('colsAlign'=>array('left', "left", "left"))
                );
        $grid->appendRow("row3", array("percentuale parole risolte con vocabolario: $risolte_v ", "percentuale parole risolte metrica: $risolte_m", "percentuale parole risolte ritmica: $risolte_r"),
                array('colsAlign'=>array('left', "left", "left"))
                );
        $grid->setWidth100(true);
        $grid->setNoWrap(true);

        $grid->render();




       /* echo "percentuale parole risolte con vocabolario  (round($testo->trovate, 3) * 100)", "percentuale parole risolte metrica  (round($testo->risolte_met, 3) * 100)", "percentuale parole risolte ritmica (round ($testo->risolte_rit, 3) * 100)"
       echo "</table>";*/
    }

}

