<?php
/**
 * Created on 27/04/2011
 *
 * Modelo da classe UsuarioInternetModel
 *
 * @filesource
 * @author			Márcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class UsuarioInternetModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'USUARIO_INTERNET';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_USUARIO_INTERNET');

    /**
     * Desativa a exclusão lógica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;
    

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_USUARIO_INTERNET',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'Zend_Filter_StringToUpper'),

							array('name'         =>'FL_SUPER_USUARIO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval')
			   			   );
			   			   
			   			   
			   			   
    /**
     * Retorna todos os usuarios da internet
     *
     * @return array
     */
    public function getUsuariosInternet(){

        try {
            // Busca todos os grupos cadastradas
            $todosUsuarios = $this->fetchAll(null, "CD_USUARIO_INTERNET ASC");
            $usuarios      = array();
            foreach($todosUsuarios as $usuario) {
                $usuarios[$usuario->CD_USUARIO_INTERNET] = $usuario->OBSERVACAO;
            }

            return $usuarios;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
}
?>