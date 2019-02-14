<?php
/**
 * Action Helper para fazer a tradução dos campos dos formulários
 *
 * @uses Zend_View_Helper_Abstract
 * @author Márcio Duarte
 * @param array  request
 *
 */

class Marca_Controller_Action_Helper_Traduz extends Zend_Controller_Action_Helper_Abstract {
    
    public function __construct() {
        
        // Carrega o modelo TraducaoModel
        Zend_Loader::loadClass('TraducaoModel');
        
        // Instancia o modelo TraducaoModel
        $traducoes = new TraducaoModel();
        
        // Registra o objeto de tradução
        Zend_Registry::set("traducoes", $traducoes);
    }
    
    /**
     * Método que tem como função fazer a tradução de strings passadas
     * 
     * @param $cd_texto  Array
     * @param $cd_idioma Integer
     * @param $campo     String
     */
    public function traduz($cd_texto="", $cd_idioma="POR", $campo="NO_TEXTO") {
        
        try {
            
            // Se o código do texto não for um array, retorna o 
            // valor passado traduzido, caso contrário retorna 
            // um array contendo todos os labels traduzidos para 
            // a aplicação específica
            if(! is_array($cd_texto)) {
                
                // Recupera o objeto de tradução
                $traducoes = Zend_Registry::get("traducoes");
                
                // Seta o código do idioma caso não tenha sido passado
                $cd_idioma = !empty($cd_idioma) ? $cd_idioma : "POR";
    
                $traduzido = $traducoes->fetchRow("UPPER(CD_TEXTO) = UPPER('{$cd_texto}') AND UPPER(CD_IDIOMA) = UPPER('{$cd_idioma}')");
    
                $retTraducao = $cd_texto;
                if (! is_null($traduzido)) {
                    $retTraducao = $traduzido->NO_TEXTO;
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
                
                // Monta o array com os índices e valores da consulta
                $traducao = array();
                foreach($resConsulta as $campoTraduzido) {
                    $traducao[$campoTraduzido->CD_TEXTO] = $campoTraduzido->NO_TEXTO;
                }
                
                // Monta o array de retorno na mesma ordem que foi passado
                $retTraducao = array();
                for($i=0; $i < count($cd_texto); $i++) {
                    if(array_key_exists($cd_texto[$i], $traducao)) {
                        foreach($traducao as $indice => $valor) {
                            if($cd_texto[$i] == $indice) {
                                $retTraducao[$indice] = $valor;
                            }
                        }
                    } else {
                        $retTraducao[$cd_texto[$i]] = $cd_texto[$i];
                    }
                }
            }
            
            // Retorna os campos traduzidos na ordem correta
            return $retTraducao;

        } catch (Zend_Exception $e) {
            echo "Erro ao traduzir: " . $e->getMessage();
        }

    }
    
}
