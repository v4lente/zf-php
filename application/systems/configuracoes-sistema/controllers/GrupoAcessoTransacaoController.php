<?php

/**
 *
 * Classe responsável por listar os acessos as transações do Sistema de determinado usuário.
 *
 * @author     Vinicius Viana Loss
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2014 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_GrupoAcessoTransacaoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Carrega os modelos de dados
        Zend_Loader::loadClass("UsuarioModel");
        Zend_Loader::loadClass("LogSessaoModel");
        Zend_Loader::loadClass("LogSessaoTransacaoModel");
        Zend_Loader::loadClass("WebSistemaModel");
        Zend_Loader::loadClass("WebMenuSistemaModel");
        Zend_Loader::loadClass("WebTransacaoModel");
        Zend_Loader::loadClass("WebGrupoModel");
    }

    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() {
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace("portoweb");
        
        // Instancia a classe que gerencia as datas
        $data = new Zend_Date();
        
        // Monta os dados da visão
        $dados = array();
        
        // Retorna a data
        $dados["dthr_acesso"] = $data->toString("dd/MM/YYYY");
        
        // Retorna o usuário logado
        $dados["usuario"]    = $sessao->perfil->NO_EMPR . " - " . $sessao->perfil->CD_USUARIO;
        $dados["cd_usuario"] = $sessao->perfil->CD_USUARIO;
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($dados);
    }

    /**
	 * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() {}

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() {}

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() {}

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() {}

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() {}

    /* Gera o relatório dos acessos
    *
    * @return void
    */
    public function relatorioAction() {

    	// Captura os parametros passados por GET
    	$params                = $this->getRequest()->getParams();

    	// Captura a sessão
    	$sessao                = new Zend_Session_Namespace('portoweb');

		// echo "<pre>"; print_r($params);die;
		
    	//Definição de parametros para o relatório
    	$params['usuario']        = "Usuário: " . $sessao->perfil->CD_USUARIO;

   		$params['where_sistema'] = "AND 1 = 1";
		if ($params['cd_sistema'] != "") {
    		$params['where_sistema'] = "AND (MENU.CD_SISTEMA = " . $params['cd_sistema'] . ")";
    	}

   		$params['where_menu'] = "AND 1 = 1";
		if ($params['no_menu'] != "") {
    		$params['where_menu'] = "AND (TRANS.CD_MENU = " . $params['no_menu'] . ")";
    	}

   		$params['where_transacao'] = "AND 1 = 1";
		if ($params['no_transacao'] != "") {
    		$params['where_transacao'] = "AND (TRANS.CD_TRANSACAO = " . $params['no_transacao'] . ")";
    	}

   		$params['where_grupo'] = "AND 1 = 1";
		if ($params['cd_grupo'] != "") {
    		$params['where_grupo'] = "AND (GP.CD_GRUPO = " . $params['cd_grupo'] . ")";
    	}

		//Tipo de relatório Transação
		if($params['tp_relatorio'] == 'T'){

			// $params['cd_sistema'];
			$params['titulo']  		  = 'Grupo de Acesso por Transação';
			
// echo "<pre>"; print_r($params);die;
			// Helper para gerar o relatório no jasper. Nome do relatório, Parâmetros da query, Formato de Impressão
			$this->_helper->GeraRelatorioJasper->gerar("grupo_acesso_transacao_t", $params, $params['_rel_metodo']);

		} else {

			$params['titulo']  		  = 'Transações por Grupo de Acesso';

			// Helper para gerar o relatório no jasper. Nome do relatório, Parâmetros da query, Formato de Impressão
			$this->_helper->GeraRelatorioJasper->gerar("grupo_acesso_transacao_g", $params, $params['_rel_metodo']);	
		}
    }
    
	/**
	 * 
	 * Busca informações do motorista via AJAX
	 */
    public function autoCompleteXAction(){
		
    	// Verifica se arrequisição foi passada por Ajax
		if($this->_request->isXmlHttpRequest()) {

			// echo "<pre>"; print_r($params);

			// Captura os parametros passados por GET
			$params = $this->getRequest()->getParams();

			if($params["code"] == 1){

				$sistemas = new WebSistemaModel();

				// Monta o where
				$where = $sistemas->addWhere(array("UPPER(NO_SISTEMA) LIKE UPPER('%" . $params["term"] . "%')"))
									->getWhere();

				// Monta o select
				$select = $sistemas->select()
								   ->setIntegrityCheck(false)
								   ->from(array("WEB_SISTEMA"), array("CD_SISTEMA", "NO_SISTEMA"))
									->where($where)
									->order("NO_SISTEMA");

				// Realiza a consulta
				$resPesquisa = $sistemas->fetchAll($select);

				if(count($resPesquisa) > 0) {
					foreach($resPesquisa as $linha) {
						$retornoJSON[] = array("id"         => $linha->CD_SISTEMA,
											   "value"      => $linha->NO_SISTEMA ,
											   "nome"      => $linha->NO_SISTEMA
											);
					}
				}
				
			} elseif($params["code"] == 2){

				$grupo = new WebGrupoModel();

				// Monta o where
				$where = $grupo->addWhere(array("UPPER(CD_GRUPO) || UPPER(NO_GRUPO) LIKE UPPER('%" . $params["term"] . "%')"))
						       ->getWhere();

				// Monta o select
				$select = $grupo->select()
							   ->setIntegrityCheck(false)
							   ->from(array("WEB_GRUPO"), array("CD_GRUPO", "NO_GRUPO"))
							   ->where($where)
							   ->order("NO_GRUPO");

				// Realiza a consulta
				$resPesquisa = $grupo->fetchAll($select);

				if(count($resPesquisa) > 0){
					foreach($resPesquisa as $linha){
						$retornoJSON[] = array("id"     => $linha->CD_GRUPO,
											   "value"  => $linha->NO_GRUPO 
											);
					}
				}
			}

			// Retorna os dados por json
			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

			// Limpa os objetos da memoria
			unset($motoristas);
		}    	
	}

	/**
	* Retorna-Dados: método utilizado para retornar dados do db
	*
	* @return registro
	*/
	public function retornaDadosXAction() {

		// Captura a sessão
		$sessao = new Zend_Session_Namespace("portoweb");

		// Verifica se arrequisição foi passada por Ajax
		if ($this->_request->isXmlHttpRequest()) {

			try {

				// Captura os parametros passados por GET
				$params = $this->getRequest()->getParams();

				// Inicializa a variável de retorno
				$retorno["QUANTIDADE"] = "0";

				switch ($params["code"]) {

					// código se $params["code"] for 1
					case 1:

						$menu = new WebMenuSistemaModel();

						// Monta o where
						$where = $menu->addWhere(array("CD_SISTEMA = ? "=> $params["cd_sistema"]))
									  ->getWhere();

						// Monta o select
						$select = $menu->select()
									   ->setIntegrityCheck(false)
									   ->from(array("WEB_MENU_SISTEMA"), array("CD_MENU", "NO_MENU"))
									   ->where($where)
									   ->order("NO_MENU");
						// Realiza a consulta
						$resPesquisa = $menu->fetchAll($select);

						$retorno = $resPesquisa->toArray();

					break;

					// código se $params["code"] for 2
					case 2:

						$transacao = new WebTransacaoModel();

						// Monta o where
						$where = $transacao->addWhere(array("WT.FL_TIPO_TRANSACAO = 'M'"))
									  ->addWhere(array("WMS.CD_MENU = ? "=> $params["cd_menu"]))
									  ->addWhere(array("WMS.CD_SISTEMA = ? "=> $params["cd_sistema"]))
									  ->getWhere();

						// Monta o select
						$select = $transacao->select()
									   ->setIntegrityCheck(false)
									   ->from(array("WT" => "WEB_TRANSACAO"), array("WT.CD_TRANSACAO", "WT.NO_TRANSACAO"))
									   ->join(array("WMS" => "WEB_MENU_SISTEMA"), "WT.CD_MENU = WMS.CD_MENU", array())
									   ->where($where)
									   ->order("NO_TRANSACAO");

						// Realiza a consulta
						$resPesquisa = $transacao->fetchAll($select);

						$retorno = $resPesquisa->toArray();

					break;
				}

				// Retorna os dados por json
				$this->_helper->json(Marca_ConverteCharset::converter($retorno), true);

			} catch(Exception $e) {
				echo $e->getMessage(); die;
			}
		}
	}
}