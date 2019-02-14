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

class Zend_View_Helper_MenuAcao extends Zend_View_Helper_Abstract
{
	public function menuAcao() {

			try {                
				// Pega os parametros da URL
				$params    = Zend_Controller_Front::getInstance()->getRequest()->getParams();
                $no_action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
				
				if ($params["controller"] != "menu") {
				
					// Captura a instância do banco
	                $db = Zend_Registry::get('db');
					
					// Se não tiver código da transacao, não monta as ações do menu 
					if (($params["module"] != "default" && empty($params['_cd_transacao'])) && trim(strtolower($params["controller"])) !== "pick-list") {
						
						// Define o objeto, se o código da transação não for passado
						// pega pelo nome da transação, caso contrário pega pelo código mesmo
						if(isset($params['_cd_transacao']) && $params['_cd_transacao'] != "") {
							$cdTransacao = "WT.CD_TRANSACAO = {$params['_cd_transacao']}";
						} else {
							$cdTransacao = "WT.OBJ_EXECUTADO LIKE '{$params["controller"]}'";
						}
						
						// Monta e executa a consulta
						$select = $db->select()
		                             ->from(array("WT"  => "WEB_TRANSACAO"), array("WT.CD_TRANSACAO"))
		                             ->join(array("WMS" => "WEB_MENU_SISTEMA"), "WT.CD_MENU 	= WMS.CD_MENU",   array())
							         ->join(array("WS"  => "WEB_SISTEMA"),      "WMS.CD_SISTEMA = WS.CD_SISTEMA", array())
		                             ->where($cdTransacao)
		                             ->where("WS.NO_PASTA_ZEND LIKE '{$params["module"]}'");
                                     
		    			$resConsulta = $db->fetchRow($select);
	    				
		    			// Se não tiver código do sistema, não monta as ações do menu 
		                if($resConsulta->CD_TRANSACAO != "") {
		                	$params['_cd_transacao'] = $resConsulta->CD_TRANSACAO;
		                } else {
		                	return "";
						}
	    			}
	    			
	    			// Seta zero para a pick-list
	    			$params['_cd_transacao'] = $params['_cd_transacao'] != "" ? $params['_cd_transacao'] : 0;
	    			
			    	// Carrega a sessão do usuário
					$sessao     = new Zend_Session_Namespace('portoweb');
					$transacoes = new WebTransacaoModel();
	
					// Busca o código do usuário do perfil
					$cd_usuario = $sessao->perfil->CD_USUARIO;
					
					if($params["module"] != "default") {
						$transacao  = $transacoes->fetchRow("CD_TRANSACAO = {$params['_cd_transacao']}");
						$acoes      = $transacao->getAcao($cd_usuario);
						
					} else {
						
						// Mostra as ações quando não existir sistema 
						Zend_Loader::loadClass("WebAcaoModel");
						$acoes_ = new WebAcaoModel();
						$acoes  = $acoes_->getAcoesMenu();
					}
                    
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
                            if($acao->LNK_IMG_ACAO != "" && $acao->AMB_DESENV == "ZEND") {
                                if(isset($metodosPadrao[$no_action]) && in_array($acao->NO_ACAO, $metodosPadrao[$no_action])) {
                                    $menuAcao .= "<img id='botao-{$acao->NO_ACAO}' class='palhetaBotoes' src='{$this->view->baseUrl()}/public/images/botoes/{$acao->LNK_IMG_ACAO}' alt='{$acao->DS_ACAO}' />";
                                } else {
                                    $menuAcao .= "<img id='botao-{$acao->NO_ACAO}' class='palhetaBotoes' src='{$this->view->baseUrl()}/public/images/botoes/{$acao->LNK_IMG_ACAO}' alt='{$acao->DS_ACAO}' style='display: none;' />";
                                }
                            }
						}
	
					}
					
		            return $menuAcao;
				}

			} catch(Zend_Exception $e) {

				return "Erro: " . $e->getMessage();

			}
	}
}