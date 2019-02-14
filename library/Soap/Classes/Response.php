<?php

/** 
 * Classe responsavel por enviar os dados de retorno
 */
class Response {
    /** 
     * Código de retorno.
     * 
     * @var integer
     */
    public $code;
	
	/** 
     * Título.
     * 
     * @var string 
     */
    public $title;
	
	/** 
     * Descrição da mensagem de retorno.
     * 
     * @var string 
     */
    public $description;
	
	/** 
     * Dados de retorno.
     * 
     * @var array 
     */
    public $data;
}

?>