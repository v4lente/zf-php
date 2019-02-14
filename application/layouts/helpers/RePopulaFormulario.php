<?php
/**
 * Action Helper para repopular formulario
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente
 *
 * @param array  request
 *
 */

class Zend_View_Helper_RePopulaFormulario extends Zend_View_Helper_Abstract
{
	public function rePopulaFormulario($request = array(), $caseMode = "") {
        
		$form["form"] = array();
		
		// Joga em array os indice e valores dos campos do formulario
		$values = array_values($request);
		$chaves = array_keys($request);

		for ($i=0; $i < count($request); $i++) {
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

		$this->view->form = $form;
    }
}