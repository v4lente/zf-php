<?php
/**
 * Created on 29/12/2010
 *
 * Modelo da classe WebMensagemModel
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebMensagemModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_MENSAGEM';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_MENSAGEM');

     /**
     * Classe para manipular a linha
     *
     * @var string
     */
    protected $_rowClass = 'WebMensagemRow';
    
    
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
	//protected $_referenceMap = array ();

    /**
	 *  Regra de negocio do modelo
	 *
	 * @var array
	 */
	protected $_rules = array(
							array('name'         =>'CD_MENSAGEM',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval'),

							array('name'         =>'TITULO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório'),
							
							array('name'         =>'DESCRICAO',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório'),
							
							array('name'         =>'DT_INI',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório'),
							
							array('name'         =>'DT_FIM',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório'),
							
							array('name'         =>'FL_URGENTE',
                                  'class'        =>'NotEmpty',
                                  'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'intval'),

							);
							
							
    /**
     * 
     * Método responsável por buscar as mensagens
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaMensagens(&$params=array()) {
        
        // Monta a condição
        $where = $this->addWhere(array("WM.CD_MENSAGEM    = ?" => $params['cd_mensagem']))
                      ->addWhere(array("WM.TITULO      LIKE ?" => $params['titulo']))
                      ->addWhere(array("WM.DESCRICAO   LIKE ?" => $params['descricao']))
                      ->addWhere(array("WM.DT_INI         = ?" => $params['dt_ini']))
                      ->addWhere(array("WM.DT_FIM         = ?" => $params['dt_fim']))
                      ->addWhere(array("WM.FL_URGENTE     = ?" => $params['fl_urgente']))
                      ->getWhere();
                      
        // Retorna as transações, menus e sistemas
        $select = $this->select()
                       ->from(array("WM" => "WEB_MENSAGEM"), array("WM.CD_MENSAGEM", 
                                                                   "WM.TITULO", 
                                                                   "WM.DESCRICAO", 
                                                                   "WM.DT_INI",
                                                                   "WM.DT_FIM",
                                                                   "WM.FL_URGENTE"))
                       ->where($where)
                       ->order("WM.CD_MENSAGEM ASC");
        
        // Retorna a consulta
        return $select;
        
    }
    
	/**
     * 
     * Método responsável por buscar as mensagens para mostrar ao usuário
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return object $select Objeto que contém os dados da query
     */
    public function queryBuscaMensagensGrupo(&$params=array()) {
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Monta a condição
        $where = $this->addWhere(array("WM.CD_MENSAGEM   = ?" => $params['cd_mensagem']))
                      ->addWhere(array("WM.TITULO     LIKE ?" => $params['titulo']))
                      ->addWhere(array("WM.DESCRICAO  LIKE ?" => $params['descricao']))
                      ->addWhere(array("WM.DT_INI       <= ?" => $params['dt_ini']))
                      ->addWhere(array("WM.DT_FIM       >= ?" => $params['dt_fim']))
                      ->addWhere(array("WM.FL_URGENTE    = ?" => $params['fl_urgente']))
                      ->getWhere();
                      
        // Retorna as transações, menus e sistemas
        $select = $this->select()
                       ->distinct()
                       ->setIntegrityCheck(false)
                       ->from(array("WM" => "WEB_MENSAGEM"), array("WM.CD_MENSAGEM", 
                                                                   "WM.TITULO", 
                                                                   "WM.DESCRICAO", 
                                                                   "WM.DT_INI",
                                                                   "WM.DT_FIM",
                                                                   "WM.FL_URGENTE"))
                       ->join(array("WGM" => "WEB_GRUPO_MENSAGEM"), "WM.CD_MENSAGEM = WGM.CD_MENSAGEM", array())
	                   ->join(array("GP"  => "WEB_GRUPO"), "WGM.CD_GRUPO = GP.CD_GRUPO", array())
	                   ->join(array("UG"  => "WEB_GRUPO_USUARIO"), "GP.CD_GRUPO = UG.CD_GRUPO", array())
	                   ->where("TRIM(UG.CD_USUARIO) LIKE TRIM('{$sessao->perfil->CD_USUARIO}')")
                       ->where($where)
                       ->order("WM.FL_URGENTE DESC, WM.DT_FIM ASC");

        // Retorna a consulta
        return $select;        
    }	
}
?>