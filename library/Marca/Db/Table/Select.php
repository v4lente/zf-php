<?php

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
 * @package    Zend_Db
 * @subpackage Select
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Select.php 18200 2009-09-17 21:25:37Z beberlei $
 */

/**
 * Class for SQL SELECT query manipulation for the Zend_Db_Table component.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Marca_Db_Table_Select extends Zend_Db_Table_Select
{

    /**
     * Class constructor
     *
     * @param Zend_Db_Table_Abstract $adapter
     */
    public function __construct(Zend_Db_Table_Abstract $table)
    {
        parent::__construct($table);
		
    }

	/**
     * 
     * Faz a ordenação da consulta
     * 
     * @param  string $column
     * @param  string $orderby
     * 
     * @return object Zend_Db_Table_Select
     */
    public function orderByList($paramsOrder="") {
        
        // Recupera o controlador do frontend
        $front = Zend_Controller_Front::getInstance();
        
		// Recebe os parâmetros passados na requisição
		$params = $front->getRequest()->getParams();
		
        if(is_array($paramsOrder) || $paramsOrder != "" || isset($params['column'])) {
		
			if(is_array($paramsOrder) || $paramsOrder != "") {
				
				if(is_array($paramsOrder) && count($paramsOrder) == 1) {
					$order = $paramsOrder[0];
					
				} if(is_array($paramsOrder) && count($paramsOrder) > 1) {
					$order = implode(", ", $paramsOrder);
				
				} else if(! is_array($paramsOrder) && $paramsOrder != "") {
					$order = $paramsOrder;
				}
				
			} else if(isset($params['column'])) {
				
				if($params['orderby'] == "") {
					$params['orderby'] = "ASC";
				}
				
				// Reordena pela listagem
				$order = $params['column'] . " " . $params['orderby'];
			}
			
			// Reseta a ordenação padrão
			$this->reset(self::ORDER)->order($order);
			
		}
        
        // Retorna o objeto Zend_Db_Table_Select
        return $this;
        
    }
    
    
	/**
	* Monta o START WITH and CONNECT BY PRIOR
	* 
	* @param String $condicao ex: CAMPO IS NULL
	* @param Strin $conecByPrior ex: CAMPO = CAMPO_PAI
	* 
	* @return String
	*/
	public function startWithConectByPrior($condicao, $conecByPrior) {
	
		try {
			
			$this.=" START WITH {$condicao} CONNECT BY PRIOR {$conecByPrior}";
			
			return $this;	
			
		} catch (Exception $e) {
			return $e->getMessage();
		}
	
	}
	
	/**
	* Monta o CONNECT BY PRIOR
	*
	* @param Strin $conecByPrior ex: CAMPO = CAMPO_PAI
	*
	* @return String
	*/
	public function conectByPrior($conecByPrior) {
	
		try {
	
			return $this.=" CONNECT BY PRIOR {$conecByPrior}";
	
		} catch (Exception $e) {
			return $e->getMessage();
		}
	
	}	

}