<?php
/**
 * View Helper para cria��o do menu com as a��es
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
				
					// Captura a inst�ncia do banco
	                $db = Zend_Registry::get('db');
					
					// Se n�o tiver c�digo da transacao, n�o monta as a��es do menu 
					if (($params["module"] != "default" && empty($params['_cd_transacao'])) && trim(strtolower($params["controller"])) !== "pick-list") {
						
						// Define o objeto, se o c�digo da transa��o n�o for passado
						// pega pelo nome da transa��o, caso contr�rio pega pelo c�digo mesmo
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
	    				
		    			// Se n�o tiver c�digo do sistema, n�o monta as a��es do menu 
		                if($resConsulta->CD_TRANSACAO != "") {
		                	$params['_cd_transacao'] = $resConsulta->CD_TRANSACAO;
		                } else {
		                	return "";
						}
	    			}
	    			
	    			// Seta zero para a pick-list
	    			$params['_cd_transacao'] = $params['_cd_transacao'] != "" ? $params['_cd_transacao'] : 0;
	    			
			    	// Carrega a sess�o do usu�rio
					$sessao     = new Zend_Session_Namespace('portoweb');
					$transacoes = new WebTransacaoModel();
	
					// Busca o c�digo do usu�rio do perfil
					$cd_usuario = $sessao->perfil->CD_USUARIO;
					
					if($params["module"] != "default") {
						$transacao  = $transacoes->fetchRow("CD_TRANSACAO = {$params['_cd_transacao']}");
						$acoes      = $transacao->getAcao($cd_usuario);
						
					} else {
						
						// Mostra as a��es quando n�o existir sistema 
						Zend_Loader::loadClass("WebAcaoModel");
						$acoes_ = new WebAcaoModel();
						$acoes  = $acoes_->getAcoesMenu();
					}
                    
					$menuAcao = "";
					
					if (count($acoes) > 0) {
                        
                        // O indice define a ACTION e os valores do array definem os BOT�ES permitidos
                        $metodosPadrao["index"]      = array("novo", "relatorio", "pesquisar", "limpar", "ajuda");
                        $metodosPadrao["pesquisar"]  = array("novo", "relatorio", "pesquisar", "limpar", "ajuda");
                        $metodosPadrao["selecionar"] = array("novo", "relatorio", "tela-pesquisa", "limpar", "salvar", "excluir", "voltar", "ajuda");
                        $metodosPadrao["novo"]       = array("tela-pesquisa", "limpar", "salvar", "voltar", "ajuda");
                        $metodosPadrao["salvar"]     = array("novo", "relatorio", "tela-pesquisa", "limpar", "salvar", "excluir", "voltar", "ajuda");
                        $metodosPadrao["excluir"]    = array("novo", "relatorio", "pesquisar", "limpar", "ajuda");
                        $metodosPadrao["relatorio"]  = array("tela-pesquisa", "ajuda");
	                    
						foreach ($acoes as $chave => $acao) {
							// Gera as a��es que existem para a transa��o e o usu�rio tem permiss�o
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