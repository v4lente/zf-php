<?php

/**
 * Created on 17/09/2010
 *
 * Classe de operaчѕes bсsicas sem as operaчѕes padrѕes em telas de manutenчуo
 *
 * @filesource
 * @author			David Valente, Mсrcio Souza Duarte, Bruno Telѓ
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Controller_Abstract_Comum extends Marca_Controller_Abstract_Base {
    
	/**
    * Overrides the default constructer so we can call our own domain logic
    *
    * @param Zend_Controller_Request_Abstract $request
    * @param Zend_Controller_Response_Abstract $response
    * @param array $invokeArgs
    */
	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
    	parent::__construct($request, $response, $invokeArgs);
	}
	
    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Base#init()
     */
    public function init() {
        parent::init();
    }
    
}