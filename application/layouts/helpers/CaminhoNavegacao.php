<?php
/**
 * View Helper para mostrar o nome do sistema corrente
 *
 * @uses Zend_Controller_View_Helper_Abstract
 *
 * @author David Valente
 *
 *
 * @return HTML
 */

class Zend_View_Helper_CaminhoNavegacao extends Zend_View_Helper_Abstract
{
	public function caminhoNavegacao() {
			
			try {
				
				// Pega os parametros da URL
				$params  = Zend_Controller_Front::getInstance()->getRequest()->getParams();
				
				// Carrega a sessão do usuário
				$sessao = new Zend_Session_Namespace('portoweb');
				
				// Seta os valores retornados da requisição
				$caminho = "";
				if(trim($params['_cd_sistema']) != "" || $sessao->caminhoNavegacao->cd_sistema != "") {
					
					if(trim($params['_cd_sistema']) != trim($sessao->caminhoNavegacao->cd_sistema)) {
						$sessao->caminhoNavegacao->cd_sistema = $params['_cd_sistema'];
						$sessao->caminhoNavegacao->no_sistema = $params['_no_sistema'];
					}
				
					// Retorna uma STRING com o nome do SISTEMA e o CONTROLADOR
					$caminho = "<span id='tit_no_sistema'>" . html_entity_decode($sessao->caminhoNavegacao->no_sistema) . "</span>";
				
					if(trim($params['_cd_menu']) != "" || $sessao->caminhoNavegacao->cd_menu != "") {
						
						if(trim($params['_cd_menu']) != trim($sessao->caminhoNavegacao->cd_menu)) {
							$sessao->caminhoNavegacao->cd_menu = $params['_cd_menu'];
							$sessao->caminhoNavegacao->no_menu = $params['_no_menu'];
						}
							
						$caminho .= " - <span id='tit_no_menu'>". html_entity_decode($sessao->caminhoNavegacao->no_menu) . "</span>";
							
						if(trim($params['_cd_transacao']) != "" || $sessao->caminhoNavegacao->cd_transacao != "") {
							
							if(trim($params['_cd_transacao']) != trim($sessao->caminhoNavegacao->cd_transacao)) {
								$sessao->caminhoNavegacao->cd_transacao = $params['_cd_transacao'];
								$sessao->caminhoNavegacao->no_transacao = $params['_no_transacao'];
							}
				
							$caminho .= " - <span id='tit_no_transacao'>" . html_entity_decode($sessao->caminhoNavegacao->no_transacao) . "</span>";
				
						}
						
					}
				}
				
		    	// Retorna o caminho em negrito
		    	return "<strong>" . $caminho . "</strong>";

			} catch(Zend_Exception $e) {

				return $e->getMessage();

			}
	}
}