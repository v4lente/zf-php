<?php
/**
 * Action Helper para ordernar as consultas do banco
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author M�rcio Duarte
 *
 * @param  string    $column     Coluna a ser ordenada
 * @param  string    $orderby    Sentido da ordena��o
 * 
 * @return string    $order      Retorna a coluna a ser ordenada e o sentido da ordena��o
 *
 */

class Marca_Controller_Action_Helper_OrdenaConsulta extends Zend_Controller_Action_Helper_Abstract
{
	public function ordenar($column="", $orderby="") {
		
	    // Recupera o controlador do frontend
        $front = Zend_Controller_Front::getInstance();
        
        // Recupera os par�metros da requisi��o
        $params = $front->getRequest()->getParams();
        
	    // Ordena��o
	    if($column != "") {
	        $order = $column;
	        if($orderby != "") {
	            $order .= " " . $orderby;
	        }
	    }
        
        if(isset($params['column'])){
            if($params['orderby'] == "") {
                $params['orderby'] = "ASC";
            }
            
            $order = $params['column'] . " " . $params['orderby'];
        }
                
        // Retorna a ordena��o
        return $order != "" ? $order : null;
        
    }
}
