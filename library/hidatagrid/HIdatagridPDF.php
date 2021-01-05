<?php
/* 
Genera una griglia del tipo DataGrid per il PDF.
Argomenti del costruttore:
$title		Stringa; titolo della griglia che compare nell'head; facoltativo ("");
$colsHead	Array di stringhe; titoli delle colonne; facoltativo (array());
$colsWidth	Array di numeri; larghezza delle colonne in pixel: obbligatorio, perché definisce anche il numero delle colonne;
$options	opzionale; è array che può contenere più opzioni, identeficate dalla chiave; al momento non c'è implementazione.

Le fasi per l'impiego della classe sono 4:

1) istanziazione: esempio:

	$grid = new HIdatagrid(
		array("Autore","Titolo","Editore"),
		array(200,100,100)
		);

2) impostazioni facoltative:		

	setRowHeight(Numero)	imposta l'altezza delle righe (default 30px);
	setNoWrap(Booleano)		definisce se è inibito l'a capo dentro le celle (default false); se posto a true, attenzione a consentire larghezze sufficienti alle celle,
							altrimenti potrebbero verificarsi disallineamenti dei titoli delle colonne;
		
3) implementazione delle singole righe, con il metodo appendRow(id, valori); esempio:

	$grid->appendRow("row1",array("Ovidius","Ex Ponto"));
	$grid->appendRow("row2",array("Martialis","Epigrammata 1"));
	$grid->appendRow("row3",array("Vergilius","Georgicon"));

4) stampa della griglia, mediante il metodo render():

	$grid->render();

Prima della terza fase possono essere modificate le precedenti o altre impostazioni; ad esempio l'altezza delle righe mediante setRowHeight().

GESTIONE DELLA PRODUZIONE DI TABELLE IN PDF

1) Generare la griglia a video con la classe HIdatagrid, con opzione setPDF(true); assegnare un nome alla griglia, se necessario (più griglie nella stessa pagina);
2) nella view scrivere il metodo Javascript HIPDF_eventuale_nome(ordine); <ordine>, che può essere vuoto, contiene la colonna su cui è fatto l'ordine (base 0) e il
simbolo A o D che indica il verso;
3) creare una view specifica per il PDF, priva del layout ($this->_helper->layout->disableLayout() nel controller) e che instanzia un oggetto di classe html2pdf_v5.02,
che produrrà un PDF con il contenuto della pagina:

<?php require_once('html2pdf_v5.02/html2pdf.class.php');
        $content = ob_get_clean();
        $pdf = new HTML2PDF('P','A4','fr', false, 'ISO-8859-15', array(10, 10, 10, 10));
        $pdf->writeHTML($content);
        $pdf->Output('miofile.pdf', 'D');
?>

4) la sequenza degli eventi sarà la seguente:
	- clic sul pulsante PDF della griglia
	- chiamata del metodo HIPDF(ordine) nella view
	- il metodo HIPDF chiama la view per il PDF con un comando del tipo:
	
		location.href="<?php echo $this->baseUrl()?>/controller/viewpdf/ordine/" + ordine;
	
	- la Action del controller produce la griglia con HIdatagridPDF, nell'ordine indicato dall'argomento <ordine>
	- nella view per il PDF l'oggetto html2pdf_v5.02 produce il PDF e lo fa scaricare


*/
class HIdatagridPDF{
	private $width = 0;
	private $title = "";
	private $colsHead = array();
	private $colsWidth = array();
	private $biColor = true;
	private $rowHeight = 0;
	private $rows = array();
	private $noWrap = false;
	private $result = "";
	private $width100 = false;
	private $colsAlign = array();
	private $colsIndexes = array();
	
	
	function __construct($title, $colsHead, $colsWidth, $options = array()){	
		$this->title = iconv('UTF-8', "ISO-8859-1" , $title);
		$this->colsHead = $colsHead;
		foreach($this->colsHead as &$value){
			$value = iconv('UTF-8', "ISO-8859-1" , $value);
		}
		
		
		$this->colsWidth = $colsWidth;
		// definisce l'allineamento di default delle celle
		foreach($this->colsWidth as $col){
			$this->colsAlign[] = "left";
			$this->colsIndexes[] = "";	// indici sulle colonne vuoti
		}
		
		// gestione delle opzioni
		foreach($options as $key => $value){
		}
	}
	public function appendRow($id, $array, $opzioni = array()){
		// Aggiunge i volori della singola riga:
		// $id	= id della riga
		// $array = i valori delle singole celle
		// $opzioni = varie		
		$this->rows[] = array("id" => $id, "values" => $array, "options" => $opzioni);
	}
	
	public function render(){	
		// E' obbligatorio definire l'argomento $colsWidth, perché dà il numero delle colonne,
		// in assenza di definizione di $colsHead;
		// Si calcola la larghezza della griglia sommando le colonne
		$this->width = 0;
		$whiteSpace = "";
		if($this->noWrap == true){
			$whiteSpace = "white-space: nowrap;";	
		}		
		foreach($this->colsWidth as $n){
			$this->width += $n;
		}
		// costruzione cornice
		echo "<div align=\"center\" style='";
		echo $whiteSpace;
		echo "'>\n";
		
		// opzione allineamento personalizzato orizzontale e altre opzioni
		$ct = 0;
		foreach($this->rows as $row){
			foreach($row["options"] as $key => $option){
				if($key == "colsAlign"){
					for($i = 0; $i < count($option); $i ++){
						$this->colsAlign[$i] = $option[$i];
					}				
				}
			}
			$ct ++;
		}		
		
		// costruzione tabella			
		echo "<table cellspacing=\"0\" cellpadding=\"0\" style=\"border-collapse:collapse\">\n";
		if(!empty($this->title)){
			echo "<tr><th colspan=\"" . count($this->colsHead) . "\" style='border:1px solid black;";
			echo "padding:5px;background-color:#edf3fe'>\n" . $this->title;
			echo "</th></tr>\n";
		}
		$stringa = "";		
		foreach($this->colsHead as $label){
			$stringa .= $label;	
		}
		if(!empty($stringa)){
			// la riga di intestazione non si costruisce se le etichette sono vuote
			echo "<tr>\n";
			$ct = 0;
			foreach($this->colsHead as $label){
				echo "<th style='text-align:" . $this->colsAlign[$ct];
				echo "; border: 1px solid black;padding:5px;background-color:#edf3fe;" . $whiteSpace . "'>";
				echo $label;
				echo "</th>\n";		
				$ct ++;
			}
			echo "</tr>\n";
		}
		
		// scrittura righe
		$ctt = 0;
		$colore = false;
		
		foreach($this->rows as $row){
			echo "<tr ";
			echo "'";
			// si imposta la l'altezza delle righe (solo per $this->rowHeight > 0)
			if($this->rowHeight > 0){
				echo " style='height:" . $this->rowHeight . "px'";
			}
			echo ">";
			$ct = 0;
			foreach($row["values"] as $cell){
				echo "<td style='";
				if(count($this->colsWidth) > 0){
					echo "width:" . $this->colsWidth[$ct] . "px;";
				}
				echo "border: 1px solid black;padding:5px;" . $whiteSpace;
				//if($this->biColor == true && $colore == true) echo " background-color:#edf3fe;";
				echo "text-align:" . $this->colsAlign[$ct] . "'>";
				echo iconv('UTF-8', "ISO-8859-1" , $cell);
				echo "</td>\n";		
				$ct ++;
			}
			echo "</tr>\n";
			$ctt ++;
			$colore = !$colore;
		}
		echo "</table>\n</div>\n";
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
	public function setBiColor($biColor){
		$this->biColor = $biColor;
	}	
	public function setName($name = ""){
		$this->gridName = $name;
	}
	
	public function setRowHeight($rowHeight){
		$this->rowHeight = $rowHeight;
	}
	public function setNoWrap($noWrap){
		$this->noWrap = $noWrap;
	}




}
?>