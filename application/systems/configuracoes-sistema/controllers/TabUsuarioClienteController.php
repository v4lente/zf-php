<?php
/**
 * Esta classe tem como objetivo manipular os dados do clientes  
 * que est�o ligados ao usu�rio.
 * 
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabUsuarioClienteController extends Marca_Controller_Abstract_Operacao {
    
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
        if (isset($params['pai_cd_usuario'])) {
            $sessao->pai_cd_usuario = $params['pai_cd_usuario'];
        }
        
        // Registra o c�digo da janela pai
        Zend_Registry::set("pai_cd_usuario", $sessao->pai_cd_usuario);
        
        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_usuario = $sessao->pai_cd_usuario;

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
        Zend_Loader::loadClass('UsuarioModel');
        Zend_Loader::loadClass('ClienteModel');
        Zend_Loader::loadClass('UsuarioClienteModel');
        
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
        
        // Instancia o modelo
        $clientes = new ClienteModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("C.RAZAO_SOCIAL", "ASC");
        
        // Define os filtros para a cosulta
        $where = $clientes->addWhere(array("UC.CD_USUARIO = ?" => $pai_cd_usuario))
                        ->getWhere();
        
        // Busca todos os usu�rios ligados ao grupo
        $select = $clientes->select()
                           ->setIntegrityCheck(false)
                           ->from(array("C" => "CLIENTE"), array("C.CD_CLIENTE",
                                                                 "C.RAZAO_SOCIAL",
                                                                 "UC.FL_PRINCIPAL",
                                                                 "UC.CD_USUARIO"))
                           ->join(array("UC" => "USUARIO_CLIENTE"), "C.CD_CLIENTE = UC.CD_CLIENTE", array())
                           ->where($where)
                           ->order($order);
        
        // Recebe a inst�ncia do paginator por singleton
        $paginator = Zend_Paginator::factory($select);
        
        // Seta o n�mero da p�gina corrente
        $pagina = $this->_getParam('pagina', 1);
        
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
        
        // Instancia o modelo
        $clientes         = new ClienteModel();
        $clientesUsuarios = new UsuarioClienteModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Busca todos os clientes que ainda n�o foram ligados ao usu�rio
        $select = $clientes->select()
                           ->setIntegrityCheck(false)
                           ->from(array("C" => "CLIENTE"), array("C.CD_CLIENTE", 
                         									     "C.RAZAO_SOCIAL", 
                         									     "UC.FL_PRINCIPAL"))
                           ->join(array("UC" => "USUARIO_CLIENTE"), "C.CD_CLIENTE = UC.CD_CLIENTE", array())
                           ->where("NOT EXISTS (" .
                                ( $clientesUsuarios->select()
                                                 ->from(array("UC2" => "USUARIO_CLIENTE"), array("UC2.CD_CLIENTE"))
                                                 ->where("C.CD_CLIENTE = UC2.CD_CLIENTE")
                                                 ->where("UC2.CD_USUARIO = '{$pai_cd_usuario}'") )
                                . ")")
                           ->order("C.CD_CLIENTE ASC");
        
        // Executa a busca
        $linhas = $clientes->fetchAll($select);
        
        $arrayClientes = array();
        foreach($linhas as $linha) {
            $arrayClientes[$linha->CD_CLIENTE] = $linha->RAZAO_SOCIAL;
        }
                           
        // Joga para a view todos os grupos
        $this->view->clientes = $arrayClientes;
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
        $clientesUsuarios = new UsuarioClienteModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Se retornar um array � por que est� sendo 
        // ligado as a��es a transa��o, caso contr�rio 
        // � por que est� sendo editado uma a��o.
        if(is_array($params["cd_cliente"])) {
            
            // Se o registro n�o existir insere, caso contr�rio edita
            if($params["operacao"] == "novo") {
                
                // Conta o n�mero total de grupos retornados
                $totalClientes = count($params["cd_cliente"]);
                
                // Percorre todas as a��es
                for($i=0; $i < $totalClientes; $i++) {
                    
                    // Verifica se o c�digo � v�lido
                    if($params["cd_cliente"][$i] != "") {
                        
                        // Monta os dados para salvar
                        $dados = array("CD_USUARIO"   => $pai_cd_usuario,
                                       "CD_CLIENTE"   => $params["cd_cliente"][$i],
                                       "FL_PRINCIPAL" => $params["fl_principal"][$i]);
                        
                        // Valida os dados obrigat�rios
                        if($clientesUsuarios->isValid($dados)) {
                            // Liga o novo cliente ao usu�rio
                            $clientesUsuarios->insert($dados);
                            
                        }
                    }
                }
                
                // Redireciona para a��o novo
                $this->_forward('index');
            }
            
        } else {
            
            $where = "CD_USUARIO = '{$pai_cd_usuario}' AND CD_CLIENTE = {$params["cd_cliente"]}";
            
            // Monta os dados para salvar
            $dados = array("FL_PRINCIPAL" => $params["fl_principal"]);
            
            // Edita o cliente
            $clientesUsuarios->update($dados, $where);
            
            // Redireciona para a��o novo
            $this->_forward('selecionar');
            
        }
        
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $clientesUsuarios = new UsuarioClienteModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Valida os dados obrigat�rios
        if($pai_cd_usuario != "" && $params['cd_cliente'] != "") {
            // Monta a condi��o do where
            $where = "CD_USUARIO = '" . $pai_cd_usuario . "' AND " .
                     "CD_CLIENTE = " . $params["cd_cliente"];
            
            // Atualiza os dados
            $delete = $clientesUsuarios->delete($where);
            
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
        }
        
    }


    /**
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $clientes         = new ClienteModel();
        $clientesUsuarios = new UsuarioClienteModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Busca todos os clientes que ainda n�o foram ligados ao usu�rio
        // com excess�o do cliente selecionado
        $select = $clientes->select()
                           ->setIntegrityCheck(false)
                           ->from(array("C" => "CLIENTE"), array("C.CD_CLIENTE", 
                         									     "C.RAZAO_SOCIAL", 
                         									     "UC.FL_PRINCIPAL"))
                           ->join(array("UC" => "USUARIO_CLIENTE"), "C.CD_CLIENTE = UC.CD_CLIENTE", array())
                           ->where("NOT EXISTS (" .
                                ( $clientesUsuarios->select()
                                                 ->from(array("UC2" => "USUARIO_CLIENTE"), array("UC2.CD_CLIENTE"))
                                                 ->where("C.CD_CLIENTE = UC2.CD_CLIENTE")
                                                 ->where("UC2.CD_USUARIO = '{$pai_cd_usuario}'") )
                                . ")")
                           ->order("C.CD_CLIENTE ASC");
        
        // Executa a busca
        $linhas = $clientes->fetchAll($select);
        
        $arrayClientes = array();
        foreach($linhas as $linha) {
            $arrayClientes[$linha->CD_CLIENTE] = $linha->RAZAO_SOCIAL;
        }
                           
        // Joga para a view todos os grupos
        $this->view->clientes = $arrayClientes;        
        
        // Define os filtros para a cosulta
        $where = $clientesUsuarios->addWhere(array("UC.CD_USUARIO = ?" => $pai_cd_usuario))
                                  ->addWhere(array("UC.CD_CLIENTE = ?" => $params["cd_cliente"]))
                                  ->getWhere();
        
        // Busca o grupo ligado ao usu�rio
        $select = $clientesUsuarios->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("UC" => "USUARIO_CLIENTE"), array("UC.CD_USUARIO",
                                                                              	"UC.CD_CLIENTE",
                                                                              	"C.RAZAO_SOCIAL",
                                                                              	"UC.FL_PRINCIPAL"))
                                  ->join(array("C"  => "CLIENTE"), "UC.CD_CLIENTE = C.CD_CLIENTE", array())
                                  ->where($where);
        
        // Executa a consulta
        $linha = $clientesUsuarios->fetchRow($select);
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($linha->toArray(), "lower");
        
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
     * Busca os dados do usuario
     *
     * @return JSON
     */
    public function retornaClienteXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Captura o c�digo da transa��o pai
            $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
            
            // Instancia o modelo
            $clientes = new ClienteModel();
            
            // Define os filtros para a cosulta
            if(is_numeric($params["term"])) {
                $where = "UPPER(NR_DOC) LIKE UPPER('{$params["term"]}%')";
            } else {
                $where = "UPPER(RAZAO_SOCIAL) LIKE UPPER('%{$params["term"]}%')";
            }
            
            // Captura o registro
            $select = $clientes->select()->where($where);
            
            // Captura o registro
            $linhas = $clientes->fetchAll($select);
            
            $retornoJSON = array();
            if(count($linhas) > 0) {
                foreach($linhas as $linha) {
                    $retornoJSON[] = array("id"           => $linha->CD_CLIENTE,
                                           "value"        => $linha->RAZAO_SOCIAL,
                                           "label"        => $linha->NR_DOC . " - " . $linha->RAZAO_SOCIAL
    									);
                }
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);

            // Limpa os objetos da memoria
            unset($clientes);
        }

    }
    
}