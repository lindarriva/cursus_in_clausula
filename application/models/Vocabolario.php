<?php

class Application_Model_Vocabolario
{



    private $db;
    
    private $pstmt_lemmi1 = null;
    private $pstmt_lemmi2 = null;
    private $pstmt_desinenze1 = null;
    private $pstmt_desinenze2 = null;
    private $pstmt_temi = null;
    private $param_lemma = "";
    private $param_desinenza = "";
    private $param_iddesinenze = "";
    private $param_idlemmi = "";
    private $result_prosodia_lemmi ="";
    private $result_prosodia_des ="";
    private $result_prosodia_temi ="";
    private $result_lemma1 = "";
    private $result_lemma2 = "";
    private $result_iddesinenze = "";
    private $result_idlemmi = "";
    private $result_modello_des = "";
    private $result_modello_temi = "";
    private $result_sub_des = "";
    private $result_sub_temi = "";
    private $result_sincopato = "";
    private $nessi = array();
    private $consonanti = "bcdfgjlmnpqrstvzwx";

    private $pstmt_dittonghi = null;
    private $param_dittonghi = "";
    private $result_dittonghi = "";

    private $pstmt_enclitiche = null;
    private $param_enclitiche = "";
    private $result_enclitiche = "";

    private $lemmata = null;

    function __construct($db, $lemmiconnection){
        require_once 'Utilities.php';
        require_once 'lemmata/Lemmata.php';
        mb_internal_encoding("UTF-8");
        $this->db = $db;
        $this->lemmata = new Lemmata_Lemmata();
        $this->lemmata->setConnection($lemmiconnection);

        error_log ('Success... ' . $this->db->host_info . "\n");
        $this->pstmt_lemmi1 = $this->db->prepare("Select prosodia, lemmapadre from lemmi where lemma=?");
        $this->pstmt_lemmi1->bind_param("s", $this->param_lemma);
        $this->pstmt_lemmi1->bind_result($this->result_prosodia_lemmi, $this->result_lemma1);//tutti i campi della tabella lemmi
        
        $this->pstmt_desinenze1 = $this->db->prepare ("Select iddesinenze, prosodia from desinenze where desinenza=?");
        $this->pstmt_desinenze1->bind_param("s", $this->param_desinenza);
        $this->pstmt_desinenze1->bind_result($this->result_iddesinenze, $this->result_prosodia_des);

        $this->pstmt_desinenze2 = $this->db->prepare ("Select modello, sub from desinenze_modelli where iddesinenze=?");
        $this->pstmt_desinenze2->bind_param("i", $this->param_iddesinenze);
        $this->pstmt_desinenze2->bind_result($this->result_modello_des, $this->result_sub_des);

        $this->pstmt_temi = $this->db->prepare ("Select idlemmi, modello, sub, prosodia, sincopato from temi where tema=?");
        $this->pstmt_temi->bind_param("s", $this->param_tema);
        $this->pstmt_temi->bind_result($this->result_idlemmi, $this->result_modello_temi, $this->result_sub_temi, $this->result_prosodia_temi, $this->result_sincopato);

        $this->pstmt_lemmi2 = $this->db->prepare("Select lemma from lemmi where idlemmi=?");
        $this->pstmt_lemmi2->bind_param("i", $this->param_idlemmi);
        $this->pstmt_lemmi2->bind_result($this->result_lemma2);

        $this->nessi = array("BL","BR","CL","CR","CHR", "DL","DR","FR","FL","GL","GR","PL","PR","PHR","PHL","TL","TR","THR","THL");

        $this->pstmt_dittonghi = $this->db->prepare("SELECT standard FROM nondittonghi WHERE word = ?");
        $this->pstmt_dittonghi->bind_param('s', $this->param_dittonghi);
        $this->pstmt_dittonghi->bind_result($this->result_dittonghi);

        $this->pstmt_enclitiche = $this->db->prepare("SELECT COUNT(*) FROM enclitiche WHERE word=?");
        $this->pstmt_enclitiche->bind_param('s', $this->param_enclitiche);
        $this->pstmt_enclitiche->bind_result($this->result_enclitiche);
    }

    
      public function get ($parola){
          return $this->lemmata->getProsodie($parola);
      }
   

    

    private function normalizza($stringa){
        // riporta le lettere con quantità alla lettera senza
        $stringa = str_replace("^", "", $stringa);
        $cc = $stringa;
        $result = "";
        for($i = 0; $i < mb_strlen($cc); $i ++){
            if(mb_substr($cc, $i, 1) == 'ă' || mb_substr($cc, $i, 1) == 'ā'){
                $result .= "a";
            }else if(mb_substr($cc, $i, 1) == 'ĕ' || mb_substr($cc, $i, 1) == 'ē'){
                $result .= "e";
            }else if(mb_substr($cc, $i, 1) == 'ĭ' || mb_substr($cc, $i, 1) == 'ī' || mb_substr($cc, $i, 1) == 'î'){
                $result .= "i";
            }else if(mb_substr($cc, $i, 1) == 'ŏ' || mb_substr($cc, $i, 1) == 'ō'){
                $result .= "o";
            }else if(mb_substr($cc, $i, 1) == 'ŭ' || mb_substr($cc, $i, 1) == 'ū' || mb_substr($cc, $i, 1) == 'û'){
                $result .= "u";
            }else if(mb_substr($cc, $i, 1) == 'ў' || mb_substr($cc, $i, 1) == 'ӯ' || mb_substr($cc, $i, 1) == 'ȳ'){
                $result .= "y";
            }else if(mb_substr($cc, $i, 1) == 'Ā' || mb_substr($cc, $i, 1) == 'Ă'){
               $result .= "A";
            }else if(mb_substr($cc, $i, 1) == 'Ē' || mb_substr($cc, $i, 1) == 'Ĕ'){
               $result .= "E";
            }else if(mb_substr($cc, $i, 1) == 'Ī' || mb_substr($cc, $i, 1) == 'Ĭ'){
               $result .= "I";
            }else if(mb_substr($cc, $i, 1) == 'Ō' || mb_substr($cc, $i, 1) == 'Ŏ'){
               $result .= "O";
            }else if(mb_substr($cc, $i, 1) == 'Ū' || mb_substr($cc, $i, 1) == 'Ŭ'){
               $result .= "U";
            }else if(mb_substr($cc, $i, 1) == 'Ȳ'){
                $result .= "Y";

            }else{
                $result .= mb_substr($cc, $i, 1);
            }
        }

        return $result;
    }

    public function close (){
         $this->pstmt_lemmi1->close();
         $this->pstmt_lemmi2->close();
         $this->pstmt_desinenze1->close();
         $this->pstmt_desinenze2->close();
         $this->pstmt_temi->close();
         $this->pstmt_dittonghi->close();
         $this->pstmt_enclitiche->close();
    }




    public function dittonghi ($stringa){
        $array = array();
        $this->param_dittonghi = $stringa; //assegna variabile
        $this->pstmt_dittonghi->execute(); //esegue la query
        while ($this->pstmt_dittonghi->fetch()){ //se la query ha risultato c'è un finto dittongo
            $tmp = $this->result_dittonghi;
            error_log($tmp . " proviene da non dittongo");
            $tmp = str_replace("AE", "A.E", $tmp);
            $tmp =  str_replace("OE", "O.E", $tmp);
            $tmp =  str_replace("AU", "A.U", $tmp);
            $tmp = str_replace ("-", "", $tmp);
            $array[]=$tmp;
        }
        if (count($array)===0){ //se non è un nodittongo
            $stringa = " " . trim($stringa) . " ";
            $stringa = str_replace("AE", "A.E", $stringa);
            $stringa =  str_replace("OE", "O.E", $stringa);
            $stringa =  str_replace("AU", "A.U", $stringa);
            $stringa =  str_replace(" CUI", " CU.I", $stringa);
            $stringa =  str_replace(" ALICUI", " ALICU.I", $stringa);
            $stringa =  str_replace(" HUIC ", " HU.IC ", $stringa);
            $stringa =  str_replace(" HEI ", " HE.I ", $stringa);
            $stringa =  str_replace(" EU ", " E.U ", $stringa);
            $stringa =  str_replace(" HEU ", " HE.U ", $stringa);
            $stringa =  str_replace(" SEU ", " SE.U ", $stringa);
            $stringa =  str_replace(" NEU ", " NE.U ", $stringa);
            $stringa =  str_replace(" CEU ", " CE.U ", $stringa);
            $stringa =  str_replace(" HEUS ", " HE.US ", $stringa);

            if(startsWith($stringa, " EU")){
                // caso di EU iniziale di parola: è sempre dittongo, tranne nei casi segnalati
                if(! startsWith ($stringa, " EUNT")
                    && ! startsWith ($stringa, " EUND")
                    &&  $stringa != " EUM "){
                    $stringa = " E.U" . substr($stringa, 3);
                }
            }
            $array[0] = $stringa;
        }
            return $array;
    }

    public function isEnclitica($value){//controlla nel db cursus.enclitiche i casi di finte enclitiche
        $result = true;
        $false_enc = $this->db->query
                ("SELECT COUNT(*) FROM enclitiche WHERE word='". $value . "'");

               $row = $false_enc->fetch_array(MYSQLI_NUM);
               if ($row[0]>0){
                   $result = false;
               }
               return $result;
   }
    
   public function semivocali($value){//controlla nel db cursus.semivocali i casi di semivocali
       $query = $this->db->query
                        ("SELECT standard FROM semivocali WHERE word = '" . $value . "'");
       $result = array();
                    while ($row = $query->fetch_row()){
                            $result[] = $row[0];
                    }
       return $result;
   }
}




