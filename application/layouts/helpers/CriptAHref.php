<?php
/**
 * Action Helper para criptografar a url passada
 *
 * @uses Zend_Controller_Action_Helper_Abstract
 *
 * @author Wagner Morales, Marcio Duarte
 *
 * @param string  $caminho      Caminho do controlador/action
 * @param array   $parametros - São os parâmetros a serem passados no link
 * @param string  $texto        Texto que será mostrado no link
 * @param array   $opcoes       Define se o link terá algum atributo extra
 * @param int     $tipo         Define se será retornado um link ou apenas a código criptografado
 *
 * @return base64_encode
 */

class Zend_View_Helper_CriptAHref extends Zend_View_Helper_Abstract
{
	public function criptAHref($caminho="", $parametros=array(), $texto="", $opcoes=array(), $tipo = 1) {

		try {
            // Criptografa a url passada
            if(is_array($parametros) && count($parametros) > 0) {
            	$paramStr = "";
            	foreach($parametros as $indice => $valor) {
            	    if($valor != "") {            	    	
            	    	$valor = str_replace("/","_barra_", $valor);
            	    	$paramStr .= $indice."/".$valor."/";
            	    }
            	}
            	
	            if($paramStr != "") {
	            	$parametros = base64_encode($paramStr);
		        }
            } else {
            	$parametros = "";
            }


            if(count($opcoes) > 0) {
	            // Verifica se tem atributos para o campo
	            $optStr = "";
	            foreach($opcoes as $indice => $valor) {
	                $optStr .= $indice . "='{$valor}' ";
	            }
            }

            $retorno = "";
            switch ($tipo) {
            	case 1: // Monta um link
            		$caminho = $this->view->baseUrl() . "/" . $caminho;
            		if (trim($parametros) != "") {
            			$parametros = "crypt/" . $parametros;
            		}
            		$retorno = "<a {$optStr} href='{$caminho}/{$parametros}'>" . $texto . "</a>";

            		break;

            	case 2: // Retorna um ARRAY
            		$retorno = array();
            		if (trim($parametros) != "") {
            			$retorno = array("crypt"=>$parametros);
            		}
            		break;

            	case 3: // Retorna uma chave criptografada
            		if (trim($parametros) != "") {
            			$parametros = "crypt/" . $parametros;
            			
	            		if ($caminho != "" && substr($caminho, -1) != "/") {
	            			$caminho .= "/";
	            		}
            		}
            		
            		$retorno = $caminho . $parametros;
            		break;
            }

            return $retorno;

		} catch(Zend_Exception $e) {
			return $e->getMessage();
		}
    }
}