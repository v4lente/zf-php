<?php

// Inclui a classe para gerar relatório pdf
require_once("Marca/GeraRelatorioPdf.php");

// Inclui a classe para gerar relatório xls
require_once("Marca/GeraRelatorioXls.php");

/**
 * View Helper para geração de relatórios
 *
 * @uses Zend_View_Helper_Abstract
 * @author Márcio Souza Duarte
 */
class Zend_View_Helper_GeraRelatorio extends Zend_View_Helper_Abstract {
    
    /**
     * 
     * Método principal da classe.
     * 
     * @param  array   $arrayObjetos
     * @param  string  $titulo
     * @param  array   $campos
     * @param  string  $tipo   			pdf/xls
     * 
     * @return object  
     */
    public function geraRelatorio($arrayObjetos = array(), 
                                  $titulo       = "Relatório do Porto", 
                                  $campos       = array(), 
                                  $tipo         = "pdf",
                                  $tamanhoFonte = 6) {

        // Captura o controlador principal
        $this->front = Zend_Controller_Front::getInstance();
	    
	    // Recupera os parametros da requisição
        $params = $this->front->getRequest()->getParams();
        
        // Captura a instância do banco
        $db = Zend_Registry::get("db");
        
        // Busca o formato da impressão do relatório
        $select = $db->select()
                     ->from(array("WT" => "WEB_TRANSACAO"), array("WT.FORMAT_REL"))
                     ->where("WT.CD_TRANSACAO = " . $params["_cd_transacao"]);
                     
        // Executa a consulta e retorna o formato do relatório
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
        
        // Gera o relatório
        if(strtolower($tipo) == "pdf") {
            // Chama a classe que gerará o pdf pelo FPDF
            new GeraRelatorioPdf($baseUrl, $arrayObjetos, $titulo, $campos, $orientacao, 'mm', 'A4', $tamanhoFonte);
        }
        elseif(strtolower($tipo) == "xls") {
            
            // Seta o cabeçalho do PHP para o XLS
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="relatorio.xls"');
            header('Cache-Control: max-age=0');
            
            // Chama a classe que gerará o xls pelo PHPExcel
            new GeraRelatorioXls($baseUrl, $arrayObjetos, $titulo, $campos, $orientacao);
        }

	}
}