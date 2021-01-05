<?php
class Application_Model_DbTable_Abstract extends Zend_Db_Table_Abstract{

    public function init(){
        parent::init();
        $this->getAdapter()->query("SET SESSION sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    }
    

}
