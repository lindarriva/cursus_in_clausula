<?php

class Application_Form_Registrazione extends Zend_Form
{

    public function init()
    {
       
// decoratori standard per gli elementi text
        $textDecorators = array(
            'ViewHelper',
            // colore rosso per gli errori
            array('Errors', array('tag' => 'div', 'escape' => false, 'placement' => 'append', 'class' => 'messaggio', 'style' => 'padding-top:10px;padding-left:17px')),
            // nessuna specifica per questo decoratore, perché non c'è Description
            array('Description', array('tag' => 'label', 'class' => 'description', 'style' => 'padding-left:4px;font-size:11px;color:#555555', 'placement' => 'append')),
            // decoratore Label: in un <p>, perciò block
            array( 'Label', array('tag' => 'p', 'escape' => false, 'style' => 'color:#555555' ) ),
            // si definisce un contenitore <p> di cui si potrà personalizzare lo stile
            array( array( 'containerP' => 'HtmlTag'), array('tag' => 'div', 'style' => 'padding-bottom:0px' ))
        );

        $notEmptyVal = new Zend_Validate_NotEmpty();
        $notEmptyVal->setMessage("&Egrave; necessario inserire un valore nel campo", Zend_Validate_NotEmpty::IS_EMPTY);

        // definisce il form
        $this->setMethod('post');
        $this->setAttrib('id', 'registra');

        $nome = $this->createElement('text', 'nome');
        $nome->setLabel('Nome *');
        $nome->setRequired(TRUE);
        $nome->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $nome->addValidator($notEmptyVal, true);
        $nome->setAttrib('class', 'textbox_2');
        $nome->setDecorators($textDecorators);

        $cognome = $this->createElement('text', 'cognome');
        $cognome->setLabel('Cognome *');
        $cognome->setRequired(TRUE);
        $cognome->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $cognome->addValidator($notEmptyVal, true);
        $cognome->setAttrib('class', 'textbox_2');
        $cognome->setDecorators($textDecorators);

        $username = $this->createElement('text', 'username');
        $username->setLabel('E-mail *');
        $username->setDescription("Questo indirizzo sarà usato come nome utente per i futuri accessi");
        $username->setRequired(TRUE);
        $username->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $username->addValidator($notEmptyVal, true);
        $username->addValidator('EmailAddress');
        $username->getValidator('EmailAddress')->setMessage("Inserire un indirizzo e-mail valido");
        $username->setAttrib('class', 'textbox_2');
        $username->setDecorators($textDecorators);

        $password = $this->createElement('password', 'password');
        $password->setLabel('Password *');
        $password->setDescription('Minimo 8, massimo 20 caratteri, solo lettere e numeri');
        $password->setRequired(TRUE);
        $password->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $password->addValidator($notEmptyVal, true);
        $password->addValidator('regex', false, array('/^[a-zA-Z0-9_]*$/'));
        $password->getValidator('regex')->setMessage("Sono ammessi solo lettere, numeri o underscore");
        $password->addValidator('StringLength', false, array(8, 20));
        $password->getValidator('StringLength')->setMessage("La password deve contenere da 8 a 20 caratteri");
        $password->setAttrib('class', 'textbox_2');
        $password->setDecorators($textDecorators);

        $password1 = $this->createElement('password', 'password1');
        $password1->setLabel('Reinserisci password *');
        $password1->setRequired(TRUE);
        $password1->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $password1->addValidator($notEmptyVal, true);
        $password1->addValidator('regex', false, array('/^[a-zA-Z0-9_]*$/'));
        $password1->getValidator('regex')->setMessage("Sono ammessi solo lettere, numeri o underscore");
        $password1->addValidator('StringLength', false, array(8, 20));
        $password1->getValidator('StringLength')->setMessage("La password deve contenere da 8 a 20 caratteri");
        $password1->addValidator('identical', true, array('token' => 'password'));
        $password1->getValidator('identical')->setMessage("Le password inserite non si corrispondono");
        $password1->setAttrib('class', 'textbox_2');
        $password1->setDecorators($textDecorators);

        $istituzione = $this->createElement('text', 'istituzione');
        $istituzione->setLabel('Istituzione');
        $istituzione->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $istituzione->setAttrib('class', 'textbox_2');
        $istituzione->setDecorators($textDecorators);

        $this->addElements(array($nome, $cognome, $username, $password, $password1, $istituzione));

        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel("Registra");
        $submit->setAttrib('onclick', 'waitOn()');
        $submit->setAttrib('class', 'pulsante grande');

        $submit->setDecorators(array(
            'ViewHelper',
            array( array( 'containerP' => 'HtmlTag' ), array('tag' => 'p') ),
            array( array('row' => 'HtmlTag'), array('tag' => 'div', 'style' => 'padding: 10px 14px 0px 4px; ' ))));

        $this->addElement($submit);

    
    }

}

