<?php
/**
 * Created on 30/12/2010
 *
 * Modelo da classe WebGrupoMensagemModel
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2009 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class WebGrupoMensagemModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_GRUPO_MENSAGEM';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_MENSAGEM', 'CD_GRUPO');

    
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
	protected $_referenceMap = array ('WebMensagem' => array ('columns'       => array ('CD_MENSAGEM' ),
														      'refTableClass' => 'WebMensagemModel',
														      'refColumns'    => array ('CD_MENSAGEM' )),

									  'WebGrupo'       => array ('columns'       => array ('CD_GRUPO' ),
														      'refTableClass' => 'WebGrupoModel',
														      'refColumns'    => array ('CD_GRUPO' ))
									  );


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

							array('name'         =>'CD_GRUPO',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
							      'filter'       =>'Zend_Filter_StringToUpper')
							);
	
	
	/**
	 *  Busca os grupos das mensagens
	 */
	public function buscaGruposMensagem(&$params=array()) {
		
		$where = $this->addWhere(array("WM.CD_MENSAGEM = ?" => $params["cd_mensagem"]))
		              ->addWhere(array("GP.CD_GRUPO    = ?" => $params["cd_grupo"]))
					  ->getWhere();
		
		// Monta e executa a consulta
        $select = $this->select()
						->setIntegrityCheck(false)
						->from(array("WM" => "WEB_MENSAGEM"), array("WM.CD_MENSAGEM", "GP.CD_GRUPO", "GP.NO_GRUPO"))
						->join(array("WGM" => "WEB_GRUPO_MENSAGEM"), "WM.CD_MENSAGEM = WGM.CD_MENSAGEM", array())
						->join(array("GP"  => "WEB_GRUPO"),			 "WGM.CD_GRUPO   = GP.CD_GRUPO",     array())
						->where($where)
						->order(array("GP.NO_GRUPO ASC"));

        // Retorna a consulta
        return $select;
		
	}
	
	
	/**
	 *  Busca os grupos/usuários das mensagens
	 */
	public function buscaGruposUsuariosMensagem(&$params=array()) {
	
		$where = $this->addWhere(array("WM.CD_MENSAGEM = ?" => $params["cd_mensagem"]))
		->addWhere(array("UG.CD_GRUPO    = ?" => $params["cd_grupo"]))
		->addWhere(array("UG.CD_USUARIO  = ?" => $params["cd_usuario"]))
		->getWhere();
	
		// Monta e executa a consulta
		$select = $this->select()
		->setIntegrityCheck(false)
		->from(array("WM" => "WEB_MENSAGEM"), array("WM.CD_MENSAGEM",
							                                            "GP.CD_GRUPO", 
							                                            "GP.NO_GRUPO", 
							                                            "US.CD_USUARIO", 
							                                            "US.NO_USUARIO"))
		->joinLeft(array("WGM" => "WEB_GRUPO_MENSAGEM"), "WM.CD_MENSAGEM = WGM.CD_MENSAGEM", array())
		//->joinLeft(array("GP"  => "GRUPO"), 			 "WGM.CD_GRUPO   = GP.CD_GRUPO",     array())
		->joinLeft(array("GP"  => "WEB_GRUPO"), 			 "WGM.CD_GRUPO   = GP.CD_GRUPO",     array())
		//->joinLeft(array("UG"  => "USUARIO_GRUPO"),      "GP.CD_GRUPO    = UG.CD_GRUPO",     array())
		->joinLeft(array("UG"  => "WE_GRUPO_USUARIO"),      "GP.CD_GRUPO    = UG.CD_GRUPO",     array())
		->joinLeft(array("US"  => "USUARIO"),            "UG.CD_USUARIO  = US.CD_USUARIO",   array())
		->where($where)
		->order(array("GP.NO_GRUPO ASC", "US.NO_USUARIO"));
	
		// Retorna a consulta
		return $select;
	
	}
	
	
	/**
	*  Busca os grupos extras que não estão conectados ao código da mensagem passada
	*/
	public function buscaGruposMensagemNaoConectados(&$params=array()) {
	
		$where = $this->addWhere(array("WM.CD_MENSAGEM = ?" => $params["cd_mensagem"]))
					  ->getWhere();
	
		// Monta e executa a consulta
		$select = $this->select()
					   ->setIntegrityCheck(false)
					   //->from(array("GP"  => "GRUPO"), array("GP.CD_GRUPO", "GP.NO_GRUPO"))
					   ->from(array("GP"  => "WEB_GRUPO"), array("GP.CD_GRUPO", "GP.NO_GRUPO"))
					   ->joinLeft(array("WGM" => "WEB_GRUPO_MENSAGEM"), "GP.CD_GRUPO     NOT IN(WGM.CD_GRUPO)",   array())
					   ->joinLeft(array("WM" => "WEB_MENSAGEM"), 		"WGM.CD_MENSAGEM = WM.CD_MENSAGEM AND {$where}", array())
					   ->order(array("GP.NO_GRUPO ASC"));
		
		// Retorna a consulta
		return $select;
	
	}
	
}
?>