<?php
/**
 * Action Helper para montagem de relat�rios feitos pelo iReport que
 * ser�o rodados do JasperReports
 *
 * @uses   Zend_View_Helper_Abstract
 * @author M�rcio Souza Duarte
 * @param  array params
 */
class Marca_Controller_Action_Helper_GeraRelatorioJasper extends Zend_Controller_Action_Helper_Abstract {

	/**
	 * 
	 * M�todo que chamar� o controlador que gerar� o relat�rio no jasper
	 * 
	 * @param string $nomeRelatorio
	 * @param array  $parametros
	 * @param string $formato
	 */
	public function gerar($nomeRelatorio = "", $parametros = array(), $formato = "PDF", $caminho="") {
		
		// Verifica se foi informado o relat�rio a ser chamado
		if($nomeRelatorio != "") {
			
			// Carrega os modelos de dados
			Zend_Loader::loadClass("WebSistemaModel");
			Zend_Loader::loadClass("WebTransacaoModel");
			
			// Captura os par�metros da requisi��o
			$front  = Zend_Controller_Front::getInstance();
			$params = $front->getRequest()->getParams();
			
			// Captura a sess�o
			$sessao = new Zend_Session_Namespace('portoweb');
			
			// Verifica se foi passado o nome do sistema
			if(! isset($parametros["_no_pasta_zend"]) || $parametros["_no_pasta_zend"] == "") {
				
				$cdSistema   = $params["_cd_sistema"];
				$cdTransacao = $params["_cd_transacao"];
				
				if(isset($sessao->caminhoNavegacao->cd_sistema) && $sessao->caminhoNavegacao->cd_sistema != "") {
					$cdSistema = $sessao->caminhoNavegacao->cd_sistema;
				}
				
				// Instancia o modelo
				$webSistema    = new WebSistemaModel();
				$webTransacao  = new WebTransacaoModel();
				
				// Busca a tupla do sistema e da transacao
				$webSistemaRow1  = $webSistema->fetchRow("CD_SISTEMA     = " . $cdSistema);
				$webTransacaoRow = $webTransacao->fetchRow("CD_TRANSACAO = " . $cdTransacao);
				
				// Define o caminho da pasta do relat�rio
				$noPastaZend = strtolower($webSistemaRow1->NO_PASTA_ZEND);
				
				// Verifica se existe o sistema origem
				if($webTransacaoRow->CD_SISTEMA_ORIGEM != "") {
					$webSistemaRow2 = $webSistema->fetchRow("CD_SISTEMA = " . $webTransacaoRow->CD_SISTEMA_ORIGEM);
					
					// Aletar o caminho da pasta do relat�rio
					$noPastaZend = strtolower($webSistemaRow2->NO_PASTA_ZEND);
				}
				
				// Seleciona a pasta
				$pasta = $noPastaZend;
			} else {
				// Seleciona a nome do sistema
				$pasta = strtolower($parametros["_no_pasta_zend"]);
			}
			
			// Passa para ma�usculo o formato de impress�o
			$formato = strtoupper($formato);
			
			// Parametros passados
			$paramStr = "";
			
			// Monta a string que ir� passar os par�metros para o jasper
			while(list($indice, $valor) = each($parametros)) {
				$valor = urlencode(str_replace("/","_barra_", $valor));
				if(strpos($indice, "_") === 0) {
					$paramStr .= $indice."/".$valor."/";
				} else {
					$paramStr .= "PARAM_" . $indice."/".$valor."/";
				}
			}

			// Pega o ambiente
			$ambiente = strtoupper(APPLICATION_ENV);
			
			// Caminho
			if($caminho != "") {
				$caminho = str_replace("/", "_barra_", $caminho);
				$caminho = "/path/" . $caminho;
			}
			
			// Criptografa a string
			$base64 = "uri/_barra_reports_barra_{$ambiente}_barra_{$pasta}_barra_{$nomeRelatorio}/{$paramStr}format/{$formato}{$caminho}";
			$crypt  = base64_encode($base64);

			// Redireciona a url para o controlador do jasper
			$redirector = new Zend_Controller_Action_Helper_Redirector();
			$redirector->gotoUrl("/default/jasper/index/crypt/{$crypt}")
					   ->redirectAndExist();
					
		} else {
			
			// Enterrompe o script se n�o for informado o nome do relat�rio
			die("Relat�rio n�o informado. Favor entrar em contato com o CPD!");
			
		}
    	
	}
	
}
