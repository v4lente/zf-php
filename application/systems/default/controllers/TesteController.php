<?php

class TesteController extends Marca_Controller_Abstract_Comum {
	
    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();
        
        Zend_Loader::loadClass("BancoModel");
    }
    
    /**
     * Metodo index
     * objetivo: M�todo principal da classe
     */
	public function indexAction() {
	    $this->_helper->layout->disableLayout();
	}
	
	/**
     * Metodo salvar
     * objetivo: utilizado para as opera��es de INSERT/UPDATE
     */
	public function salvarAction() {
	    
	    // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
	    // Instancia o modelo
	    $banco = new BancoModel();
	    
	    // Monta o array com os dados de inser��o
	    $dados = array(
	               "NO_ABREV" => $params["no_abrev"], 
	               "NO_BANCO" => $params["no_banco"]
	             );
	             
	    // Seta a condi��o para altera��o da tupla
	    $where = "NR_BANCO = " . $params["nr_banco"];

	    // Grava os dados no banco
	    $banco->update($dados, $where);
	}
	
    /**
     * Metodo excluir
     * objetivo: utilizado para a opera��o de DELETE
     */
    public function excluirAction() { 
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
                
        // Instancia o modelo de dados
        $banco = new BancoModel();
        
        // Monta a condi��o do exclus�o
        $where = "NR_BANCO  = " . $params['nr_banco']; 
        
        // Exclui o registro
        $banco->delete($where);
    }
	
    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $banco = new BancoModel();
        
        // Define os filtros para a consulta
        $where = $banco->addWhere(array("NR_BANCO    = ?" => $params['nr_banco']))
                       ->addWhere(array("NO_ABREV LIKE ?" => $params["no_abrev"]))
                       ->addWhere(array("NO_BANCO LIKE ?" => $params['no_banco']))                                      
                       ->getWhere();
        
        // Monta a query
        $select = $banco->select()
                        ->where($where)
                        ->order("NO_BANCO ASC");
        
        // Executa a consulta
        $resConsulta = $banco->fetchAll($select);
        
        // Joga para a view o resultado da consulta
        $this->view->resConsulta = $resConsulta;
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params);
    }

}
