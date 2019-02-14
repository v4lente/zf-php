<?php
/**
 * View Helper retornar as ações do usuário para a view
 *
 * @uses Zend_Controller_View_Helper_Abstract
 *
 * @author Márcio Souza Duarte
 *
 *
 * @return HTML
 */

class Zend_View_Helper_AcoesUsuario extends Zend_View_Helper_Abstract {
    
    public function acoesUsuario() {

        try {
            
            // Captura a instância do banco
            $db = Zend_Registry::get('db');
            
            // Pega os parametros da URL
            $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
            
            // Carrega a sessão do usuário
            $sessao = new Zend_Session_Namespace('portoweb');
            
            $retAcoes     = "";
            $cd_usuario   = $_SESSION['CD_USUARIO']; //$sessao->perfil->CD_USUARIO
            $cd_transacao = $params['_cd_transacao'];
            
            if($cd_usuario != "" && $cd_transacao != "") {
                
                // Busca todas as transações cadastradas para o usuário logado
                $select = $db->select()->from(array("GRTRA" => "WEB_GRUPO_TRANSACAO"), array("WACAO.NO_ACAO"))
                                       ->join(array("TRAN"  => "WEB_TRANSACAO"),       "GRTRA.CD_TRANSACAO = TRAN.CD_TRANSACAO" , array())
                                       ->join(array("WACAO" => "WEB_ACAO"),            "TRAN.FL_TIPO_TRANSACAO = WACAO.FL_TIPO_ACAO 
                                                                                   AND (GRTRA.FL_PERMISSAO     = WACAO.FL_PERMISSAO 
                                                                                    OR  WACAO.FL_PERMISSAO     = 'C')" , array())
                                       ->join(array("GRUSU" => "WEB_GRUPO_USUARIO"),   "GRTRA.CD_GRUPO     = GRUSU.CD_GRUPO" , array())
                                       ->where("GRUSU.CD_USUARIO = '{$cd_usuario}'")
                                       ->where("GRTRA.CD_TRANSACAO = {$cd_transacao}")
                                       ->where("TRAN.OBJ_EXECUTADO IS NOT NULL")
                                       ->group("WACAO.NO_ACAO")
                                       ->order("WACAO.NO_ACAO DESC");
                
                $acoes = $db->fetchAll($select);
                
                if (count($acoes) > 0) {
                    
                    foreach ($acoes as $acao) {
                        if($retAcoes != "") {
                            $retAcoes .= ",";
                        }
                        
                        // Monta o array das ações
                        $retAcoes .= "'{$acao->NO_ACAO}'";
                    }
    
                }
            
            }
            
            return $retAcoes;

        } catch(Zend_Exception $e) {

            return $e->getMessage();

        }
    }
}
