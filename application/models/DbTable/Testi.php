<?php

class Application_Model_DbTable_Testi extends Application_Model_DbTable_Abstract
{

    protected $_name = 'testi';

    public function getTesti($idUtenti){
        $select = $this->select()->where('idutenti = ?', $idUtenti)
                ->order("sigla");
        $rows = $this->fetchAll($select);
        if (!$rows) {
            throw new Exception("Could not find row $idUtenti");
        }
        return $rows;
    }

    public function getTesto($idTesti){
        $row = $this->fetchRow('idtesti = ' . $idTesti);
        return $row;
    }

    public function isFile($nome, $idUtenti){
        $select = $this->select()->where("idutenti = ?", $idUtenti)
                ->where("nome = ?", $nome);
        $rows = $this->fetchAll($select);
        $result = false;
        if(count($rows) > 0) $result = true;
        return $result;
    }
    public function isFileExcluding($nome, $idUtenti, $idTesti){
        // è già in uso il nome del file (escluso il presente)
        $select = $this->select()->where("idutenti = ?", $idUtenti)
                ->where("idtesti <> ?", $idTesti)
                ->where("nome = ?", $nome);
        $rows = $this->fetchAll($select);
        $result = false;
        if(count($rows) > 0) $result = true;
        return $result;
    }
    public function isSigla($sigla, $idUtenti){
        $select = $this->select()->where("idutenti = ?", $idUtenti)
                ->where("sigla = ?", $sigla);
        $rows = $this->fetchAll($select);
        $result = false;
        if(count($rows) > 0) $result = true;
        return $result;
    }

    public function isSiglaExcluding($sigla, $idUtenti, $idTesti){
        // è già in uso la sigla (escluso il presente)
        $select = $this->select()->where("idutenti = ?", $idUtenti)
                ->where("idtesti <> ?", $idTesti)
                ->where("sigla = ?", $sigla);
        $rows = $this->fetchAll($select);
        $result = false;
        if(count($rows) > 0) $result = true;
        return $result;
    }
    
    public function getLastId(){
      /*  $select = $this->select()->from($this->_name, array('idtesti'))
                ->order("idtesti DESC")
                ->limit(1,0);
        $rows = $this->fetchAll();
        if (count($rows)===0){
            return 0;
        } else {
        return $rows[0]->idtesti;
        }*/
        $idtesti = 0;
        $select = $this->select()->order ('idtesti');
        $rows = $this->fetchAll($select);
        for ($i=0; $i<count($rows); $i++){
            $idtesti = $rows[$i]->idtesti;
        }
        return $idtesti;
    }

    public function getCambiaImpostazioni($idTesti,$monosillabi, $sinalefe){
        $select = $this->select()->where("idtesti = ?", $idTesti);
        $row = $this->fetchRow($select);
        $result = false;
        if ($row->monosillabi!=$monosillabi || $row->sinalefe !=$sinalefe){
            $result = true;
        }
        return $result;
    }

    public function isMonosillabi ($idTesti){
        $select = $this->select()->where("idtesti = ?", $idTesti);
        $row = $this->fetchRow($select);
        $result = false;
        if ($row->monosillabi == 1){
            $result = true;
        }
        return $result;
    }
 
    

    public function isRisoltoRitmo($idTesti){
        
        $select = $this->select()->where("idtesti = ?", $idTesti);
        $row = $this->fetchRow($select);
        $result = false;
        if ($row->risolte_rit ==1){
            $result = true;
        }
        return $result;
    }


}