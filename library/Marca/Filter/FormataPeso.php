<?php
/**
 * 
 * Forma o valor passado para o formato PESO
 * @author 
 *
 */
class Marca_Filter_FormataPeso implements Zend_Filter_Interface
{
	public function filter($value)
	{
		
		if (trim($value) == "") {
			$value = 0;
		}
		
		// Transforma o valor que chegou para um valor filtrado
		$retorno  = number_format($value, 3, ',', '');
		
		// Retorna o novo valor
		return $retorno;
	}
}