<?php
/**
 * Esta classe tem como objetivo manipular os dados de acesso web 
 * que est�o ligados ao usu�rio.
 * 
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabUsuarioGrupoAcessoWebController extends Marca_Controller_Abstract_Operacao {
    
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
        Zend_Loader::loadClass('WebGrupoModel');
        Zend_Loader::loadClass('WebGrupoUsuarioModel');
        Zend_Loader::loadClass('GrupoModel');
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
        $grupos = new WebGrupoModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("WG.NO_GRUPO", "ASC");
        
        // Define os filtros para a cosulta
        $where = $grupos->addWhere(array("WGU.CD_USUARIO = ?" => $pai_cd_usuario))
                        ->getWhere();
        
        // Busca todos os usu�rios ligados ao grupo
        $select = $grupos->select()
                         ->setIntegrityCheck(false)
                         ->from(array("WG" => "WEB_GRUPO"), array("WG.CD_GRUPO", 
                                                                  "WG.NO_GRUPO",
                                                                  "WG.DS_GRUPO",
                                                                  "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY')"),
                                                                  "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_FIM, 'DD/MM/YYYY')"),
                                                                  "WGU.CD_USUARIO"))
                         ->join(array("WGU" => "WEB_GRUPO_USUARIO"), "WG.CD_GRUPO = WGU.CD_GRUPO", array())
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
    
    
    public function importarAction() {
        
        // use different layout script with this action:
        $this->_helper->layout->setLayout('layout-flutuante');
        
        // Recupera a inst�ncia da base de dados
        $db = Zend_Registry::get("db");

        // Captura os parametros passados por GET
        $params = $this->getRequest()->getParams();

        // Instancia o modelo
        $grupos = new WebGrupoModel();
   
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
        $grupos         = new WebGrupoModel();
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Busca todos os grupos que ainda n�o foram ligados ao usu�rio
        $select = $grupos->select()
                         ->setIntegrityCheck(false)
                         ->from(array("WG" => "WEB_GRUPO"), array("WG.CD_GRUPO", "WG.NO_GRUPO", "WG.DS_GRUPO"))
                         ->where(" WG.FL_ATIVO = 1 AND NOT EXISTS (" .
                            ($gruposUsuarios->select()
                                             ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("WGU.CD_GRUPO"))
                                             ->where("WG.CD_GRUPO = WGU.CD_GRUPO")
                                             ->where("WGU.CD_USUARIO = '{$pai_cd_usuario}'") )
                            . ")")
                         ->order("WG.CD_GRUPO ASC");
        
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
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        $this->view->importou = 0;
                
        if($params['importar'] == 'importar'){
            
            
            $params['usuario_final'] = $pai_cd_usuario;
                        
            $grupos = new GrupoModel();
            
            $resultado = $grupos->retornaGruposUsuario($params);
            
            if (count($resultado) > 0){
                
                for($g=0; $g < count($resultado); $g++) {

                    $dadosInsert = array("CD_USUARIO"    => $pai_cd_usuario,
                                         "CD_GRUPO"      => $resultado[$g],
                                         "DT_ACESSO_INI" => date('d/m/Y'));

                    // Liga o novo grupo ao usu�rio
                    $gruposUsuarios->insert($dadosInsert);
                        
                }
                 
                 $this->view->importou = 1;
            }else{
                 $this->view->importou = 2;
                
            }      
        }else if(is_array($params["cd_grupo"])) {
            
            // Se o registro n�o existir insere, caso contr�rio edita
            if($params["operacao"] == "novo") {
                
                // Conta o n�mero total de grupos retornados
                $totalGrupos = count($params["cd_grupo"]);
                
                // Percorre todas as a��es
                for($i=0; $i < $totalGrupos; $i++) {
                    
                    // Verifica se o c�digo � v�lido
                    if($params["cd_grupo"][$i] != "") {
                        
                        // Monta os dados para salvar
                        $dados = array("CD_USUARIO"    => $pai_cd_usuario,
                                       "CD_GRUPO"      => $params["cd_grupo"][$i],
                        			   "DT_ACESSO_INI" => $params["dt_acesso_ini"][$i],
                        			   "DT_ACESSO_FIM" => $params["dt_acesso_fim"][$i]);
                        
                        // Valida os dados obrigat�rios
                        if($gruposUsuarios->isValid($dados)) {
                            // Liga o novo grupo ao usu�rio
                            $gruposUsuarios->insert($dados);
                        }
                    }
                }
                
                // Redireciona para a��o novo
                $this->_forward('index');
            }
            
        } else {
            
            // Monta a condi��o de edi��o
	        $where = " CD_GRUPO        = "  . $params["cd_grupo"] . 
	          		 " AND CD_USUARIO  = '" . $pai_cd_usuario . "'";

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
				$msg             = array("msg"    => array("O registro n�o foi salvo, somente uma data final do Per�odo de acesso pode ser nula para o mesmo usu�rio."),
                                  		 "titulo" => "ATEN��O",
                						 "tipo"   => 4);
				$mensagemSistema->send(serialize($msg));
				
			} else {
			    
			    // Monta os dados para salvar
                $dados = array("DT_ACESSO_FIM" => $params["dt_acesso_fim"]);
                            
                // Monta a condi��o de edi��o
                $where = " CD_GRUPO           = "  . $params["cd_grupo"]      . 
		          		 " AND CD_USUARIO     = '" . $pai_cd_usuario    . "'" .
		          		 " AND DT_ACESSO_INI  = '" . $params["dt_acesso_ini"] . "'";
                
                // Edita o grupo do usu�rio
                $update = $gruposUsuarios->update($dados, $where);
			    
			}

            // Redireciona para a��o selecionar
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
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Valida os dados obrigat�rios
        if($pai_cd_usuario != "" && $params['cd_usuario'] != "") {
            // Monta a condi��o do where
            $where = "CD_USUARIO = '" . $pai_cd_usuario . "' AND " .
                     "CD_GRUPO   =  " . $params["cd_grupo"];
            
            // Atualiza os dados
            $delete = $gruposUsuarios->delete($where);
            
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
     * Seleciona um grupo referente ao usu�rio
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $grupos         = new WebGrupoModel();
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
        
        // Busca todos os grupos que ainda n�o foram ligados ao usu�rio
        // com excess�o do grupo selecionado
        $select = $grupos->select()
                         ->setIntegrityCheck(false)
                         ->from(array("WG" => "WEB_GRUPO"), array("WG.CD_GRUPO", 
                         										  "WG.NO_GRUPO", 
                         										  "WG.DS_GRUPO"))
                         ->where(" WG.FL_ATIVO = 1 AND  NOT EXISTS (" .
                            ( $gruposUsuarios->select()
                                             ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("WGU.CD_GRUPO"))
                                             ->where("WG.CD_GRUPO = WGU.CD_GRUPO")
                                             ->where("WGU.CD_USUARIO = '{$pai_cd_usuario}'")
                                             ->where("WGU.CD_GRUPO <> {$params['cd_grupo']}") )
                            . ")")
                         ->order("WG.CD_GRUPO ASC");
        
        // Executa a busca
        //echo $select; die; 
        $linhas = $grupos->fetchAll($select);
        
        $arrayGrupo = array();
        foreach($linhas as $linha) {
            $arrayGrupo[$linha->CD_GRUPO] = $linha->NO_GRUPO;
        }
         
        // Joga para a view todos os grupos
        $this->view->grupos = $arrayGrupo;        
        
        if($params["dt_acesso_ini"]!="")
        { 
           $formatadata = str_replace("!", "/", $params["dt_acesso_ini"]);
           
        }   
        // Define os filtros para a cosulta
        $where = $gruposUsuarios->addWhere(array("WGU.CD_USUARIO = ?" => $pai_cd_usuario))
                                ->addWhere(array("TRUNC(WGU.DT_ACESSO_INI) = ?" => $formatadata))
                                ->addWhere(array("WGU.CD_GRUPO   = ?" => $params["cd_grupo"]))
                                ->getWhere();
        
        
        // Busca o grupo ligado ao usu�rio
        $select = $gruposUsuarios->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("WGU.CD_USUARIO",
                                                                                   "WG.CD_GRUPO",
                                                                                   "WG.NO_GRUPO",
                                                                                   "WG.DS_GRUPO",
                                 												   "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                         										  				   "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_FIM, 'DD/MM/YYYY')")))
                                  ->join(array("WG"  => "WEB_GRUPO"), "WGU.CD_GRUPO = WG.CD_GRUPO", array())
                                  ->where($where);
        
        // Executa a consulta
        $linha = $gruposUsuarios->fetchRow($select);
      
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
    public function retornaGrupoXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Captura o c�digo da transa��o pai
            $pai_cd_usuario = Zend_Registry::get("pai_cd_usuario");
            
            // Instancia o modelo
            $grupos = new WebGrupoModel();
            
            // Define os filtros para a cosulta
            $where = $grupos->addWhere(array("CD_GRUPO = ?" => $params["cd_grupo"]))
                            ->addWhere(array("FL_ATIVO = 1" ))
                            ->getWhere();
            
            // Captura o registro
            $select = $grupos->select()
                             ->from(array("WEB_GRUPO"), array("CD_GRUPO", 
                                                              "NO_GRUPO",
                                                              "DS_GRUPO"))
                             ->where($where);
          
            // Captura o registro
            $linha = $grupos->fetchRow($select);
            
            // Verifica se encontrou o usu�rio
            if($linha->CD_GRUPO != "") {
                $linha = $linha->toArray();
            }
                        
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($grupos);
        }

    }
    
       public function retornaUsuarioXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        try {
    		// Verifica se arrequisi��o foi passada por Ajax
    		if($this->_request->isXmlHttpRequest()) {
				
    			// Captura os parametros passados por GET
    			$params = $this->getRequest()->getParams();
                
                // Instancia o modelo de dados
                $usuario = new UsuarioModel();
                
                $where = " UPPER(CD_USUARIO) LIKE '%".$params['term']."%' OR UPPER(NO_USUARIO) LIKE '%".$params['term']."%'";
                
                $retorno = $usuario->buscaUsuariosSemArray($where);
                
                $retornoJSON = array();
    			if(count($retorno) > 0) { 
                    //PRINT_R($retorno); die;
    				foreach($retorno as $linha) {
						
    					$retornoJSON[] = array("id"    => $linha->CD_USUARIO,
    	                    				   "value" => $linha->CD_USUARIO,
    	                                       "label" => $linha->CD_USUARIO." - ".$linha->NO_USUARIO);												
    				}
    
    			}
                
    			// Helper json
    			$this->_helper->json(Marca_ConverteCharset::converter($retornoJSON), true);
                
    		}
    			
    	} catch (Zend_Exception $e) {
    		// Dispara um erro
    		echo "Erro de ajax [retornaEmpregadoXAction] ".$e->getMessage();
    	}
    
    }
    
    
       
            
}