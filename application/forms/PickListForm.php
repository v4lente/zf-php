<?php
include_once (APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");

class PickListForm extends BaseFormAbstract {
		
	
	/**
	 * Construtor da classe
	 * 
	 * @return void
	 */
	public function __construct($name = 'form_picklist', $options = array('disableLoadDefaultDecorators' => true)) {
		
		// Chama o construtor pai
		parent::__construct ( $name, $options );
	
	}
}
?>