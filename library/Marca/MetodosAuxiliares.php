<?php
/**
 * Created on 18/11/2009
 *
 * Modelo da classe Marca_MetodosAuxiliares
 *
 * Esta classe possui funções diversificadas
 *
 * @filesource
 * @author			Márcio Souza Duarte
 * @copyright		Copyright 2009 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */

class Marca_MetodosAuxiliares {

	/**
     * Efetua a traducao do texto passado
     *
     *
     * @param string $cd_texto    Define a variável a ser traduzida
     * @param string $cd_idioma   Define para que idioma será traduzido
     * @param string $campo       Define o campo da consulta na tebela que retornará a tradução (NO_TEXTO ou outro campo)
     * @return string
     */
    public function traduz($cd_texto="", $cd_idioma="POR", $campo="NO_TEXTO") {
		// Passa para maiusculo o código do texto
		$cd_texto = strtoupper($cd_texto);
		// Monta a consulta
		$sql =	"SELECT NVL({$campo}, ' ') AS NO_TEXTO ".
				"FROM traducao ".
				"WHERE cd_texto = '{$cd_texto}' ".
				"AND cd_idioma = '{$cd_idioma}'";
		// Executa a consulta
		$resultado = Zend_Registry::get("db")->fetchAll($sql);

		//busca a traducao do texto
		$traducao = "";
		foreach($resultado as $linha){
			$traducao = trim($linha->NO_TEXTO);
		}

		// Se não traduzir pega o próprio código do texto
        if(empty($traducao)){
			$traducao = $cd_texto;
		}

		// Retorna o texto traduzido
		return $traducao;

	}

	/**
     * Remove todos os acentos de um texto
     *
     * @param string $str
     * @return String
     */
    public function removeAcentos($str) {

		// Variavel recebendo a string a ser tratada
		$var = $str;

		// Variavel recebendo a string já fazendo as substituições
		$var = ereg_replace("[ÁÀÂÃ]","A",$var);
		$var = ereg_replace("[áàâãª]","a",$var);
		$var = ereg_replace("[ÉÈÊ]","E",$var);
		$var = ereg_replace("[éèê]","e",$var);
		$var = ereg_replace("[Í]","I",$var);
		$var = ereg_replace("[í]","i",$var);
		$var = ereg_replace("[ÓÒÔÕ]","O",$var);
		$var = ereg_replace("[óòôõº]","o",$var);
		$var = ereg_replace("[ÚÙÛ]","U",$var);
		$var = ereg_replace("[úùû]","u",$var);
		$var = str_replace("Ç","C",$var);
		$var = str_replace("ç","c",$var);

		// Retorna o resultado das modificações
		return $var;

    }
}
?>