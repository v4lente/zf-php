<?php
/**
 * Created on 04/07/2010
 *
 * Modelo da classe AcessoModel
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2010 Marca
 * @package         zendframework
 * @subpackage      zendframework.application.models
 * @version         1.0
 */

class AcessoModel extends Marca_Db_Table_Abstract {

    /**
     * Nome da tabela relacionada
     *
     * @var string
     */
    protected $_name = 'ACESSO';

    /**
     * Chave primária da tabela
     *
     * @var string
     */
    protected $_primary = array('SEQ_ACESSO');
    
    
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
							array('name'         =>'PL_VEICULO',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'Zend_Filter_StringToUpper')
							,
							array('name'         =>'DTHR_ENTRADA',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório')
							,
							array('name'         =>'CD_USU_ENTRADA',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'Zend_Filter_StringToUpper')
							,
							array('name'         =>'FL_LIBERA_QQ_BALANCA',
						          'class'        =>'NotEmpty',
							      'errorMessage' =>'Campo obrigatório',
						          'filter'       =>'intval')
						);


	/**
     * 
     * Verifica se o veículo encontra-se no pátio
     * 
     * @param  array  $params Estes são os parâmetros de busca
     * @return bool   $total  Objeto que contém os dados da query
     */
    public function verificaVeiculoPatio(&$params=array()) {


       $pl_veiculo = "";
       if($params['pl_veiculo'] != "") {
           $arrayTroca = array("-", "_", " ");
           $pl_veiculo = str_replace($arrayTroca, "", $params['pl_veiculo']);
           $pl_veiculo = strtoupper(trim($pl_veiculo));
       }
        
        // Define os filtros para a cosulta
        $where = $this->addWhere(array("UPPER(TRIM(PL_VEICULO)) = ?" => $pl_veiculo))
                      ->getWhere();
        
        // Monta e executa a consulta
        $select = $this->select()
                       ->from($this, array("TOTAL" => new Zend_Db_Expr("MAX(SEQ_ACESSO)")))
                       ->where($where)
                       ->where("CD_USU_SAIDA IS NULL");
        
        // Executa a consulta
        $retorno = $this->fetchRow($select);   
        
        // Retorna a consulta
        return $retorno->TOTAL > 0 ? true : false;
        
    }

	
	/**
     * Retorna as áreas para montagem do combo
     *
     * @return array array("cd_area" => "no_area")
     */
    public function retornaTransp($transp) {

    	$db = Zend_Registry::get('db');

    	try {
		
			// Query para buscar transportadora
			$select = "SELECT TEMP.NR_DOC_TRANSP AS CODIGO, F_FORMATA_CNPJCPF(TEMP.NR_DOC_TRANSP) AS NR_DOC_TRANSP, TEMP.NO_TRANSP AS TRANSPORTADORA
					  FROM (
						  SELECT DISTINCT
								 TO_CHAR(GENF.NR_DOC_TRANSP) AS NR_DOC_TRANSP,
								 GENF.NO_TRANSP              AS NO_TRANSP
							FROM ACESSO                    AC
					  INNER JOIN ACESSO_EXPORTACAO         AE      ON AC.SEQ_ACESSO            = AE.SEQ_ACESSO
					  INNER JOIN ACESSO_EXPORTACAO_NF_MERC AENFM   ON AE.SEQ_ACESSO_EXPORTACAO = AENFM.SEQ_ACESSO_EXPORTACAO
					  INNER JOIN GUIA_ENTRADA_NF_MERC      GENFM   ON AENFM.NR_GUIA_ENTRADA    = GENFM.NR_GUIA_ENTRADA
																  AND AENFM.NR_SEQ             = GENFM.NR_SEQ
																  AND AENFM.SEQ_MERC           = GENFM.SEQ_MERC 
					  INNER JOIN GUIA_ENTRADA_NF           GENF    ON GENFM.NR_GUIA_ENTRADA    = GENF.NR_GUIA_ENTRADA
																  AND GENFM.NR_SEQ             = GENF.NR_SEQ
					UNION
						  SELECT DISTINCT
								 TRANSP.NR_DOC       AS NR_DOC_TRANSP,
								 TRANSP.RAZAO_SOCIAL AS NO_TRANSP
							FROM ACESSO                    AC
					  INNER JOIN AGENDAMENTO_VEICULO       AV      ON AC.NR_AGENDA             = AV.NR_AGENDA
					  INNER JOIN VEICULOS_AGENDA           VA      ON AV.NR_AGENDA             = VA.NR_AGENDA
																  AND AC.PL_VEICULO            = VA.PL_VEICULO
					  INNER JOIN CLIENTE                   TRANSP  ON VA.CD_TRANSP             = TRANSP.CD_CLIENTE
					) TEMP
					WHERE UPPER(F_FORMATA_CNPJCPF(TEMP.NR_DOC_TRANSP) || TEMP.NO_TRANSP) LIKE UPPER('%".$transp."%')";							
			
            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
	
    public function retornaConteineres($cont) {

    	$db = Zend_Registry::get('db');

    	try {
		
			// Query para buscar transportadora
			$select = "SELECT F_FORMATA_CONTEINER(TEMP.NR_CONTAINER) AS NR_CONTAINER, TEMP.NR_CONTAINER as CONTAINER
						FROM (
							SELECT NR_CONTAINER
							  FROM EXPORTACAO
							 WHERE UPPER(EXPORTACAO.NR_CONTAINER)             LIKE UPPER('".$cont."%')
							UNION
							SELECT NR_CONTAINER
							  FROM ARMAZENAGEM_CARGAS
							 WHERE UPPER(ARMAZENAGEM_CARGAS.NR_CONTAINER)     LIKE UPPER('".$cont."%')
							UNION
							SELECT NR_CONTAINER
							  FROM SAIDA_VEICULO
							 WHERE UPPER(SAIDA_VEICULO.NR_CONTAINER)          LIKE UPPER('".$cont."%')
							UNION
							SELECT NR_CONTAINER
							  FROM SAIDA_VEICULO_TRANSITO
							 WHERE UPPER(SAIDA_VEICULO_TRANSITO.NR_CONTAINER) LIKE UPPER('".$cont."%')
							UNION
							SELECT NR_CONTAINER
							  FROM SAIDA_VEIC_CONTR_OPER
							 WHERE UPPER(SAIDA_VEIC_CONTR_OPER.NR_CONTAINER)  LIKE UPPER('".$cont."%')
						) TEMP ";							
			
            return $select;

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
	
}
?>