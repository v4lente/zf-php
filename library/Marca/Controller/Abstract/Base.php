<?php

/**
 * Created on 12/02/2010
 *
 * Classe que servirá de base para os controladores
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Controller_Abstract_Base extends Zend_Controller_Action {

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
    	Zend_Loader::loadClass("WebSistemaModel");
    	Zend_Loader::loadClass("WebTransacaoModel");
    	Zend_Loader::loadClass("WebMenuSistemaModel");

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

        if(strtolower(trim($no_action)) == "selecionar") {
        	$this->view->operacao = "editar";
        } else {
        	$this->view->operacao = "novo";
        }

        // Busca a sessão portoweb existente
        $sessao = new Zend_Session_Namespace('portoweb');
        Zend_Registry::set("portoweb", $sessao);

        // Captura os parametros passados por GET
		$params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

		// Parametros para a paginação
		$this->view->extraParams = $params;

		// Verifica se a coluna e a ordenação de uma tabela estão sendo passado
		// e devolve para a view os dados para poder continuar ordenando e mostrando
		// corretamente as setas de ordenação
		if(isset($params["column"])) {
			// Joga na view o parametro column se existir
			$this->view->column = $params["column"];
		}

		// Parametro para ordenação
    	if(isset($params["orderby"])) {
			// Joga na view o parametro orderby se existir
    		$this->view->orderby = $params["orderby"];
		}

		// Se for setada a mensagem, joga para a view
		if (! empty($params["mensagemSistema"])) {
			$this->view->mensagemSistema = $params["mensagemSistema"];
		}

		// seta parametros para a pagina anterior
		$parametrosPaginaAnterior   = $this->_helper->OperacaoAnterior->getParametrosAnterior();
		$this->view->paginaAnterior = $parametrosPaginaAnterior;
		$this->view->paginaAtual    = $this->getRequest()->getRequestUri();
		
		// Se não for a tela de login grava os dados do menu
		if($no_controller != "login") {
		
    		//Persiste os dados para montagem do menu e do menu de ações
    		if (isset($params['_cd_sistema'])) {
    			$_SESSION["portoweb"][$no_module][$no_controller] = array ("_cd_sistema"        => $params['_cd_sistema'],
																		   "_no_sistema"        => $params['_no_sistema'],
    			                                                           "_cd_menu"           => $params['_cd_menu'],
																		   "_no_menu"           => $params['_no_menu'],
    			                                                           "_cd_transacao"      => $params['_cd_transacao'],
																		   "_no_transacao"      => $params['_no_transacao'],
                                                                           "_fl_tipo_transacao" => $params['_fl_tipo_transacao'],
                                                                           "_fl_permissao"      => $params['_fl_permissao']);
    			
    		} else if (isset($_SESSION["portoweb"][$no_module][$no_controller]["_cd_sistema"])) { // Aqui o usuário já entrou na transação e gravou em sessão
    			
    		    $cdSistema      = $_SESSION["portoweb"][$no_module][$no_controller]["_cd_sistema"];
				$noSistema      = $_SESSION["portoweb"][$no_module][$no_controller]["_no_sistema"];
    		    $cdMenu         = $_SESSION["portoweb"][$no_module][$no_controller]["_cd_menu"];
				$noMenu         = $_SESSION["portoweb"][$no_module][$no_controller]["_no_menu"];
    		    $cdTransacao    = $_SESSION["portoweb"][$no_module][$no_controller]["_cd_transacao"];
				$noTransacao    = $_SESSION["portoweb"][$no_module][$no_controller]["_no_transacao"];
                $flTpTransacao  = $_SESSION["portoweb"][$no_module][$no_controller]["_fl_tipo_transacao"];
                $flPermissao    = $_SESSION["portoweb"][$no_module][$no_controller]["_fl_permissao"];
    		    
    		    Zend_Controller_Front::getInstance()
    				->getRequest()
    				->setParam("_cd_sistema",        $cdSistema)
					->setParam("_no_sistema",        $noSistema)
    				->setParam("_cd_menu",           $cdMenu)
					->setParam("_no_menu",           $noMenu)
    				->setParam("_cd_transacao",      $cdTransacao)
					->setParam("_no_transacao",      $noTransacao)
                    ->setParam("_fl_tipo_transacao", $flTpTransacao)
                    ->setParam("_fl_permissao",      $flPermissao);
					
    		} else if ($no_module != "default") { // Se entrar aqui é por que entrou em uma transação sem ser pelo menu
				
				$webTransacao = new WebTransacaoModel();
				
				$dados = array("no_pasta_zend" => $no_module, "obj_executado" => $no_controller);
				$query = $webTransacao->queryBuscaTransacoes($dados);
				$wtRow = $webTransacao->fetchRow($query);
				
				$_SESSION["portoweb"][$no_module][$no_controller] = array ("_cd_sistema"        => $params['_cd_sistema'],
																		   "_no_sistema"        => $params['_no_sistema'],
    			                                                           "_cd_menu"           => $params['_cd_menu'],
																		   "_no_menu"           => $params['_no_menu'],
    			                                                           "_cd_transacao"      => $params['_cd_transacao'],
																		   "_no_transacao"      => $params['_no_transacao'],
                                                                           "_fl_tipo_transacao" => $params['_fl_tipo_transacao'],
                                                                           "_fl_permissao"      => $params['_fl_permissao']);
				
				Zend_Controller_Front::getInstance()
					->getRequest()
					->setParam("_cd_sistema",   $wtRow->CD_SISTEMA)
					->setParam("_no_sistema",   $wtRow->NO_SISTEMA)
					->setParam("_cd_menu",      $wtRow->CD_MENU)
					->setParam("_no_menu",      $wtRow->NO_MENU)
					->setParam("_cd_transacao", $wtRow->CD_TRANSACAO)
					->setParam("_no_transacao", $wtRow->NO_TRANSACAO);
                    
            }
    		
    		// Verifica se há valor no código da transação
    		$cdTransacao = $_SESSION["portoweb"][$no_module][$no_controller]["_cd_transacao"];
    		if($cdTransacao != "") {
        		// Guarda em sessão o formato de cada relatório 
        		// ([R]etrato => (PORTRAIT) / [P]aisagem => (LANDSCAPE)) 
        		// referente a cada transação,
        		$transacoes = new WebTransacaoModel();
        		$transacao  = $transacoes->fetchRow("CD_TRANSACAO = " . $cdTransacao);
        		$sessao->format_rel = $transacao->FORMAT_REL != "" 
        		                          ? ($transacao->FORMAT_REL == "R" ? "portrait" : "landscape") 
        		                          : "portrait";
    		}
		} 

		// Controle do layout para o login (deve estar aqui e não no init()).
		if ($no_controller == 'login') {

			// Busca o dispositivo
			$view = Zend_Controller_Front::getInstance()->getParam('bootstrap')
														->getResource('layout')
														->getView();
			$userAgent = $view->userAgent();
			$device = $userAgent->getDevice();

			// Define o layout a ser utilizado
//			if($userAgent->getBrowserType() == "mobile") {
				//$this->_helper->layout->setLayout("layout-mobile");
//                $fc = Zend_controller_front::getInstance();
//				header("Location: http://" . $_SERVER["SERVER_NAME"] . $fc->getBaseUrl() . "-mobile");
                
//			} else {
				$this->_helper->layout->setLayout("layout-login");
//			}

		}

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

		if (isset($sessao->_autenticacao)) {
    		
			// Limpa a url externa
			unset($sessao->linkUrl);
			
    		// Inicializa a classe de autenticação
	    	$this->_autenticacao = $sessao->_autenticacao;
	    	
	    	// Captura e valida a sessão do usuário
	        $identidade_usuario = $this->_autenticacao->getIdentity();
	        
	        // Ambiente de desenvolvimento que foi logado
	        $ambiente_usuario   = $this->_autenticacao->getApplicationEnv();
	        
	    	if(empty($identidade_usuario) || $ambiente_usuario != APPLICATION_ENV) {
		    	$this->_redirect($this->baseUrl . "login/sair");
		    }

			// Busca o dispositivo
			$view = Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                    ->getResource('layout')
                                                    ->getView();
			$userAgent = $view->userAgent();
			$device = $userAgent->getDevice();

			// Define o layout a ser utilizado
			if($userAgent->getBrowserType() == "mobile") {
				//$this->_helper->layout->setLayout("layout-mobile");
				$this->_helper->layout->setLayout("layout");
			} else if($identidade_usuario == "PUBLICO") {
				// Se o usuário for publico seta o layout-publico
	            $this->_helper->layout->setLayout("layout-publico");
	        } else {
				$this->_helper->layout->setLayout("layout");
			}

	        // Se for uma janela flutuante, seta o layout específico
	        if(isset($params["janela-flutuante"]) && $params["janela-flutuante"] == 1) {
	        	$this->_helper->layout->setLayout("layout-flutuante");
	        }
			
    	} else {
			
    		// verifica se é uma requisição ajax
    		if($this->_request->isXmlHttpRequest()) {
    			// Seta o header para sem autorização para cancelar a requisição
    			header('HTTP/1.1 401 Unauthorized');
    			die;    			
    		} else {
				
				$no_module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
				if ($no_module != "default") {
					// Grava o local para onde o usuário deseja entrar
					$url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER ['REQUEST_URI'];
					$sessao->linkUrl = $url;
				}
				
    			$this->_redirect($this->baseUrl . "login/sair/".$sessao->msgSessao);
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