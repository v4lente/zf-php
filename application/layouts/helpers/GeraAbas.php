<?php
/**
 * Action Helper para as abas com outras transa��es
 *
 * @uses   Zend_View_Helper_Abstract
 * @author M�rcio Souza Duarte
 * @param  array  $titulos        Define o t�tulo da aba
 * @param  array  $sistemas       Seta o sistema
 * @param  array  $controladores  Seta o controlador
 * @param  array  $parametros     Define os par�metros extras que ser�o enviados para a tab
 * @param  array  $estilos        Define o estilo da aba
 * @return string $tabs
 *
 */

class Zend_View_Helper_GeraAbas extends Zend_View_Helper_Abstract {
    
	public function geraAbas($titulos, $sistemas, $controladores, $parametros, $estilos) {

		try {
			
		    // Captura a inst�ncia do banco
		    $db = Zend_Registry::get("db");
            
            // Pega os parametros da URL
            $params        = Zend_Controller_Front::getInstance()->getRequest()->getParams();
            $no_controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
            
            // Captura a sess�o
            $sessao = new Zend_Session_Namespace('portoweb');
		    
		    $tabs  = "<div id='tabs' class='tabs'>";
		    $tabs .= "<div id='altura_tab' style='position: fixed; border: 0px solid #f00;'></div>";
		    $tabs .= "<ul id='tabs_ul'>";
		    $totalTitulos = count($titulos);
		    for($i=0; $i < $totalTitulos; $i++) {
                if($i==0) {
    		        $tabs .= "<div id='foottip'>";
    		        $tabs .= "<li><a id=\"tab_{$i}\" href=\"#div_{$i}\" ativo=\"1\">{$titulos[$i]}</a></li>";
                    $tabs .= "</div>";
                } else {
                    $tabs .= "<li><a id=\"tab_{$i}\" href=\"#div_{$i}\" ativo=\"0\">{$titulos[$i]}</a></li>";
                }
		    }
		    $tabs .= "</ul>";
		    
		    // Seta a base url
		    $baseUrl = $this->view->baseUrl();
		    
		    // Seta o nome do sistema do formul�rio pai
		    $sistema = $this->view->no_module;
		    
		    // Percorre cada aba passada
		    for($i=0; $i < $totalTitulos; $i++) {
		        
		        // Se n�o for passado o sistema, pega o corrente
		        if(! isset($sistemas[$i]) || $sistemas[$i] == "") {
		            $sistemas[$i] = $sistema;
		        }

		        // Se n�o existir estilo, pega um padr�o para o iframe
		        if(! isset($estilos[$i]) || $estilos[$i] == "") {
                    $estilos[$i] = "";
                }
		        
		        // Captura o objeto executado a partir do sistema e da transa��o passada
		        $_sistema     = trim($sistemas[$i]);
		        $_controlador = trim($controladores[$i]);
		        
		        // Busca o c�digo do sistema, menu e transa��o do objeto
		        $select = $db->select()
		                     ->from(array("WT"  => "WEB_TRANSACAO"),    array("WT.CD_TRANSACAO", 
		                     												  "WMS.CD_MENU", 
		                     												  "WMS.CD_SISTEMA",
                                                                              "WT.FL_TIPO_TRANSACAO"))
                             ->join(array("WMS" => "WEB_MENU_SISTEMA"), "WT.CD_MENU     = WMS.CD_MENU" ,  array())
                             ->join(array("WS"  => "WEB_SISTEMA"),      "WMS.CD_SISTEMA = WS.CD_SISTEMA", array())
                             ->where("UPPER(WT.OBJ_EXECUTADO) LIKE UPPER('{$_controlador}')")
                             ->where("UPPER(WS.NO_PASTA_ZEND) LIKE UPPER('{$_sistema}')");
                
                // Executa a consulta
		        $linhaConsulta = $db->fetchRow($select);
                
                // Busca a permiss�o da janela pai se � Manuten��o ou Consulta
                $sql = $db->select()
                          ->from(array("WT" => "WEB_TRANSACAO"), array("WG.FL_PERMISSAO"))
                          ->join(array("WG" => "WEB_GRUPO_TRANSACAO"), "WT.CD_TRANSACAO = WG.CD_TRANSACAO", array())
                          ->join(array("WU" => "WEB_GRUPO_USUARIO"),   "WG.CD_GRUPO     = WU.CD_GRUPO", array())
                          ->where("UPPER(WT.OBJ_EXECUTADO) LIKE UPPER('{$no_controller}')")
                          ->where("UPPER(WU.CD_USUARIO)    LIKE UPPER('{$sessao->perfil->CD_USUARIO}')");
                
                $res = $db->fetchAll($sql);
                
                // Se algum grupo que o usu�rio pertence for mestre, seta como mestre o pai
                $flPermissao = "C";
                foreach($res as $linha) {
                    if($linha->FL_PERMISSAO == "M") {
                        $flPermissao = "M";
                        break;
                    }
                }
		        
		        // Monta os dados internos do sistema
		        $codigosInternos = "_cd_sistema/"         . $linhaConsulta->CD_SISTEMA        . 
		                           "/_cd_menu/"           . $linhaConsulta->CD_MENU           . 
		                           "/_cd_transacao/"      . $linhaConsulta->CD_TRANSACAO      . 
                                   "/_fl_tipo_transacao/" . $linhaConsulta->FL_TIPO_TRANSACAO . 
                                   "/_fl_permissao/"      . $flPermissao;
		         
		        // Define os atributos que ser�o passados da transa��o pai para a transa��o filha
		        $parametrosExtras = $parametros[$i];
		        
		        // Monta o caminho do iframe
		        $src = $baseUrl . "/" . $sistemas[$i] . "/" . $controladores[$i] . "/index/" . $codigosInternos . "/" . $parametrosExtras;
		        
                $tabs .= "<div id='div_{$i}' class='grupo'>";
                if($i == 0) {
                    $tabs .= "<iframe id='ifrm_" . $i . "' src='" . $src . "' params='" . $src . "' style='" . $estilos[$i] . "'></iframe>";
                } else {
                    $tabs .= "<iframe id='ifrm_" . $i . "' src='' params='" . $src . "' style='" . $estilos[$i] . "'></iframe>";
                }
                $tabs .= "</div>";
		    }
            $tabs .= "</div>";
            
            // Retorna as abas montadas
            return $tabs;
			
		} catch(Exception $e){
            
			echo $e->getMessage();
		}
	}
	
}