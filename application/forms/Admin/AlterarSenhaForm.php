<?php

include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");

class Admin_AlterarSenhaForm extends BaseFormAbstract {

    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct() { 
        
        // Chama o construtor pai
        parent::__construct();
        
        // Cria os elementos do formulário
        $usuario = new Zend_Form_Element_Text('cd_usuario');
        $usuario->setLabel('Usuário')
                ->setRequired(true)
                ->addValidator('NotEmpty')
                ->setDecorators($this->_standardElementDecorator)
                ->removeDecorator('Errors')
                ->addFilters(array('StringToUpper'));

        $senha = new Zend_Form_Element_Password('senha');
        $senha->setLabel('Senha')
              ->setRequired(true)
              ->addValidator('NotEmpty')
              ->setDecorators($this->_standardElementDecorator)
              ->removeDecorator('Errors');
              
        $rsenha = new Zend_Form_Element_Password('rsenha');
        $rsenha->setLabel('Repetir Senha')
              ->setRequired(true)
              ->addValidator('NotEmpty')
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
        $this->addElements(array($usuario, $senha, $rsenha));
        
        // Agrupa os elementos do formulário
        $this->addDisplayGroup(array('cd_usuario', 'senha', 'rsenha'), 'elementGroup',
            array('decorators' => $this->_standardGroupDecorator)
        ); 
        
        //$this->addDisplayGroup(array('salvar', 'ajuda', 'fechar'), 'submitGroup',
        //    array('decorators' => $this->_buttonGroupDecorator)
        //);
    } 
    
}
