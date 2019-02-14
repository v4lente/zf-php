<?php

/**
 * Created on 27/03/2013
 *
 * Classe Queue
 *
 * Esta classe tem a fun��o de gravar as mensagens do sistema e mostrar 
 * posterior as mesmas.
 * Foi criada para utilizar o m�todo "record" ao inv�s do "send" quando
 * se utiliza um "redirect".
 *
 * @filesource
 * @author			M�rcio
 * @copyright		Copyright 2013 Marca
 * @package			zendframework
 * @subpackage		zendframework.application
 * @version			1.0
 */

class Marca_Queue extends Zend_Queue {
    
    public function __construct($spec, $options = array()) {
        
        parent::__construct($spec, $options);
        
        // Busca a sess�o portoweb existente
        $sessao = new Zend_Session_Namespace('portoweb');
        
        if (isset($sessao->mensagemSistema) && 
            count($sessao->mensagemSistema) > 0) {
            
            $mensagemSistema = $this;
			$mensagemSistema->send($sessao->mensagemSistema);
            unset($sessao->mensagemSistema);
        }
        
    }
    
    /**
	 * 
	 * Grava a mensagem na sessao
	 * 
	 * @param array $msg
	 */
	public function record($msg) {
        
        // Busca a sess�o portoweb existente
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Grava a mensagem na sessao
        $sessao->mensagemSistema = $msg;
        
    }
    
}