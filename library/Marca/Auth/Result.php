<?php
/**
 * @see Zend_Auth_Result
 */
require_once 'Zend/Auth/Result.php';

/**
 *
 * Classe Marca_Auth_Result
 *
 * Classe responsável por tratar erros específicos do oracle
 * do usuário no sistema.
 *
 * @category   Marca Sistemas
 * @package    Marca_Auth_Result
 * @copyright  Copyright (c) 1991-2011 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: Result.php 0 2011-10-07 10:00:00 David $
 * @see Zend_Auth_Result 
 */

class Marca_Auth_Result extends Zend_Auth_Result 
{
    
    /**
     * Conta do usuario bloqueada.
     */
    const FAILURE_ACCOUNT_USER_LOCKED   = 28000;
    
    /**
     *  Tipo de mensagem que vai ser gerada
     *  Utilizado para setar a cor da caixa com a mensagem (alerta)
     */
    private $_tipo;

    /**
     * Sets the result code, identity, and failure messages
     *
     * @param  int     $code
     * @param  mixed   $identity
     * @param  array   $messages
     * @return void
     */
    public function __construct($code, $identity, array $messages = array(), $tipo = 4)
    {
       
    	parent::__construct($code, $identity, $messages);
       
       	$this->_code             = $code;
       	$this->_identity         = $identity;
       	$this->_messages         = $messages;
       	$this->_tipo             = $tipo;
    }
   
    
	/**
     * getTipo - retorna o tipo de alerta de mensagem
     *
     * @return int
     */
    public function getTipo()
    {
        return $this->_tipo;
    }
    
}