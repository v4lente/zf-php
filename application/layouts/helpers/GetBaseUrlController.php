<?php
/**
 * Action Helper para retornar o caminho completo do controlador
 *
 * @uses Zend_Controller_Action_Helper_Abstract
 *
 * @author Wagner Morales, Marcio Duarte
 *
 * @return string
 */

class Zend_View_Helper_GetBaseUrlController extends Zend_View_Helper_Abstract
{
    public function getBaseUrlController() {

        try {
            // Retorna o caminho completo do controlador
            return  $this->view->baseUrl()      . "/" .
                    $this->view->no_module      . "/" . 
                    $this->view->no_controller;

        } catch(Zend_Exception $e) {
            return $e->getMessage();
        }
    }
    
}