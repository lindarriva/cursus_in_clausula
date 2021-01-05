<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        
        
       
    }

    public function indexAction()
    {
        
            

        $frmLogin = new Application_Form_Login();
        $frmLogin->setAction($this->view->baseUrl("index/index/"));

        $this->view->messaggio = "";
        $this->view->login = true;
        if(Zend_Auth::getInstance()->hasIdentity()){
            $this->view->login = false;
            return;
        }

      if($this->getRequest()->isPost() && $this->_getParam('submit') != null){
            // analizzo il submit
            if($frmLogin->isValid($_POST)){

                
                
                    // se è valido verifico l'autenticazione e invio ad altra pagina
                    $accessi = new Application_Model_Accessi();
                    $data = $frmLogin->getValues();

                    if($accessi->_process($data['username'], $data['password'])){
                       
                        $this->view->login = false;


                    }else{
                        $frmLogin->populate($_POST);
                        $this->view->messaggio = "Nome utente o password non validi";
                    }
                

            }else{
                // se non è valido riscrivo la pagina con i messaggi di errore
                $frmLogin->populate($_POST);
            }
        }
        
    
        $this->view->form = $frmLogin;
        
    
    }


     
    public function registraAction(){
        $frmRegistra = new Application_Form_Registrazione();
        $frmRegistra->setAction($this->view->baseUrl("index/registra/"));
        $this->view->form = $frmRegistra;
        if($this->getRequest()->isPost()){
            // analizzo il submit
            if($frmRegistra->isValid($_POST)){

                $utenti = new Application_Model_DbTable_Utenti();
                $data = $frmRegistra->getValues();
                $userName = $this->_getParam('username');
                    if($utenti->isUtente($userName)){
                        $frmRegistra->populate($_POST);
                        $this->view->messaggio = "Esiste già un utente registrato con lo stesso indirizzo email; si invita ad inserire un diverso nome utente.";
                    } else {
                        unset($data["password1"]);
                        $utenti->insert($data);
                    
                    // se è valido verifico l'autenticazione e invio ad altra pagina
                    $accessi = new Application_Model_Accessi();


                    $accessi->_process($data['username'], $data['password']);

                     // si invia la mail
                    $header = "From: info@cursusinclausula.eu\n";
                    $header .= "MIME-Version: 1.0\n";
                    $header .= "Content-Type: text/plain; charset=\"UTF-8\"\n";
                    $header .= "Content-Transfer-Encoding: 7bit\n\n";
                    $oggetto = "Registrazione CursusinClausula";
                    $messaggio = "Gentile " . $data['nome'] . " " . $data['cognome'] . ",\n";
                    $messaggio .= "La ringraziamo per aver eseguito la registrazione presso Cursus in Clausula on line.\n\n";
                    $messaggio .= "Le ricordiamo che le Sue credenziali per accedere alla sessione personale sono:\n";
                    $messaggio .= "Nome utente: " . $data["username"] . "\n";
                    $messaggio .= "Password: " . $data["password"] . "\n\n";
                    $messaggio .= "\nUn cordiale saluto\n";
                    $messaggio .= "Lo staff di Cursus in Clausula\n";
                    mail($data['username'], $oggetto, $messaggio, $header);

                    $header = "From: info@cursusinclausula.eu\n";
                    $header .= "MIME-Version: 1.0\n";
                    $header .= "Content-Type: text/plain; charset=\"UTF-8\"\n";
                    $header .= "Content-Transfer-Encoding: 7bit\n\n";
                    $oggetto = "Registrazione CursusinCla";
                    $messaggio = "Il nuovo utente è Nome utente: " . $data["cognome"] . " ". $data["nome"]. "\n";
                    $messaggio .= " proveniente da ". $data["istituzione"];
                    $messaggio .= "contattabile a questo indirizzo:" . $data["username"] . "\n";
                    mail("linda.spinazze@gmail.com", $oggetto, $messaggio, $header);


                    $this->_forward('registrato', 'index');

                    }
            }else{
                // se non è valido riscrivo la pagina con i messaggi di errore
                $frmRegistra->populate($_POST);
            }
        }

    }

    public function registratoAction(){

    }


    public function logoutAction()
    {
        $this->_helper->layout->disableLayout();
        $storage = new Zend_Auth_Storage_Session();
        $storage->clear();
        Zend_Auth::getInstance()->clearIdentity();
        $this->_helper->redirector('index');

    }

    


}



