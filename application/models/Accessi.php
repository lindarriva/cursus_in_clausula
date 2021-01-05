<?php

class Application_Model_Accessi{



    public function hasIdentity(){
        return Zend_Auth::getInstance()->hasIdentity();
    }

    public function getIdUtente(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $idUtenti = $auth->getIdentity()->idutenti;
        }
        return $idUtenti;
    }
    public function getUserName(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $result = $auth->getIdentity()->username;
        }
        return $result;
    }

    public function _process($username, $password){
        // Registra l'autenticazione: registra il record della tabella Utenti;
        // Questa potrà essere richiamata con:
        // $auth = Zend_Auth::getInstance();
        // if ($auth->hasIdentity()) {
        //        $username = $auth->getIdentity()->email;
        //        $livello = $auth->getIdentity()->livello;
        //        ecc.
        // }
        $namespace = new Zend_Session_Namespace('Zend_Auth');
        $namespace->setExpirationSeconds(7200);
        $adapter = $this->_getAuthAdapter();
        $adapter->setIdentity($username);
        $adapter->setCredential($password);
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate($adapter);
        if ($result->isValid()) {
            $user = $adapter->getResultRowObject();
            $auth->getStorage()->write($user);
            return true;
        }
        return false;
    }

    protected function _getAuthAdapter() {
        // verifica username e password
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
        $authAdapter->setTableName('utenti')
        ->setIdentityColumn('username')
        ->setCredentialColumn('password');
        return $authAdapter;
    }

    public static function controlla(){
        try {
            if(!Zend_Auth::getInstance()->hasIdentity()) {
                $r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $r->gotoUrl('/index/index')->redirectAndExit();
            }
        }
        catch(Exception $ex) {
          error_log("Si è verificato un errore: " . $ex->getMessage());
        }
    }

    public static function controllaAjax(){
        return Zend_Auth::getInstance()->hasIdentity();
    }


}

