<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe AcessoTransacaoModel
 *
 * @filesource
 * @author          David
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebGrupoTransacaoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_GRUPO_TRANSACAO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_GRUPO', 'CD_TRANSACAO', 'FL_PERMISSAO', 'DT_ACESSO_INI');

     /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'WebGrupoTransacaoRow';
    
    
    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
	 *  Tabelas que a classe faz referencia
	 *
	 * @var array
	 */
	protected $_referenceMap = array ('TransacaoAcao' => array ('columns'       => array ('CD_TRANSACAO'),
															'refTableClass' => 'WebTransacaoModel',
															'refColumns'    => array ('CD_TRANSACAO')),

									  'Grupo'     => array ('columns'       => array ('CD_GRUPO' ),
															'refTableClass' => 'WebGrupoModel',
															'refColumns'    => array ('CD_GRUPO' ))
									  );

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_GRUPO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'CD_TRANSACAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'FL_PERMISSAO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),
							
							array('name'         =>'DT_ACESSO_INI',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')

							);

}
?>