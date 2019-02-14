<?php
/**
 * Action Helper para mostrar uma mensagem na view 
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente
 *
 * @param $msg string Mensagem a ser mostrada
 * @param $titulo string titulo da caixa de mensagem
 * @param $tipo integer tipo do alerta da mensagem
 *
 */

class Marca_Controller_Action_Helper_MostraMensagem extends Zend_Controller_Action_Helper_Abstract
{
	// Gera uma mensagem para ser mostrada na view
	public function show($msg, $titulo = "ERRO", $tipo = 3, $callback="") {
		$mensagemSistema = Zend_Registry::get("mensagemSistema");
		$msg = array("msg"      => array($msg),
		             "titulo"   => $titulo,
		        	 "tipo"     => $tipo,
					 "callback" => $callback);
		$mensagemSistema->send(serialize($msg));
    }
}
