<?php
/**
 * Created on 03/08/2010
 *
 * Manipula a linha da classe AcessoTransacaoRow
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application.models
 * @version			1.0
 */

class WebGrupoTransacaoRow extends Zend_Db_Table_Row_Abstract {

	/**
	 * (non-PHPdoc)
	 * @see library/Zend/Db/Table/Row/Zend_Db_Table_Row_Abstract#init()
	 */
	public function init(){

	}

    /**
     * Retorna os privilégios
     *
     * @return array
     */
    public function getPrivilegios(){

    	try {
	    	// Pega o adaptador padrão do banco
			$db = Zend_Registry::get('db');

			// Busca todas as transações cadastradas para o usuário logado
			$select = $db->select()->from(array("grutran"=>"WEB_GRUPO_TRANSACAO"), array("tranacao.CD_ACAO, acao.NO_ACAO"))
								   ->join(array("tranacao"=>"WEB_TRANSACAO_ACAO"), "tranacao.CD_TRANSACAO = grutran.CD_TRANSACAO AND tranacao.CD_ACAO = grutran.CD_ACAO", array())
								   ->join(array("acao"=>"WEB_ACAO"), "acao.CD_ACAO = tranacao.CD_ACAO", array())
								   ->join(array("tran"=>"WEB_TRANSACAO"), "tran.CD_TRANSACAO = tranacao.CD_TRANSACAO", array())
								   ->join(array("menusis"=>"WEB_MENU_SISTEMA"), "menusis.CD_MENU = tran.CD_MENU", array())
								   ->where("grutran.CD_GRUPO      = {$this->CD_GRUPO} ")
								   ->where("menusis.CD_SISTEMA    = {$this->CD_SISTEMA} ")
								   ->where("tranacao.CD_TRANSACAO = {$this->CD_TRANSACAO} ")
								   ->where("tran.OBJ_EXECUTADO IS NOT NULL")
								   ->where("acao.FL_MENU = 1");

			$transPrivilegios = $db->fetchAll($select);
			$privilegios      = array();

	        foreach($transPrivilegios as $privilegio) {
	        	$privilegios[$privilegio->CD_ACAO] = $privilegio->NO_ACAO;
	        }

	        return $privilegios;

    	} catch (Exception $e) {
    		echo $e->getMessage();
    	}
    }
}
?>