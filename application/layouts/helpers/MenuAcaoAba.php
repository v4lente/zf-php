<?php
/**
 * View Helper para criação do menu com as ações
 *
 * @uses Zend_Controller_View_Helper_Abstract
 *
 * @author David Valente
 *
 *
 * @return HTML
 */

class Zend_View_Helper_MenuAcaoAba extends Zend_View_Helper_Abstract
{
	public function menuAcaoAba() {

			try {
                
			    // Carrega o modelo de dados
                Zend_Loader::loadClass('WebTransacaoModel');
			    
				// Pega os parametros da URL
				$params         = Zend_Controller_Front::getInstance()->getRequest()->getParams();
                $no_module      = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
                $no_controller  = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
                $no_action      = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
                
				if (empty($_SESSION["portoweb"][$no_module][$no_controller]["_cd_transacao"])) {
					return "";
				}
                
		    	// Carrega a sessão do usuário
				$sessao     = new Zend_Session_Namespace('portoweb');
				$transacoes = new WebTransacaoModel();

				$cd_usuario = $_SESSION['CD_USUARIO']; //$sessao->perfil->CD_USUARIO
				$transacao  = $transacoes->fetchRow("CD_TRANSACAO = " . $_SESSION["portoweb"][$no_module][$no_controller]["_cd_transacao"]);
				$acoes      = $transacao->getAcao($cd_usuario);
                
				$menuAcao = "";
				if (count($acoes) > 0) {
                    
                    // O indice define a ACTION e os valores do array definem os BOTÕES permitidos
                    $metodosPadrao["index"]      = array("novo", "relatorio", "pesquisar", "limpar", "ajuda");
                    $metodosPadrao["pesquisar"]  = array("novo", "relatorio", "pesquisar", "limpar", "ajuda");
                    $metodosPadrao["selecionar"] = array("novo", "relatorio", "tela-pesquisa", "limpar", "salvar", "excluir", "voltar", "ajuda");
                    $metodosPadrao["novo"]       = array("tela-pesquisa", "limpar", "salvar", "voltar", "ajuda");
                    $metodosPadrao["salvar"]     = array("novo", "relatorio", "tela-pesquisa", "limpar", "salvar", "excluir", "voltar", "ajuda");
                    $metodosPadrao["excluir"]    = array("novo", "relatorio", "pesquisar", "limpar", "ajuda");
                    $metodosPadrao["relatorio"]  = array("tela-pesquisa", "ajuda");
                    
					foreach ($acoes as $chave => $acao) {
						// Gera as ações que existem para a transação e o usuário tem permissão
                        if($acao->LNK_IMG_ACAO != "" && $acao->AMB_DESENV == "ZEND" && ($acao->FL_PERMISSAO == "C" || $_SESSION["portoweb"][$no_module][$no_controller]["_fl_permissao"] == $acao->FL_PERMISSAO)) {
                            if(isset($metodosPadrao[$no_action]) && in_array($acao->NO_ACAO, $metodosPadrao[$no_action])) {
                                $menuAcao .= "<img id='botao-{$acao->NO_ACAO}' class='palhetaBotoesAba' src='{$this->view->baseUrl()}/public/images/botoes/{$acao->LNK_IMG_ACAO}' alt='{$acao->DS_ACAO}' />";
                            } else {
                                $menuAcao .= "<img id='botao-{$acao->NO_ACAO}' class='palhetaBotoesAba' src='{$this->view->baseUrl()}/public/images/botoes/{$acao->LNK_IMG_ACAO}' alt='{$acao->DS_ACAO}' style='display: none;' />";
                            }
                        }
					}

				}
                
	            return $menuAcao;

			} catch(Zend_Exception $e) {

				return $e->getMessage();

			}
	}
}