<?php

// Inclui a classe para gerar relat�rio pdf
require_once("Marca/GeraRelatorioPdf.php");

// Inclui a classe para gerar relat�rio xls
require_once("Marca/GeraRelatorioXls.php");

/**
 * View Helper para gera��o de relat�rios
 *
 * @uses Zend_View_Helper_Abstract
 * @author M�rcio Souza Duarte
 */
class Zend_View_Helper_GeraRelatorio extends Zend_View_Helper_Abstract {
    
    /**
     * 
     * M�todo principal da classe.
     * 
     * @param  array   $arrayObjetos
     * @param  string  $titulo
     * @param  array   $campos
     * @param  string  $tipo   			pdf/xls
     * 
     * @return object  
     */
    public function geraRelatorio($arrayObjetos = array(), 
                                  $titulo       = "Relat�rio do Porto", 
                                  $campos       = array(), 
                                  $tipo         = "pdf",
                                  $tamanhoFonte = 6) {

        // Captura o controlador principal
        $this->front = Zend_Controller_Front::getInstance();
	    
	    // Recupera os parametros da requisi��o
        $params = $this->front->getRequest()->getParams();
        
        // Captura a inst�ncia do banco
        $db = Zend_Registry::get("db");
        
        // Busca o formato da impress�o do relat�rio
        $select = $db->select()
                     ->from(array("WT" => "WEB_TRANSACAO"), array("WT.FORMAT_REL"))
                     ->where("WT.CD_TRANSACAO = " . $params["_cd_transacao"]);
                     
        // Executa a consulta e retorna o formato do relat�rio
        $orientacao = $db->fetchRow($select)->FORMAT_REL;
         
        // No FPDF Paisagem (P = Landscape (L)) e 
        //         Retrato  (R = Portrait (P))
        if($orientacao == "P") {
            $orientacao = "L";
        } else {
            $orientacao = "P";
        }
        
        // Captura a baseUrl
        $baseUrl = $this->view->baseUrl();
        
        // Transforma o objeto em array
        if(is_object($arrayObjetos)) {
        	$arrayObjetos = $arrayObjetos->toArray();
        }
        
        // Gera o relat�rio
        if(strtolower($tipo) == "pdf") {
            // Chama a classe que gerar� o pdf pelo FPDF
            new GeraRelatorioPdf($baseUrl, $arrayObjetos, $titulo, $campos, $orientacao, 'mm', 'A4', $tamanhoFonte);
        }
        elseif(strtolower($tipo) == "xls") {
            
            // Seta o cabe�alho do PHP para o XLS
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="relatorio.xls"');
            header('Cache-Control: max-age=0');
            
            // Chama a classe que gerar� o xls pelo PHPExcel
            new GeraRelatorioXls($baseUrl, $arrayObjetos, $titulo, $campos, $orientacao);
        }

	}
}