<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de TransacaoAcao.
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabTransacaoAcaoController extends Marca_Controller_Abstract_Operacao {
    
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
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura e grava o c�digo da transa��o da janela pai
        if (isset($params['pai_cd_transacao'])) {
            $sessao->pai_cd_transacao = $params['pai_cd_transacao'];
        }
        
        Zend_Registry::set("pai_cd_transacao", $sessao->pai_cd_transacao);
        
        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_transacao = $sessao->pai_cd_transacao;

    }
    
    /**
     * M�todo inicial para carregamento de classes do controlador
     *
     * @return void
     */
    public function init() {
        
        // Carrega o m�todo de inicializa��o da classe pai
        parent::init();
        
        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-aba");
        
        // Carrega o modelo de dados
        Zend_Loader::loadClass('WebTransacaoAcaoModel');
        Zend_Loader::loadClass('WebAcaoModel');
           
    }


    /**
     * M�todo principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a classe de sistemas web
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WA.NO_ACAO", "ASC");
          
        // Define os filtros para a cosulta
        $where = $transacoesAcao->addWhere(array("WT.CD_TRANSACAO = ?" => $pai_cd_transacao))
                                ->getWhere();
        
        // Busca todas as transa��es ligadas as a��es para a aba tab-transacao-acao
        $select = $transacoesAcao->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("WT"  => "WEB_TRANSACAO"), array("WT.*", 
                                                                               "WTA.ORD_ACAO", 
                                                                               "WA.CD_ACAO", 
                                                                               "WA.NO_ACAO", 
                                                                               "WA.DS_ACAO",
                                                                               "WA.FL_MENU"))
                                 ->join(array("WTA" => "WEB_TRANSACAO_ACAO"), "WT.CD_TRANSACAO = WTA.CD_TRANSACAO", array())
                                 ->join(array("WA"  => "WEB_ACAO"), "WTA.CD_ACAO = WA.CD_ACAO", array())
                                 ->where($where)
                                 ->order($order);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $transacoesAcao->fetchAll($select);
        
        // Seta o n�mero da p�gina corrente
        $pagina = $this->_getParam('pagina', 1);

        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($resConsulta);
        
        // Define a p�gina corrente
        $paginator->setCurrentPageNumber($pagina);
        
        // Define o total de linhas por p�gina
        $paginator->setItemCountPerPage($sessao->perfil->QT_LINHAS);
        
        // Joga para a view a pagina��o
        $this->view->paginator = $paginator;
    }


    /**
     * Gera um documento para cadastro de um novo aditivo
     *
     * @return void
     */
    public function novoAction(){

        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classe de sistemas web
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Define os filtros para a cosulta para a ordem
        $where = $transacoesAcao->addWhere(array("WTA.CD_TRANSACAO = ?" => $pai_cd_transacao))
                                ->getWhere();
        
        // Busca o �ltima ordem e incrementa um para 
        // deixar sugerido ao cadastrar uma nova transa��o/a��o
        $select = $db->select()
                     ->from(array("WT"  => "WEB_TRANSACAO"), array("ORD_ACAO" => "MAX(WTA.ORD_ACAO)"))
                     ->join(array("WTA" => "WEB_TRANSACAO_ACAO"), "WTA.CD_TRANSACAO = WT.CD_TRANSACAO", array())
                     ->where($where);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $linhaConsulta = $db->fetchRow($select);
        
        // Joga na view o �ltimo n�mero de ordem incrementando de 1
        $this->view->ultimaOrdemAcao = $linhaConsulta->ORD_ACAO + 1; 
        
        // Busca todas as a��es que ainda n�o est�o ligadas a transa��o selecionada
        $stmt = $db->query("SELECT WA.* 
                            FROM WEB_ACAO WA 
                            WHERE NOT EXISTS (
                                SELECT WTA.CD_ACAO 
                                FROM WEB_TRANSACAO_ACAO WTA 
                                WHERE WTA.CD_TRANSACAO = {$pai_cd_transacao} 
                                  AND WA.CD_ACAO = WTA.CD_ACAO)
                                  AND WA.FL_TIPO_ACAO IN ('C', (SELECT WT.FL_TIPO_TRANSACAO 
                                                                  FROM WEB_TRANSACAO WT
                                                                 WHERE WT.CD_TRANSACAO = {$pai_cd_transacao})) 
                            ORDER BY WA.NO_ACAO");
        
        // Joga em um array as a��es
        $resConsultaAcoes = $stmt->fetchAll();
        $arrayAcoes = array();
        foreach($resConsultaAcoes as $acao) {
            $arrayAcoes[$acao->CD_ACAO] = $acao->NO_ACAO;
        }
        
        // Joga para a view as a��es
        $this->view->acoes = $arrayAcoes; 
    
    }
    
    
    /**
     * Salva uma a��o a transa��o
     *
     * @return void
     */
    public function salvarAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Associa as vari�veis do banco
        $db = Zend_Registry::get('db');
        
        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Instancia o modelo
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
                
        // Se retornar um array � por que est� sendo 
        // ligado as a��es a transa��o, caso contr�rio 
        // � por que est� sendo editado uma a��o.
        if(is_array($params["cd_acao"])) {
            
            // Se o registro n�o existir insere, caso contr�rio edita
            if($params["operacao"] == "novo") {
                
                // Conta o n�mero total de a��es retornadas
                $totalAcoes = count($params["cd_acao"]);
                
                // Percorre todas as a��es
                for($i=0; $i < $totalAcoes; $i++) {
                    
                    // Verifica se o c�digo � v�lido
                    if($params["cd_acao"][$i] != "") {
                        
                        if($params["ord_acao"][$i] == "") {
                            $params["ord_acao"][$i] = "0";
                        }
                        
                        // Monta os dados para salvar
                        $dados = array("CD_TRANSACAO" => $pai_cd_transacao,
                                       "CD_ACAO"      => $params["cd_acao"][$i],
                                       "ORD_ACAO"     => $params["ord_acao"][$i]);
                        
                        // Valida os dados obrigat�rios
                        if($transacoesAcao->isValid($dados)) {
                            // Insere a nova a��o
                            $transacoesAcao->insert($dados);
                            
                        }
                    }
                }
                
                // Redireciona para a��o novo
                $this->_forward('index');
            }
            
        } else {
            
            // Se n�o for passado valor, coloca a ordem como zero
            if($params["ord_acao"] == "") {
                $params["ord_acao"] = "0";
            }
            
            // Monta os dados para salvar
            $dados = array("CD_TRANSACAO" => $pai_cd_transacao,
                           "CD_ACAO"      => $params["cd_acao"],
                           "ORD_ACAO"     => $params["ord_acao"]);
            
            // Valida os dados obrigat�rios
            if($transacoesAcao->isValid($dados)) {
                
                // Se o registro n�o existir insere, caso contr�rio edita
                if($params["operacao"] != "novo") {
                    
                    // Monta a condi��o do where
                    $where = "CD_TRANSACAO = " . $pai_cd_transacao . " AND " . 
                             "CD_ACAO      = " . $params["cd_acao_origem"];
                    
                    // Se for alterada a a��o exclui ela da tabela e 
                    // insere a nova
                    if($params["cd_acao"] != $params["cd_acao_origem"]) {
                        
                        // Atualiza os dados
                        $delete = $transacoesAcao->delete($where);
                        
                        // Verifica se o registro foi exclu�do
                        if($delete) {
                            // Insere a nova a��o
                            $transacoesAcao->insert($dados);
                        }
                        
                        $this->_request->setParam("cd_acao", $params["__cd_acao"]);
                        
                        // Recupera os parametros da requisi��o ap�s a altera��o de campo
                        $params = $this->_request->getParams();
                        
                    } else {
                    
                        // limpa os dados que n�o ser�o editados
                        unset($dados["CD_TRANSACAO"]);
                        unset($dados["CD_ACAO"]);
                        
                        // Atualiza os dados
                        $transacoesAcao->update($dados, $where);
                        
                    }
                    
                    // Redireciona para a��o de selecionar
                    $this->_forward('selecionar', null, null, $params);
                    
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
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Valida os dados obrigat�rios
        if($pai_cd_transacao != "" && $params['cd_acao'] != "") {
            // Monta a condi��o do where
            $where = "CD_TRANSACAO = " . $pai_cd_transacao . " AND " . 
                     "CD_ACAO      = " . $params["cd_acao"];
            
            // Atualiza os dados
            $delete = $transacoesAcao->delete($where);
            
            // Verifica se o registro foi exclu�do
            if($delete) {
                // Limpa os dados da requisi��o
                $params = $this->_helper->LimpaParametrosRequisicao->limpar();
                
                // Redireciona para o index
                $this->_forward("index", null, null, $params);
                
            } else {
                // Se n�o conseguir excluir, retorna pra sele��o do registro
                $this->_forward("selecionar");
            }
        } else {
            // Se n�o conseguir excluir, retorna pra sele��o do registro
            $this->_forward("selecionar");
        }
        
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classe de sistemas web
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Busca todas as a��es que ainda n�o est�o ligadas a transa��o selecionada
        // e que n�o seja a a��o selecionada
        $stmt = $db->query("SELECT WA.* 
                            FROM WEB_ACAO WA 
                            WHERE NOT EXISTS ( 
                                SELECT WTA.CD_ACAO 
                                FROM WEB_TRANSACAO_ACAO WTA 
                                WHERE WTA.CD_TRANSACAO = " . $pai_cd_transacao . 
                              " AND WA.CD_ACAO = WTA.CD_ACAO 
                                AND WA.CD_ACAO <> " . $params['cd_acao'] . ") 
                            ORDER BY WA.NO_ACAO");
        
        // Joga em um array as a��es
        $resConsultaAcoes = $stmt->fetchAll();
        $arrayAcoes = array();
        foreach($resConsultaAcoes as $acao) {
            $arrayAcoes[$acao->CD_ACAO] = $acao->NO_ACAO;
        }
        
        // Joga para a view as a��es
        $this->view->acoes = $arrayAcoes; 
        
        // Define os filtros para a cosulta
        $where = $transacoesAcao->addWhere(array("WTA.CD_TRANSACAO = ?" => $pai_cd_transacao))
                                ->addWhere(array("WTA.CD_ACAO = ?" => $params['cd_acao']))
                                ->getWhere();
        
        // Busca os dados para serem mostrados na view
        $select = $transacoesAcao->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("WT"  => "WEB_TRANSACAO"), array("WT.CD_TRANSACAO", 
                                                                               "WT.NO_TRANSACAO",
                                                                               "WTA.ORD_ACAO",
                                                                               "WMS.NO_MENU", 
                                                                               "WS.NO_SISTEMA",
                                                                               "WA.CD_ACAO",
                                                                               "WA.NO_ACAO",
                                                                               "WA.LNK_IMG_ACAO",
                                                                               "WA.DS_ACAO"))
                                 ->join(array("WMS" => "WEB_MENU_SISTEMA"), "WT.CD_MENU = WMS.CD_MENU", array())
                                 ->join(array("WS"  => "WEB_SISTEMA"), "WMS.CD_SISTEMA = WS.CD_SISTEMA", array())
                                 ->join(array("WTA"  => "WEB_TRANSACAO_ACAO"), "WTA.CD_TRANSACAO = WT.CD_TRANSACAO", array())
                                 ->join(array("WA"  => "WEB_ACAO"), "WTA.CD_ACAO = WA.CD_ACAO", array())
                                 ->where($where);
        
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $linhaConsulta = $transacoesAcao->fetchRow($select);
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($linhaConsulta->toArray(), "lower");
        
    }


    /**
     * Pesquisa os aditivos
     *
     * @return void
     */
    public function pesquisarAction() { }
    
    
    /**
     * Gera o relat�rio de aditivos a partir de uma listagem
     *
     * @return void
     */
    public function relatorioAction() { }
    
    
    /**
     * Verifica a ordem de exibi��o da transa��o ligada a a��o
     *
     * @return JSON
     */
    public function verificaOrdemExibicaoXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $transacoesAcao = new WebTransacaoAcaoModel();
            
            // Se o usu�rio n�o passar nenhum valor na ordem
            // for�a a consulta n�o encontrar registro
            if($params['ord_acao'] == "" || $params['ord_acao'] == "0") {
                $params['ord_acao'] = "-1";
            }
            
            // Define os filtros para a cosulta
            $where = $transacoesAcao->addWhere(array("CD_TRANSACAO = ?" => $params['cd_transacao']))
                                    ->addWhere(array("ORD_ACAO = ?"     => $params['ord_acao']))
                                    ->getWhere();

            // Captura o registro
            $linha = $transacoesAcao->fetchRow($where);
            
            // Converte a linha para array
            if($linha->CD_TRANSACAO != "") {
                $linha = $linha->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($transacoesAcao);
        }

    }
    
    
    /**
     * Retorna os dados das a��es
     *
     * @return JSON
     */
    public function retornaAcoesXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $acoes = new WebAcaoModel();
            
            // Define os filtros para a cosulta
            $where = $acoes->addWhere(array("CD_ACAO = ?" => $params['cd_acao']))
                                    ->getWhere();

            // Captura o registro
            $linha = $acoes->fetchRow($where);
            
            // Converte a linha para array
            if($linha->CD_ACAO != "") {
                $linha = $linha->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($acoes);
        }

    }
    
}