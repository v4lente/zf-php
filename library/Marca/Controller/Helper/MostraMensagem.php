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

class Marca_Controller_Helper_MostraMensagem {
	
	
	/**
	 * 
	 * Joga para a view as mensagens
	 * 
	 * @param array $mensagens
	 * @param string $titulo
	 * @param int $tipo
	 */
	public function show($mensagens=array(), $titulo="Mensagem do Sistema", $tipo=1, $callback="") {
        
	    // Inicializa o script
	    $script = "";
	    
	    // Verifica se existe alguma mensagem
	    if(count($mensagens) > 0) {
	        
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
	    }
	    
        // Retorna um script que será executado na view        
        return $script;        
    }
    
}