<?php
/* 
Genera una griglia del tipo DataGrid per il PDF
E' necessario che siano linkati i files HIdatagrid.css e HIdatagrid.js.
Argomenti del costruttore:
$name		Identifica la griglia in presenza di più griglie nella stessa pagina, dando nomi differenti alle funzioni chiamate dagli eventi per il click sulla riga o per l'ordinamento delle colonne; si può lasciare vuote se non ci sono azioni oppure se nella pagina c'è una sola griglia			
$title		Stringa; titolo della griglia che compare nell'head; facoltativo ("");
$colsHead	Array di stringhe; titoli delle colonne; facoltativo (array());
$colsWidth	Array di numeri; larghezza delle colonne in pixel: obbligatorio, perché definisce anche il numero delle colonne;
$height		Numerico; altezza della griglia; facoltativo (0): se è lasciato a 0 la griglia è tanto lunga quante sono le righe;
			se è definito, invece, la griglia ha altezza fissa e si attiva se necessario la barra di scroll;
$options	opzionale; è array che può contenere più opzioni, identeficate dalla chiave; al momento sono implementate:

"colsIndex": indici per le singole colonne; deve essere un array con tanti elementi quante sono le colonne; 
"dojo-help": pulsante per l'help; è una stringa con l'id del div che contiene il testo dell'help;
"buttons": pulsanti di comando che si posizionano sulla barra del titolo; è un array con un elemento per ciascun pulsante;
		   ogni elemento è un array con due elementi: 
		   		"label" che contiene l'etichetta del pulsante;
		   		"action" che contiene la chiamata a una funzione Javascript chiamata quando il pulsante è cliccato


Le fasi per l'impiego della classe sono 4:

1) istanziazione: esempio:

	$grid = new HIdatagrid(
		"Opere", 
		array("Autore","Titolo","Editore"),
		array(200,100,100),
		300,
		array("colsIndex" => array("D", "A", ""))
		);

2) impostazioni facoltative:		

	setRowHeight(Numero)	imposta l'altezza delle righe (default 30px);
	setNoWrap(Booleano)		definisce se è inibito l'a capo dentro le celle (default false); se posto a true, attenzione a consentire larghezze sufficienti alle celle,
							altrimenti potrebbero verificarsi disallineamenti dei titoli delle colonne;
	setHightLight(Booleano)	definisce se le righe devono essere evidenziate al passaggio del mouse (default true);
	setBiColor(Booleano)	definisce se le righe devono essere di due colori (default true);
	setName(String)			cambia il nome della griglia assegnato dal costruttore;
	setHeight(Numero)		cambia l'altezza della griglia assegnata dal costruttore;
	setTitle(Stringa)		cambia il titolo assegnato dal costruttore;
	setColsHead(Array)		cambia le intestazioni delle colonne assegnate dal costruttore; 
	setColsWidth(Array)		cambia le larghezze delle colonne assegnate dal costruttore;
	setWidth100($value)		forza la griglia ad occupare tutta la larghezza disponibile;
	setRowSelection(Bool.)	attiva la selezione delle righe; è indispensabile dare o (aver dato con il costruttore) 
							un nome alla griglia, in maniera che non si creino interferenze tra tabelle diverse 
							(però se non c'è problema di interferenza si può lasciare il nome vuoto); 
							è indispensabile il link al file /public/js/HIdatagrid.js;	
	setSelectedId(Stringa)	preseleziona una riga al momento dell'apertura;	l'argomento è l'id della riga che va selezionata;
	setAction(Stringa);		definisce una o più azioni Javascript della riga; (default "");
							se setSelection(nomeTabella) è attivato, si crea un azione onclick="evidenzia(this, nomeTabella)";
							attenzione: poiché lo script 'evidenzia', se inserito in questa classe, non sarebbe presente 
							in casi di azione ajax, si è deciso di collocarlo esternamente nel file /public/js/Hidatagrid.js,
							preferibilmente da linkare in layout.php
	setPDF(Booleano)		compare un pulsante nella barra del titolo; il clic sul pulsante genera l'evento onclick="HIPDF_nome_griglia(ordine)" 
							che catturato potrà permettere di produrre una griglia per PDF con gli stessi dati e lo stesso ordine;
							l'argomento 'ordine' contiene il numero della colonna ordinata e il verso, nella forma 0A, 0D, 1A ecc.					
		
3) implementazione delle singole righe, con il metodo appendRow(id, valori, [opzioni]); esempio:

	$grid->appendRow("row1",array("Ovidius","Ex Ponto"));
	$grid->appendRow("row2",array("Martialis","Epigrammata 1"));
	$grid->appendRow("row3",array("Vergilius","Georgicon", "colsAlign" => array("center", "center")));
	
	opzioni attualmente implementate:
	- "colsAlign": un array con un attributo css di allineamento (left, center, right) per ciascuna colonna;  
	- "selected": un valore booleano che, se vero, evidenzia la riga al momento della creazione della griglia
	

4) stampa della griglia, mediante il metodo render():

	$grid->render();

Prima della terza fase possono essere modificate le precedenti o altre impostazioni; ad esempio l'altezza delle righe mediante setRowHeight().

ORDINAMENTO SU COLONNE

Si imposta nel costruttore mediante un elemento nell'array $options di chiave "colsIndex"; che deve essere a sua volta un array formato da tanti elementi quante sono le colonne, ciascuno dei quali informa su quale deve essere l'effetto del click sull'intestazione della relativa colonna; i valori ammessi sono:

A = ordinare in modo crescente
D = ordinare in modo decrescente
empty = l'intestazione non è cliccabile

Ad esempio, se l'argomento $options è:

array("colsIndex" => array("D", "A", ""))

l'indicazione è di rendere cliccabili le intestazioni delle prime due colonne e non della terza; l'effetto del clic sulla seconda colonna sarà di ottenere un ordinamento crescente su di essa, mentre il clic sulla prima produrrà su questa un ordine decrescente; questa impostazione è sensata se la tabella viene presentata inizialmente ordinata in modo crescente sulla prima colonna.

La produzione dei dati ordinati non è a carico della classe HIdatagrid, che si limita a rendere cliccabili le intestazioni delle colonne e a generare gli eventi relativi; perciò sono necessari:

1) nella view la funzione javascript HIindex(ordine) a cui invia il clic;
2) una procedura php nel controller che riceva via ajax dalla precedente le indicazioni e generi una nuova HIdatagrid con i dati ordinati nel modo richiesto.

1) Funzione javascript HIindex+gridName(ordine): l'argomento 'ordine' contiene le informazioni sull'ordine richiesto ed è una stringa di due caratteri, un numero e una lettera:
- il numero indica la colonna da ordinare (in base 0);
- la lettera indica il tipo di ordinamento (A = ascendente D = discendente).
La funzione passerà via ajax queste informazioni al controller.

2) Controller: otterrà dal database i dati ordinati nel modo richiesto e costruirà la griglia inserendo nell'array "colsIndex" tutti valori A, tranne quello della colonna su cui è fatto l'ordinamento, che sarà invertito rispetto a quello ricevuto dalla view; ad esempio se la view ha richiesto l'ordinamento ascendente della seconda colonna (il che significa che nella funzione javascript HIindex(ordine) l'argomento 'ordine' aveva valore 1A), in "colsIndex" l'elemento relativo alla seconda colonna dovrà avere valore D; e viceversa.


*/
class HIdatagrid{
	private $gridName = ""; 
	private $selection = false;
	// selectedId è l'id della riga eventualmente già selezionata in apertura (in genere in riapertura)
	private $selectedId = "";
	private $width = 0;
	private $height = 0;
	private $title = "";
	private $colsHead = array();
	private $colsWidth = array();
	private $hightLight = true;
	private $biColor = true;
	private $action = array();	
	private $javascript = array();
	private $rowHeight = 0;
	private $rows = array();
	private $scroll = false;
	private $noWrap = false;
	private $result = "";
	private $scrollBarWidth = 15;
	private $width100 = false;
	private $colsAlign = array();
	private $isLink = false;
	private $colsIndexes = array();
	private $colIndex = "";
	private $nColIndex = 0;
	private $pdf = false;
	private $idDojoHelp = "";
	private $buttons = array();
	private $language = "it";
	private $frame = true;
	
	function __construct($name, $title, $colsHead, $colsWidth, $height, $options = array()){	
		$this->gridName = $name; 
		$this->title = $title;
		$this->colsHead = $colsHead;
		$this->colsWidth = $colsWidth;
		$this->height = $height;
		// definisce l'allineamento di default delle celle
		foreach($this->colsWidth as $col){
			$this->colsAlign[] = "left";
			$this->colsIndexes[] = "";	// indici sulle colonne vuoti
		}
		
		// gestione delle opzioni
		
		foreach($options as $key => $value){
			if($key == "colsIndex"){
				$this->colsIndexes = $value;
				// indici per le singole colonne; deve essere un array con tanti elementi quante sono le colonne; 
				// ciascuno avrà un valore che indicherà l'effetto del clic sull'intestazione della colonna:
				// empty = nessun effetto
				// A = indicizzare in modo ascendente su questa colonna
				// D = indicizzare in modo discendente su questa colonna
				// Per rendere efficace questa opzione è necessario che pagina che contiene la griglia abbia la funzione javascript:
				// HIdatagridIndex(value)
				// che dovrà gestirne l'esecuzione
				
				// crea una stringa che identifica la colonna su cui è fatto l'ordinamento
				$k = false;
				foreach($this->colsIndexes as $order => &$col){
					if(substr($col, 0, 1) == "*"){
						// colonna su cui è fatto l'ordinamento
						$col = substr($col, 1);
						$this->nColIndex = $order;
						if($col == "D"){
							$this->colIndex = $order . "A";
						}else{
							$this->colIndex = $order . "D";
						}						
					}
				}
			}else if($key == "dojo-help"){
				$this->idDojoHelp = $value;
			}else if($key == "buttons"){
				$this->buttons = $value;
			}
		}
	}
	public function appendRow($id, $array, $opzioni = array()){
		// Aggiunge i volori della singola riga:
		// $id	= id della riga
		// $array = i valori delle singole celle
		// $opzioni = attualmente implementate:
		// "colsAlign" un array con un attributo css di allineamento (left, center, right) per ciascuna colonna;  
		// "selected" un valore booleano che, se vero, evidenzia la riga al momento della creazione della griglia
				
		$this->rows[] = array("id" => $id, "values" => $array, "options" => $opzioni);
	}
	
	public function render(){	
		// E' obbligatorio definire l'argomento $colsWidth, perché dà il numero delle colonne,
		// in assenza di definizione di $colsHead;
		// Si calcola la larghezza della griglia sommando le colonne
		$this->width = 0;
		foreach($this->colsWidth as $n){
			$this->width += $n;
		}
		// correttivo per la cornice 
		$this->width += 40;	
		// se l'argomento $height è > 0, allora la tabella ha altezza definita con scroll verticale
		if($this->height > 0){
			$this->scroll = true;
			// si verifica se la barra di scroll è superflua, perché le righe sono poche			
			$h = 30;
			if($this->rowHeight > 0) $h = $this->rowHeight;
			$n = $h * count($this->rows);
			if($n < $this->height){
				$this->scroll = false;
				$this->height = 0;
			}else{
				// correttivo in larghezza per lo scroll
				$this->width += $this->scrollBarWidth;				
			}
		}
		// Azioni javascript: 
		// prima di tutto si verifica se è impostata la selezione per riga
		if($this->selection == true){
			$this->javascript[0] = "onclick='evidenzia(this, \"" . $this->gridName . "\", #####)";
			foreach($this->action as $key => $valore){
				if(strtolower($key) == "onclick"){
					$this->javascript[0] .= ";" . $valore;
					unset($this->action[$key]);
				}
			}
			$this->javascript[0] .= "'";
		}
		// altre eventuali azioni javascript diverse da onclick
		foreach($this->action as $key => $valore){
			$this->javascript[] = $key . "='" . $valore . "'";
		}
		
		// elemento hidden per la memorizzazione della riga selezionata
		$var = "<input type=\"hidden\" id=\"EVI" . $this->gridName . "\" value=\"";
		if(!empty($this->selectedId)){
			$var .= $this->gridName . $this->selectedId;
		}
		$var .= "\" >\n";
		echo $var;
		// costruzione cornice
		if($this->frame){
			echo "<div class='HIext' align=\"left\" style='";
			if($this->width100 == true){
				echo "width:100%;"; 
			}else if($this->width > 0){
				echo "width:" . $this->width . "px;"; 
			}
			if($this->noWrap == true){
				echo "white-space: nowrap;";	
			}
			echo "'>\n";
			echo "<div class='HIhead'>\n<b>" . $this->title . "</b>";
		
			foreach($this->buttons as $button){
				echo "&nbsp;&nbsp;&nbsp;<input type=\"button\" class=\"HI\" value=\"" . $button["label"] . "\" onclick=\"" . $button["action"] . "\"/>";
			}
		
			if($this->pdf){
				$stringa = $this->gridName;
				if(!empty($stringa)) $stringa = "_" . $stringa;
				//echo "&nbsp;&nbsp;&nbsp;<button data-dojo-type=\"dijit/form/Button\" type=\"button\"";
            	//echo " data-dojo-props=\"iconClass:'pdfIcon'\" id=\"" . $this->gridName . "PDF\"";
            	//echo " onclick=\"HIPDF" . $stringa . "('";
				//echo $this->colIndex . "')\"";
    	        //echo " showLabel=\"false\">Scarica il PDF</button>";
						
				echo "&nbsp;&nbsp;&nbsp;<input type=\"button\" class=\"HI\" value=\"PDF\" onclick=\"HIPDF" . $stringa . "('";
				echo $this->colIndex . "')\"/>";			
			}
		
			if(!empty($this->idDojoHelp)){
				echo "&nbsp;<div id=\"" . $this->idDojoHelp . "\" class=\"help\"></div>\n";
			}
			echo "</div>\n";
		}
		
		echo "<div class='HIbody";
		if(!$this->frame) echo " noFrame" ;
		echo "'>\n";
		
		// opzione allineamento personalizzato orizzontale e altre opzioni
		$ct = 0;
		foreach($this->rows as $row){
			foreach($row["options"] as $key => $option){
				if($key == "colsAlign"){
					for($i = 0; $i < count($option); $i ++){
						$this->colsAlign[$i] = $option[$i];
					}				
				}else if($key == "selected"){
					// riga selezionata
					if($option == true){
						$this->selectedRow = $ct;
					}
				}
			}
			$ct ++;
		}		
		
		// costruzione head fisso
		$stringa = "";		
		foreach($this->colsHead as $label){
			$stringa .= $label;	
		}
		if(!empty($stringa)){
			// la riga di intestazione non si costruisce se le etichette sono vuote
			echo "<div>\n";	
			echo "<table class='HIhead'>\n";
			echo "<tr class='HI'>\n";
			$ct = 0;
			foreach($this->colsHead as $key => $label){
				echo "<th class='HI";
				if($ct == count($this->colsHead) - 1){
					echo " HIlast";				
				}
				echo "' style='";
				// si imposta la larghezza delle colonne (obbligatoria)
				if(count($this->colsWidth) > 0){
					echo "width:";
					$n = $this->colsWidth[$ct];
					if($ct == count($this->colsHead) - 1 && $this->scroll == true) $n = $n + $this->scrollBarWidth;
					echo $n . "px;";
				}			
				echo " text-align:" . $this->colsAlign[$ct] . "'>\n";
				if(!empty($this->colsIndexes[$ct])){
					$stringa = $this->gridName;
					if(!empty($stringa)) $stringa = "_" . $stringa;
					echo "<span class=\"HIindex";
					if($key == $this->nColIndex){
						if($this->language == "it"){
							$t = "Inverti l'ordinamento";
						}else{
							$t = "Invert the order";
						}
						echo " HIscelto \" title=\"" . $t . "\"";
					}else{
						if($this->language == "it"){
							$t = "Ordina su questa colonna";
						}else{
							$t = "Order on this column";
						}
						echo "\" title=\"" . $t . "\"";
					}
					echo " onclick=\"HIindex" . $stringa . "('" . $ct . $this->colsIndexes[$ct] . "')\">";
					echo $label . "</span>";
				}else{
					echo $label . "\n";
				}
				echo "</th>\n";		
				$ct ++;
			}
			echo "</tr>\n";
			echo "</table>\n";
			echo "</div>\n";	
		}
		// costruzione tabella	
		echo "<div class='HIdivtable' style='";
		
		if(!$this->frame) echo "border-top-width: 1px;" ;
		
		if($this->height > 0){
			echo "height:" . $this->height . "px;"; 
		}
		if($this->scroll == false){
			echo "overflow:visible;";
		}		
		echo "'>\n";	
		echo "<table class='HI'>\n";
		
		// scrittura righe
		$ctt = 0;
		foreach($this->rows as $row){
			echo "<tr ";
			echo "id='" . $this->gridName . $row["id"] . "' ";
			$disabled = false;		
			if($this->selectedId != "" && $row["id"] == $this->selectedId){
				// riga preselezionata
				echo "class='evi' name=\"EVI" . $this->gridName . "\" ";			
			}else{			
				echo "class='HI";
				if($this->hightLight == true) echo " hl";		
				if($this->biColor == true) echo " biColor";
				$disabled = false;
				foreach($row["options"] as $key => $option){
					if($key == "disabled"){
						if($option == true){
							$disabled = $option;					
						}
					}
				}
				if($disabled === true) echo " disabled";	
				echo "'";
			}
			
			// si imposta la l'altezza delle righe (solo per $this->rowHeight > 0)
			if($this->rowHeight > 0){
				echo " style='height:" . $this->rowHeight . "px'";
			}
			
			if($disabled == false){
				foreach($this->javascript as $action){
					if($this->biColor && round($ctt / 2, 0) * 2 == $ctt){
						// riga dispari in griglia bicolore
						$action = str_replace("#####", "\"SCURO\"", $action);
					}else{
						$action = str_replace("#####", "\"\"", $action);
					} 
					echo " " . $action;
				}
			}			
			echo ">";
			$ct = 0;
			foreach($row["values"] as $cell){
				echo "<td class='HI";
				if($ct == count($row) - 1){
					//echo " last";				
				}
				if($this->isLink){
					echo " HIlink";				
				}
				echo "' style='";
				if(count($this->colsWidth) > 0){
					echo "width:" . $this->colsWidth[$ct] . "px;";
				}
				echo "text-align:" . $this->colsAlign[$ct] . "'>";
				echo $cell;
				echo "</td>\n";		
				$ct ++;
			}
			echo "</tr>\n";
			$ctt ++;
		}
		echo "</table>\n</div>\n</div>\n";
		if($this->frame){
			echo "</div>\n";
		}
	}
	

	public function setHeight($height){
		$this->height = $height;
	}
	public function setTitle($title){
		$this->title = $title;
	}
	public function setColsHead($colsHead){
		$this->colsHead = $colsHead;
	}
	public function setColsWidth($colsWidth){
		$this->colsWidth = $colsWidth;
	}
	public function setWidth100($value){
		$this->width100 = $value;
	}
	public function setHightLight($hightLight){
		$this->hightLight = $hightLight;
	}
	public function setBiColor($biColor){
		$this->biColor = $biColor;
	}	
	public function setAction($action){
		// l'argomento è un array in cui la chiave ha il metodo ("onclick", "onchange" ecc.)
		// mentre il valore ha l'azione
		$this->action = $action;
		$this->isLink = true;	// la riga è associata a un'azione, perciò il puntatore sarà 'mano'
	}
	public function setName($name = ""){
		$this->gridName = $name;
	}
	public function setRowSelection($value){
		// attiva la selezione delle righe; 
		// è indispensabile dare o (aver dato con il costruttore) un nome alla griglia, in maniera che
		// non si creino interferenze tra tabelle diverse (però se non c'è problema di interferenza si può lasciare il nome vuoto);
		// è indispensabile il link al file /public/js/HIdatagrid.js;
		$this->selection = $value;
		$this->isLink = $value;	// la riga è associata a un'azione, perciò il puntatore sarà 'mano'
	}
	public function setSelectedId($value){
		// preseleziona una riga al momento dell'apertura;
		if($value == 0) $value = "";
		$this->selectedId = $value;
	}
	
	public function setRowHeight($rowHeight){
		$this->rowHeight = $rowHeight;
	}
	public function setNoWrap($noWrap){
		$this->noWrap = $noWrap;
	}
	public function setPDF($pdf){
		$this->pdf = $pdf;
	}

	public function setLanguage($lingua){
		$this->language = $lingua;
	}
	public function setFrame($value){
		$this->frame = $value;
	}


}
?>