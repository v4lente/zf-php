<?php
include_once(APPLICATION_PATH . "/forms/Marca/BaseFormAbstract.php");

class Contratos_ContratoForm extends BaseFormAbstract {

    /**
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct() { 
        
        // Chama o construtor pai
        parent::__construct(); 

        // Cria os elementos do formulário
        $cd_contrato = new Zend_Form_Element_Text('cd_contrato');
        $cd_contrato->setLabel('Código')
                    ->setRequired(false)
                    ->addValidator('NotEmpty')
                    ->setDecorators($this->_standardElementDecorator)
                   ->removeDecorator('Errors');

        $ano_contrato = new Zend_Form_Element_Text('ano_contrato');
        $ano_contrato->setLabel('Ano')
                     ->setRequired(true)
                     ->addValidator('NotEmpty')
                     ->setDecorators($this->_standardElementDecorator)
                     ->removeDecorator('Errors');
            
        $cd_tp_contrato = new Zend_Form_Element_Select('cd_tp_contrato');
        $cd_tp_contrato->setLabel('Tipo de Contrato')
                       ->setRequired(true)
                       ->setDecorators($this->_standardElementDecorator)
                       ->removeDecorator('Errors');

        $cd_contrato_porto = new Zend_Form_Element_Text('cd_contrato_porto');
        $cd_contrato_porto->setLabel('Identificação do Contrato')
                          ->setRequired(true)
                          ->addValidator('NotEmpty')
                          ->setDecorators($this->_standardElementDecorator)
                          ->removeDecorator('Errors');
                          
        $cd_tp_rel_porto = new Zend_Form_Element_Select('cd_tp_rel_porto');
        $cd_tp_rel_porto->setLabel('Relação Porto')
                        ->setRequired(true)
                        ->setDecorators($this->_standardElementDecorator)
                        ->removeDecorator('Errors');
                        
        $fl_paga_recebe = new Zend_Form_Element_Text('fl_paga_recebe');
        $fl_paga_recebe->setLabel('Tipo de Relação')
                       ->setDecorators($this->_standardElementDecorator)
                       ->setAttrib("readonly", "readonly")
                       ->removeDecorator('Errors');
        
        $cd_tp_rel_outro = new Zend_Form_Element_Select('cd_tp_rel_outro');
        $cd_tp_rel_outro->setLabel('Relação da outra Parte')
                        ->setRequired(true)
                        ->setDecorators($this->_standardElementDecorator)
                        ->removeDecorator('Errors');
        
        $cd_cliente = new Zend_Form_Element_Text('cd_cliente');
        $cd_cliente->setLabel('Cód. Cliente (outra parte)')
                   ->setDecorators($this->_standardElementDecorator)
                   ->removeDecorator('Errors');
        
        $cd_licitacao = new Zend_Form_Element_Text('cd_licitacao');
        $cd_licitacao->setLabel('Identificação do Contrato')
                     ->setDecorators($this->_standardElementDecorator)
                     ->removeDecorator('Errors');
        
        $no_cliente = new Zend_Form_Element_Text('no_cliente');
        $no_cliente->setLabel('Razão Social')
                   ->setDecorators($this->_standardElementDecorator)
                   ->setAttrib("readonly", "readonly")
                   ->removeDecorator('Errors');
        
        $bt_cliente = new Zend_Form_Element_Button('bt_cliente');
        $bt_cliente->setLabel('?')
                   ->setDecorators($this->_standardElementDecorator);
                   
        $nr_processo = new Zend_Form_Element_Text('nr_processo');
        $nr_processo->setLabel('Nr. Processo')
                    ->setDecorators($this->_standardElementDecorator)
                    ->removeDecorator('Errors');
        
        $bt_processo = new Zend_Form_Element_Button('bt_processo');
        $bt_processo->setLabel('?')
                    ->setDecorators($this->_standardElementDecorator);
        
        $ds_processo = new Zend_Form_Element_Text('ds_processo');
        $ds_processo->setLabel('Descrição Processo')
                    ->setDecorators($this->_standardElementDecorator)
                    ->setAttrib("readonly", "readonly")
                    ->removeDecorator('Errors');
        
        $cd_licitacao = new Zend_Form_Element_Text('cd_licitacao');
        $cd_licitacao->setLabel('Código Licitação')
                     ->setDecorators($this->_standardElementDecorator)
                     ->removeDecorator('Errors');
        
        $no_orgao_licitador = new Zend_Form_Element_Text('no_orgao_licitador');
        $no_orgao_licitador->setLabel('Orgão Licitador')
                           ->setDecorators($this->_standardElementDecorator)
                           ->removeDecorator('Errors');
        
        $dt_assinatura = new Zend_Form_Element_Text('dt_assinatura');
        $dt_assinatura->setLabel('Data da Assinatura')
                      ->setDecorators($this->_standardElementDecorator)
                      ->removeDecorator('Errors');
        
        $ck_indeterminado = new Zend_Form_Element_MultiCheckbox('ck_indeterminado', array(
								    'multiOptions' => array(
								        '0' => 'Indeterminado',
								        '1' => 'Outros'
								    ),
								    'separator' => ''
								));
        $ck_indeterminado->setLabel('Prazo')
                         ->setDecorators($this->_standardElementDecorator)
                         ->removeDecorator('Errors');
        
        $dt_ini_vigencia = new Zend_Form_Element_Text('dt_ini_vigencia');
        $dt_ini_vigencia->setLabel('Data Início Vigência')
                        ->setDecorators($this->_standardElementDecorator)
                        ->removeDecorator('Errors');
        
        $dt_fim_vigencia = new Zend_Form_Element_Text('dt_fim_vigencia');
        $dt_fim_vigencia->setLabel('Data Fim Vigência')
                        ->setDecorators($this->_standardElementDecorator)
                        ->removeDecorator('Errors');
        
        $ds_objeto = new Zend_Form_Element_Textarea('ds_objeto');
        $ds_objeto->setLabel('Descrição do Objeto')
                  ->setDecorators($this->_standardElementDecorator)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->setAttrib('rows', 3)
                  ->setAttrib('cols', 35)
                  ->removeDecorator('Errors');
        
        $observacao = new Zend_Form_Element_Textarea('observacao');
        $observacao->setLabel('Observações')
                   ->setDecorators($this->_standardElementDecorator)
                   ->addFilter('StripTags')
                   ->addFilter('StringTrim')
                   ->setAttrib('rows', 3)
                   ->setAttrib('cols', 35)
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
        $this->addElements(array(
                                 $cd_contrato, 
                                 $ano_contrato,
                                 $cd_tp_contrato,
                                 $cd_contrato_porto,
                                 $cd_tp_rel_porto,
                                 $fl_paga_recebe,
                                 $cd_tp_rel_outro,
                                 $cd_cliente,
                                 $bt_cliente,
                                 $no_cliente,
                                 $cd_licitacao,
                                 $nr_processo,
                                 $bt_processo,
                                 $ds_processo,
                                 $no_orgao_licitador,
                                 $dt_assinatura,
                                 $ck_indeterminado,
                                 $dt_ini_vigencia,
                                 $dt_fim_vigencia,
                                 $ds_objeto,
                                 $observacao
                                 ));
        
        // Agrupa os elementos do formulário
        $this->addDisplayGroup(array('cd_contrato', 
                                     'ano_contrato',
        							 'cd_tp_contrato',
                                     'cd_contrato_porto',
                                     'cd_tp_rel_porto',
                                     'fl_paga_recebe',
                                     'cd_tp_rel_outro',
                                     'cd_cliente',
                                     'bt_cliente',
                                     'no_cliente',
                                     'cd_licitacao',
                                     'nr_processo',
                                     'bt_processo',
                                     'ds_processo',
                                     'no_orgao_licitador',
                                     'dt_assinatura',
                                     'ck_indeterminado',
                                     'dt_ini_vigencia',
                                     'dt_fim_vigencia',
                                     'ds_objeto',
                                     'observacao'), 'elementGroup',
            array('decorators' => $this->_standardGroupDecorator)
        ); 
        
        //$this->addDisplayGroup(array('salvar', 'ajuda', 'fechar'), 'submitGroup',
        //    array('decorators' => $this->_buttonGroupDecorator)
        //);
    } 
    
}
