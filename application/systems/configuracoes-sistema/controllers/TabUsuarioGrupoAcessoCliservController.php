<?php
/**
 * Esta classe tem como objetivo manipular os dados de acesso Cliente/Servidor 
 * que estão ligados ao usuário.
 * 
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabUsuarioGrupoAcessoCliservController extends Marca_Controller_Abstract_Operacao {
    
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
        if (isset($params['pai_cd_usuario'])) {
            $sessao->pai_cd_usuario = $params['pai_cd_usuario'];
        }
        
        // Registra o código da janela pai
        Zend_Registry::set("pai_cd_usuario", $sessao->pai_cd_usuario);
        
        // Joga para a view o código da transação pai
        $this->view->pai_cd_usuario = $sessao->pai_cd_usuario;

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
        Zend_Loader::loadClass('UsuarioModel');
        Zend_Loader::loadClass('GrupoModel');
        Zend_Loader::loadClass('UsuarioGrupoModel');
        
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
        
        // Instancia o modelo
        $grupos = new GrupoModel();
        
        // Captura o código da transação pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Ordenação da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("G.NO_GRUPO", "ASC");
        
        // Define os filtros para a cosulta
        $where = $grupos->addWhere(array("GU.CD_USUARIO = ?" => $pai_cd_usuario))
                        ->getWhere();
        
        // Busca todos os usuários ligados ao grupo
        $select = $grupos->select()
                         ->setIntegrityCheck(false)
                         ->from(array("G" => "GRUPO"), array("G.CD_GRUPO", 
                                                             "G.NO_GRUPO",
                                                             "G.CD_UNID",
                         									 "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(GU.DT_ACESSO_INI, 'DD/MM/YYYY')"),
                                                             "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(GU.DT_ACESSO_FIM, 'DD/MM/YYYY')"),
                                                             "GU.CD_USUARIO"))
                         ->join(array("GU" => "USUARIO_GRUPO"), "G.CD_GRUPO = GU.CD_GRUPO", array())
                         ->where($where)
                         ->order($order);
        
        // Recebe a instância do paginator por singleton
        $paginator = Zend_Paginator::factory($select);
        
        // Seta o número da página corrente
        $pagina = $this->_getParam('pagina', 1);
        
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
        
        // Instancia o modelo
        $grupos         = new GrupoModel();
        $gruposUsuarios = new UsuarioGrupoModel();
        
        // Captura o código da transação pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Busca todos os grupos que ainda não foram ligados ao usuário
        $select = $grupos->select()
                         ->setIntegrityCheck(false)
                         ->from(array("G" => "GRUPO"), array("G.CD_GRUPO", 
                         									 "G.NO_GRUPO", 
                         									 "G.CD_UNID"))
                         ->where("NOT EXISTS (" .
                            ( $gruposUsuarios->select()
                                             ->from(array("GU" => "USUARIO_GRUPO"), array("GU.CD_GRUPO"))
                                             ->where("G.CD_GRUPO = GU.CD_GRUPO")
                                             ->where("GU.CD_USUARIO = '{$pai_cd_usuario}'") )
                            . ")")
                         ->order("G.NO_GRUPO ASC");
        
        // Executa a busca
        $linhas = $grupos->fetchAll($select);
        
        $arrayGrupo = array();
        foreach($linhas as $linha) {
            $arrayGrupo[$linha->CD_GRUPO] = $linha->NO_GRUPO;
        }
                           
        // Joga para a view todos os grupos
        $this->view->grupos = $arrayGrupo;
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
        $gruposUsuarios = new UsuarioGrupoModel();
        
        // Captura o código da transação pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Se retornar um array é por que está sendo 
        // ligado as ações a transação, caso contrário 
        // é por que está sendo editado uma ação.
        if(is_array($params["cd_grupo"])) {
            
            // Se o registro não existir insere, caso contrário edita
            if($params["operacao"] == "novo") {
                
                // Conta o número total de grupos retornados
                $totalGrupos = count($params["cd_grupo"]);
                
                // Percorre todas as ações
                for($i=0; $i < $totalGrupos; $i++) {
                    
                    // Verifica se o código é válido
                    if($params["cd_grupo"][$i] != "") {
                        
                        // Monta os dados para salvar
                        $dados = array("CD_USUARIO"    => $pai_cd_usuario,
                                       "CD_GRUPO"      => $params["cd_grupo"][$i],
                        			   "DT_ACESSO_INI" => $params["dt_acesso_ini"][$i],
                        			   "DT_ACESSO_FIM" => $params["dt_acesso_fim"][$i]);
                        
                        // Valida os dados obrigatórios
                        if($gruposUsuarios->isValid($dados)) {
                            // Liga o novo grupo ao usuário
                            $gruposUsuarios->insert($dados);
                            
                        }
                    }
                }
                
                // Redireciona para ação novo
                $this->_forward('index');
            }
            
        } else {
            
             // Monta a condição de edição
	        $where = " CD_GRUPO        = "  . $pai_cd_grupo . 
	          		 " AND CD_USUARIO  = '" . $params["cd_usuario"] . "'";

			$linhaGrupoUsuario = $gruposUsuarios->fetchAll($where);			
			$dataFinalEmbranco = 0;
		
			foreach ($linhaGrupoUsuario as $grupoUsuario) {
				
				// Conta a quantidade de datas finais embranco(NULL)
				if (trim($grupoUsuario->DT_ACESSO_FIM) == "") {
					$dataFinalEmbranco++;
				}
				
			}
		
			if ($dataFinalEmbranco > 0 && trim($params['dt_acesso_fim']) == "") {
			
				$mensagemSistema = Zend_Registry::get("mensagemSistema");
				$msg             = array("msg"    => array("O registro não foi salvo, somente uma data final do Período de acesso pode ser nula para o mesmo usuário."),
                                  		 "titulo" => "ATENÇÃO",
                						 "tipo"   => 4);
				$mensagemSistema->send(serialize($msg));
				
			} else {
            
                // Monta os dados para salvar
                $dados = array("DT_ACESSO_FIM" => $params["dt_acesso_fim"]);
                            
                // Monta a condição de edição
                $where = " CD_GRUPO           = '" . $params["cd_grupo"]      . "'" . 
		          		 " AND CD_USUARIO     = '" . $params["cd_usuario"]    . "'" .
		          		 " AND DT_ACESSO_INI  = '" . $params["dt_ini_acesso"] . "'";
                
                // Edita o grupo do usuário
                $update = $gruposUsuarios->update($dados, $where);
                
			}
            
            // Redireciona para ação selecionar
            $this->_forward('selecionar');
            
        }
        
    }


    /**
     * Exclui um aditivo selecionado de um contrato
     *
     * @return void
     */
    public function excluirAction() {
        
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Instancia o modelo
        $gruposUsuarios = new UsuarioGrupoModel();
        
        // Captura o código da transação pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Valida os dados obrigatórios
        if($pai_cd_usuario != "" && $params['cd_usuario'] != "") {
            // Monta a condição do where
            $where = "CD_USUARIO = '" . $pai_cd_usuario . "' AND " .
                     "CD_GRUPO   = '" . $params["cd_grupo"] . "'";
            
            // Atualiza os dados
            $delete = $gruposUsuarios->delete($where);
            
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
        $grupos         = new GrupoModel();
        $gruposUsuarios = new UsuarioGrupoModel();
        
        // Captura o código da transação pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Busca todos os grupos que ainda não foram ligados ao usuário
        // com excessão do grupo selecionado
        $select = $grupos->select()
                         ->setIntegrityCheck(false)
                         ->from(array("G" => "GRUPO"), array("G.CD_GRUPO", 
                         									 "G.NO_GRUPO", 
                         									 "G.CD_UNID"))
                         ->where("NOT EXISTS (" .
                            ( $gruposUsuarios->select()
                                             ->from(array("GU" => "USUARIO_GRUPO"), array("GU.CD_GRUPO"))
                                             ->where("G.CD_GRUPO = GU.CD_GRUPO")
                                             ->where("GU.CD_USUARIO = '{$pai_cd_usuario}'")
                                             ->where("GU.CD_GRUPO <> '{$params['cd_grupo']}'") )
                            . ")")
                         ->order("G.NO_GRUPO ASC");
        
        // Executa a busca
        $linhas = $grupos->fetchAll($select);
        
        $arrayGrupo = array();
        foreach($linhas as $linha) {
            $arrayGrupo[$linha->CD_GRUPO] = $linha->NO_GRUPO;
        }

        // Joga para a view todos os grupos
        $this->view->grupos = $arrayGrupo;        
        
        // Define os filtros para a cosulta
        $where = $gruposUsuarios->addWhere(array("GU.CD_USUARIO = ?" => $pai_cd_usuario))
                                ->addWhere(array("GU.CD_GRUPO   = ?" => $params["cd_grupo"]))
                                ->getWhere();
        
        // Busca o grupo ligado ao usuário
        $select = $gruposUsuarios->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("GU" => "USUARIO_GRUPO"), array("GU.CD_USUARIO",
                                                                              "G.CD_GRUPO",
                                                                              "G.NO_GRUPO",
                                                                              "G.CD_UNID",
                                 											  "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(GU.DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                         										  			  "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(GU.DT_ACESSO_FIM, 'DD/MM/YYYY')")))
                                  ->join(array("G"  => "GRUPO"), "GU.CD_GRUPO = G.CD_GRUPO", array())
                                  ->where($where);
        
        // Executa a consulta
        $linha = $gruposUsuarios->fetchRow($select);
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($linha->toArray(), "lower");
        
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
     * Busca os dados do usuario
     *
     * @return JSON
     */
    public function retornaGrupoXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Captura o código da transação pai
            $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
            
            // Instancia o modelo
            $grupos = new GrupoModel();
            
            // Define os filtros para a cosulta
            $where = $grupos->addWhere(array("CD_GRUPO = ?" => $params["cd_grupo"]))
                            ->getWhere();
            
            // Captura o registro
            $select = $grupos->select()
                             ->from(array("GRUPO"), array("CD_GRUPO", 
                                                          "NO_GRUPO",
                                                          "CD_UNID"))
                             ->where($where);
            
            // Captura o registro
            $linha = $grupos->fetchRow($select);
            
            // Verifica se encontrou o usuário
            if($linha->CD_GRUPO != "") {
                $linha = $linha->toArray();
            }
                        
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($grupos);
        }

    }
    
}