<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe SistemaModel
 *
 * @filesource
 * @author          David Valente
 * @copyright       Copyright 2010 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebSistemaModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_SISTEMA';

    /**
     * Chave primÃ¡ria da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_SISTEMA');

     /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'WebSistemaRow';
    
    
    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
     * Tabelas dependente
     *
     * @var array
     */
    protected $_dependentTables = array('WebMenuSistemaModel');

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_SISTEMA',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'NO_SISTEMA',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
			   			   );
    
			   			   

	/**
     * Retorna todos os sistemas
     *
     * @return array array("cd_sistema"=>"no_sistema")
     */
    public function getSistemas() {

    	try {
			// Busca todos os sistemas cadastradas
			$todosSistemas = $this->fetchAll(null, "NO_SISTEMA ASC");
			$sistemas      = array();
	        foreach($todosSistemas as $sistema) {
	        	$sistemas[$sistema->CD_SISTEMA] = $sistema->NO_SISTEMA;
	        }

	        return $sistemas;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }

	/**
     * Retorna todos as transações do sistema
     *
     * @return array array("cd_sistema"=>"no_sistema")
     */
    public function getWebTransacao(){

    	try {
	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');

			// Busca todas as transações cadastradas para o usuário logado
			$select	= $db->select()->from(array("trans"=>"WEB_TRANSACAO"), array("trans.CD_TRANSACAO, trans.NO_TRANSACAO, trans.CD_MENU, menusis.CD_SISTEMA"))
								   ->join(array("menusis"=>"WEB_MENU_SISTEMA"), "menusis.CD_MENU = trans", array())
								   ->where("menusis.CD_SISTEMA) = {$this->CD_SISTEMA}");

			return $db->fetchAll($select);

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
    
    /**
     * 
     * Método responsável por buscar os sistemas
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaSistemas(&$params=array()) {
                
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("NO_SISTEMA    LIKE ?" => $params['no_sistema']))
                      ->addWhere(array("NO_PASTA_ZEND LIKE ?" => $params['no_pasta_zend']))
                      ->addWhere(array("DS_SISTEMA    LIKE ?" => $params['ds_sistema']))
                      ->getWhere();

        // Monta a busca na view
        $select = $this->select()
                       ->where($where)
                       ->order("ORD_SISTEMA ASC");
        
        // Retorna a consulta
        return $select;
        
    }
    
    
	/**
     * 
     * Método responsável por buscar os sistemas do CLIENTE/SERVIDOR e WEB
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function listaSistemas() {

    	$db = Zend_Registry::get("db");
    	      
        // Monta a query
        $select_web = $db->select()         			   
          		   	     ->from(array("SIS"=>"WEB_SISTEMA"), array(new Zend_Db_Expr("TO_CHAR(CD_SISTEMA) AS CD_SISTEMA"), "NO_SISTEMA", new Zend_Db_Expr("'WEB' AMBIENTE")));

		$select_pb = $db->select()         			   
         			    ->from(array("SIS"=>"SISTEMA"), array("CD_SISTEMA", "NO_SISTEMA", new Zend_Db_Expr("'CLIENTE/SERVIDOR' AMBIENTE")));

		$select = $db->select()
					 ->union(array($select_web, $select_pb))					   
					 ->order("NO_SISTEMA ASC");
		
		$resultado = $db->fetchAll($select);
		
        // Retorna a consulta
        return $resultado;
        
    }

}
?>