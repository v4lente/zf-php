<?php
/**
 * Created on 15/09/2010
 *
 * Modelo da classe UsuarioRelatorioModel
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2010 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class UsuarioRelatorioModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'USUARIO_RELATORIO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_USUARIO', 'CD_RELATORIO');
    
    
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
    protected $_referenceMap = array ('Usuario'          => array ('columns'       => array ('CD_USUARIO' ),
                                                                   'refTableClass' => 'UsuarioModel',
                                                                   'refColumns'    => array ('CD_USUARIO' )),

                                      'RelatorioUsuario' => array ('columns'       => array ('CD_RELATORIO' ),
                                                                   'refTableClass' => 'RelatorioUsuario',
                                                                   'refColumns'    => array ('CD_RELATORIO' ))
                                      );
    
    /**
     *  Regra de negocio do modelo
     *
     * @var array
     */
    protected $_rules = array(
                            array('name'         =>'CD_USUARIO',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório',
                                  'filter'       =>'Zend_Filter_StringToUpper'),

                            array('name'         =>'CD_RELATORIO',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório'),

                            array('name'         =>'CONFIG_ANALISE',
                                  'filter'       =>'Zend_Filter_StringToUpper')
                           );
                           
                           
    /**
     * Retorna os relatorios do usuário
     * [Tabelas]: RELATORIO_USUARIO, USUARIO_RELATORIO
     *
     * @param  string (where) condição da consulta
     * @param  string ordenação dos campos
     * @return array Array(Object)
     */
    public function getRelatorios($where = " 1=1 ", $order = "") {

        try {
            // Pega o adaptador padrão do banco
            $db = Zend_Registry::get('db');
            
            // Verifica se foi passada ordenação
            if(empty($order)) {
                $order = "ru.CD_RELATORIO";
            }
            
            // Busca todos os relatórios do usuário
            $select = $db->select()->from(array("ru" => "RELATORIO_USUARIO"), array("ru.CD_RELATORIO, ru.NO_MODULO, ru.DS_GRUPO, ru.NO_RELATORIO, ru.TX_CONSIDERACOES_COL"))
                                   ->join(array("ur" => "USUARIO_RELATORIO"), "ru.CD_RELATORIO = ur.CD_RELATORIO", array())
                                   ->where($where)
                                   ->order($order);
            
            return $db->fetchAll($select);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

}
?>