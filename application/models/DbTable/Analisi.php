<?php


class Application_Model_DbTable_Analisi extends Application_Model_DbTable_Abstract
{

    protected $_name = 'analisi';

    public function getAnalisi($idTesti, $order='idanalisi'){
            $select = $this->select()->where('idtesti = ?', $idTesti)
                    ->order($order);
            $rows = $this->fetchAll($select);
            return $rows;
        }


    public function getnClausole($idTesti){
            $select = $this->select()->where('idtesti = ?', $idTesti)
                    ->from($this->_name, array('count(*) as rows'));
            $row = $this->fetchRow($select);

            return $row->rows;
        }


    public function getRitmo ($idTesti){
        $select = $this->select()->where('idtesti = ?', $idTesti)
                ->distinct()->from($this->_name, array('rit_standard'))->where('rit_standard <> ?', 'monosillabi')->order('rit_standard');
        $rows = $this->fetchAll($select);
        if (count($rows)===0){
            return 0;
        } else {

            return $rows;
        }
    }

    public function getQuantOrl ($idTesti){
        $select = $this->select()->where('idtesti = ?', $idTesti)
//                ->where('quant_calcoli regexp ?', '\|')
                ->distinct()->from($this->_name, array('quant_calcoli','penultima','ultima'))->order('quant_calcoli');
        $rows = $this->fetchAll($select);
        if (count($rows)===0){
            return 0;
        } else {

            return $rows;
        }
    }

    
    public function getTab($idTesti, $pattern, $campo){
    error_log("schema" . $pattern);
    $select = $this->select()->where('idtesti = ?', $idTesti)
                ->where($campo .'= ?', $pattern)->from($this->_name, array('clausola'))->order('idanalisi');
        $rows = $this->fetchAll($select);
        error_log(count($rows));
        if (count($rows)===0){
            return 0;
        } else {

            return $rows;
        }
    }

    public function getPSTMTosservata ($idTesti, $campo){ //costruisce query parametrica
             return $this->getAdapter()->query('select count(*) as conta
            from analisi where idtesti = ? and ' . $campo . ' = ?', array($idTesti, "xxx"));
     }

   public function getPSTMTattese ($idTesti, $campo){ //costruisce query parametrica
             return $this->getAdapter()->query('select count(*) as conta
            from analisi where idtesti = ? and ' . $campo . ' like ?', array($idTesti, "xxx"));
     }

   
public function getQuantita ($idTesti){
        $select = $this->select()->where('idtesti = ?', $idTesti)
                ->distinct()->from($this->_name, array('quantita'))->order('idanalisi');
        $rows = $this->fetchAll($select);
        if (count($rows)===0){
            return 0;
        } else {

            return $rows;
        }
    }



    public function getCursus ($idTesti){
        $select = $this->select()->where('idtesti = ?', $idTesti)
            ->distinct()->from($this->_name, array('cursus'))->order('cursus');
        $rows = $this->fetchAll($select);
        if (count($rows)===0){
            return 0;
        } else {
        return $rows;
       }

    }

    public function getPSTMTosservaCursus ($idTesti, $cursus){ //costruisce query parametrica
             return $this->getAdapter()->query('select count(*) as conta
            from analisi where idtesti = ? and cursus = ?', array($idTesti, "xxx"));
     }

   public function getPSTMTatteseCursus ($idTesti){ //costruisce query parametrica
             return $this->getAdapter()->query('select count(*) as conta
            from analisi where idtesti = ? and cursus ?', array($idTesti, "xxx"));
     }


    public function getExpected ($idTesti){
        $select = $this->select()->where('idtesti = ?', $idTesti)
            ->distinct()->from($this->_name, array('rit_standard'))->order('rit_standard');
        $rows =$this->fetchAll($select);
        if (count($rows)===0){
            return 0;
        } else {
        $standard = "";
        $pos1 = array();
        $pos2 = array();
        //$rows è un array con singolo record rit_standard = valore ovvero $analisi->rit_standard = 2 3p
        
       foreach ($rows as $value){
           $standard = $value->rit_standard;
           $array = explode (" ", $standard);
           
           for ($i=0; $i<count($array); $i++){
               if ($i===0){
                   if ($array[$i]==="2"){
                       $array[$i]= "2p";
                   }
                   $pos1[] = substr($array[$i], 1, 1);//elenco tipi in penultima, eliminando il numero!!
               } else {
                   $pos2[] = $array[$i];//elenco tipi in ultima
               }
           }
           $pos1 = array_unique ($pos1);
           $n_penultima = count($pos1); //conta i tipi
           $pos2 = array_unique ($pos2);
           $n_ultima = count ($pos2); //conta i tipi
       }  
           
      
     //$array è un array multiplo che contiene l'array pos1 in [0] e l'array pos2 in [1]?
        
      $pstmt = $this->_name->getPSTMTattese($idTesti);
      $exp = $pstmt->execute (array($idTesti, $array));
      $exp = $pstmt->fetch();

      //una volta ottenuto questo risultato, devo riconvertire i valori di rit_standard
     //nei corrispondenti valori di ritmi, e assegnare la tipologia
           
           
           
        return $expected;//array con la frequenza attesa di ogni tipo
        //corrispondente a numero tipi di "ritmi", non "rit_standard!!
        //parametrica anche per attese? forse meglio di sì, ma da usare
        //qui intra nos, non nel controller...

      }
    }



    
}


?>
