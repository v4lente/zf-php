<?php

//ini_set("soap.wsdl_cache_enabled", "0");

class RestserverController extends Marca_Controller_Abstract_Rest_Base {
	
	/**
     * Objeto com os dados do usuário autenticado
     *
     * @var Marca_Auth
     */
    protected $_autenticacao;
    
	public function init() {
		
		parent::init();			
		
	}

	
    /**
     * The index action handles index/list requests; it should respond with a
     * list of the requested resources.
     */ 
    public function indexAction() {
		
    	$parametros = $this->_request->getParams();
    	/*
    	try {
    		parent::authenticate($parametros["usuario"], $parametros["senha"]);
    		
	        if($this->_autenticacao->getIdentity())
	    		$this->view->success = "true";
	    	else 
	    		$this->view->success = "false";
	    	
			$this->view->version = "1.0";
			
        } catch(Exception $e) {
        	$this->view->success = "_false_";
        	$this->view->version = "1.0";
        }
        
        unset($this->_autenticacao);
        */
    	
    	//$this->view->resultado = array("a"=>1, "b"=>2);
    	foreach($parametros as $indice => $valor) {
    		eval("\$this->view->$indice = '$valor';");
    	}
        
	}
 
    public function getAction() {
		$this->view->method = "get";
    }
 
    public function postAction() {    	 
		$this->view->method = "post";
    }
    
    public function putAction() {
    	$parametros = $this->_request->getParams();
    	//$parametros = $this->_request->isPost();
    	$parametros['dados'] = urldecode($parametros['dados']);
    	$arr = Zend_Json::decode($parametros['dados']);
		$this->view->resposta = $arr['nome'].' - '.$arr['sobrenome'];
		
    } 
    
    public function deleteAction() {
		$this->view->method = "delete";
    }
    
	public function newAction() {
		$this->view->method = "new";
    }
}
