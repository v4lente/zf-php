<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe PrivilegioModel
 *
 * @filesource
 * @author          David
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebTransacaoAcaoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_TRANSACAO_ACAO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_TRANSACAO', 'CD_ACAO');
    
    
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

    protected $_dependentTables = array('WebGrupoTransacaoModel');

    /**
	 *  Tabelas que a classe faz referencia
	 *
	 * @var array
	 */
    protected $_referenceMap = array ('Transacao' => array ('columns'       => array ('CD_TRANSACAO'),
															'refTableClass' => 'WebTransacaoModel',
															'refColumns'    => array ('CD_TRANSACAO')),

									  'Acao'      => array ('columns'       => array ('CD_ACAO'),
															'refTableClass' => 'WebAcaoModel',
															'refColumns'    => array ('CD_ACAO'))
									  );

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_TRANSACAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'CD_ACAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval')
							);
}
?>