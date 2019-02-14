<?php
/**
 * Created on 03/08/2011
 *
 * Modelo da tabela ALL_VIEWS
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2011 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class AllViewsModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'ALL_VIEWS';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array( 'OWNER', 'TABLE_NAME');
    
    
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
							array('name'         =>'OWNER',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
							,
							array('name'         =>'VIEW_NAME',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
							);

    /**
     * Retorna os dados das views
     *
     * @return object Zend_Db_Select
     */
    public function queryBuscaDadosTabelasViews(&$params=array()){

    	try {
			
			// Define os filtros para a cosulta
	        $where = $this->addWhere(array("ALL_VIEWS.VIEW_NAME LIKE ?" => $params['view_name']))
	        			  ->addWhere(array("COL.TNAME           LIKE ?" => $params['tname']))
	                      ->getWhere();
			
    		// Busca todas as views cadastradas
			$select	= $this->select()
						   ->setIntegrityCheck(false)
						   ->from(array("ALL_VIEWS"), array("TABLE_NAME"       => "COL.TNAME", 
													        "NO_TABELA"        => "ALL_TAB_COMMENTS.COMMENTS",
													        "COLUMN_NAME"      => "COL.CNAME", 
													        "NO_COLUNA"        => "ALL_COL_COMMENTS.COMMENTS",
													        "PK"               => new Zend_Db_Expr("NULL"),
													        "TIPO"             => new Zend_Db_Expr("CASE 
																			                            WHEN COL.COLTYPE = 'VARCHAR'   THEN 'STRING'
																		 					            WHEN COL.COLTYPE = 'VARCHAR2'  THEN 'STRING'
																		 					            WHEN COL.COLTYPE = 'NVARCHAR2' THEN 'STRING'
																		 					            WHEN COL.COLTYPE = 'CHAR'      THEN 'STRING'
																		 					            WHEN COL.COLTYPE = 'NUMBER'    THEN 'NUMÉRICO'
																		 					            WHEN COL.COLTYPE = 'RAW' 	   THEN 'BLOB'
																		 					            WHEN COL.COLTYPE = 'LONG RAW'  THEN 'BLOB'
																		 					            WHEN COL.COLTYPE = 'CLOB'      THEN 'BLOB'
																			                        ELSE
																			                            COL.COLTYPE
																			                        END"),
													        "TAMANHO"          => new Zend_Db_Expr("DECODE (COL.COLTYPE, 'NUMBER', CASE WHEN (TO_NUMBER (SCALE) > 0) THEN PRECISION || ',' || SCALE ELSE TO_CHAR (PRECISION) END, WIDTH)"),   
													        "NULLABLE"         => new Zend_Db_Expr("DECODE(COL.NULLS, 'Y', 'NULL', 'NOT NULL')"),
													        "DATA_DEFAULT"     => "COL.DEFAULTVAL",
													        "SEARCH_CONDITION" => new Zend_Db_Expr("NULL"),
													        "CONSTRAINT_NAME"  => new Zend_Db_Expr("NULL"),
													        "TABELA_PAI"	   => new Zend_Db_Expr("NULL")))
						   ->joinLeft(array("COL"),              "ALL_VIEWS.VIEW_NAME  = COL.TNAME",                    array())
						   ->joinLeft(array("ALL_TAB_COMMENTS"), "ALL_VIEWS.VIEW_NAME  = ALL_TAB_COMMENTS.TABLE_NAME AND 
   															      ALL_VIEWS.OWNER      = ALL_TAB_COMMENTS.OWNER",       array())
						   ->joinLeft(array("ALL_COL_COMMENTS"), "COL.TNAME            = ALL_COL_COMMENTS.TABLE_NAME AND 
														          COL.CNAME            = ALL_COL_COMMENTS.COLUMN_NAME", array())
						   ->where("ALL_VIEWS.OWNER = 'PORTO'")
						   ->where($where)
						   ->order("1 ASC, 5 ASC, 3 ASC");

			return $select;

    	} catch (Exception $e) {
    		echo $e->getMessage();
        }
    }

}
?>