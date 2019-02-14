<?php
/**
 *
 * Classe que tem como função listar os grupos, usuários e períodos de 
 * acesso as funcionalidades do Sistema Porto RG.
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_GrupoAcessoPeriodoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("WebGrupoModel");
        Zend_Loader::loadClass("UsuarioModel");
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 
        
        // Instancia os modelos de dados
        $webGrupos = new WebGrupoModel();
        
        // Busca todos os grupos da web e cliente/servidor
        $dados = array();
        $grupos = $webGrupos->fetchAll($webGrupos->queryBuscaGruposWebCliServ($dados));
        $this->view->grupos = $grupos;
        
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { 
        
    }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() { 
        
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() { 
        
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        
        // Chama o método relatório da classe pai
        parent::relatorio();
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
    	
    	// Instancia os modelos de dados
        $webGrupo = new WebGrupoModel();
        
        // Verifica o tipo de agrupamento para fazer a consulta
        // Se for do tipo Grupo entra na primeira condição, se 
        // for do tipo Usuário entra na segunda opção
        $this->view->agrupamento = $params["agrupamento"]; 
        if($params["agrupamento"] == "G") {
            $query = $webGrupo->queryBuscaGruposUsuariosWebCliServ($params);
        } else {
            $query = $webGrupo->queryBuscaUsuariosGruposWebCliServ($params);
        }
        
        // Retorna para a view os dados da consulta
        $this->view->resConsulta = $webGrupo->fetchAll($query);
    }
    
	/**
	 * 
	 * Busca informações dos usuários
	 */
    public function retornaUsuarioXAction(){
		
    	// Verifica se arrequisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {
			 
			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			// Instancia a classe model 
			$usuarios = new UsuarioModel();
            
			// Monta a condição
			$where = "UPPER(NO_USUARIO) LIKE UPPER('{$params["term"]}%')";
			
			// Pega os usuários			
			$retorno = $usuarios->fetchAll($where);
			
			if(count($retorno) > 0) {
				
           		foreach($retorno as $linha) {
                	
           			// Se o evento vem do campo do nome do motorista
                	$retornoJSON[] = array("value"      => $linha->NO_USUARIO,
                	                       "label"      => $linha->CD_USUARIO . " - " . $linha->NO_USUARIO,
                	                       "cd_usuario" => $linha->CD_USUARIO);
                    
           			
           		}
           }
			
			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			// Limpa os objetos da memoria
			unset($motoristas);
		}
	}
    
}