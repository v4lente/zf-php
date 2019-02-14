<?php

/**
 *
 * Classe IndexController
 *
 * Esta classe tem como objetivo receber as requisi��es
 * quando n�o � passado par�metro no navegador, ou seja,
 * ela � respons�vel por mostrar o conte�do inicial do
 * sistema.
 *
 * @category   Marca Sistemas
 * @package    IndexController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: IndexController.php 0 2009-11-10 17:25:00 david $
 */


/**
 * @category   Marca Sistemas
 * @package    IndexController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class IndexController extends Marca_Controller_Abstract_Comum {

    /**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {
        // Redireciona para o controlador do login
        $this->_redirect("login");
    }

}
