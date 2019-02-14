<?php
/**
 * View Helper para criação do menu
 *
 * @uses Zend_Controller_View_Helper_Abstract
 *
 * @author David Valente
 *
 *
 * @return HTML
 */

class Zend_View_Helper_Menu extends Zend_View_Helper_Abstract
{
	public function menu() {
        
		try {
			
			Zend_Loader::loadClass("UsuarioModel");
			Zend_Loader::loadClass("WebMenuSistemaModel");
            Zend_Loader::loadClass("WebTransacaoModel");
			
			// Captura a requisição
			$request = Zend_Controller_Front::getInstance()->getRequest();
							
			// Pega os parametros da URL
			$params = $request->getParams();
            
			// Define a operação na view
	        $no_module     = $request->getModuleName();
	        $no_controller = $request->getControllerName();
	        $no_action     = $request->getActionName();
			
	    	// Carrega a sessão do usuário
			$sessao = new Zend_Session_Namespace('portoweb');
			
			// Seta na sessão o CÓDIGO do USUÁRIO logado
    	    $cd_usuario   = $_SESSION['CD_USUARIO'] = $sessao->perfil->CD_USUARIO;
			
			// Joga o menu de sistemas para a sessão
			if(! isset($sessao->_menuSistemas)) {
				
				// Instancia a classe de dados
				$usuarios = new UsuarioModel();
				
			    // Busca os sistemas do usuário
    	    	$usuario      = $usuarios->fetchRow("CD_USUARIO = '{$cd_usuario}'");
    	    	$menuSistemas = $usuario->getSistemas();
    	    	
    	    	// Seta os sistemas na sessão
    	    	$sessao->_menuSistemas = $menuSistemas; 
			
			} else {
			    
			    // Caputa os sistemas da sessão
			    $menuSistemas = $sessao->_menuSistemas;
			}
	    	$abasSistema  = array();
			
	    	// Início das abas
	    	$menu .= "<div id='smoothmenu1' class='ddsmoothmenu'>";
	    	// Início do bloco
			$menu .= "<ul>";

			// Inicio do bloco com os sistemas
			$menu .= "<li id='aba_principal'><a href='#' class=''>Sistemas</a>\n";
			$menu .= "<ul>\n";
			foreach ($menuSistemas as $chave => $sistema) {
				
                // Se entrar no sistema de documentos vai para a transação de documentos do porto automaticamente
                if(trim($sistema->NO_SISTEMA) == "Documentos" && $sessao->perfil->TP_USUARIO == "P" && $sessao->perfil->CD_USUARIO != "PUBLICO") {
                    // listagem com os sistemas
                    $menu .= "<li><a href='#' onclick='Eventos.menuSistemas(\"".$this->view->baseUrl()."/documentos/documentos-porto/index\")' class=''>{$sistema->NO_SISTEMA}</a></li>\n";
                    
                } else {
                    // Criptografa os parametros da url
                    $crypt = $this->view->criptAHref("", array("_cd_sistema"=>$sistema->CD_SISTEMA, "_no_sistema"=>htmlentities($sistema->NO_SISTEMA)), "", array(), 3);
                    
                    // listagem com os sistemas
                    $menu .= "<li><a href='#' onclick='Eventos.menuSistemas(\"".$this->view->baseUrl()."/menu/index/{$crypt}\")' class=''>{$sistema->NO_SISTEMA}</a></li>\n";
                }
				
			}
			$menu .= "</ul>\n";
			// Fim do bloco com os sistemas
            
            if(! empty($params['_cd_sistema'])) {
                
			    $abasMenuSistema = array();
			    
			    // Joga para a sessão os menus e transações
			    if(! isset($sessao->_cdSistemaSelecionado) || 
			       $sessao->_cdSistemaSelecionado == ""    || 
			       $params['_cd_sistema'] != $sessao->_cdSistemaSelecionado) {
			    
			    	$webMenus = new WebMenuSistemaModel();
			    	
			        // Seta o sistema selecionado na sessão
			        $sessao->_cdSistemaSelecionado = $params['_cd_sistema'];
			        
    	    		// Busca o menu pelo sistema
    	    		$menuSistema = $webMenus->fetchRow("CD_SISTEMA = " . $params['_cd_sistema']);
    	    		
    	    		// Retorna as abas com os menus do usuário
    	    		$abasMenuSistema = $menuSistema->getAbasMenuSistema($cd_usuario);
    	    		
    	    		// Joga para a sessão
    	    		$sessao->_abasMenuSistema = $abasMenuSistema;
    	    		
			    } else {
			        
			        // Captura os menus/transações da sessão
			        $abasMenuSistema = $sessao->_abasMenuSistema;
			    }
	    		
	    		// Monta os menus e transações do sistema
	    		$cd_menu = 0;
	    		foreach($abasMenuSistema as $linha) {
	    			
	    			if($linha->CD_MENU != $cd_menu) {
	    				if($cd_menu != 0) {
	    					// Fecha um menu antes de abrir outro
	    					$menu .= "</ul></li>\n";
	    				}
	    				
	    				// Seta o último menu selecionado
	    				$cd_menu = $linha->CD_MENU;

	    				// Inicio da aba que agrupa as transações
						$menu .= "<li><a href='#'>" . $linha->NO_MENU . "</a>";
						
						// Início do bloco das transações
						$menu .= "<ul>\n";
	    			}
	    			
	    			// Verifica o ambiente de desenvolvimento da transação, 
			        // caso seja POWR, abrirá a transação em um iframe na 
			        // view index do menu
					if (strtoupper(trim($linha->AMB_DESENV)) == 'ZEND') {
						
						// Criptografa os parametros
						$parametros = array("_cd_sistema"   => $params['_cd_sistema'], 
											"_no_sistema"   => $params['_no_sistema'],
											"_cd_menu"      => $linha->CD_MENU, 
											"_no_menu"      => htmlentities($linha->NO_MENU), 
											"_cd_transacao" => $linha->CD_TRANSACAO,
											"_no_transacao" => htmlentities($linha->NO_TRANSACAO));
						
						$crypt = $this->view->criptAHref("", $parametros, "", array(), 3);
						
						// Se a transação pertencer a um recurso padrão seta o sistema como default
						if(trim(strtolower($linha->OBJ_EXECUTADO)) == "gera-rel" || 
						   trim(strtolower($linha->OBJ_EXECUTADO)) == "exec-rel") {
							$linha->NO_PASTA_ZEND = "default";
						}
						
						// Controle de verificação da action passada no objeto
						$divBarras = explode("/", $linha->OBJ_EXECUTADO);
						if(count($divBarras) == 1) {
							
							// Se a transação estiver apontando para outro sistema, pega a pasta do segundo
							// sistema referenciado em vez da pasta do sistema original vinculado
							if($linha->NO_PASTA_ZEND2 != "") {
								$linha->NO_PASTA_ZEND = $linha->NO_PASTA_ZEND2;
							}
							
						    $objExecutado = $linha->NO_PASTA_ZEND . "/" . $linha->OBJ_EXECUTADO . "/index";
						} else {
						    $objExecutado = $linha->OBJ_EXECUTADO;
						}
						
						// Monta a listagem com as transações por ABA
						$menu .= "<li id='aba_filha'><a href='#' onclick='Eventos.menuSistemas(\"".$this->view->baseUrl()."/{$objExecutado}/{$crypt}\", \"".$linha->FL_NOVA_JANELA."\")'> {$linha->NO_TRANSACAO}</a></li>\n";

					} else {
						
						$separaUrl = array();
						if (strpos($linha->OBJ_EXECUTADO, "?") !== false) {
							
							$separaUrl     = explode("?", $linha->OBJ_EXECUTADO);
							$separaUrl[1] .= "&_cd_sistema=" . $params['_cd_sistema'];
							
						} else {
							
							$separaUrl[0]  = $linha->OBJ_EXECUTADO;
							$separaUrl[1]  = "_cd_sistema=" . $params['_cd_sistema'];
						}
						
						// Criptografa os parametros
						$parametros = array("_cd_sistema"   => $params['_cd_sistema'], 
											"_no_sistema"   => $params['_no_sistema'],
											"_cd_menu"      => $linha->CD_MENU, 
											"_no_menu"      => htmlentities($linha->NO_MENU), 
											"_cd_transacao" => $linha->CD_TRANSACAO,
											"_no_transacao" => htmlentities($linha->NO_TRANSACAO),
											"arquivo"       => $separaUrl[0],
											"parametrosUrl" => $separaUrl[1]);
						
						$crypt = $this->view->criptAHref("", $parametros, "", array(), 3);
						
						$menu .= "<li id='aba_filha'><a href='#' onclick='Eventos.menuSistemas(\"".$this->view->baseUrl()."/menu/index/{$crypt}\", \"".$linha->FL_NOVA_JANELA."\")'> {$linha->NO_TRANSACAO}</a></li>\n";    								
			    	}
	    			
	    		}
	    		
	    		// Finaliza o último bloco
	    		$menu .= "</ul></li>\n";
	    		
			}
			
			// Fim bloco com as abas
			$menu .= "</ul>";
			$menu .= "</div>";
			
			// Retorna o menu montado
            return $menu;

		} catch(Zend_Exception $e) {

			return $e->getMessage();

		}
	}
}