<?php
/**
 * Created on 20/08/2012
 *
 * Modelo da classe WebGrupoTabelaConsultaModel
 *
 * @filesource
 * @author          Maur�cio Pesenti Spolavori
 * @copyright       Copyright 2011 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */
class WebGrupoTabelaConsultaModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'WEB_GRUPO_TABELA_CONSULTA';

    /**
     * Chave prim�ria da tabela
     *
     * @var string
     */
    protected $_primary = array('CD_GRUPO', 'CD_CONSULTA');

    /**
     * Desativa a exclus�o l�gica
     *
     * @var boolean
     */
    protected $_logicalDelete = false;

    /**
	 *  Regra de negocio do modelo(sao os campos notNull)
	 *  
	 * @var array
	 */
	protected $_rules = array(						
							array('name'         =>'CD_GRUPO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigat�rio',
						          'filter'       =>'intval'),
        
							array('name'         =>'CD_CONSULTA',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigat�rio',
						          'filter'       =>'intval'));

	/**
	* 
	* M�todo respons�vel por buscar as consultas relacionadas a um grupo
	*
	* Est� sendo utilizado nas transa��es: TabGrupoConsulta
	* 
	* @return object $select Objeto que cont�m os dados da query
	*/
	public function buscaConsultas(&$params=array()) {
		// Captura a sess�o
		$sessao = new Zend_Session_Namespace('portoweb');

		try {
		
			// Define os filtros para a cosulta
			$where = $this->addWhere(array("WG.CD_GRUPO    = ?" => $params['cd_grupo']))
						  ->addWhere(array("WG.CD_CONSULTA = ?" => $params['cd_consulta']))
						  ->getWhere();

			// Monta a busca
			$select = $this->select()
					->setIntegrityCheck(false)
					->from( array ("WG" => "WEB_GRUPO_TABELA_CONSULTA "), array("WG.CD_GRUPO", 
                                                                                "MC.CD_CONSULTA",
                                                                                "TITULO"        => new Zend_Db_Expr("TRIM(MC.TITULO)"),
                                                                                "CD_USUARIO"    => new Zend_Db_Expr("TRIM(MC.CD_USUARIO)"),
                                                                                "DTHR_CADASTRO" => new Zend_Db_Expr("TO_CHAR(MC.DTHR_CADASTRO, 'DD/MM/YYYY') || ' ' || TO_CHAR(MC.DTHR_CADASTRO, 'DD/MM/YYYY')"),
                                                                                "FL_ATIVO"      => new Zend_Db_Expr("CASE
                                                                                                                        WHEN MC.FL_ATIVO = 1 THEN 
                                                                                                                             'SIM' 
                                                                                                                         ELSE 
                                                                                                                            'N�O' 
                                                                                                                      END")
                                                                                ))
					->join ( array ("MC" => "CONSULTA"), "MC.CD_CONSULTA = WG.CD_CONSULTA", array())
					->where($where)
					->order("1 ASC");

			//echo $select;
			
			return $select;

		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	
}
?>