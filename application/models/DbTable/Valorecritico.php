<?php

class Application_Model_DbTable_Valorecritico extends Application_Model_DbTable_Abstract
{

    protected $_name = 'chiquadro';

    public function getValoreCritico ($conta_tipi){
        
        $select = $this->select()->where('gradi_liberta = ?', $conta_tipi);
            $row = $this->fetchRow($select);
        if ($row == null){
            return 0;
        } else {

            return $row->val_critico;
        }
            

    }


    // 'select valore_critico from chiquadro where gradi_liberta = '


}

