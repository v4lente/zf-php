<?php
/**
 * Helper que exclui os arquivos
 *
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/   
 */
class Marca_Controller_Helper_ManipulaArquivo {
	
	
	private $path;
	
	
	private $arquivo;
	
	
	public function __construct($path, $arquivo) {
		
	}
	/**
	 * @param $arquivo the $arquivo to set
	 */
	function setArquivo($arquivo) {
		$this->arquivo = $arquivo;
	}

	/**
	 * @param $path the $path to set
	 */
	function setPath($path) {
		$this->path = $path;
	}

	/**
	 * @return the $arquivo
	 */
	function getArquivo() {
		return $this->arquivo;
	}

	/**
	 * @return the $path
	 */
	function getPath() {
		return $this->path;
	}


	
	public function excluir(){
	
		$path="images/captcha/";
       $diretorio=dir($path);

        while ($arquivo = $diretorio->read())
        {   
            if(is_file($path.$arquivo)) {
                unlink($path.$arquivo);
            }
                        
        }
        $diretorio->close();
	
	}
	
        
		
	
} 