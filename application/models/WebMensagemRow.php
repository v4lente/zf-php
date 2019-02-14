<?php
/**
 *
 * Manipula a linha da classe WebMensagemModel
 *
 * @filesource
 * @author			Márcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class WebMensagemRow extends Zend_Db_Table_Row_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Db/Table/Row/Zend_Db_Table_Row_Abstract#init()
	 */
    public function init(){
        
	    // Se retornar da consulta o campo transforma a datahora para data
	    if(isset($this->DT_INI)) {
	        
	        // Separa a data da hora
	        $divDtIni = explode(" ", $this->DT_INI);
	        
	        // Separar por barra
    	    list($dia, $mes, $ano) = explode("/", $divDtIni[0]);
    	    
    	    // Faz o filtro da data
    	    $this->DT_INI = date("d/m/Y", mktime(0, 0, 0, $mes, $dia, $ano));
	    }
        
	    if(isset($this->DT_FIM)) {
	        
	        // Separa a data da hora
            $divDtFim = explode(" ", $this->DT_FIM);
	        
            // Separar por barra
            list($dia, $mes, $ano) = explode("/", $divDtFim[0]);
            
            // Faz o filtro da data
            $this->DT_FIM = date("d/m/Y", mktime(0, 0, 0, $mes, $dia, $ano));
        }
        
	}
   
}
?>