<?php
/**
 * Action Helper para repopular formulario
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente
 *
 * @param array  params
 *
 */

class Marca_Controller_Action_Helper_RePopulaFormulario extends Zend_Controller_Action_Helper_Abstract
{
	public function repopular($params = array(), $caseMode = "") {

		$form["form"] = array();
		
		// Verifica se o parametro passado é uma instancia de 
		if ($params instanceof Zend_Db_Table_Row_Abstract) {
			$params = $params->toArray();
		}

		// Joga em array os indice e valores dos campos do formulario
		$values = array_values($params);
		$chaves = array_keys($params);

		for ($i=0; $i < count($params); $i++) {
			if(!is_array($values[$i])){
				if (trim($caseMode) != "") {

				    switch (strtolower($caseMode)) {

				    	case "lower":
				            $form[strtolower($chaves[$i])]= $values[$i];
				            break;

				        case "upper":
                            $form[strtoupper($chaves[$i])]= $values[$i];
                            break;

				        default:
				        	$form[strtolower($chaves[$i])]= $values[$i];

				    }

				} else {
					$form[$chaves[$i]]= $values[$i];
				}

			} else {
				foreach($values[$i] as $indice => $value) {
					$form[$chaves[$i]][$indice] = $value;
				}
			}
		}

		// Recupera a view
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')
                                                    ->getResource('layout')
                                                    ->getView();
		$view->form = $form;

    }
}
