<?php
/**
 *
 * Classe Marca_HtmMcTable
 *
 * Esta classe tem como objetivo criar um relatório PDF 
 *
 * @category   Marca Sistemas
 * @package    Marca_HtmMcTable
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: HtmMcTable.php 0 2010-09-22 11:20:00 Marcio $
 */
class Marca_HtmMcTable {
    
    var $widths;
    var $aligns;
    public function __construct($wPage){
        $this->resultado   = array();
        $this->totalLinhas = -1;
        $this->wPage       = $wPage;
        
    }
        
    //////////////////////////////////////////
    // Função para gerar o cabeçalho
    //////////////////////////////////////////
    function Header(){
        //Verifica se é para mostrar o cabecalho
        if(!$this->showHeader) return;
        //busca parametros para mostrar nome da empresa e o nome do sistema no cabecalho
        $PARAM = $_SESSION['PARAM'];
        $porto = $PARAM[0]['no_empr'];
        ////////     --> o comando abaixo não estava pegando o titulo traduzido
        //$tit_sistema=$PARAM[0]['tit_sistema'];
        ////////     --> solução: trazer a classe bd para ca e utilizar o metodo de tradução em tempo de execução
        //$bd=$GLOBALS['bd'];
        //$tit_sistema=$bd->traduz('tit_sistema');
        $tit_sistema = "Teste";
        $this->tW = $this->wPage;
        $this->no_porto = $porto;
        $this->no_rel = $PARAM[0]['no_rel'] . ' (Relatório '.$PARAM[0]['cd_rel'].')';
    }
    
    //////////////////////////////////////////
    // Função para setar larguras das colunas
    //////////////////////////////////////////
    function SetWidths($w){
        //Set the array of column widths
        $this->widths=$w;
    }
    
    //////////////////////////////////////////
    // Função para setar alinhamentos
    //////////////////////////////////////////
    function SetAligns($a){
        //Set the array of column alignments
        $this->aligns=$a;
    }
    
    //////////////////////////////////////////
    // Função para gerar saída da informações de cada registro no PDF
    //////////////////////////////////////////
    function showRow($data, $lw=false, $lh=false, $cab=false){
        $this->totalLinhas += 1;
        
        for($i=0;$i<count($data);$i++){
            if(isset($this->formatos))
                if(!$cab)
                    $data[$i] = $this->formataDados($this->formatos[$i], $data[$i]);
        }
        if(isset($this->espacejamento)){
            if(strtoupper($this->espacejamento)=='S')
                $this->espacejamento=1;
            if(strtoupper($this->espacejamento)=='D')
                $this->espacejamento=2;
            if(strtoupper($this->espacejamento)=='T')
                $this->espacejamento=3;
        }else{
                $this->espacejamento=1;
        }       
        $h=18*$this->espacejamento;
        
        /*
        $this->tpl->assign('trH',$h);
        $b='';
        $_b='';
        if($lh){
            $this->tpl->assign('bgColor','style="background-color:#CCCCCC;"');
            $b='<b>';
            $_b='</b>';
        }
        */
        $data2 = array();
        for($i=0;$i<count($data);$i++) {
            $w=$this->widths[$i];
            $a=$this->aligns[$i];
            //$this->tpl->newBlock('_td');
            //$this->tpl->assign('tdW',$w);
            //$this->tpl->assign('tdA',$a);
            $texto=$data[$i];
            if(trim($texto==''))
                $texto='&nbsp;';
            //$this->tpl->assign('texto',$b.$texto.$_b);
            $data2[$i] = $b . $texto . $_b;
            if(isset($this->debug) && $this->debug==true){
                echo ' :::: ['.$i.'] = '.$data[$i];
                if($i==(sizeof($data)-1))
                    echo "|||<br>\n";
            }
        }
        
        $this->resultado[] = $data2;
        
        //$this->tpl->newBlock('sublinha');
        //$this->tpl->assign('i',sizeof($data));
    }
    
    //////////////////////////////////////////
    // Função para gerar os totais e subtoais
    //////////////////////////////////////////
    function totalizador($i,$data,$xRow='asdf'){
        if(is_numeric($xRow) && !isset($this->subTotais[$xRow][$i])){
            if($this->totais[$i]==''){
                $this->subTotais[$xRow][$i]='';
                return;
            }else{
                if(strtolower($this->totais[$i])=='count'){
                    $this->subTotais[$xRow][$i]=1;
                }else if(strtolower($this->totais[$i])=='avg'){
                    $this->subAvg[$xRow][$i]=$data[$i];
                    $this->subTotais[$xRow][$i]=$data[$i];
                }else{
                    $this->subTotais[$xRow][$i]=$data[$i];
                }
                //echo $this->subTotais[$xRow][$i]."\n";
                return;
            }
        }
        
        if(strtolower($this->totais[$i])=='count'){
            //subtotais quebras
            if(is_numeric($xRow)){ 
                if(!isset($this->subTotais[$xRow][$i])){
                    $this->subTotais[$xRow][$i]=1;
                }else{
                    $this->subTotais[$xRow][$i]+=1;
                }
                return $this->subTotais[$xRow][$i];
            }else{
                //totais
                $this->totalCol[$i]+=1;
            }   
        }elseif(strtolower($this->totais[$i])=='max' && $this->numRegs!=0){
            //subtotais quebras
            if(is_numeric($xRow)){
                if($this->subTotais[$xRow][$i]<$data[$i]){
                    $this->subTotais[$xRow][$i]=$data[$i];
                    return $this->subTotais[$xRow][$i];
                }
            }else{
                //totais
                if($this->totalCol[$i]<$data[$i]){
                    $this->totalCol[$i]=$data[$i];
                }
            }
        }elseif(strtolower($this->totais[$i])=='min' && $this->numRegs!=0){
            if($this->numRegs==1){
                if(is_numeric($xRow)){
                    $this->subTotais[$xRow][$i]=$data[$i];
                    return $this->subTotais[$xRow][$i];
                }else{
                    $this->totalCol[$i]=$data[$i];
                }
            }   
            if(is_numeric($xRow)){
                if($this->subTotais[$xRow][$i]>=$data[$i]){
                    $this->subTotais[$xRow][$i]=$data[$i];
                    return $this->subTotais[$xRow][$i];
                }   
            }else{
                if($this->totalCol[$i]>=$data[$i]){
                    $this->totalCol[$i]=$data[$i];
                }
            }
        }elseif(strtolower($this->totais[$i])=='avg' && $this->numRegs!=0){
            if(is_numeric($xRow)){
                if(!isset($this->subCount[$xRow][$i])){
                    $this->subCount[$xRow][$i]=1;
                }else{
                    $this->subCount[$xRow][$i]+=1;
                }
                if(!isset($this->subAvg[$xRow][$i])){
                    $this->subAvg[$xRow][$i]=$data[$i];
                }else{
                    $this->subAvg[$xRow][$i]+=$data[$i];
                }
                $fator=1;
                //echo 'SUB '."- $xRow - $i - ".$this->subCount[$xRow][$i].' == '.$this->subAvg[$xRow][$i].' += '.$data[$i]."\n";
                $this->subTotais[$xRow][$i]=$this->subAvg[$xRow][$i]/($this->subCount[$xRow][$i]+$fator);
                return $this->subTotais[$xRow][$i];
            }else{
                $this->totAvg[$i]+=$data[$i];
                //echo 'TOT '.$this->numRegs.' == '.$this->totAvg[$i].' += '.$data[$i]."\n";
                $this->totalCol[$i]=$this->totAvg[$i]/($this->numRegs+1);
            }
        }elseif(strtolower($this->totais[$i])=='sum'){
            if(is_numeric($xRow)){
                if(!isset($this->subTotais[$xRow][$i])){
                    $this->subTotais[$xRow][$i]=$data[$i];
                }else{
                    $this->subTotais[$xRow][$i]+=$data[$i];
                }
            }else{
                $this->totalCol[$i]+=$data[$i];
            }   
        }
                //echo "\n -".$this->subTotais[$xRow][$i]." - \n";
        //$this->subTotais[$i]=$this->totalCol[$i];
    }
    
    //////////////////////////////////////////
    // Função para processar as informações
    //////////////////////////////////////////
    function Row($data, $lw=false, $lh=false){
        //echo sizeof($data).'<br>';
        if(!isset($this->numRegs)){
            $this->xRow=false;
            $this->numRegs=0;
            $controle=0;
            for($xy=0;$xy<sizeof($data);$xy++){
                if($this->totais[$xy]!=''){
                    $this->mostraTot=true;
                }
                if($this->quebras[$xy]=='s'){
                //echo $this->totais[$xy].' - ';
                    $this->xRow[$controle]=$xy;
                    $controle++;
                }
            }
        }else{
            $this->numRegs++;
        }
        for($xy=0;$xy<sizeof($this->xRow);$xy++){
            $showSub[$xy]=false;
        }
            //echo 'func'."<br>";
        for($i=0;$i<count($data);$i++){
            //$data[$i]=$this->formataDados($this->formatos[$i],$data[$i]);
            $labelSubTotais[$i]='';
            //$this->subTotais[$i]='';

            ////////////////////////////////
            // inicializa arrays
            ///////////////////////////////
            if($this->numRegs==0){
                $this->totalCol[$i]='';
                $this->colAux[$i]='';
                //if(strtolower($this->totais[$i])=='avg'){
                    $this->totAvg[$i]=$data[$i];
                    //$this->subAvg[$i]=$data[$i];
                //}
            }   
            
            
            for($xy=0;$xy<sizeof($this->xRow);$xy++){
            //echo $showSub[$xy].' - '.sizeof($this->xRow)."\n\n";
                if(!$showSub[$xy]){
                    $this->totalizador($i,$data,$xy);
                    //echo "\n".'- '.$data[$i].' - '.$showSub[$xy].' -'."\n";
                }else{
                //echo 'temp';
                    $dataTemp[$i]=$data[$i];
                }
                if(!isset($labelSubTotais[$xy][$i]))
                    $labelSubTotais[$xy][$i]='';
            }
            
            $this->totalizador($i,$data,'');
            //////////////////////////////////////  
            // executa as quebras de subtotais
            //////////////////////////////////////
            $dataF[$i]=$data[$i];
            if($this->colAux[$i]==$data[$i] && $this->quebras[($i)]=='s'){
                $dataF[$i]='';
                
            }else if($this->colAux[$i]!=$data[$i]){
                if($i>0){
                    if($this->colAux[($i-1)]!=$data[($i-1)] && $this->quebras[($i-1)]=='s'  && $this->quebras[($i)]=='s'){
                        $dataF[($i-1)]=$this->colAux[($i-1)];
                    }
                }
                $this->colAux[$i]=$data[$i];
                for($xy=0;$xy<sizeof($this->xRow);$xy++){
                    if(isset($this->mostraTot) && $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){
                        if(!isset($xyFlag))
                            $xyFlag=$xy;
                        $showSub[$xy]=true;
                        $showFlag=true;
                        $labelSubTotais[$xy][$i]='SUBTOTAL';
                    }
                    if($this->totais[$i]!='' || $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){//
                        $this->subTotais[$xy][$i]=$labelSubTotais[$xy][$i].'  '.$this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i]=trim($this->subTotais[$xy][$i]);
                    }
                }//end for
            }
            if(isset($xyFlag)){
                for($xy=$xyFlag+1;$xy<sizeof($this->xRow);$xy++){
                    if($showSub[$xy]==true)
                        continue;
                    if(isset($this->mostraTot) && $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){
                        $showSub[$xy]=true;
                        $showFlag=true;
                        $labelSubTotais[$xy][$i]='SUBTOTAL';
                    }
                    if($this->totais[$i]!='' || $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){//
                        $this->subTotais[$xy][$i]=$labelSubTotais[$xy][$i].'  '.$this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i]=trim($this->subTotais[$xy][$i]);
                    }
                }//end for
            }   
            /////////////////////////////
            // mostra total no fim do relatório
            /////////////////////////////
            
            if($this->numRegs==$this->dadosNumRegs-1 && $this->numRegs!=0){
            //echo $this->numRegs.' - '.$this->dadosNumRegs.'<br>';
                $showTotais=true;
                /*
                for($xy=0;$xy<sizeof($this->xRow);$xy++){
                    if($this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){//
                        $showSub[$xy]=true;
                        $showFlag=true;
                        $labelSubTotais[$xy][$i]='SUBTOTAL';
                    }
                    if($this->totais[$i]!='' || $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){//
                        $this->subTotais[$xy][$i]=$labelSubTotais[$xy][$i].'  '.$this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i]=trim($this->subTotais[$xy][$i]);
                    }
                }//end for
                */
            }
            
        }// end for
        
        if(isset($this->newPage)){
            for($x=0;$x<sizeof($data);$x++){
                //if($this->quebras[$x]!='s'){
                    $dataF[$x]=$data[$x];
                    //echo '<br>'.$x.'---'.$dataF[$x].'---<br>newpage<br>';
                //}
            }   
            unset($this->newPage);
        }
        if(!isset($showTotais)){
            $xyTempCont=0;
            for($xy=sizeof($this->xRow)-1;$xy>=0;$xy--){
                if($showSub[$xy]==true){
                    $xyTemp[$xyTempCont]=$xy;
                    $xyTempCont++;
                    $this->showRow($this->subTotais[$xy],true,true);
                    $this->subTotais[$xy]='';
                    $this->subAvg[$xy]='';
                    if(isset($this->subCount[$xy]))
                        unset($this->subCount[$xy]);//=1;
                    $this->firstTime=true;
                }
            }
            $data=$dataF;
            $this->showRow($data,true);
        }
        if(isset($dataTemp) && !isset($showTotais)){
            //echo 'dataTemp<br>'.$this->numRegs;
            for($i=0;$i<count($data);$i++){
                $this->subTotais[$xy][$i]='';
                $this->subAvg[$xy][$i]='';
                $this->subCount[$xy]=1;
                for($xyTempCont=0;$xyTempCont<sizeof($xyTemp);$xyTempCont++){
                    $this->totalizador($i,$data,$xyTemp[$xyTempCont]);
                }
            }
        }
        if(isset($showTotais)){
            $data=$dataF;
            $this->showRow($data,true);
            for($i=0;$i<sizeof($data);$i++){
                for($xy=0;$xy<sizeof($this->xRow);$xy++){
                    if(isset($this->mostraTot) && $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){//
                        $showSub[$xy]=true;
                        $showFlag=true;
                        $labelSubTotais[$xy][$i]='SUBTOTAL';
                    }
                    if($this->totais[$i]!='' || $this->quebras[$i]=='s' && $this->xRow[$xy]==$i && $this->numRegs!=0){//
                        $this->subTotais[$xy][$i]=$labelSubTotais[$xy][$i].'  '.$this->subTotais[$xy][$i];
                        $this->subTotais[$xy][$i]=trim($this->subTotais[$xy][$i]);
                    }
                }//end for
            }
            $xyTempCont=0;
            for($xy=sizeof($this->xRow)-1;$xy>=0;$xy--){
                if($showSub[$xy]==true){
                //echo '<br>>>><br>';
                    $xyTemp[$xyTempCont]=$xy;
                    $xyTempCont++;
                    $this->showRow($this->subTotais[$xy],true,true);
                    //$data=$dataF;
                    //$this->showRow($data,true);
                    $this->subTotais[$xy]='';
                    $this->subAvg[$xy]='';
                    unset($this->subCount[$xy]);//=1;
                    $this->firstTime=true;
                }
            }
            
            
            //
            //$this->showRow($labelTotais,true,true);
            $mostra=false;
            for($x=0;$x<sizeof($this->totalCol);$x++){
                if($this->totalCol[$x]!=''){
                    $mostra=true;
                    break;
                }
            }
            if($mostra){    
                $this->totalCol[0]='TOTAL '.$this->totalCol[0];
                $this->showRow($this->totalCol,true);
            }
        }
    }// end method
    //////////////////////////////////////////
    // Função para processar as quebras de linha
    //////////////////////////////////////////
    function NbLines($w,$txt){
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0){
            $w=$this->w-$this->rMargin-$this->x;
        }
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n"){
            $nb--;
        }   
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb){
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
    // Função para formatação de dados 
    //////////////////////////////////////////
    function formataDados($fmt,$valor){
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
?>