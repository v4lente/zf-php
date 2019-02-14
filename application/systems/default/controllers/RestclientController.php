<?php

class RestclientController extends Zend_Controller_Action {

	public function init() 	{
		$this->_helper->layout->disableLayout(true);
		//$this->_helper->viewRenderer->setNoRender();
        die("asdf");
	}
	
	public function indexAction() {
		
		$arr['nome'] = 'thiago';
		$arr['sobrenome'] = 'cabelleira';			
		$arr_json = Zend_Json::encode($arr);
		$arr_json = urlencode($arr_json);
		//Zend_Debug::dump($arr_json);
		
	    $client = new Zend_Rest_Client('http://www.portoriogrande.com.br/desenv/marcio/zf/restserver/');
	    
	    $result = $client->get();
	    
	    //$result = $client->post();
	    $client = null;
	      
	    //enviando tudo para a view do arquivo
	    $this->view->setEncoding('iso-8859-1');
	    
	    //foreach($result as $indice => $valor) {
    	//	eval("\$this->view->$indice = '$valor';");
    	//}
    	$this->view->resposta = $result->resposta;
	    
	}
	
	public function autenticarAction() {
		
	    $client = new Zend_Rest_Client('http://www.portoriogrande.com.br/desenv/marcio/zf/restserver/');
	    $result = $client->post();
	    $client = null;
	      
	    //enviando tudo para a view do arquivo
	    $this->view->setEncoding('iso-8859-1');
	    $this->view->result = $result;
	    
	}

	public function getAction() {
		
	    $client = new Zend_Rest_Client('http://www.portoriogrande.com.br/desenv/marcio/zf/restserver/get/');
	    $result = $client->get();
	    $client = null;
	      
	    //enviando tudo para a view do arquivo
	    $this->view->setEncoding('iso-8859-1');
	    $this->view->result = $result;
	    
	}	
	
	public function postAction() {
		
	    $client = new Zend_Rest_Client('http://www.portoriogrande.com.br/desenv/marcio/zf/restserver/');
	    $result = $client->post();
	    $client = null;
	      
	    //enviando tudo para a view do arquivo
	    $this->view->setEncoding('iso-8859-1');
	    $this->view->result = $result;
	    
	}
	
	public function newAction() {
		
	    $client = new Zend_Rest_Client('http://www.portoriogrande.com.br/desenv/marcio/zf/restserver/new/');
	    $result = $client->post();
	    $client = null;
	      
	    //enviando tudo para a view do arquivo
	    $this->view->setEncoding('iso-8859-1');
	    $this->view->result = $result;
	    
	}

	public function putAction() {
		
	    $client = new Zend_Rest_Client('http://www.portoriogrande.com.br/desenv/marcio/zf/restserver/put/');
	    $result = $client->get();
	    $client = null;
	      
	    //enviando tudo para a view do arquivo
	    $this->view->setEncoding('iso-8859-1');
	    $this->view->result = $result;
	    
	}	
		
}