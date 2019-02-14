<?php

/**
 *
 * Classe MenuController
 *
 * Esta classe carrega o menu dos sistemas do usu�rio.
 *
 * @category   Marca Sistemas
 * @package    MenuController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: MenuController.php 0 2009-11-10 17:25:00 marcio $
 */
class MenuController extends Marca_Controller_Abstract_Comum {
    
    /**
     * M�todo inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {

        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();
		
        // Carrega as classes
        Zend_Loader::loadClass("UsuarioModel");
        Zend_Loader::loadClass("WebMensagemModel");
        Zend_Loader::loadClass("WebGrupoMensagemModel"); 
        Zend_Loader::loadClass("Marca_MetodosAuxiliares");          
    }

 	/**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {		
		
    	// Pega os parametros da requisi��o
    	$params  = $this->_request->getParams();
		
    	// Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');	
		        
        // Caso tenha mensagem de paralisa��o
        if (isset($sessao->mensagemParalisacao)) {
        
        	// Pega do registro a fila
			$mensagemSistema = Zend_Registry::get("mensagemSistema");
				
    		// Gera a mensagem
			$mensagemSistema->send(serialize($sessao->mensagemParalisacao));
			
			// Limpa da sess�o a variavel
			unset($sessao->mensagemParalisacao);
        
        }
        
        //echo "<br />" . $sessao->perfil->ID_SESSAO;
        
        // Carrega o modelo das mensagens
    	$mensagens = new WebMensagemModel();
        
    	// Busca a data de hoje
        $dataAtual = date("d/m/Y");
    	
    	// Define a data 
    	$params["dt_ini"] = $dataAtual;
    	$params["dt_fim"] = $dataAtual;
    	
    	// Consulta as mensagens do grupo do usu�rio
    	$select = $mensagens->queryBuscaMensagensGrupo($params);
        
    	// Busca as mensagens do grupo do usu�rio
    	$mensagensWeb = $mensagens->fetchAll($select);
    	$this->view->mensagensWeb = $mensagensWeb;
        
        // Troca ifens por barras
    	$arquivo = str_replace("-", "/", $params['arquivo']);

    	if (trim($params["parametrosUrl"]) != "") {
    		$arquivo .= "?" . $params["parametrosUrl"];
    	}
    	
    	// Captura o caminho do sistema
        $baseUrl    = Zend_Controller_Front::getBaseUrl();
        if(strpos($baseUrl, "/") === 0) {
            $baseUrl = substr($baseUrl, 1, strlen($baseUrl) - 1);
        }
        
        $divBaseUrl = explode("/", $baseUrl);

        if(APPLICATION_ENV == "desenv") {
        	$portoweb = $divBaseUrl[0] . "/" . $divBaseUrl[1];
        } else {
        	$portoweb = $divBaseUrl[0];
        }
        
    	// Bloco responsavel por setar os parametros referente as transa��es do legado
    	$this->view->iSrc = "";
    	if (! empty($arquivo)) { // Seta a propriedade src do iframe com o parametro arquivo
            $this->view->cd_transacao = $params["_cd_transacao"];
    		$this->view->iSrc   = "/{$portoweb}/{$arquivo}"; // Caminho do arquivo
    		$this->view->iStyle = "style='border:0px solid #f00; width: 99%; min-height:480px; display: block;'";
    	}
		
    }
    
    /**
    * Busca os bot�es para as transa��es do legado
    *
    * @return JSON
    */
    public function retornaNoarqajudaXAction() {
    
    	// Verifica se arrequisi��o foi passada por Ajax
    	if($this->_request->isXmlHttpRequest()) {
    
    		// Instancia o modelo
    		$db = Zend_Registry::get("db");
    
    		$params  = $this->_request->getParams();
	
    		$sql = "SELECT NO_ARQ_AJUDA 
    			      FROM WEB_TRANSACAO 
    			     WHERE CD_TRANSACAO = " . $params['cd_transacao'];
    
    		$linha = $db->fetchRow($sql);
    
    		// Retorna os dados por json
    		$this->_helper->json(Marca_ConverteCharset::converter($linha->NO_ARQ_AJUDA), true);
    
    		// Limpa os objetos da memoria
    		unset($db);
    	}
    }

}
