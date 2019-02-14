<?php
/**
 * Action Helper para limpar os parâmetros da requisição
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author Márcio Duarte
 * 
 * @return array     $params
 *
 */

class Marca_Controller_Action_Helper_LimpaParametrosRequisicao extends Zend_Controller_Action_Helper_Abstract
{
	public function limpar() {
		
	    // Recupera o controlador do frontend
        $front = Zend_Controller_Front::getInstance();
        
        // Recupera os parâmetros da requisição
        $params = $front->getRequest()->getParams();
                
	    // Limpa os parâmetros não obrigatórios
        foreach($params as $indice => $valor) {
            if($indice != "module"     && 
               $indice != "controller" &&
               $indice != "action"     &&
               $indice != "operacao"   &&
               !(strpos($indice, "_") === 0)) {
               
                $params[$indice] = "";
            }
        }
        
        // Retorna o caminho em negrito
        return $params;
        
    }
}
