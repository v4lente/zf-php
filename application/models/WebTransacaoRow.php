<?php
/**
 * Created on 03/08/2010
 *
 * Manipula a linha da classe TransacaoRow
 *
 * @filesource
 * @author			David Valente
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class WebTransacaoRow extends Zend_Db_Table_Row_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Db/Table/Row/Zend_Db_Table_Row_Abstract#init()
	 */
	public function init(){}


    /**
     * Retorna todas as AÇÕES
     *
     * @return array
     */
    public function getAcao($cd_usuario = ""){

    	try {

    		if ($cd_usuario == "") {

    			throw new Exception("Atenção: Código do usuário em branco.");
    		}

	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');
			
			// Busca todas as transações cadastradas para o usuário logado
			$select	= $db->select()->from(array("GRTRA" => "WEB_GRUPO_TRANSACAO"), array("WACAO.NO_ACAO", 
																						 "WACAO.ORD_ACAO", 
																						 "WACAO.DS_ACAO",
                                                                                         "TRAN.AMB_DESENV",
																						 "WACAO.LNK_IMG_ACAO",
                                                                                         "WACAO.FL_PERMISSAO"))
                                   ->join(array("TRAN" => "WEB_TRANSACAO"),        "GRTRA.CD_TRANSACAO     = TRAN.CD_TRANSACAO",   array())
								   ->join(array("WACAO" => "WEB_ACAO"),            "TRAN.FL_TIPO_TRANSACAO = WACAO.FL_TIPO_ACAO 
                                                                               AND (GRTRA.FL_PERMISSAO     = WACAO.FL_PERMISSAO
                                                                                OR  WACAO.FL_PERMISSAO     = 'C')" ,               array())
								   ->join(array("GRUSU" => "WEB_GRUPO_USUARIO"),   "GRTRA.CD_GRUPO         = GRUSU.CD_GRUPO" ,     array())
								   ->where("GRUSU.CD_USUARIO   = '{$cd_usuario}'")
								   ->where("GRTRA.CD_TRANSACAO = {$this->CD_TRANSACAO}")
								   ->where("WACAO.FL_MENU      = 1")
								   ->where("TRUNC(SYSDATE) >= TRUNC(GRTRA.DT_ACESSO_INI)")
								   ->where("(GRTRA.DT_ACESSO_FIM IS NULL OR TRUNC(SYSDATE) <= TRUNC(GRTRA.DT_ACESSO_FIM))")
								   ->group("WACAO.NO_ACAO, WACAO.ORD_ACAO, WACAO.DS_ACAO, TRAN.AMB_DESENV, WACAO.LNK_IMG_ACAO, WACAO.FL_PERMISSAO")
								   ->order("WACAO.ORD_ACAO DESC");
            
			return $db->fetchAll($select);

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }

}
?>