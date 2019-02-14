<?php

/*
// Turn off all error reporting
error_reporting(0);

// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Reporting E_NOTICE can be good too (to report uninitialized
// variables or catch variable name misspellings ...)
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL ^ E_NOTICE);

// Report all PHP errors
error_reporting(E_ALL);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

ini_set("display_errors", 1);
*/

ini_set("soap.wsdl_cache_enabled", "0");

// Carrega a(s) classe(s) que ser�(�o) utilizada(s) via soap
require_once LIBRARY_PATH . '/Soap/Classes/AuthUser.php';
require_once LIBRARY_PATH . '/Soap/Classes/Response.php';
require_once LIBRARY_PATH . '/Soap/Classes/MovFibria.php';
require_once LIBRARY_PATH . '/Soap/Classes/Array2XML.php';

/**
 * Classe ServiceTeconController
 *
 * Com o intuito de remodelar o Sistema de Estat�stica da SUPRG, este � o novo 
 * formato de arquivo que ser� em XML para o envio de informa��es via Web Service, 
 * tendo como objetivo listar as informa��es de movimenta��o operacionais realizadas 
 * pelo TECON.
 * 
 * @category   Marca Sistemas
 * @package    ServiceTeconController
 * @copyright  Copyright (c) 1991-2014 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: ServiceTeconController.php 0 2014-01-13 09:15:00 marcio $
 */
class IntegracaoPesagemController extends Zend_Controller_Action {

    // Armazena na vari�vel o endere�o do webserver no servidor
    private $_URI = '';
    
    /**
     * M�todo inicial ao qual carrega o wsdl do servidor.
     *
     * @return void
     */
    public function init() {
        
        // Captura a sess�o
		//$sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega as classes de autentica��o
        Zend_Loader::loadClass('Marca_Auth_Adapter_Autentica');
		Zend_Loader::loadClass('UsuarioModel');
        
        // Define o WSDL
        $site = 'http://' . $_SERVER['SERVER_NAME'] . $this->view->baseUrl();
        Zend_Registry::set('site', $site);
        
        // Seta o local da wsdl
        $this->_URI = $site . '/default/integracao-pesagem';

        // Desabilita o layout padr�o
        $this->_helper->layout->disableLayout();
        
    }
    
    /**
     * M�todo principal da classe ao qual define o que 
     * ser� carregado pelo lado do cliente.
     *
     * @return void
     */
    public function indexAction() {
        		
		if(APPLICATION_ENV == "suporte") {
			die("Servi�o momentaniamente desativado!");
		}
		
        $params = $this->getRequest()->getParams();
        
        if (isset($params['wsdl'])) {
             // Desabilita a renderiza��o da view
            $this->_helper->viewRenderer->setNoRender();
            
            //return the WSDL
            $this->hadleWSDL();
            
        } else {
             // Desabilita a renderiza��o da view
            $this->_helper->viewRenderer->setNoRender();
        
            //handle SOAP request
            $this->handleSOAP();
        }
    }

    /**
     * M�todo ao qual mostra a estrutura da classe a ser manipulada.
     *
     * @return void
     */
    private function hadleWSDL() {
        $strategy = 'Zend_Soap_Wsdl_Strategy_ArrayOfTypeComplex';
        $autodiscover = new Zend_Soap_AutoDiscover($strategy);
        //$site = Zend_Registry::get('site');
        //$autodiscover = new SoapServer($site . "/public/soap/fibria/pesagem.wsdl", array('soap_version' => SOAP_1_2));
        $autodiscover->setClass('MovFibria');
        //$autodiscover->setUri($this->_URI);
        $autodiscover->handle();
    }

    /**
     * M�todo que permite manipular as classes criadas para a transfer�ncia
     * de arquivos entre o Tecon e o Porto de Rio Grande.
     *
     * @return void
     */
    private function handleSOAP() {
        $options = array('uri' => $this->_URI, 'soap_version' => SOAP_1_2);
        $soap = new Zend_Soap_Server(null, $options);
        $soap->setClass('MovFibria');
        $soap->handle();
    }
    
    
    /**
     * M�todo de exemplo de comunica��o entre as partes.
     *
     * @return void
     */
    /*public function sendDocumentAction() {

        try {
            
			// Instancia a classe de retorno
			$response = new Response();
			
            // Op��es do cliente
            $options = array(
                'location'     => $this->_URI,
                'uri'          => $this->_URI,
                'soap_version' => SOAP_1_2,
                'compression'  => SOAP_COMPRESSION_ACCEPT//,
                //'login'		   => 'FIBRIA',
                //'password'     => 'f'
            );
            
            // Instancia o cliente soap
            $movFibria = new Zend_Soap_Client(null, $options);
            
            // Abre o arquivo xml para comunica��o via soap
            if(is_file('./soap/fibria/pesagem.xml')) {
                
                // Converte o arquivo xml em objeto
                $xmlObject = simplexml_load_file('./soap/fibria/pesagem.xml');
                
                // Carrega o documento para valida��o do xml
				$dom = new DOMDocument('1.0', 'utf-8');
                
                $dom->load('./soap/fibria/pesagem.xml');
                
                // Valida o esquema
                if($dom->schemaValidate('./soap/fibria/pesagem.xsd')) {

                    // Converte para json os dados a serem enviados
                    $jsonObject = json_encode($xmlObject);
                    
					$auth = new AuthUser();
					$auth->user     = "FIBRIA"; 
					$auth->password = "fibria2015";
					
                    // Chama o m�todo put da classe soap
                    $dados = $movFibria->enviaPesagem($xmlObject, $auth);
					
                } else {
                    throw new Zend_Exception(utf8_encode('Erro ao validar o esquema do XML.'));
                }
                
                if(is_soap_fault($dados)) {
                    throw new $dados;
                }
                
                $this->view->retorno = json_decode(json_encode($dados), true);
				
            } else {
                throw new Zend_Exception(utf8_encode('N�o foi poss�vel abrir o arquivo XML.'));
            }
            
        } catch (SoapFault $f) {
			
			// Retorna o erro
			$response->code        = "0";
			$response->title       = $fault->faultstring;
			$response->description = $fault->detail;
			
            $this->view->retorno = json_decode(json_encode($response), true);
            
        } catch (Zend_Exception $e) {
            $this->view->retorno = array("code" => "0", "title" => "Erro", "description" => $e->getMessage());
        }
        
        // Destroi o objeto
        unset($movFibria);
    }*/
	
}


?>