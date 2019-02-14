<?php
/**
 * @see Zend_Auth
 */
require_once 'Zend/Auth.php';

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Auth
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Auth.php 18039 2009-09-09 03:35:19Z ralph $
 */


/**
 * @category   Zend
 * @package    Zend_Auth
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Marca_Auth extends Zend_Auth 
{
	
	/**
     * Singleton instance
     *
     * @var Zend_Auth
     */
    protected static $_instance = null;
	
    /**
     *  Ambiente de execução da aplicação     
     */
    private $_application_env;

    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct()
    {
    	$this->_application_env = APPLICATION_ENV;   
    }
    
	/**
     * Returns an instance of Zend_Auth
     *
     * Singleton pattern implementation
     *
     * @return Zend_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    /**
     * 
     * Utilizado para fazer a comparação do ambiente juntamente com o usuário logado
     * 
     * @return APPLICATION_ENV
     */
    public function getApplicationEnv() {
    	
    	return $this->_application_env;    
    }    
	
}