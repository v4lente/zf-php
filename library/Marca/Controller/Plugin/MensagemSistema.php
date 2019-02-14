<?php
/**
 *
 * Plugin que controlará as mensagens do sistema
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class Marca_Controller_Plugin_MensagemSistema extends Zend_Controller_Plugin_Abstract {

	
	/**
	 * (non-PHPdoc)
	 * @see Zend_Controller_Plugin_Abstract::dispatchLoopShutdown()
	 */
	public function dispatchLoopShutdown(){		
		
		// Se não for uma requisição ajax
		if(! $this->getRequest()->isXmlHttpRequest()) {

		 	// Pega o objeto queue(fila) que foi registrado
		 	$mensagemSistema = Zend_Registry::get("mensagemSistema");
			
		 	// Instancia o helper que vai tratar de gerar a mensagem
			$mostraMensagem = new Marca_Controller_Helper_MostraMensagem(); 
			
			// Seta a quantidade de registros a receber
			$mensagens = $mensagemSistema->receive(count($mensagemSistema));
			
			// Transforma de OBJETO para ARRAY
			$mensagem = $mensagens->toArray();
			
			// Paga o indice ZERO da MATRIZ 
			$arrMensagem = @unserialize($mensagem[0]['body']);
			
			// Chama o mnetodo que monta a mensagem
			$msg = $mostraMensagem->show($arrMensagem["msg"], $arrMensagem["titulo"], $arrMensagem["tipo"], $arrMensagem["callback"]);
					
			// Insere no corpo da view a variavel mensagemSistema
			$this->getResponse()->insert("mensagemSistema", $msg);
						
			// Elimina a queue(fila)
			$mensagemSistema->deleteQueue('mensagemSistema');
		 	
		}
				
	}
}