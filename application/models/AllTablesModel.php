<?php
/**
 * Created on 02/08/2011
 *
 * Modelo da tabela ALL_TABLES
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2011 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class AllTablesModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'ALL_TABLES';

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
							array('name'         =>'TABLE_NAME',
							      'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
							);

    /**
     * Retorna os dados das tabelas, colunas e comentários do dicionário de dados
     *
     * @return object Zend_Db_Select
     */
    public function queryBuscaDadosTabelasDicionarioDados(&$params=array()) {

    	try {
						
			// Busca as chaves primárias
			$selectPK = $this->select()
						   ->setIntegrityCheck(false)
						   ->from(array("ALL_CONSTRAINTS"), array("ALL_CONSTRAINTS.TABLE_NAME", 
						   										  "ALL_CONS_COLUMNS.COLUMN_NAME", 
						   										  "PK" => new Zend_Db_Expr("'PK'")))
						   ->join(array("ALL_CONS_COLUMNS"), "ALL_CONSTRAINTS.CONSTRAINT_NAME = ALL_CONS_COLUMNS.CONSTRAINT_NAME AND
           													  ALL_CONSTRAINTS.OWNER           = ALL_CONS_COLUMNS.OWNER", array())
						   ->where("ALL_CONSTRAINTS.OWNER           = 'PORTO'")
						   ->where("ALL_CONSTRAINTS.CONSTRAINT_TYPE = 'P'");
						   
			// Busca os checks de campos
			$selectCheck = $this->select()
						        ->setIntegrityCheck(false)
						        ->from(array("ALL_CONSTRAINTS"), array("ALL_CONSTRAINTS.TABLE_NAME", 
						        									   "ALL_CONS_COLUMNS.COLUMN_NAME", 
						        									   "SEARCH_CONDITION"))
						        ->join(array("ALL_CONS_COLUMNS"), "ALL_CONSTRAINTS.CONSTRAINT_NAME = ALL_CONS_COLUMNS.CONSTRAINT_NAME AND
           													  	   ALL_CONSTRAINTS.OWNER           = ALL_CONS_COLUMNS.OWNER", array())
						        ->where("ALL_CONSTRAINTS.OWNER              = 'PORTO'")
						        ->where("ALL_CONSTRAINTS.CONSTRAINT_NAME LIKE 'CKC%'");
    		
			// Busca as referências
			$selectRef = $this->select()
						      ->setIntegrityCheck(false)
						      ->from(array("ALL_CONSTRAINTS"), array("TABELA_PAI" => new Zend_Db_Expr("(SELECT CONSTRAINT_R.TABLE_NAME  
                 																						  FROM ALL_CONSTRAINTS AC, 
																						                       ALL_CONSTRAINTS CONSTRAINT_R 
																						                 WHERE AC.R_CONSTRAINT_NAME = CONSTRAINT_R.CONSTRAINT_NAME 
																						                   AND AC.CONSTRAINT_NAME = ALL_CONSTRAINTS.CONSTRAINT_NAME 
																						                   AND AC.OWNER = 'PORTO')"),
       																 "ALL_CONS_COLUMNS.TABLE_NAME",
       																 "ALL_CONS_COLUMNS.COLUMN_NAME", 
       																 "ALL_CONSTRAINTS.CONSTRAINT_NAME"))
						      ->join(array("ALL_CONS_COLUMNS"), "ALL_CONSTRAINTS.CONSTRAINT_NAME = ALL_CONS_COLUMNS.CONSTRAINT_NAME AND
           						   							     ALL_CONSTRAINTS.OWNER           = ALL_CONS_COLUMNS.OWNER", array())
						      ->where("ALL_CONSTRAINTS.OWNER           = 'PORTO'")
						      ->where("ALL_CONSTRAINTS.CONSTRAINT_TYPE = 'R'");
			
			// Define os filtros para a cosulta
	        $where = $this->addWhere(array("ALL_TABLES.TABLE_NAME       LIKE ?" => $params['table_name']))
	        			  ->addWhere(array("ALL_TAB_COLUMNS.COLUMN_NAME LIKE ?" => $params['column_name']))
	                      ->getWhere();
			
    		// Busca todas as tabelas cadastradas
			$select	= $this->select()
						   ->setIntegrityCheck(false)
						   ->from(array("ALL_TABLES"), array("ALL_TABLES.TABLE_NAME",
													         "NO_TABELA" => "ALL_TAB_COMMENTS.COMMENTS",
													         "ALL_TAB_COLUMNS.COLUMN_NAME",
													         "NO_COLUNA" => "ALL_COL_COMMENTS.COMMENTS",
													       	 "CONSTRAINTS_PK.PK",
						   									 "TIPO"     => new Zend_Db_Expr("CASE 
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'VARCHAR'   THEN 'STRING'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'VARCHAR2'  THEN 'STRING'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'NVARCHAR2' THEN 'STRING'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'CHAR'      THEN 'STRING'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'NUMBER'    THEN 'NUMÉRICO'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'RAW' 	  THEN 'BLOB'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'LONG RAW'  THEN 'BLOB'
																 					             WHEN ALL_TAB_COLUMNS.DATA_TYPE = 'CLOB'      THEN 'BLOB'
																 					      	 ELSE
																 					             DATA_TYPE
																 					         END"),
													         "TAMANHO"  => new Zend_Db_Expr("DECODE(ALL_TAB_COLUMNS.DATA_TYPE, 'NUMBER', CASE WHEN (TO_NUMBER(ALL_TAB_COLUMNS.DATA_SCALE) > 0) THEN ALL_TAB_COLUMNS.DATA_PRECISION || ',' || ALL_TAB_COLUMNS.DATA_SCALE ELSE TO_CHAR(ALL_TAB_COLUMNS.DATA_PRECISION) END, ALL_TAB_COLUMNS.DATA_LENGTH)"),
													         "NULLABLE" => new Zend_Db_Expr("DECODE(ALL_TAB_COLUMNS.NULLABLE, 'Y', 'NULL', 'NOT NULL')"),
													         "ALL_TAB_COLUMNS.DATA_DEFAULT",
													         "CONSTRAINTS_CHECK.SEARCH_CONDITION",
														     "CONSTRAINT_REF.CONSTRAINT_NAME",
													 		 "CONSTRAINT_REF.TABELA_PAI"))
						   ->joinLeft(array("ALL_TAB_COLUMNS"),  "ALL_TABLES.TABLE_NAME       = ALL_TAB_COLUMNS.TABLE_NAME AND 
   															      ALL_TABLES.OWNER            = ALL_TAB_COLUMNS.OWNER",  array())
						   ->joinLeft(array("ALL_TAB_COMMENTS"), "ALL_TABLES.TABLE_NAME       = ALL_TAB_COMMENTS.TABLE_NAME AND 
   															      ALL_TABLES.OWNER            = ALL_TAB_COMMENTS.OWNER", array())
						   ->joinLeft(array("ALL_COL_COMMENTS"), "ALL_TAB_COLUMNS.TABLE_NAME  = ALL_COL_COMMENTS.TABLE_NAME AND 
														          ALL_TAB_COLUMNS.COLUMN_NAME = ALL_COL_COMMENTS.COLUMN_NAME AND 
														          ALL_TAB_COLUMNS.OWNER       = ALL_COL_COMMENTS.OWNER", array())
						   ->joinLeft(array("CONSTRAINTS_PK"    => $selectPK),    "ALL_TAB_COLUMNS.TABLE_NAME  = CONSTRAINTS_PK.TABLE_NAME AND 
   																		           ALL_TAB_COLUMNS.COLUMN_NAME = CONSTRAINTS_PK.COLUMN_NAME",    array())
						   ->joinLeft(array("CONSTRAINTS_CHECK" => $selectCheck), "ALL_TAB_COLUMNS.TABLE_NAME  = CONSTRAINTS_CHECK.TABLE_NAME AND 
   																			       ALL_TAB_COLUMNS.COLUMN_NAME = CONSTRAINTS_CHECK.COLUMN_NAME", array())
						   ->joinLeft(array("CONSTRAINT_REF"    => $selectRef),   "ALL_TAB_COLUMNS.TABLE_NAME  = CONSTRAINT_REF.TABLE_NAME AND 
   																			       ALL_TAB_COLUMNS.COLUMN_NAME = CONSTRAINT_REF.COLUMN_NAME",	 array())
						   ->where("ALL_TABLES.OWNER = 'PORTO'")
						   ->where($where)
						   ->order("1 ASC, 5 ASC, 3 ASC");

			return $select;

    	} catch (Exception $e) {
    		echo $e->getMessage();
        }
    }

}
?>