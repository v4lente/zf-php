<?php

/**
 * Created on 28/06/2013
 *
 * Classe que servirá de base para os controladores
 *
 * @filesource
 * @author			Bruno Teló, Márcio Souza Duarte
 * @copyright		Copyright 2013 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Controller_Abstract_BaseMobile extends Zend_Controller_Action {

     /**
     * Objeto de autenticação
     *
     * @var Zend_Auth
     */
    private $_autenticacao = null;

    /**
    * Overrides the default constructer so we can call our own domain logic
    *
    * @param Zend_Controller_Request_Abstract $request
    * @param Zend_Controller_Response_Abstract $response
    * @param array $invokeArgs
    */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

    	parent::__construct($request, $response, $invokeArgs);

    	Zend_Loader::loadClass("UsuarioModel");
/*
    	Zend_Loader::loadClass("WebSistemaModel");
    	Zend_Loader::loadClass("WebTransacaoModel");
    	Zend_Loader::loadClass("WebMenuSistemaModel");
*/
    	// Define a operação na view
        $no_module     = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        $no_controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        $no_action     = Zend_Controller_Front::getInstance()->getRequest()->getActionName();

        $this->view->no_module 	   = $no_module;
        $this->view->no_controller = $no_controller;
        $this->view->no_action 	   = $no_action;

        // Registra os [modulo/controller/action]
        Zend_Registry::set("no_module",     $no_module);
        Zend_Registry::set("no_controller", $no_controller);
        Zend_Registry::set("no_action",     $no_action);
        Zend_Registry::set("msg_padrao_bd", false);

/*
        if(strtolower(trim($no_action)) == "selecionar") {
        	$this->view->operacao = "editar";
        } else {
        	$this->view->operacao = "novo";
        }
*/
        // Busca a sessão portoweb existente
        $sessao = new Zend_Session_Namespace('portoweb');
        Zend_Registry::set("portoweb", $sessao);
        
        $sessao->bd->msg_padrao_bd = false;
/*
        // Captura os parametros passados por GET
		$params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

		// Parametros para a paginação
		$this->view->extraParams = $params;
*/      
    }

	/**
     * (non-PHPdoc)
     * @see library/Zend/Controller/Zend_Controller_Action#init()
     */
    public function init() {

    	// Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura os parametros passados por GET
        $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

        $no_controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();

        if($no_controller != "" && $no_controller != "login") {
            
            if (isset($sessao->_autenticacao)) {

                $this->_helper->layout->setLayout("layout");

                // Inicializa a classe de autenticação
                $this->_autenticacao = $sessao->_autenticacao;

                // Captura e valida a sessão do usuário
                $identidade_usuario = $this->_autenticacao->getIdentity();

                // Ambiente de desenvolvimento que foi logado
                $ambiente_usuario   = $this->_autenticacao->getApplicationEnv();

                if(empty($identidade_usuario) || $ambiente_usuario != APPLICATION_ENV) {
                    $this->_redirect($this->baseUrl . "login/sair");
                }

            } else {

                // verifica se é uma requisição ajax
                if($this->_request->isXmlHttpRequest()) {
                    // Seta o header para sem autorização para cancelar a requisição
                    header('HTTP/1.1 401 Unauthorized');
                    die;    			
                } else {
                    $this->_redirect($this->baseUrl . "login/sair/".$sessao->msgSessao);
                }

            }

        }
    
    }

    /**
     * Metodo index
     * metodo do padrão da zend
     * objetivo: é chamado caso nenhuma action seja definida
     */
    abstract protected function indexAction();

    /**
     *
     * Retorna a URL do controlador
     *
     * @return string
     */
    public function getBaseUrlController(){

    	$no_module     = Zend_Registry::get("no_module");
        $no_controller = Zend_Registry::get("no_controller");

    	return  $no_module . "/" . $no_controller;
    }

    /**
     * Retorna uma instáncia do Zend_Auth
     *
     * @return Zend_Auth
     */
    public function getAutenticacao() {
        return $this->_autenticacao;
    }
    
    
	/**
     * Retorna uma instáncia do classe Zend_Session_Namespace	 
     *
     * @return Zend_Session_Namespace	 
     */
    public function getSessao() {
    	return new Zend_Session_Namespace('portoweb');
    }  
    
}