<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de TransacaoAcao.
 *
 * @author     Márcio Souza Duarte
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
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura e grava o código da transação da janela pai
        if (isset($params['pai_cd_transacao'])) {
            $sessao->pai_cd_transacao = $params['pai_cd_transacao'];
        }
        
        Zend_Registry::set("pai_cd_transacao", $sessao->pai_cd_transacao);
        
        // Joga para a view o código da transação pai
        $this->view->pai_cd_transacao = $sessao->pai_cd_transacao;

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
        Zend_Loader::loadClass('WebTransacaoAcaoModel');
        Zend_Loader::loadClass('WebAcaoModel');
           
    }


    /**
     * Método principal da classe
     *
     * @return void
     */
    public function indexAction() {
        
        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();
        
        // Instancia a classe de sistemas web
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o código da transação pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WA.NO_ACAO", "ASC");
          
        // Define os filtros para a cosulta
        $where = $transacoesAcao->addWhere(array("WT.CD_TRANSACAO = ?" => $pai_cd_transacao))
                                ->getWhere();
        
        // Busca todas as transações ligadas as ações para a aba tab-transacao-acao
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
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $transacoesAcao->fetchAll($select);
        
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
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o código da transação pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Define os filtros para a cosulta para a ordem
        $where = $transacoesAcao->addWhere(array("WTA.CD_TRANSACAO = ?" => $pai_cd_transacao))
                                ->getWhere();
        
        // Busca o última ordem e incrementa um para 
        // deixar sugerido ao cadastrar uma nova transação/ação
        $select = $db->select()
                     ->from(array("WT"  => "WEB_TRANSACAO"), array("ORD_ACAO" => "MAX(WTA.ORD_ACAO)"))
                     ->join(array("WTA" => "WEB_TRANSACAO_ACAO"), "WTA.CD_TRANSACAO = WT.CD_TRANSACAO", array())
                     ->where($where);
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $linhaConsulta = $db->fetchRow($select);
        
        // Joga na view o último número de ordem incrementando de 1
        $this->view->ultimaOrdemAcao = $linhaConsulta->ORD_ACAO + 1; 
        
        // Busca todas as ações que ainda não estão ligadas a transação selecionada
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
        
        // Joga em um array as ações
        $resConsultaAcoes = $stmt->fetchAll();
        $arrayAcoes = array();
        foreach($resConsultaAcoes as $acao) {
            $arrayAcoes[$acao->CD_ACAO] = $acao->NO_ACAO;
        }
        
        // Joga para a view as ações
        $this->view->acoes = $arrayAcoes; 
    
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
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o código da transação pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
                
        // Se retornar um array é por que está sendo 
        // ligado as ações a transação, caso contrário 
        // é por que está sendo editado uma ação.
        if(is_array($params["cd_acao"])) {
            
            // Se o registro não existir insere, caso contrário edita
            if($params["operacao"] == "novo") {
                
                // Conta o número total de ações retornadas
                $totalAcoes = count($params["cd_acao"]);
                
                // Percorre todas as ações
                for($i=0; $i < $totalAcoes; $i++) {
                    
                    // Verifica se o código é válido
                    if($params["cd_acao"][$i] != "") {
                        
                        if($params["ord_acao"][$i] == "") {
                            $params["ord_acao"][$i] = "0";
                        }
                        
                        // Monta os dados para salvar
                        $dados = array("CD_TRANSACAO" => $pai_cd_transacao,
                                       "CD_ACAO"      => $params["cd_acao"][$i],
                                       "ORD_ACAO"     => $params["ord_acao"][$i]);
                        
                        // Valida os dados obrigatórios
                        if($transacoesAcao->isValid($dados)) {
                            // Insere a nova ação
                            $transacoesAcao->insert($dados);
                            
                        }
                    }
                }
                
                // Redireciona para ação novo
                $this->_forward('index');
            }
            
        } else {
            
            // Se não for passado valor, coloca a ordem como zero
            if($params["ord_acao"] == "") {
                $params["ord_acao"] = "0";
            }
            
            // Monta os dados para salvar
            $dados = array("CD_TRANSACAO" => $pai_cd_transacao,
                           "CD_ACAO"      => $params["cd_acao"],
                           "ORD_ACAO"     => $params["ord_acao"]);
            
            // Valida os dados obrigatórios
            if($transacoesAcao->isValid($dados)) {
                
                // Se o registro não existir insere, caso contrário edita
                if($params["operacao"] != "novo") {
                    
                    // Monta a condição do where
                    $where = "CD_TRANSACAO = " . $pai_cd_transacao . " AND " . 
                             "CD_ACAO      = " . $params["cd_acao_origem"];
                    
                    // Se for alterada a ação exclui ela da tabela e 
                    // insere a nova
                    if($params["cd_acao"] != $params["cd_acao_origem"]) {
                        
                        // Atualiza os dados
                        $delete = $transacoesAcao->delete($where);
                        
                        // Verifica se o registro foi excluído
                        if($delete) {
                            // Insere a nova ação
                            $transacoesAcao->insert($dados);
                        }
                        
                        $this->_request->setParam("cd_acao", $params["__cd_acao"]);
                        
                        // Recupera os parametros da requisição após a alteração de campo
                        $params = $this->_request->getParams();
                        
                    } else {
                    
                        // limpa os dados que não serão editados
                        unset($dados["CD_TRANSACAO"]);
                        unset($dados["CD_ACAO"]);
                        
                        // Atualiza os dados
                        $transacoesAcao->update($dados, $where);
                        
                    }
                    
                    // Redireciona para ação de selecionar
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
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o código da transação pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Valida os dados obrigatórios
        if($pai_cd_transacao != "" && $params['cd_acao'] != "") {
            // Monta a condição do where
            $where = "CD_TRANSACAO = " . $pai_cd_transacao . " AND " . 
                     "CD_ACAO      = " . $params["cd_acao"];
            
            // Atualiza os dados
            $delete = $transacoesAcao->delete($where);
            
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
        } else {
            // Se não conseguir excluir, retorna pra seleção do registro
            $this->_forward("selecionar");
        }
        
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Recupera a instância da base de dados
        $db = Zend_Registry::get("db");
        
        // Instancia a classe de sistemas web
        $transacoesAcao = new WebTransacaoAcaoModel();
        
        // Captura o código da transação pai
        $pai_cd_transacao = Zend_Registry::get("pai_cd_transacao");
        
        // Busca todas as ações que ainda não estão ligadas a transação selecionada
        // e que não seja a ação selecionada
        $stmt = $db->query("SELECT WA.* 
                            FROM WEB_ACAO WA 
                            WHERE NOT EXISTS ( 
                                SELECT WTA.CD_ACAO 
                                FROM WEB_TRANSACAO_ACAO WTA 
                                WHERE WTA.CD_TRANSACAO = " . $pai_cd_transacao . 
                              " AND WA.CD_ACAO = WTA.CD_ACAO 
                                AND WA.CD_ACAO <> " . $params['cd_acao'] . ") 
                            ORDER BY WA.NO_ACAO");
        
        // Joga em um array as ações
        $resConsultaAcoes = $stmt->fetchAll();
        $arrayAcoes = array();
        foreach($resConsultaAcoes as $acao) {
            $arrayAcoes[$acao->CD_ACAO] = $acao->NO_ACAO;
        }
        
        // Joga para a view as ações
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
        
        // Define os parâmetros para a consulta e retorna o resultado da pesquisa
        $linhaConsulta = $transacoesAcao->fetchRow($select);
        
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
     * Verifica a ordem de exibição da transação ligada a ação
     *
     * @return JSON
     */
    public function verificaOrdemExibicaoXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Instancia o modelo
            $transacoesAcao = new WebTransacaoAcaoModel();
            
            // Se o usuário não passar nenhum valor na ordem
            // força a consulta não encontrar registro
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
     * Retorna os dados das ações
     *
     * @return JSON
     */
    public function retornaAcoesXAction() {

        // Verifica se arrequisição foi passada por Ajax
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