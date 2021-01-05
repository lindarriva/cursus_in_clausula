# cursus_in_clausula
archivio di materiali tecnico-informatici per l'applicazione/sito Cursus in Clausula - progetto finanziato su  FIRB 2008, Università di Udine; principal investigator: Emanuela Colombi

## overview

Il sito è sviluppato nell'ambiente di sviluppo integrato (IDE) NEtBeans e si basa sul modello MVC (Model View Controller) sfruttando Zend Framework. Il lato server di elaborazione dati e dialogo con il database MySql è completamente gestito in PHP 5.5 (upgrade a PHP 7.4 in dicembre 2020); per uno sviluppo maggiormente interoperabile e più friendly del lato client si è fatto ricorso al toolkit Dojo (pacchetto di librerie di configurazione e script Java che assicurano la compatibilità tra browser, a partire da Internet Explorer 8).

Fondamentale settare come impostazione generale di codifica: "UTF-8" 

### DB 
CURSUS è costituito da 12 tabelle.

5 per la gestione di utenti e dati:
 - utenti
 - testi: gestione tabella archivio di ogni singolo utente
 - chiquadro: tabella per calcolo chi
 - analisi: immagazzina le clausole scandite nelle varie tipologie: quant_orlandi, rit_standard, cursus
 - singulariter: per le ricerche singole
 
7 tabelle per la gestione del vocabolario: 
 - semivocali
 - enclitiche
 - nondittonghi
 - desinenze
 - desinenze_modelli
 - temi
 - lemmi.

### APPLICATION
#### TESTI

__View: testi.phtml__
è la pagina "archivio testi", dove l'utente può:
	- caricare un nuovo testo e visualizzarlo nella tabella sinistra (testiController>testiAction)
	- modificare il testo, il titolo, la codifica
	- eliminare il file
	- controllare il report degli errori
  
__TestiController.php__
ha tre action principali e pubbliche: pdfAction // testiAction // ajaxAction
public function testiAction(): - 	fa l'upload del file e creaGrigliaTesti 
				-	salva il file nel db tabella: testi, 
				-	fa l'analisi del testo e riempie la db tabella: analisi (private function: eseguiAnalisi, che a sua volta chiama la private *function analisi*).

public function ajaxAction(): 
									-mostraTesto
									-mostraReport
									-cambiaEncoding
									-salvaUTF
									-eliminaTesto
									-eliminaTutto
									-apriTesto
									-modifica
									-salva
NB: azione chiamata in view analisi	-cambia impostazioni dell'analisi (con sinalefe/con monosillabi)
																									
La __*function analisi*__ è complessa ed è il cuore delle successive operazioni!!
Prende come parametro d'entrata $idTesti con il quale si interfaccia direttamente con il testo.
Dopo aver caricato il file (chiamata "eseguiAnalisi","salva", "salvaUTF","cambiaEncoding"; mentre "analisi"  in "cambiaImpostazioni", "mostraMonosillabi" ) e letto il file inizia la vera e propria analisi: 
	- riconosce i segmenti delimitati da ". ? !" e scarta i casi di sigle (="maiuscola." oppure "maiuscolaminuscola.") sostituendo il punto con @ (es. "C." -> "C@ ")
  - ???????	controllo su caratteri >5	
	- riempimento della tabella analisi del DB cursus grazie all'utilizzo delle classi(models:Analisi) *Segmento - Parola - Sillaba* per riempire i vari campi del db
	

	* per prima cosa ricorro all'oggetto: __Model_Segmento.php__
	1. preleva solo le parole utili a contare almeno 8 sillabe; 
	2. controllo se esiste sinalefe: in Segmento.php se sillaba è "S", allora annullo la quantità!
	non considera i segmenti che abbiano meno di 6 sillabe.
 ++++++++++++++++++++++++++++++++++++++++++++++++
	* passo all'analisi delle parole, usando l'oggetto: __Model_Parola.php__
	1. controllo enclitiche (smonto e rimonto)
	2. controllo parole nel vocabolario; 
	3. tengo traccia di omografi e doppie possibilità di scansione: riporto nel log (livello di errore) - le risolvo casualmente dal vocabolario, tranne i casi di maiuscola, messi in subordine.
	4. quelle non trovate nel vocabolario sono processate ed eventualmente risolte con divisione in sillabe
	5. divisione in sillabe (*procedura complessa: 2 cicli for: $j e $i*)
	6. assegnamento quantità
	7. assegnamento ritmo
	* manipolazione dell'oggetto: __Model_Sillaba.php__
	1. traduco le quantità in sequenze Orlandi
	2. traduco le sequenze in ritmo e cursus
	...andando a riempire i vari campi della tabella analisi
	
Quindi la *function analisi* si occupa anche di controllare il report degli errori/modifiche/consigli di marcatura all'utente, riempiendo ad hoc la tabella cursus.testi (dove si contano le parole, il titolo, la sigla, il log...).

#### ANALISI
__view: Analisi.phtml__
analisi di 4 tipologie: 
            1. generale, generica (tabella in pagina a sè)
						pagina con le 4 proposte diversificate:	
						2. ritmo (tipo: n, np)
						3. cursus (tipo: òooòo=planus)
						4. orlandi


__ANALISI ORLANDI__
è strutturata per considerare gli incontri tra vocali di parole diverse come iato
TUTTAVIA, abbiamo mantenuto ugualmente la stessa tipologia di calcoli anche in caso di conteggio prosodico con sinalefe presupponendo lo cesura tra parole prima delle sillabe interessate da sinalefe: perciò DESIDERIUM APERIRE sarà visualizzato in tabella ORLANDI come ++----+* (e sarà assimilato ai casi ++--|--+* per il calcolo delle frequenze osservate/attese) e in tabella CURSUS come xooooxo (velox) -- considerando la stessa clausola senza sinalefe otterremo invece:
---|--+* , ovviamente la "traduzione" in cursus resterà invariata: velox


******
RCD il prefisso fisso "EVItesti" è un parametro della Grid, o meglio un elemento (INPUT; TYPE: hidden; ID: [la riga associata, di solito il nome della grid]) utile a richiamare l'oggetto/GRID specificato dall'id.
 	

