<?php
/**
 * Action Helper para gerar mensagens
 *
 * @uses Zend_Controller_Action_Helper_Abstract
 *
 * @author David Valente, Marcio Duarte
 *
 * @param array  $mensagens
 * @param string  $titulo
 * @param integer $tipo
 *
 * @return string
 */

class Zend_View_Helper_MostraMensagem extends Zend_View_Helper_Abstract
{
	public function mostraMensagem($mensagens=array(), $titulo="Mensagem do Sistema", $tipo=1, $callback="") {

		$mensagem = "Array(";

        for($i=0; $i < count($mensagens); $i++) {
            if($i > 0) {
            	$mensagem .= ",";
            }

            $mensagem .= "'" . $mensagens[$i] . "'";

        }
        $mensagem .= ")";
		
		if(empty($tipo)) {
			$tipo = 1;
		}

		if(empty($callback)) {
			$callback = null;
		}
        
        $script = "<script type='text/javascript'>
        			   $(document).ready(function() {
        		       	   Base.montaMensagemSistema({$mensagem}, '{$titulo}', {$tipo}, '{$callback}');
        		       });
        		   </script>";

        // Retorna um script que ser� executado na view
        return $script;
    }
}