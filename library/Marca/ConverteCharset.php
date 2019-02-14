<?php

/**
 * Created on 14/07/2010
 *
 * Modelo da classe Marca_ConverteCharset
 *
 * Esta classe serve para converter dados de um charset para outro
 *
 * @filesource
 * @author          Márcio Souza Duarte
 * @copyright       Copyright 2010 Marca
 * @package         zendframework
 * @subpackage      zendframework.library.marca
 * @version         1.0
 */
class Marca_ConverteCharset {

    /**
     * Converte o charset dos caracteres
     *
     * @param array $input
     * @param string $chrDe
     * @param string $chrPara
     * @param boolean $encode_keys
     *
     * @return array
     */
    public static function converter($input, $chrDe="iso-8859-1", $chrPara="utf-8", $encode_keys=false) {
        if(is_array($input) || is_object($input)) {
            $result = array();
            foreach($input as $k => $v) {
                // Se $encode_keys = TRUE codifica as chaves
            	$key = ($encode_keys) ? iconv($chrDe, $chrPara, $k) : $k;
                $result[$key] = self::converter( $v, $chrDe, $chrPara, $encode_keys);
            }
        }
        else {
            $result = iconv($chrDe, $chrPara, $input);
        }

        return $result;
    }

}