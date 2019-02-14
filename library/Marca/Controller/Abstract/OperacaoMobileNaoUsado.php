<?php

/**
 * Created on 28/06/2013
 *
 * Classe de opera��es b�sicas
 *
 * @filesource
 * @author			M�rcio Souza Duarte, Bruno Tel�
 * @copyright		Copyright 2013 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Controller_Abstract_OperacaoMobile extends Marca_Controller_Abstract_BaseMobile {

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
     * Metodo novoAction
     * m�todo do padr�o da marca
     * objetivo: utilizado para formularios de cadastro
     */
    abstract protected function novoAction();

    /**
     * Metodo salvarAction
     * m�todo do padr�o da marca
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
    abstract protected function salvarAction();

    /**
     * Metodo excluirAction
     * m�todo do padr�o da marca
     * objetivo: utilizado para a opera��o de DELETE
     */
    abstract protected function excluirAction();

    /**
     * Metodo pesquisarAction
     * m�todo do padr�o da marca
     * objetivo: utilizado para executar pesquisas
     */
    abstract protected function pesquisarAction();

    /**
     * Metodo selecionarAction
     * m�todo do padr�o da marca
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    abstract protected function selecionarAction();

    /**
     * Metodo relatorioAction
     * m�todo do padr�o da marca
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    abstract protected function relatorioAction();
    
    /**
     * Metodo relatorio
     * objetivo: definir par�metros para a gera��o de relatorios
     */
    public function relatorio() {
        
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '128M');

        // Desabilita o layout padr�o
        $this->_helper->layout->disableLayout();
        
    }

}