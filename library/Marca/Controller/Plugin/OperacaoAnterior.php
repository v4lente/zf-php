<?php
/**
 *
 * Plugin que irá armazenar na sessão os parâmetros da requisição
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application
 * @version			1.0
 */
class Marca_Controller_Plugin_OperacaoAnterior extends Zend_Controller_Plugin_Abstract {


    /**
     * (non-PHPdoc)
     * @see library/Zend/Controller/Plugin/Zend_Controller_Plugin_Abstract::postDispatch()
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request){

    	// Grava na sessão do usuário a conexão
		$sessao = new Zend_Session_Namespace('portoweb');
		
		// Recupera o controlador do frontend
        $front  = Zend_Controller_Front::getInstance();

    	// Captura os parametros passados por GET
		$params = $this->getRequest()->getParams();
		
		$modulo     = trim($params["module"]);
		$controller = trim($params["controller"]); 
        $action     = trim($params["action"]);
		
		// Verfica se o systema não é o padrão e 
		// aplica uma excessão para o controlador "exec-rel"
		// ao qual permite setar a página anterior pois neste 
		// controlador é possível navegar
		if ((($modulo != "default" && 
		    ($front->getRequest()->isXmlHttpRequest() === false) && 
		    (strpos($controller, "tab-") === false)) || 
		    ($modulo == "default" && $controller == "exec-rel")) && 
		    ($action == "index" || $action == "pesquisar")) {
				
	        	// Grava a última requisição da action index ou pesquisar
		    	$sessao->voltar["paginaAnterior"] = $params;

		}

    }
}