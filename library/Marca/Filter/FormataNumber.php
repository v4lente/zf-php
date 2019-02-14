<?php
/**
 * 
 * Forma o valor passado para o formato Number(float)
 * @author 
 *
 */
class Marca_Filter_FormataNumber implements Zend_Filter_Interface
{
	public function filter($value)
	{	
		// Transforma o valor que chegou para um valor filtrado
		$retorno = str_replace(".", "", $value);
		
		// Retorna o novo valor
		return $retorno;
	}
}