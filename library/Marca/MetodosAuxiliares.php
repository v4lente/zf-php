<?php
/**
 * Created on 18/11/2009
 *
 * Modelo da classe Marca_MetodosAuxiliares
 *
 * Esta classe possui fun��es diversificadas
 *
 * @filesource
 * @author			M�rcio Souza Duarte
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
     * @param string $cd_texto    Define a vari�vel a ser traduzida
     * @param string $cd_idioma   Define para que idioma ser� traduzido
     * @param string $campo       Define o campo da consulta na tebela que retornar� a tradu��o (NO_TEXTO ou outro campo)
     * @return string
     */
    public function traduz($cd_texto="", $cd_idioma="POR", $campo="NO_TEXTO") {
		// Passa para maiusculo o c�digo do texto
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

		// Se n�o traduzir pega o pr�prio c�digo do texto
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

		// Variavel recebendo a string j� fazendo as substitui��es
		$var = ereg_replace("[����]","A",$var);
		$var = ereg_replace("[����]","a",$var);
		$var = ereg_replace("[���]","E",$var);
		$var = ereg_replace("[���]","e",$var);
		$var = ereg_replace("[�]","I",$var);
		$var = ereg_replace("[�]","i",$var);
		$var = ereg_replace("[����]","O",$var);
		$var = ereg_replace("[�����]","o",$var);
		$var = ereg_replace("[���]","U",$var);
		$var = ereg_replace("[���]","u",$var);
		$var = str_replace("�","C",$var);
		$var = str_replace("�","c",$var);

		// Retorna o resultado das modifica��es
		return $var;

    }
}
?>