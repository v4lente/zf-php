<?php
/**
 * View Helper para tradução
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente / Márcio Souza Duarte
 *
 * @param string  $cd_texto
 * @param string  $cd_idioma
 * @param integer $campo
 *
 * @return string
 */

class Zend_View_Helper_Traduz extends Zend_View_Helper_Abstract
{
	public function traduz($cd_texto="", $cd_idioma="POR", $campo="NO_TEXTO") {

		try {
            
		    // Se o código do texto não for um array, retorna o 
		    // valor passado traduzido, caso contrário retorna 
		    // um array contendo todos os labels traduzidos para 
		    // a aplicação específica
		    if(! is_array($cd_texto)) {
		        
		        // Carrega o modelo TraducaoModel
    			Zend_Loader::loadClass('TraducaoModel');
    
    			// Instancia o modelo TraducaoModel
    			$traduz = new TraducaoModel();
    
    			// Traduz o label passado
    			$traduzido = $traduz->fetchRow("UPPER(CD_TEXTO) = UPPER('{$cd_texto}') AND UPPER(CD_IDIOMA) = UPPER('{$cd_idioma}')");
    
    			$traducao = $cd_texto;
    			if (! is_null($traduzido)) {
    				$traducao = $traduzido->NO_TEXTO;
    			}
    			
		    } else {
		        
		        // Recupera o objeto do banco
		        $db = Zend_Registry::get("db");
		        
		        // Monta a condição de retorno
		        $where = "";
		        foreach($cd_texto as $valor) {
		            if($where != "") {
		                $where .= " OR ";
		            }
		            
		            $where .= "(UPPER(T.CD_TEXTO) = UPPER('" . $valor . "'))";
		        }
		        
		        // Gera a consulta com os labels passados
		        $select = $db->select()
		                     ->from(array("T" => "TRADUCAO"), "T.CD_TEXTO, T.NO_TEXTO")
		                     ->where("UPPER(T.CD_IDIOMA) = UPPER('" . $cd_idioma . "')")
		                     ->where($where);
		        
		        // Executa a consulta         
		        $resConsulta = $db->fetchAll($select);
		        
		        // Monta o array de retorno
		        $traducao = array();
		        foreach($resConsulta as $campoTraduzido) {
		            $traducao[$campoTraduzido->CD_TEXTO] = $campoTraduzido->NO_TEXTO;
		        }
		    }

			// Retorna o texto traduzido
			return $traducao;

		} catch (Exception $e) {
			echo "Erro ao traduzir {$e->getMensagem()}";
		}

	}
}