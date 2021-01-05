<?php

class Application_Model_DbTable_Utenti extends Application_Model_DbTable_Abstract
{

    protected $_name = 'utenti';

    public function isUtente($userName){
        // verifica che un nome utente non sia già in uso
        $select = $this->select()->where("username = ?", $userName);
        $rows = $this->fetchAll($select);
        $result = false;
        if(count($rows) > 0) $result = true;
        return $result;
    }

}


