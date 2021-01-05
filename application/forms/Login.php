<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        // definisce il form
        $this->setMethod('post');
        $this->setAttrib('id', 'loginForm');


        // crea un elemento <input type="text"> di nome 'username'
        $username = $this->createElement('text', 'username');
        // assegna un etichetta al campo
        $username->setLabel('E-mail');
        // campo descrizione, che di default è posizionato dopo il campo, ma che può essere posizionato prima con 'placement' => 'prepend'
        //$username->setDescription('Campo obbligatorio');
        // qualifica il campo come obbligatorio
        $username->setRequired(TRUE);

        // imposta filtri e validatori
        $username->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $username->addFilter('StripTags');

        // imposta validatori
        // Per ottenere un messaggio personalizzato nel caso di campo vuoto, si adotta questa soluzione:
        // - si crea un validatore specifico e gli assegna il messaggio;
        // - attenzione: aggiungerlo per primo, altrimenti compaiono anche i messaggi successivi;
        // - se non si definisce questo validatore, il campo vuoto produce il messaggio di default in inglese
        $notEmptyVal = new Zend_Validate_NotEmpty();
        $notEmptyVal->setMessage("&Egrave; necessario inserire un valore nel campo", Zend_Validate_NotEmpty::IS_EMPTY);
        $username->addValidator($notEmptyVal, true);

        $username->addValidator('EmailAddress', true);
        $username->getValidator('EmailAddress')->setMessage("Non è un indirizzo e-mail valido");

        // si assegna un attributo al tag (class, style ecc.)
        $username->setAttrib('class', 'textbox_2');

        // I decoratori servono a personalizzare l'elemento
        // Ogni decorator è un array che contiene:
        // - 'ViewHelper': se non si inserisce l'elemento non è mostrato
        // - un array che ha come primo item 'Errors' e come secondo un array con una serie di proprietà, tra cui style
        //   attenzione: se manca questo array gli errori non compaiono!
        // - un array che ha come primo item 'Label' e come secondo un array con una serie di proprietà
        //   attenzione: se manca questo array la label non compare!
        // - n array per contenere l'elemento in un tag html; ciascuno è formato da:
        //   - un array con un solo elemento che ha come chiave un nome e come valore 'HtmlTag'
        //   - un array di coppie chiave valore che ne definiscono le caratteristiche
        // Referenze sui decoratori:
        // - http://www.sergiorinaudo.com/creare-un-form-con-zend-form-decorator-pattern-e-display-group-con-esempio-completo-form-di-registrazione-utente/
        // - http://wiip.fr/content/les-decorateurs-zend-form

        // decoratori standard per gli elementi text
        $textDecorators = array(
            'ViewHelper',
            // colore rosso per gli errori
            array('Errors', array('escape' => false, 'placement' => 'append', 'class' => 'messaggio', 'style' => 'padding-top:5px;padding-left:10px')),
            // nessuna specifica per questo decoratore, perché non c'è Description
            'Description',
            // decoratore Label: in un <p>, perciò block
            array( 'Label', array( 'tag' => 'p', 'escape' => false,   'style' => 'color:#555555; padding:3px; ' ) ),
            // si definisce un contenitore <p> di cui si potrà personalizzare lo stile
            array( array( 'containerP' => 'HtmlTag'), array('tag' => 'div', 'style' => 'padding: 0px 4px 0px 4px;' ))
        );
        // assegno i decoratori standard a username
        $username->setDecorators($textDecorators);
        $username->getDecorator('containerP')->setOption('style', 'margin-bottom:7px');
        // crea un elemento <input type="password"> di nome 'password'
        $password = $this->createElement('password', 'password');
        $password->setLabel('Password');
        $password->setRequired(TRUE);
        $password->addFilter('StringTrim');   // toglie spazi bianchi prima e dopo
        $password->addValidator($notEmptyVal, true);
        $password->setAttrib('class', 'textbox_2');

        // assegno i decoratori standard a password
        $password->setDecorators($textDecorators);
        // modifico lo style del contenitore
        $password->getDecorator('containerP')->setOption('style', 'margin-bottom:7px');

        $submit = $this->createElement('submit', 'submit');
        $submit->setLabel("Login");
        $submit->setAttrib('class', 'pulsante grande');
        $submit->setDecorators(array('ViewHelper'));    // tolgo tutti i decoratori, così si allinea col pulsante seguente

        $registrati = $this->createElement('button', 'registrati');
        $registrati->setLabel("Registrazione");
        $registrati->setAttrib('class', 'pulsante grande');
        $registrati->setAttrib('onclick', 'registra()');
        $registrati->setDecorators(array('ViewHelper')); // tolgo tutti i decoratori, così si allinea col pulsante seguente

        
       

// si aggiunge l'elemento al form
        $this->addElements(array($username, $password, $submit, $registrati));
    }


}

