<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de GrupoTransacaoAcao.
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabGrupoTransacaoAcaoController extends Marca_Controller_Abstract_Operacao {
    
    /**
     * 
     * Construtor da classe
     * 
     * @return void
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        
        // Chama o construtor pai
        parent::__construct($request, $response, $invokeArgs);
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura e grava o código da transação da janela pai
        if (isset($params['pai_cd_grupo'])) {
            $sessao->pai_cd_grupo = $params['pai_cd_grupo'];
        }
        
        Zend_Registry::set("pai_cd_grupo", $sessao->pai_cd_grupo);
        
        // Joga para a view o código da transação pai
        $this->view->pai_cd_grupo = $sessao->pai_cd_grupo;

    }
    
    /**
     * Método inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {
        
        // Carrega o método de inicialização da classe pai
        parent::init();
	        
        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");
        
        // Carrega o modelo de dados
        Zend_Loader::loadClass('WebGrupoModel');
        Zend_Loader::loadClass('WebGrupoUsuarioModel');
        Zend_Loader::loadClass('WebGrupoTransacaoModel');
        Zend_Loader::loadClass('WebAcaoModel');
        Zend_Loader::loadClass('WebMenuSistemaModel');
        Zend_Loader::loadClass('WebTransacaoModel');
        Zend_Loader::loadClass('WebTransacaoAcaoModel');
        
    }


    /**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a classe de sistemas web
        $grupoTransacoes = new WebGrupoTransacaoModel();
        
        // Captura o código da transação pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WG.NO_GRUPO", "ASC");
          
        // Define os filtros para a cosulta
        $where = $grupoTransacoes->addWhere(array("WG.CD_GRUPO = ?" => $pai_cd_grupo))
                                     ->getWhere();
        

        $dt_acesso_ini = new Zend_Db_Expr("TO_CHAR(WGT.DT_ACESSO_INI, 'DD/MM/YYYY') as DT_ACESSO_INI");
        $dt_acesso_fim = new Zend_Db_Expr("TO_CHAR(WGT.DT_ACESSO_FIM, 'DD/MM/YYYY') as DT_ACESSO_FIM");
                                     
        // Busca todas as transações ligadas as ações para a aba tab-transacao-acao
        $select = $grupoTransacoes->select()
                                  ->setIntegrityCheck(false)
                                  ->from(array("WG"  => "WEB_GRUPO"), array("WG.CD_GRUPO", 
                                                                            "WG.NO_GRUPO",
                                                                            "WS.CD_SISTEMA",
                                                                            "WS.NO_SISTEMA",
                                                                            "WMS.CD_MENU", 
                                                                            "WMS.NO_MENU", 
                                                                            "WGT.CD_TRANSACAO", 
                                                                            "WT.NO_TRANSACAO",
                                                                            "WGT.FL_PERMISSAO",
                                  										    $dt_acesso_ini,
                                  											$dt_acesso_fim))
                                  ->join(array("WGT" => "WEB_GRUPO_TRANSACAO"), "WG.CD_GRUPO          = WGT.CD_GRUPO",    array())
                                  ->join(array("WT"  => "WEB_TRANSACAO"),       "WGT.CD_TRANSACAO     = WT.CD_TRANSACAO", array())
                                  ->join(array("WMS" => "WEB_MENU_SISTEMA"),    "WT.CD_MENU           = WMS.CD_MENU",     array())
                                  ->join(array("WS"  => "WEB_SISTEMA"),         "WMS.CD_SISTEMA       = WS.CD_SISTEMA",   array())
                                  ->where($where)
                                  ->order($order);
                                 
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $grupoTransacoes->fetchAll($select);
        
        // Seta o número da página corrente
        $pagina = $this->_getParam('pagina', 1);

        // Recebe a instância do paginator por singleton
        $paginator = Zend_Paginator::factory($resConsulta);
        
        // Define a página corrente
        $paginator->setCurrentPageNumber($pagina);
        
        // Define o total de linhas por página
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
        
        // Joga para a view a paginação
        $this->view->paginator = $paginator;
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){

        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classe de sistemas web
        $grupoTransacoes = new WebGrupoTransacaoModel();
        
        // Captura o código da transação pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WS.NO_SISTEMA", "ASC");
          
        // Define os filtros para a cosulta
        /*$where = $grupoTransacoes->addWhere(array("WG.CD_GRUPO = ?" => $pai_cd_grupo))
                                 ->getWhere();*/
        $where = "1=1";
        
        // Busca todos os sistemas que estão ligados ao grupo
        $select = $grupoTransacoes->select()
                                  ->setIntegrityCheck(false)
                                  ->distinct()
                                  ->from(array("WTA" => "WEB_TRANSACAO_ACAO"), array("WS.CD_SISTEMA", 
                                                                                     "WS.NO_SISTEMA"))
                                  ->joinRight(array("WT"  => "WEB_TRANSACAO"),       "WTA.CD_TRANSACAO     = WT.CD_TRANSACAO", array())
                                  ->joinRight(array("WMS" => "WEB_MENU_SISTEMA"),    "WT.CD_MENU           = WMS.CD_MENU",     array())
                                  ->joinRight(array("WS"  => "WEB_SISTEMA"),         "WMS.CD_SISTEMA       = WS.CD_SISTEMA",   array())
                                  ->where($where)
                                  ->order($order);
        
        // Joga em um array os sistemas
        $resConsultaSistemas = $grupoTransacoes->fetchAll($select);
        $arraySistemas = array();
        foreach($resConsultaSistemas as $sistema) {
            $arraySistemas[$sistema->CD_SISTEMA] = $sistema->NO_SISTEMA;
        }
        
        // Joga para a view as ações
        $this->view->sistemas = $arraySistemas; 
    
    }
    
    
    /**
     * Salva uma ação a transação
     *
     * @return void
     */
    public function salvarAction() {
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Associa as variáveis do banco
        $db = Zend_Registry::get('db');
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Instancia o modelo
        $grupoTransacoes = new WebGrupoTransacaoModel();
        
        // Captura o código da transação pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Se retornar um array é por que está sendo 
        // ligado as ações a transação, caso contrário 
        // é por que está sendo editado uma ação.
        if(is_array($params["cd_transacao"])) {
            
            // Se o registro não existir insere, caso contrário edita
            if($params["operacao"] == "novo") {
                
                // Conta o número total de transacoes retornadas
                $totalTrans = count($params["cd_transacao"]);
                
                // Percorre todas as ações
                for($i=0; $i < $totalTrans; $i++) {
                    
                    if($params["cd_transacao"][$i] != "") {
                        
                        // Monta os dados para salvar
                        $dados = array("CD_GRUPO"      => $pai_cd_grupo,
                                       "CD_TRANSACAO"  => $params["cd_transacao"][$i],
                                       "FL_PERMISSAO"  => $params["fl_permissao"][$i],
                                       "DT_ACESSO_INI" => $params["dt_acesso_ini"][$i],
                                       "DT_ACESSO_FIM" => $params["dt_acesso_fim"][$i]);
                        
                        // Valida os dados obrigatórios
                        if($grupoTransacoes->isValid($dados)) {
                            // Insere a nova ação para o grupo
                            $grupoTransacoes->insert($dados);
                        }
                        
                    }
                   
                }
                
                // Redireciona para ação novo
                $this->_forward('index');
            }
            
        } else {
            
            // Monta os dados para salvar
            $dados = array("CD_GRUPO"      => $pai_cd_grupo,
                           "CD_TRANSACAO"  => $params["cd_transacao"],
                           "FL_PERMISSAO"  => $params["fl_permissao"],
                           "DT_ACESSO_INI" => $params["dt_acesso_ini"],
            			   "DT_ACESSO_FIM" => $params["dt_acesso_fim"]);
            
            // Valida os dados obrigatórios
            if($grupoTransacoes->isValid($dados)) {
                
                // Se o registro não existir insere, caso contrário edita
                if($params["operacao"] != "novo") {
                    
                    // limpa os dados que não serão editados
                    unset($dados["CD_GRUPO"]);
                    unset($dados["CD_TRANSACAO"]);
                    
                    // Monta a condição do where
                    $where = " CD_GRUPO      =  " . $pai_cd_grupo . " AND " .
                             " CD_TRANSACAO  =  " . $params["cd_transacao"];
                    
                    
                   	$linhaGrupoTransacaoAcao = $grupoTransacoes->fetchAll($where);					
					$dataFinalEmbranco       = 0;
			
					foreach ($linhaGrupoTransacaoAcao as $grupoTransacaoAcao) {
						
						// Conta a quantidade de datas finais embranco(NULL)
						if (trim($grupoTransacaoAcao->DT_ACESSO_FIM) == "") {
							$dataFinalEmbranco++;
						}						
					}
					
					//	Verifica se tem mais de uma data final nula para o mesmo usuario e grupo
                	if ($dataFinalEmbranco > 0 && trim($params['dt_acesso_fim']) == "") {
					
						$mensagemSistema = Zend_Registry::get("mensagemSistema");
						$msg             = array("msg"    => array("O registro não foi salvo, somente uma data final do Período de acesso pode ser nula para o mesmo GRUPO/TRANSAÇÃO."),
		                                  		 "titulo" => "ATENÇÃO",
		                						 "tipo"   => 4);
						$mensagemSistema->send(serialize($msg));
												
					} else {
						
                  		// Monta a condição do where
                    	$where = " CD_GRUPO      =  " . $pai_cd_grupo            . " AND " .
                             	 " CD_TRANSACAO  =  " . $params["cd_transacao"]  . " AND " .
                    		     " TO_CHAR(DT_ACESSO_INI, 'DD/MM/YYYY') = '" . $params["dt_acesso_ini"] . "'";
                    
                  		// Atualiza os dados
                    	$grupoTransacoes->update($dados, $where);
                  	
                  	}
                    
                    // Redefine o código da ação passada para poder 
                    // repopular corretamente a view selecionar
                    //$this->_request->setParam("cd_acao", $dados["CD_ACAO"]);
                    
                    // Redireciona para ação de selecionar
                    $this->_forward('selecionar');
                    
                }                
            }            
        }        
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction(){
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $grupoTransacoes = new WebGrupoTransacaoModel();
        
        // Captura o código da transação pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Valida os dados obrigatórios
        if($pai_cd_grupo != "" && $params['cd_transacao'] != "") {
            // Monta a condição do where
            $where = "CD_GRUPO     = " . $pai_cd_grupo           . " AND " . 
                     "CD_TRANSACAO = " . $params["cd_transacao"] . " AND " . 
            		 "TO_CHAR(DT_ACESSO_INI, 'DD/MM/YYYY') = '" . $params["dt_acesso_ini"] . "'";
            
            // Atualiza os dados
            $delete = $grupoTransacoes->delete($where);

            // Verifica se o registro foi excluído
            if($delete) {
                // Limpa os dados da requisição
                $params = $this->_helper->LimpaParametrosRequisicao->limpar();
                
                // Redireciona para o index
                $this->_forward("index", null, null, $params);
                
            } else {
                // Se não conseguir excluir, retorna pra seleção do registro
                $this->_forward("selecionar");
            }
        }
        
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $grupoTransacoes = new WebGrupoTransacaoModel();
        $transacaoAcoes  = new WebTransacaoAcaoModel();
        
        // Captura o código da transação pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Define os filtros para a cosulta
        $where = $grupoTransacoes->addWhere(array("WGT.CD_GRUPO     = ?" => $pai_cd_grupo))
                                 ->addWhere(array("WGT.CD_TRANSACAO = ?" => $params['cd_transacao']))
                                 ->addWhere(array("WGT.FL_PERMISSAO = ?" => $params['fl_permissao']))
                                 ->addWhere(array("TO_CHAR(WGT.DT_ACESSO_INI, 'DD/MM/YYYY') = ?"   => $params['dt_acesso_ini']))
                                 ->getWhere();
        
        // Busca os dados para serem mostrados na view
        $select = $grupoTransacoes->select()
                                  ->setIntegrityCheck(false)
                                  ->from(array("WG"  => "WEB_GRUPO"), array("WG.CD_GRUPO", 
                                                                            "WG.NO_GRUPO",
                                                                            "WS.CD_SISTEMA",
                                                                            "WS.NO_SISTEMA",
                                                                            "WMS.CD_MENU", 
                                                                            "WMS.NO_MENU", 
                                                                            "WGT.CD_TRANSACAO", 
                                                                            "WT.NO_TRANSACAO",
                                                                            "WGT.FL_PERMISSAO",
                                                                            "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(WGT.DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                         										  		    "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(WGT.DT_ACESSO_FIM, 'DD/MM/YYYY')")))
                                  ->join(array("WGT" => "WEB_GRUPO_TRANSACAO"), "WG.CD_GRUPO = WGT.CD_GRUPO", array())
                                  ->join(array("WT"  => "WEB_TRANSACAO"),       "WGT.CD_TRANSACAO = WT.CD_TRANSACAO", array())
                                  ->join(array("WMS" => "WEB_MENU_SISTEMA"),    "WT.CD_MENU = WMS.CD_MENU", array())
                                  ->join(array("WS"  => "WEB_SISTEMA"),         "WMS.CD_SISTEMA = WS.CD_SISTEMA", array())
                                  ->where($where);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $linhaConsulta = $grupoTransacoes->fetchRow($select);
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($linhaConsulta->toArray(), "lower");
        
    }


    /**
     * Pesquisa os aditivos
     *
     * @return void
     */
    public function pesquisarAction() { }
    
    
    /**
     * Gera o relatório de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() { }
    
    
    /**
     * Verifica se existe menus para o sistema passado
     *
     * @return JSON
     */
    public function retornaMenusXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $menuSistemas = new WebMenuSistemaModel();
            
            // Ordenação da consulta
            $order = $this->_helper->OrdenaConsulta->ordenar("NO_MENU", "ASC");
            
            // Define os filtros para a cosulta
            $where = $menuSistemas->addWhere(array("CD_SISTEMA = ?" => $params['cd_sistema']))
                                  ->getWhere();
                                  
            // Busca os menus
            $select = $menuSistemas->select()
                                   ->from(array("WEB_MENU_SISTEMA"), array("CD_MENU", "NO_MENU"))
                                   ->where($where)
                                   ->order($order);
            
            // Captura o registro
            $linhas = $menuSistemas->fetchAll($where);
                        
            // Converte a linha para array
            if($linhas[0]->CD_MENU != "") {
                $linhas = $linhas->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linhas), true);

            // Limpa os objetos da memoria
            unset($menuSistemas);
        }

    }
    
    
    /**
     * Verifica se existe transações para o menu passado
     *
     * @return JSON
     */
    public function retornaTransacoesXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $transacoes = new WebTransacaoModel();
            
            // Ordenação da consulta
            $order = $this->_helper->OrdenaConsulta->ordenar("NO_TRANSACAO", "ASC");
            
            // Define os filtros para a cosulta
            $where = $transacoes->addWhere(array("CD_MENU = ?" => $params['cd_menu']))
                                ->getWhere();
                                  
            // Busca os menus
            $select = $transacoes->select()
                                 ->from(array("WEB_TRANSACAO"), array("CD_TRANSACAO", "NO_TRANSACAO"))
                                 ->where($where)
                                 ->order($order);
            
            // Captura o registro
            $linhas = $transacoes->fetchAll($where);
                        
            // Converte a linha para array
            if(count($linhas) > 0 && $linhas[0]->CD_TRANSACAO != "") {
                $linhas = $linhas->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linhas), true);

            // Limpa os objetos da memoria
            unset($transacoes);
        }

    }
    
    
    /**
     * Busca os dados do sistema, menuSistema e transação
     *
     * @return JSON
     */
    public function retornaDadosXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Captura o código da transação pai
            $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
            
            // Cria o array que retornará os dados da busca
            $arrayRetorno = array();
            
            // Instancia o modelo
            $transacoes      = new WebTransacaoModel();
            $transacaoAcoes  = new WebTransacaoAcaoModel();
            $grupoTransacoes = new WebGrupoTransacaoModel();
            
            // Define os filtros para a cosulta
            $where = $transacoes->addWhere(array("WT.CD_TRANSACAO = ?" => $params['cd_transacao']))
                                ->getWhere();

            // Faz a busca pelos dados 
            $select = $transacoes->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("WT" => "WEB_TRANSACAO"), array("WT.CD_TRANSACAO", 
                                                                              "WMS.CD_MENU",
                                                                              "WMS.CD_SISTEMA"))
                                 ->join(array("WMS" => "WEB_MENU_SISTEMA"), "WT.CD_MENU = WMS.CD_MENU", array())
                                 ->where($where);
            
            // Captura o registro
            $linha = $transacoes->fetchRow($select);
            
            // Recupera os dados das tabelas
            $arrayRetorno[0][0] = $linha->CD_SISTEMA;
            $arrayRetorno[1][0] = $linha->CD_MENU;
            $arrayRetorno[2][0] = $linha->CD_TRANSACAO;

            // Retorna todos os menus que dependem do sistema
            $menuSistemas = new WebMenuSistemaModel();
            $arrayMenus   = $menuSistemas->getMenus($linha->CD_SISTEMA);
            foreach($arrayMenus as $indice => $valor) {
                $arrayRetorno[1][1][] = array("CD_MENU" => $indice, "NO_MENU" => $valor);
            }
            
            
            // Retorna todos as transações que dependem do menu
            $arrayTransacoes = $transacoes->getTransacoes($linha->CD_MENU);
            foreach($arrayTransacoes as $indice => $valor) {
                $arrayRetorno[2][1][] = array("CD_TRANSACAO" => $indice, "NO_TRANSACAO" => $valor);
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($arrayRetorno), true);

            // Limpa os objetos da memoria
            unset($transacoes);
            unset($menuSistemas);
        }

    }
    
}