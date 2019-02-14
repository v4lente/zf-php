<?php
/**
 * Created on 04/08/2010
 *
 * Modelo da classe MetodoModel
 *
 * @filesource
 * @author          David, Marcio
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebAcaoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_ACAO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_ACAO');
    
    
    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
     * sequencia da tabela
     *
     * @var string
     */
    //protected $_sequence = 'CD_ACAO';

    /**
	 *  Tabelas que a classe faz referencia
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('Transacao' => array ('columns'       => array ('CD_SISTEMA', 'CD_TRANSACAO'),
															'refTableClass' => 'TransacaoModel',
															'refColumns'    => array ('CD_SISTEMA', 'CD_TRANSACAO'))
									  );

	/**
     * Tabelas dependente
     *
     * @var array
     */
    protected $_dependentTables = array('WebTransacaoAcaoModel');

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_ACAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'NO_ACAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
							);
							
    /**
     * Retorna todos as ações
     *
     * @return array array("cd_acao"=>"no_acao")
     */
    public function getAcoes() {

        try {
            // Busca todos as ações cadastradas
            $todosAcoes = $this->fetchAll(null, "NO_ACAO ASC");
            $acoes      = array();
            foreach($todosAcoes as $acao) {
                $acoes[$acao->CD_ACAO] = $acao->NO_ACAO;
            }

            return $acoes;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    
	/**
     * Retorna todos as ações para o montar o menu
     *
     * @return array
     */
    public function getAcoesMenu() {

        try {
            // Busca todos as ações cadastradas
            return $this->fetchAll("FL_MENU = 1 AND CD_ACAO <= 13", "ORD_ACAO DESC");

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * 
     * Método responsável por buscar as ações da web
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaAcoes(&$params=array()) {
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("CD_ACAO      = ?" => $params['cd_acao']))
                      ->addWhere(array("NO_ACAO   LIKE ?" => $params['no_acao']))
                      ->addWhere(array("DS_ACAO   LIKE ?" => $params['ds_acao']))
                      ->addWhere(array("FL_MENU      = ?" => $params['fl_menu']))
                      ->addWhere(array("FL_TIPO_ACAO = ?" => $params['fl_tipo_acao']))
                      ->getWhere();
        
        // Monta a busca na view
        $select = $this->select()
                       ->where($where)
                       ->order("CD_ACAO ASC");
        
        // Retorna a consulta
        return $select;
        
    }
}
?>