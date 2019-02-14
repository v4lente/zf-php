<?php

include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");

class Contratos_CadTipoValorForm extends BaseFormAbstract {

    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct() { 
        
        // Chama o construtor pai
        parent::__construct();

        // Cria os elementos do formulário
        $codigo = new Zend_Form_Element_Text('cd_tp_valor');
        $codigo->setLabel('Código')              
               ->setDecorators($this->_standardElementDecorator)
               ->removeDecorator('Errors');

        $descricao = new Zend_Form_Element_Text('no_tp_valor');
        $descricao->setLabel('Descrição')                  
                  ->setDecorators($this->_standardElementDecorator)
                  ->removeDecorator('Errors');
              
        $submit = new Zend_Form_Element_Submit('salvar');
        $submit->setLabel('Salvar')
               ->setDecorators($this->_buttonElementDecorator);
               
        $ajuda = new Zend_Form_Element_Button('ajuda');
        $ajuda->setLabel('Ajuda')
               ->setDecorators($this->_buttonElementDecorator)
               ->setAttrib('onClick', 'alert(\'Ainda não possui ajuda!\');');
               
        $fechar = new Zend_Form_Element_Button('fechar');
        $fechar->setLabel('Fechar')
               ->setDecorators($this->_buttonElementDecorator)
               ->setAttrib('onClick', 'window.close();');
        
        // Adiciona os elementos no formulário
        $this->addElements(array($codigo, $descricao));
        
        // Agrupa os elementos do formulário
        $this->addDisplayGroup(array('cd_tp_valor', 'no_tp_valor'), 'elementGroup',
            array('decorators' => $this->_standardGroupDecorator)
        ); 
        
        //$this->addDisplayGroup(array('salvar', 'ajuda', 'fechar'), 'submitGroup',
        //    array('decorators' => $this->_buttonGroupDecorator)
        //);
    } 
    
}
