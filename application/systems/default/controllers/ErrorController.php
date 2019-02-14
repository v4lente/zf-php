<?php

/**
 * Created on 18/11/2009
 *
 * Classe ErrorController
 * 
 * Mostra os erros do sistema.
 *
 * @filesource
 * @author			Márcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.modules
 * @version			1.0
 */
class ErrorController extends Zend_Controller_Action {
    
    /**
     * Método principal da classe.
     * 
     * Esse método recupera o erro e joga na tela para 
     * o usuário ver. Aqui deve-se tratar o erro antes 
     * de apresentar na página.
     * 
     * @return void
     */
    public function errorAction()
    {	
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Página não encontrada';
                break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Erro na aplicação';
                break;
        }
        
        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;
    }


}

