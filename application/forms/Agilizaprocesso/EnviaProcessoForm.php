<?php
include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");

class Agilizaprocesso_EnviaProcessoForm extends BaseFormAbstract {

    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct() { 
        
        // Chama o construtor pai
        parent::__construct();
        
        // Cria os elementos do formul�rio
        $cd_local = new Zend_Form_Element_Text('cd_local');
        $cd_local->setLabel('Destinat�rio')
	              ->setRequired(true)
	              ->setAttrib('class', 'required numero')	              
	              ->setAttrib('title', 'C�digo do destinat�rio')
	              ->addValidator('NotEmpty')
	              ->setDecorators($this->_standardElementDecorator)
	              ->removeDecorator('Errors')
	              ->setAttrib('onblur','Interna.retornaLocalX(this.value, event);')
	              ->setAttrib('onkeypress','Interna.retornaLocalX(this.value, event);');
               
        // Cria um BUTTON
	    $bt_matric = new Zend_Form_Element_Button('bt_matric');
        $bt_matric->setLabel('?')
         		  ->setAttrib('reference', 'cd_local')
                  ->setDecorators($this->_standardElementDecorator);
                
        // Cria um INPUT TEXT             
        $no_local = new Zend_Form_Element_Text('no_local');
        $no_local->setAttrib('readonly','readonly')                  
                 ->setDecorators($this->_standardElementDecorator)
                 ->removeDecorator('Errors');
        
        // Adiciona os elementos no formul�rio
        $this->addElements(array($cd_local, $bt_matric,$no_local));
        
        // Agrupa os elementos do formul�rio
        $this->addDisplayGroup(array('cd_local', 'bt_matric', 'no_local'), 'elementGroup',
            array('decorators' => $this->_standardGroupDecorator)
        );        
        
    }    
}