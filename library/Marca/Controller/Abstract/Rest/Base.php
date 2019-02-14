<?php 

/**
 * Created on 12/02/2010
 *
 * Modelo da classe Marca_BaseRestControllerAbstract
 * 
 * Esta � uma interface usada para conectar no banco de dados
 *
 * @filesource
 * @author			M�rcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Controller_Abstract_Rest_Base extends Zend_Rest_Controller {


	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Zend_Controller_Action#init()
	 */
	public function init() {

		Zend_Loader::loadClass('Marca_Auth_Adapter_Autentica');
		
		$this->_helper->layout->disableLayout();
	}	
 	
	/**
	 * Recebe os dados do controlador e retorna os mesmos em XML 
	 * 
	 * @param object $obj
	 * @return void
	 */
	public static function getXML($obj) {
    	$doc = new DOMDocument();
		$doc->formatOutput = true;
		$root_element = $doc->createElement("response");
		$doc->appendChild($root_element);
 
    	foreach($obj as $var => $value) {
        	$statusElement = $doc->createElement($var);
			if (!is_array($value)) {
				$statusElement->appendChild($doc->createTextNode($value));
				$root_element->appendChild($statusElement);
			} else {
				Marca_Controller_Abstract_Rest_Base::_xmlHelper(&$doc, &$root_element, &$statusElement, &$value);
			}
		}
		
		echo $doc->saveXML();
    }
    
    /**
	 * Monta a estrutura XML recursivamente 
	 * 
	 * @param object $doc
	 * @param object $root_element
	 * @param object $statusElement
	 * @param string $value
	 * @return void
	 */
	public static function _xmlHelper(&$doc, &$root_element, &$statusElement, &$value) {
		if (is_array($value)) {
			foreach ($value as $key => $val) {
				if (is_array($val)) {
					Marca_Controller_Abstract_Rest_Base::_xmlHelper(&$doc, &$root_element, &$statusElement, $val);
				} else if (is_object($val)) {
					$se = $doc->createElement(str_replace('object','',get_class($val)));
					$arr = get_object_vars($val);
					Marca_Controller_Abstract_Rest_Base::_xmlHelper(&$doc, &$root_element, &$se, $arr);
					$root_element->appendChild($se);
 
				} else {
					//print $key . " => " . $val . "\n";
					$se = $doc->createElement($key);
					$se->appendChild($doc->createTextNode($val));
					$statusElement->appendChild($se);
				}
			}
			//print_r($value);
		} else {
			$statusElement->appendChild($doc->createTextNode($value));
			$root_element->appendChild($statusElement);
		}
	}
	
	
	/**
     * Autentica os usu�rios para poderem consumir o servi�o
     * 
     * @param string $usuario
     * @param string $senha
     * @return array
     */ 
	public function authenticate($usuario, $senha) {
		
		try {
			// Inicializa a classe de autentica��o
	        $this->_autenticacao = Marca_Auth::getInstance();
	        $this->_autenticacao->setStorage(new Zend_Auth_Storage_Session('login_usuario_rest'));
	        
			$resultado = $this->_autenticacao->authenticate(new Marca_Auth_Adapter_Autentica($usuario, $senha));
			// Retorna o c�digo de valida��o
			switch($resultado->getCode()) {
			    case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
			        return Array(false, "FAILURE_IDENTITY_NOT_FOUND");
			        break;
			    case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
			        return Array(false, "FAILURE_CREDENTIAL_INVALID");
			        break;
			    default:
			        return Array(true, "AUTENTICADO");
			        break;
			}
		} catch(Exception $e) {
			return false;
		}
		
	}
	
	/**
     * M�todo obrigat�rio da interface
     * 
     * @return void
     */ 
	public function indexAction() {}
 	
	/**
     * M�todo obrigat�rio da interface
     * 
     * @return void
     */
	public function getAction() {}
    
	/**
     * M�todo obrigat�rio da interface
     * 
     * @return void
     */
    public function postAction() {}
    
    /**
     * M�todo obrigat�rio da interface
     * 
     * @return void
     */
    public function putAction() {} 
    
    /**
     * M�todo obrigat�rio da interface
     * 
     * @return void
     */
    public function deleteAction() {}
    
}
