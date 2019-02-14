<?php
/**
 * Helper que recupera os dados da requisição anterior
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte
 * @copyright		Copyright 2010 Marca
 * @package			zendframework
 * @subpackage		zendframework.application
 * @version			1.0
 */
class Marca_Controller_Action_Helper_OperacaoAnterior extends Zend_Controller_Action_Helper_Abstract {

	/**
	 * Retorna uma url com os parametros da requisição anterior
	 *
	 * @return string
	 */
	public function getUrlAnterior() {

		// Recupera a sessão do usuário
		$sessao = new Zend_Session_Namespace('portoweb');
        $voltar = $sessao->voltar;

        // Recupera o controlador do frontend
        $front = Zend_Controller_Front::getInstance();

        // Inicializa a string que retornará a url da página anterior
		$url    = "";

		if (count($voltar["paginaAnterior"]) > 0) {

			$params = $voltar["paginaAnterior"];

			$url  = $front->getBaseUrl();

			$url .= "/" . $params['module'];
			$url .= "/" . $params['controller'];
			$url .= "/" . $params['action'];

			unset($params['module']);
			unset($params['controller']);
			unset($params['action']);

			foreach ($params as $chave => $valor){

				if (! empty($valor)) {
					$url .= "/" . $chave . "/" . urlencode($valor);
				}
			}
		}

		// Retorna a url da página anterior
		return $url;
	}

	/**
	 * Retorna os parametros da requisição anterior
	 *
	 * @return array
	 */
	public function getParametrosAnterior() {

		// Recupera a sessão do usuário
		$sessao  = new Zend_Session_Namespace('portoweb');

        // Recupera o controlador do frontend
        $front   = Zend_Controller_Front::getInstance();

        // Seta as variáveis
        $caminho = "";
        $params  = array();

        //Zend_Debug::dump($sessao->voltar);

        if(is_array($sessao->voltar["paginaAnterior"])) {

			if (count($sessao->voltar["paginaAnterior"]) > 0) {

				$params   = $sessao->voltar["paginaAnterior"];

				$caminho  = $front->getBaseUrl();

				$caminho .= "/" . $params['module'];
				$caminho .= "/" . $params['controller'];
				$caminho .= "/" . $params['action'];

				unset($params['module']);
				unset($params['controller']);
				unset($params['action']);
			}
        }

		// Retorna a url da página anterior
		return array($caminho, $params);
	}
}