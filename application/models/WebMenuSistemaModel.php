<?php
/**
 * Created on 03/12/2009
 *
 * Modelo da classe TransacaoModel
 *
 * @filesource
 * @author          David
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebMenuSistemaModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_MENU_SISTEMA';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_MENU');
    
    
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
    protected $_dependentTables = array('WebTransacaoModel');

     /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'WebMenuSistemaRow';

    /**
	 *  Tabelas que a classe faz referencia
	 *
	 * @var array
	 */
    protected $_referenceMap = array ('Sistema' => array ('columns'       => array ('CD_SISTEMA'),
									 				   	  'refTableClass' => 'WebSistemaModel',
													   	  'refColumns'    => array ('CD_SISTEMA'))
									 );

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_MENU',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'NO_MENU',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),

							array('name'         =>'ORD_MENU',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'intval'),

							array('name'         =>'CD_SISTEMA',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'intval')

			   			   );
			   			   
    /**
     * Retorna os menus dos sistemas
     *
     * @param  string $cd_sistema Código do sistema
     * @return array  array("cd_menu" => "no_menu")
     */
    public function getMenus($cd_sistema="") {

        try {
            $where = null;
            if($cd_sistema != "") {
                $where = "CD_SISTEMA = {$cd_sistema}";
            }
            
            // Busca todos os menus cadastrados
            $resPesquisa = $this->fetchAll($where, "NO_MENU");
            $retorno     = array();
            foreach($resPesquisa as $linha) {
                $retorno[$linha->CD_MENU] = $linha->NO_MENU;
            }

            return $retorno;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    
    /**
     * 
     * Método responsável por buscar os menus
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaMenusSistema(&$params=array()) {
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("WM.CD_MENU     = ?" => $params['cd_menu']))
                      ->addWhere(array("WS.CD_SISTEMA  = ?" => $params['cd_sistema']))
                      ->addWhere(array("WM.NO_MENU LIKE ?"  => $params['no_menu']))
                      ->addWhere(array("WM.DS_MENU LIKE ?"  => $params['ds_menu']))
                      ->addWhere(array("WM.ORD_MENU LIKE ?" => $params['ord_menu']))
                      ->getWhere();
        
        // Retorna os menus e seus sistemas
        $select = $this->select()
                       ->setIntegrityCheck(false)
                       ->from(array("WM" => "WEB_MENU_SISTEMA"), array("WM.*", "WS.*"))
                       ->join(array("WS" => "WEB_SISTEMA"), "WS.CD_SISTEMA = WM.CD_SISTEMA", array())
                       ->where($where)
                       ->order("WM.NO_MENU ASC");
                       
        // Retorna a consulta
        return $select;
        
    }
    
}
?>