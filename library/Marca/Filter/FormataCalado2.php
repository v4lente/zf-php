<?php
/**
 * 
 * Formata o valor passado para o CALADO com 2 casas decimais
 * @author 
 *
 */
class Marca_Filter_FormataCalado2 implements Zend_Filter_Interface
{
	public function filter($value)
	{
		// Ajusta o valor para poder formatar
		$value = str_replace(",", ".", str_replace(".", "", $value));
		
		// Transforma o valor que chegou para um valor filtrado
		$retorno  = number_format($value, 2, ',', '');
		
		// Retorna o novo valor
		return $retorno;
	}
}