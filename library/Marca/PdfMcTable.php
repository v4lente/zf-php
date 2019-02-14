<?php
/**
 *
 * Classe Marca_PdfMcTable
 *
 * Esta classe tem como objetivo criar um relat�rio PDF 
 *
 * @category   Marca Sistemas
 * @package    Marca_PdfMcTable
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: PdfMcTable.php 0 2010-09-22 11:20:00 Marcio $
 */
class Marca_PdfMcTable extends FPDF {
    
    var $widths;
    var $aligns;
    
    /**
     * Construtor da classe
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4') {
        parent::FPDF($orientation, $unit, $format);
    }
    
    //////////////////////////////////////////
    // Fun��o para gerar o cabe�alho
    //////////////////////////////////////////
    function Header() {
        //Verifica se � para mostrar o cabecalho
        if(!$this->showHeader) return;
        //busca parametros para mostrar nome da empresa e o nome do sistema no cabecalho
        $PARAM = $_SESSION['PARAM'];
        $porto = $PARAM[0]['no_empr'];
        ////////     --> o comando abaixo n�o estava pegando o titulo traduzido
        //$tit_sistema = $PARAM[0]['tit_sistema'];
        ////////     --> solu��o: trazer a classe bd para ca e utilizar o metodo de tradu��o em tempo de execu��o
        //$bd = Zend_Registry::get("db");
        $tit_sistema = "Teste"; //$bd->traduz('tit_sistema');
        //Logo
        //coloca o logo no cabecalho (fun��o da classe principal)
        //Parametros
        //Nome do arquivo que cont�m a imagem. 
        //Abscissa do canto superior-esquerdo.
        //Ordenada do canto superio-esquerdo.
        //Largura da imagem na p�gina. Se n�o informada ou zero, ela ser� calculada automaticamente. 
        //Altura da imagem na p�gina. Se n�o informada ou zero, ele ser� calculada automaticamente.
        //Formato da imagem. Os poss�veis valores s�o (diferenciando mai�sculas e min�sculas): JPG, JPEG, PNG. Se n�o informado, o tipo ser� inferido pela extens�o do arquivo. 
        //URL ou identificador retornado pela fun��o AddLink(). 
        $this->Image('../public/images/logo_porto.jpg', 10, 10, 15);
        //Arial bold 10
        //Seta a fonte
        //Tipo
        //estilo
        //tamanho
        $this->SetFont('Arial', 'B', 10);
        //Move to the right
        //a funcao FLOOR e uma funcao do php que etorna o pr�ximo menor valor inteiro ao se arredondar para baixo o valor
        //ex: 4.55 = 4 ou 4.99 = 4
        //neste caso o $this->w indica o width da pagina
        $w = floor($this->w);
        //Calcula o tamanho do cabecalho
        $this->wCab = floor($w - ($this->lMargin + $this->rMargin));
        //??deve calcular o tamanho do texto que vai no cabecalho ???
        $this->wTextoCab  = ($w - 15);;
        $this->wTextoCab *= -1;
        //monta a borda do cabecalho    
        $this->Cell($this->wCab, 15, '', 1, 0, 'C');
        //Title
        //coloca o conteudo do cabecalho
        $this->Cell($this->wTextoCab, 5, $porto, 0, 2, 'C');
        $this->Cell($this->wTextoCab, 5, $PARAM[0]['no_rel'] . '  (Relat�rio '.$PARAM[0]['cd_rel'] . ')', 0, 2, 'C');
        //$this->Cell($this->wTextoCab,5,'(Relat�rio '.$GLOBALS['cd_rel'].')',0,2,'C');
        //Line break
        $this->Ln(10);
    
    }
    
    //////////////////////////////////////////
    // Fun��o para gerar o rodap�
    //////////////////////////////////////////
    function Footer() {
        if(!$this->showHeader) return;
        //Position at 1.5 cm from bottom
        $this->SetY(-15);
        //Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        //Data e numero da pagina
        $x = $this->GetX();
        $y = $this->GetY();
        $this->MultiCell(($this->wCab), 5, date('d/m/Y  G:i:s'), 0, 'L');
        //Put the position to the right of the cell
        $this->SetXY($x, $y);
        $this->MultiCell(($this->wCab), 5, $this->PageNo() . '/{nb}', 0, 'R');
    }
    
    //////////////////////////////////////////
    // Fun��o para setar larguras das colunas
    //////////////////////////////////////////
    function SetWidths($w) {
        //Set the array of column widths
        $this->widths = $w;
    }
    
    //////////////////////////////////////////
    // Fun��o para setar alinhamentos
    //////////////////////////////////////////
    function SetAligns($a) {
        //Set the array of column alignments
        $this->aligns = $a;
    }
    
    //////////////////////////////////////////
    // Fun��o para gerar HR (linha horizontal)
    //////////////////////////////////////////
    function HR($a = 0, $d = 0) {
        if($a != 0){
            $this->Ln($a);
        }
        $x = floor($this->w-($this->lMargin + $this->rMargin));
        $this->Line(0 + $this->lMargin, $this->GetY(), $x, $this->GetY());
        if($d != 0) {
            $this->Ln($d);
        }
    }
    
    //////////////////////////////////////////
    // Fun��o para gerar sa�da da informa��es de cada registro no PDF
    //////////////////////////////////////////
    function showRow($data, $lw = false, $lh = false, $cab = false) {
        //calcula a altura de cada linha
        $nb = 0;
        for($i = 0; $i < count($data); $i++) {
            if(isset($this->formatos))
                if(! $cab)
                    $data[$i] = $this->formataDados($this->formatos[$i], $data[$i]);
            
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        
        if(isset($this->espacejamento)) {
            if(strtoupper($this->espacejamento) == 'S')
                $this->espacejamento = 1;
            if(strtoupper($this->espacejamento) == 'D')
                $this->espacejamento = 2;
            if(strtoupper($this->espacejamento) == 'T')
                $this->espacejamento = 3;
        } else {
                $this->espacejamento = 1;
        }
        
        $h = 5.5 * $this->espacejamento * $nb;
        
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        
        for($i = 0; $i < count($data); $i++) {

            //Draw the cells of the row
            $w = $this->widths[$i];
            $a = $this->aligns[$i];

            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            
            //Draw the border
            $lw = ($lw) ? $w : 0;
            $lh = ($lh) ? $h * (-1) : 0;
            $this->SetFillColor(200, 200, 200);
            $this->Rect($x, ($y + $h), $lw, $lh, 'F');
            
            //Print the text
            if(isset($this->debug) && $this->debug == true) {
                echo ' :::: [' . $i . '] = ' . $data[$i];
                if($i == (sizeof($data) - 1))
                    echo "|||<br>\n";
            }
            
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        
        //Go to the next line
        $this->Ln($h);
    }
    
    //////////////////////////////////////////
    // Fun��o para gerar os totais e subtotais
    //////////////////////////////////////////
    function totalizador($i, $data, $xRow = 'asdf') {
        if(is_numeric($xRow) && !isset($this->subTotais[$xRow][$i])) {
            if($this->totais[$i] == '') {
                $this->subTotais[$xRow][$i] = '';
                return;
            } else {
                if(strtolower($this->totais[$i]) == 'count') {
                    $this->subTotais[$xRow][$i] = 1;
                } else if(strtolower($this->totais[$i]) == 'avg') {
                    $this->subAvg[$xRow][$i]    = $data[$i];
                    $this->subTotais[$xRow][$i] = $data[$i];
                } else {
                    $this->subTotais[$xRow][$i] = $data[$i];
                }
                
                return;
            }
        }
        
        if(strtolower($this->totais[$i]) == 'count') {
            //subtotais quebras
            if(is_numeric($xRow)){ 
                if(!isset($this->subTotais[$xRow][$i])) {
                    $this->subTotais[$xRow][$i]  = 1;
                }else{
                    $this->subTotais[$xRow][$i] += 1;
                }
                return $this->subTotais[$xRow][$i];
            } else {
                //totais
                $this->totalCol[$i] += 1;
            }   
        }elseif(strtolower($this->totais[$i]) == 'max' && $this->numRegs != 0) {
            //subtotais quebras
            if(is_numeric($xRow)) {
                if($this->subTotais[$xRow][$i] < $data[$i]) {
                    $this->subTotais[$xRow][$i] = $data[$i];
                    return $this->subTotais[$xRow][$i];
                }
            } else {
                //totais
                if($this->totalCol[$i] < $data[$i]) {
                    $this->totalCol[$i] = $data[$i];
                }
            }
        }elseif(strtolower($this->totais[$i]) == 'min' && $this->numRegs != 0) {
            if($this->numRegs == 1) {
                if(is_numeric($xRow)) {
                    $this->subTotais[$xRow][$i] = $data[$i];
                    return $this->subTotais[$xRow][$i];
                }else {
                    $this->totalCol[$i] = $data[$i];
                }
            }   
            if(is_numeric($xRow)){
                if($this->subTotais[$xRow][$i] >= $data[$i]){
                    $this->subTotais[$xRow][$i] = $data[$i];
                    return $this->subTotais[$xRow][$i];
                }   
            } else {
                if($this->totalCol[$i] >= $data[$i]) {
                    $this->totalCol[$i] = $data[$i];
                }
            }
        }elseif(strtolower($this->totais[$i]) == 'avg' && $this->numRegs != 0) {
            if(is_numeric($xRow)) {
                if(!isset($this->subCount[$xRow][$i])) {
                    $this->subCount[$xRow][$i]  = 1;
                }else {
                    $this->subCount[$xRow][$i] += 1;
                }
                if(!isset($this->subAvg[$xRow][$i])) {
                    $this->subAvg[$xRow][$i]  = $data[$i];
                }else {
                    $this->subAvg[$xRow][$i] += $data[$i];
                }
                $fator=1;
                
                $this->subTotais[$xRow][$i] = $this->subAvg[$xRow][$i] / ($this->subCount[$xRow][$i] + $fator);
                return $this->subTotais[$xRow][$i];
            }else{
                $this->totAvg[$i]  += $data[$i];
                $this->totalCol[$i] = $this->totAvg[$i] / ($this->numRegs+1);
            }
        }elseif(strtolower($this->totais[$i]) == 'sum') {
            if(is_numeric($xRow)) {
                if(!isset($this->subTotais[$xRow][$i])) {
                    $this->subTotais[$xRow][$i]  = $data[$i];
                }else {
                    $this->subTotais[$xRow][$i] += $data[$i];
                }
            }else {
                $this->totalCol[$i] += $data[$i];
            }   
        }
        
    }
    
    //////////////////////////////////////////
    // Fun��o para processar as informa��es
    //////////////////////////////////////////
    function Row($data, $lw = false, $lh = false) {
        if(!isset($this->numRegs)) {
            $this->xRow    = false;
            $this->numRegs = 0;
            $controle      = 0;
            for($xy = 0; $xy < sizeof($data); $xy++) {
                if($this->totais[$xy] != '') {
                    $this->mostraTot = true;
                }
                if($this->quebras[$xy] == 's') {
                    $this->xRow[$controle] = $xy;
                    $controle++;
                }
            }
        }else{
            $this->numRegs++;
        }
        
        for($xy = 0; $xy < sizeof($this->xRow); $xy++) {
            $showSub[$xy] = false;
        }
        
        for($i = 0; $i < count($data); $i++) {
            $labelSubTotais[$i] = '';
            ////////////////////////////////
            // inicializa arrays
            ///////////////////////////////
            if($this->numRegs == 0){
                $this->totalCol[$i] = '';
                $this->colAux[$i]   = '';
                $this->totAvg[$i]   = $data[$i];
            }
            
            for($xy = 0; $xy < sizeof($this->xRow); $xy++) {
                if(!$showSub[$xy]) {
                    $this->totalizador($i, $data, $xy);
                } else {
                    $dataTemp[$i] = $data[$i];
                }
                
                if(! isset($labelSubTotais[$xy][$i]))
                    $labelSubTotais[$xy][$i] = '';
            }
            
            $this->totalizador($i, $data, '');
            //////////////////////////////////////  
            // executa as quebras de subtotais
            //////////////////////////////////////
            $dataF[$i] = $data[$i];
            if($this->colAux[$i] == $data[$i] && $this->quebras[($i)] == 's') {
                $dataF[$i] = '';
                
            } else if($this->colAux[$i] != $data[$i]) {
                if($i > 0) {
                    if($this->colAux[($i - 1)] != $data[($i - 1)] && $this->quebras[($i - 1)] == 's' && $this->quebras[($i)] == 's') {
                        $dataF[($i - 1)] = $this->colAux[($i - 1)];
                    }
                }
                
                $this->colAux[$i] = $data[$i];
                for($xy = 0; $xy < sizeof($this->xRow); $xy++) {
                    if(isset($this->mostraTot) && $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs!=0) {
                        if(!isset($xyFlag))
                            $xyFlag = $xy;
                        
                        $showSub[$xy]            = true;
                        $showFlag                = true;
                        $labelSubTotais[$xy][$i] = 'SUBTOTAL';
                    }
                    if($this->totais[$i] != '' || $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0) {
                        $this->subTotais[$xy][$i] = $labelSubTotais[$xy][$i] . '  ' . $this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i] = trim($this->subTotais[$xy][$i]);
                    }
                }//end for
            }
            
            if(isset($xyFlag)) {
                for($xy = ($xyFlag + 1); $xy < sizeof($this->xRow); $xy++){
                    if($showSub[$xy] == true)
                        continue;
                    
                    if(isset($this->mostraTot) && $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0) {
                        $showSub[$xy]            = true;
                        $showFlag                = true;
                        $labelSubTotais[$xy][$i] = 'SUBTOTAL';
                    }
                    
                    if($this->totais[$i] != '' || $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0) {
                        $this->subTotais[$xy][$i] = $labelSubTotais[$xy][$i] . '  ' . $this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i] = trim($this->subTotais[$xy][$i]);
                    }
                }//end for
            }
            
            /////////////////////////////
            // mostra total no fim do relat�rio
            /////////////////////////////
            if(($this->numRegs == ($this->dadosNumRegs - 1)) && $this->numRegs != 0){
                $showTotais = true;
            }
            
        }// end for
        
        if(isset($this->newPage)) {
            for($x = 0; $x < sizeof($data); $x++) {
                    $dataF[$x] = $data[$x];
            }
            unset($this->newPage);
        }
        
        $xyTempCont = 0;
        for($xy = (sizeof($this->xRow) - 1); $xy >= 0; $xy--) {
            if($showSub[$xy] == true) {
                $xyTemp[$xyTempCont] = $xy;
                $xyTempCont++;
                $this->showRow($this->subTotais[$xy], true, true);
                $this->subTotais[$xy] = '';
                $this->subAvg[$xy]    = '';
                if(isset($this->subCount[$xy]))
                    unset($this->subCount[$xy]);
                $this->firstTime = true;
            }
        }
        
        $data = $dataF;
        $this->showRow($data, true);
        
        if(isset($dataTemp)) {
            for($i = 0; $i < count($data); $i++) {
                $this->subTotais[$xy][$i] = '';
                $this->subAvg[$xy][$i]    = '';
                $this->subCount[$xy]      = 1;
                
                for($xyTempCont = 0; $xyTempCont < sizeof($xyTemp); $xyTempCont++){
                    $this->totalizador($i, $data, $xyTemp[$xyTempCont]);
                }
            }
        }
        
        if(isset($showTotais)) {
            $data = $dataF;
            for($i = 0; $i < sizeof($data); $i++) {
                for($xy = 0; $xy < sizeof($this->xRow); $xy++) {
                    if(isset($this->mostraTot) && $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0) {
                        if(! isset($xyFlag))
                            $xyFlag = $xy;
                        
                        $showSub[$xy]            = true;
                        $showFlag                = true;
                        $labelSubTotais[$xy][$i] = 'SUBTOTAL';
                    }
                    
                    if($this->totais[$i] != '' || $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0){
                        $this->subTotais[$xy][$i] = $labelSubTotais[$xy][$i] . '  ' . $this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i] = trim($this->subTotais[$xy][$i]);
                    }
                }//end for
                
                if(isset($xyFlag)){
                    for($xy = ($xyFlag + 1); $xy < sizeof($this->xRow); $xy++) {
                        if($showSub[$xy] == true)
                            continue;
                        
                        if(isset($this->mostraTot) && $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0) {
                            $showSub[$xy]            = true;
                            $showFlag                = true;
                            $labelSubTotais[$xy][$i] = 'SUBTOTAL';
                        }
                        
                        if($this->totais[$i] != '' || $this->quebras[$i] == 's' && $this->xRow[$xy] == $i && $this->numRegs != 0) {
                            $this->subTotais[$xy][$i] = $labelSubTotais[$xy][$i] . '  ' . $this->subTotais[$xy][$i];
                            $this->subTotais[$xy][$i] = trim($this->subTotais[$xy][$i]);
                        }
                    }//end for
                }   
            }
            
            $xyTempCont = 0;
            for($xy = (sizeof($this->xRow) - 1); $xy >= 0; $xy--) {
                if($showSub[$xy] == true) {
                    $xyTemp[$xyTempCont] = $xy;
                    $xyTempCont++;
                    $this->showRow($this->subTotais[$xy], true, true);
                    $this->subTotais[$xy] = '';
                    $this->subAvg[$xy]    = '';
                    unset($this->subCount[$xy]);
                    $this->firstTime      = true;
                }
            }
            
            $mostra = false;
            for($x = 0; $x < sizeof($this->totalCol); $x++) {
                if($this->totalCol[$x] != '') {
                    $mostra = true;
                    break;
                }
            }
            
            if($mostra) {    
                $this->totalCol[0] = 'TOTAL ' . $this->totalCol[0];
                $this->showRow($this->totalCol, true);
            }
        }
    }// end method
    
    //////////////////////////////////////////
    // Fun��o para processar as quebras de p�ginas
    //////////////////////////////////////////
    function CheckPageBreak($h) {
        //If the height h would cause an overflow, add a new page immediately
        if(($this->GetY() + $h) > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->showRow($this->cabecalhos, true, true, true);
        } else if(($this->GetY() + $h) > ($this->PageBreakTrigger - $h)) {
            $this->newPage = true;
        }
    }
    
    //////////////////////////////////////////
    // Fun��o para processar as quebras de linha
    //////////////////////////////////////////
    function NbLines($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if($w == 0){
            $w = $this->w - $this->rMargin-$this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s    = str_replace("\r", '', $txt);
        $nb   = strlen($s);
        if($nb > 0 and $s[$nb-1] == "\n"){
            $nb--;
        }   
        $sep = -1;
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $nl  = 1;
        while($i < $nb){
            $c=$s[$i];
            if($c=="\n"){
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax){
                if($sep==-1){
                    if($i==$j)
                        $i++;
                }else{
                    $i=$sep+1;
                }
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }else{
                $i++;
            }   
        }
        return $nl;
    }
    //////////////////////////////////////////
    // Fun��o para formata��o de dados 
    //////////////////////////////////////////
    function formataDados($fmt,$valor){
        //echo 'Valor: '.$valor.'                  >> Formata��o: '.$fmt.'<br>';
        switch($fmt){
            case '1':
                return $valor;
            break;
            case '2':
                if($valor=='')
                    $valor=0;
                $valor=number_format($valor,2,',','.');
                return $valor;
            break;
            case '3':
                if($valor=='')
                    $valor=0;
                $valor=number_format($valor,2,',','.');
                return 'R$ '.$valor;
            break;
            case '4':
                if($valor=='')
                    $valor=0;
                $valor=round($valor);
                return 'R$ '.$valor;
            break;
            case '5':
                if($valor=='' || $valor==0){
                    $valor='';
                }else{  
                    $valor=round($valor);
                }
                return $valor;
            break;
            case '6':
                if($valor=='' || $valor==0){
                    $valor='';
                }else{  
                    $valor=number_format($valor,2,',','.');
                }
                return $valor;
            break;
            case '7':
                if($valor==''){
                    $valor=0;
                }
                $valor=round($valor);
                $valor=number_format($valor,0,'','.');
                return $valor;
            break;
            case '8':
                if($valor==''){
                    $valor=0;
                }
                $valor=round($valor);
                $valor=number_format($valor,2,'','.');
                return 'R$ '.$valor;
            break;
            /////////////////////////////////
            // rotina para datas
            ////////////////////////////////
            case '9':
                if($valor==''){
                    //return 'dd/mm/yyyy';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 0, 10);
                }
                return $valor;
            break;
            case '10':
                if($valor==''){
                    return;// 'dd/mm/yyyy hh:mm:ss';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 0, 19);
                }
                return $valor;
            break;
            case '11':
                if($valor==''){
                    return;// 'hh:mm:ss';
                }
                if(strpos($valor,':')){
                    $valor=substr($valor, 11);
                }
                return $valor;
            break;
            case '12':
                if($valor==''){
                    return;// 'yyyy';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 6, 4);
                }
                return $valor;
            break;
            case '13':
                if($valor==''){
                    return;// 'mm';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 3, 2);
                }
                return $valor;
            break;
            case '14':
                if($valor==''){
                    return;// 'dd';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 0, 2);
                }
                return $valor;
            break;
            case '15':
                if($valor==''){
                    return;// 'mm/yyyy';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 3, 7);
                }
                return $valor;
            break;
            case '16':
                if($valor==''){
                    return;// 'dd/mm';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 0, 5);
                }
                return $valor;
            break;
            case '17':
                if($valor==''){
                    return;// 'yyyy/mm';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 0, 10);
                    $valorArr=explode('/',$valor);
                    $valor=$valorArr[2].'/'.$valorArr[1];
                }
                return $valor;
            break;
            case '18':
                if($valor==''){
                    return;// 'mm/dd';
                }
                if(strpos($valor,'/')){
                    $valor=substr($valor, 0, 10);
                    $valorArr=explode('/',$valor);
                    $valor=$valorArr[1].'/'.$valorArr[0];
                }
                return $valor;
            break;
            /////////////////////////////////
            // fim rotina para datas
            ////////////////////////////////
            case '19':
                if($valor=='')
                    $valor=0;
                $valor=number_format($valor,2,',','.');
                return '$ '.$valor;
            break;
            case '20':
                if($valor==''){
                    $valor=0;
                }
                $valor=round($valor);
                $valor=number_format($valor,0,'','.');
                return '$ '.$valor;
            break;
            case '21':
                if($valor=='' || $valor==0){
                    $valor='';
                }else{  
                    $valor=round($valor);
                    $valor=number_format($valor,0,'','.');
                }
                return $valor;
            case '22':
                if($valor=='')
                    $valor=0;
                $valor=number_format($valor,3,',','.');
                return $valor;
            break;
            case '23':
                if($valor=='')
                    $valor=0;
                $valor=number_format($valor,3,',','.');
                return 'R$ '.$valor;
            break;
            case '24':
                if($valor=='')
                    $valor=0;
                $valor=number_format($valor,3,',','.');
                return '$ '.$valor;
            break;
            case '25':
                if($valor=='' || $valor==0){
                    $valor='';
                }else{  
                    $valor=number_format($valor,3,',','.');
                }
                return $valor;
            case '26':
                if($valor=='' || $valor==0){
                    $valor='';
                }else{  
                    $valor=number_format($valor,3,',','.');
                }
                return 'R$ '.$valor;
            break;
            case '27':
                if($valor=='' || $valor==0){
                    $valor='';
                }else{  
                    $valor=number_format($valor,3,',','.');
                }
                return '$ '.$valor;
            break;
            case '28':
                while(strlen($valor)<14){
                    $valor='0'.$valor;
                }
                $parte1 = substr($valor, 0, 2); 
                $parte2 = substr($valor, 2, 3); 
                $parte3 = substr($valor, 5, 3); 
                $parte4 = substr($valor, 7, 4); 
                $parte5 = substr($valor, 11, 2); 
                //Formatando 
                $valor = $parte1.'.'.$parte2.'.'.$parte3.'/'.$parte4.'-'.$parte5; 
                return $valor;
            break;
            case '29':
                while(strlen($valor)<11){
                    $valor='0'.$valor;
                }
                $parte1 = substr($valor, 0, 3); 
                $parte2 = substr($valor, 3, 3); 
                $parte3 = substr($valor, 6, 3); 
                $parte4 = substr($valor, 9, 2); 
                //Formatando 
                $valor = $parte1.'.'.$parte2.'.'.$parte3.'-'.$parte4; 
                return $valor;
            break;
            default:
                return $valor;
            break;
        }
    }
}
