<?php

/**
 * Created on 17/09/2010
 *
 * Classe de operações básicas
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte, Bruno Teló
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */
abstract class Marca_Controller_Abstract_Operacao extends Marca_Controller_Abstract_Base {
	
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
     * método do padrão da marca
     * objetivo: utilizado para formularios de cadastro
     */
    abstract protected function novoAction();

    /**
     * Metodo salvarAction
     * método do padrão da marca
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    abstract protected function salvarAction();

    /**
     * Metodo excluirAction
     * método do padrão da marca
     * objetivo: utilizado para a operação de DELETE
     */
    abstract protected function excluirAction();

    /**
     * Metodo pesquisarAction
     * método do padrão da marca
     * objetivo: utilizado para executar pesquisas
     */
    abstract protected function pesquisarAction();

    /**
     * Metodo selecionarAction
     * método do padrão da marca
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    abstract protected function selecionarAction();

    /**
     * Metodo relatorioAction
     * método do padrão da marca
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    abstract protected function relatorioAction();
    
    /**
     * Metodo relatorio
     * objetivo: definir parâmetros para a geração de relatorios
     */
    public function relatorio() {
        
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '128M');

        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();
        
    }

}