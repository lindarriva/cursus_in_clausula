<?php

class BibliografiaController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        ini_set('session.gc_maxlifetime', '7800');

    }

    public function bibliografiaAction()
    {
        Application_Model_Accessi::controlla();
       $testi = new Application_Model_DbTable_Testi();
        $accessi = new Application_Model_Accessi();
//        if(!$accessi->hasIdentity()){
//            $this->_forward('index','index');
//            return;
//        }
        $idUtenti = $accessi->getIdUtente();
        $this->view->messaggio = "";
        
    }


     
     
}

