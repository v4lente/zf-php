<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe UsuarioGrupoModel
 *
 * @filesource
 * @author          David
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebGrupoUsuarioModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_GRUPO_USUARIO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_GRUPO', 'CD_USUARIO', 'DT_ACESSO_INI');

    
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
	protected $_referenceMap = array ('Usuario' => array ('columns'       => array ('CD_USUARIO' ),
														  'refTableClass' => 'UsuarioModel',
														  'refColumns'    => array ('CD_USUARIO' )),

									  'Grupo'   => array ('columns'       => array ('CD_GRUPO' ),
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

							array('name'         =>'CD_USUARIO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'Zend_Filter_StringToUpper'),
							
							array('name'         =>'DT_ACESSO_INI',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
							);
}
?>