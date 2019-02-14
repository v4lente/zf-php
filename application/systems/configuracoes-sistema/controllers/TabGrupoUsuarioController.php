<?php
/**
 * Esta classe tem como objetivo manipular os dados da Aba de GrupoUsuario.
 *
 * @author     M�rcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_TabGrupoUsuarioController extends Marca_Controller_Abstract_Operacao {
    
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
        if (isset($params['pai_cd_grupo'])) {
            $sessao->pai_cd_grupo = $params['pai_cd_grupo'];
        }
        
        Zend_Registry::set("pai_cd_grupo", $sessao->pai_cd_grupo);
        
        // Joga para a view o c�digo da transa��o pai
        $this->view->pai_cd_grupo = $sessao->pai_cd_grupo;

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
        Zend_Loader::loadClass('WebGrupoTransacaoModel');
        Zend_Loader::loadClass('WebAcaoModel');
        Zend_Loader::loadClass('WebMenuSistemaModel');
        Zend_Loader::loadClass('WebTransacaoModel');
        Zend_Loader::loadClass('WebTransacaoAcaoModel');
        
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
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Ordena��o da consulta
        $order = $this->_helper->OrdenaConsulta->ordenar("U.CD_USUARIO", "ASC");
        
        // Define os filtros para a cosulta
        $where = $gruposUsuarios->addWhere(array("WGU.CD_GRUPO = ?" => $pai_cd_grupo))
                                ->getWhere();
        
                                
                                
		$dt_acesso_ini = new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY') as DT_ACESSO_INI");
        $dt_acesso_fim = new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_FIM, 'DD/MM/YYYY') as DT_ACESSO_FIM");
                                        
        // Busca todos os usu�rios ligados ao grupo
        $select = $gruposUsuarios->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("WGU.CD_USUARIO", 
                                                                                   "U.NO_USUARIO",
                                                                                   "TP_USUARIO" => new Zend_Db_Expr("DECODE(U.TP_USUARIO, 'P', 'PORTO', 'C', 'CLIENTE')"),
													                               $dt_acesso_ini, 
													                               $dt_acesso_fim))
                                  ->join(array("U"  => "USUARIO"), "WGU.CD_USUARIO = U.CD_USUARIO", array())
                                  ->where($where)
                                  ->order($order);
                                 
        // Define os par�metros para a consulta e retorna o resultado da pesquisa
        $resConsulta = $gruposUsuarios->fetchAll($select);
        
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
        
        // Instancia o modelo
        $usuarios = new UsuarioModel();
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Busca todos os usu�rios que ainda n�o foram ligados ao grupo
        $select = $usuarios->select()
                           ->setIntegrityCheck(false)
                           ->from(array("U" => "USUARIO"), array("U.CD_USUARIO", "U.NO_USUARIO"))
                           ->order("U.CD_USUARIO ASC");
        
        // Executa a busca
        $linhas = $usuarios->fetchAll($select);
        
        $arrayUsuario1 = array();
        $arrayUsuario2 = array();
        foreach($linhas as $linha) {
            $arrayUsuario1[$linha->CD_USUARIO] = $linha->CD_USUARIO;
            $arrayUsuario2[$linha->CD_USUARIO] = $linha->NO_USUARIO;
        }
                           
        // Joga para a view todos os usu�rios
        $this->view->usernames = $arrayUsuario1;
        $this->view->usuarios  = $arrayUsuario2;
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
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Se retornar um array � por que est� sendo 
        // ligado as a��es a transa��o, caso contr�rio 
        // � por que est� sendo editado uma a��o.
        if(is_array($params["cd_usuario"])) {
            
            // Se o registro n�o existir insere, caso contr�rio edita
            if($params["operacao"] == "novo") {
                
                // Conta o n�mero total de usu�rios retornados
                $totalUsuarios = count($params["cd_usuario"]);
                
                // Percorre todas as a��es
                for($i=0; $i < $totalUsuarios; $i++) {
                    
                    // Verifica se o c�digo � v�lido
                    if($params["cd_usuario"][$i] != "") {
                        
                        // Monta os dados para salvar
                        $dados = array("CD_GRUPO"      => $pai_cd_grupo,
                                       "CD_USUARIO"    => $params["cd_usuario"][$i],
                        			   "DT_ACESSO_INI" => $params["dt_acesso_ini"][$i],
                        			   "DT_ACESSO_FIM" => $params["dt_acesso_fim"][$i]);
                        
                        // Valida os dados obrigat�rios
                        if($gruposUsuarios->isValid($dados)) {
                            // Insere o novo usu�rio
                            $gruposUsuarios->insert($dados);
                            
                        }
                    }
                }
                
                // Redireciona para a��o novo
                $this->_forward('index');
            }
            
        } else {
			
        	// Monta os dados para salvar
			$dados = array("CD_GRUPO"      => $pai_cd_grupo,
            			   "CD_USUARIO"    => $params["cd_usuario"],
                           "DT_ACESSO_INI" => $params["dt_acesso_ini"]);
			    	
	        // Valida os dados obrigat�rios
	        if($gruposUsuarios->isValid($dados)) {
	            	
	            // Monta a condi��o de edi��o
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
					$msg             = array("msg"    => array("O registro n�o foi salvo, somente uma data final do Per�odo de acesso pode ser nula para o mesmo usu�rio."),
	                                  		 "titulo" => "ATEN��O",
	                						 "tipo"   => 4);
					$mensagemSistema->send(serialize($msg));
					
				} else {

		        	// Monta a condi��o de edi��o
		        	$where = " CD_GRUPO           = "  . $pai_cd_grupo . 
		          		 	 " AND CD_USUARIO     = '" . $params["cd_usuario"]    . "'" .
		          		 	 " AND TO_CHAR(DT_ACESSO_INI, 'DD/MM/YYYY')  = '" . $params["dt_acesso_ini"] . "'";

			    	// Monta os dados para salvar
			    	$dados = array("DT_ACESSO_FIM" => $params["dt_acesso_fim"]);
			    	
			    	// Edita o grupo do usu�rio
			        $gruposUsuarios->update($dados, $where);
				}

            	// Redireciona para a��o selecionar
            	$this->_forward('selecionar');            	
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
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Valida os dados obrigat�rios
        if($pai_cd_grupo != "" && $params['cd_usuario'] != "" && trim($params["dt_acesso_ini"]) != "") {
            // Monta a condi��o do where
            $where = "CD_GRUPO   = " . $pai_cd_grupo . " AND " .
                     "CD_USUARIO = '" . $params["cd_usuario"] . "' AND ".
            		 "TO_CHAR(DT_ACESSO_INI, 'DD/MM/YYYY') = '{$params["dt_acesso_ini"]}'";
            
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
     * Seleciona um aditivo referente a um contrato
     * @return void
     */
    public function selecionarAction() {
        
        // Recupera os parametros da requisi��o
        $params = $this->_request->getParams();
        
        // Instancia a classe de sistemas web
        $gruposUsuarios = new WebGrupoUsuarioModel();
        
        // Captura o c�digo da transa��o pai
        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
        
        // Define os filtros para a cosulta
        $where = $gruposUsuarios->addWhere(array("WGU.CD_GRUPO   = ?" => $pai_cd_grupo))
                                ->addWhere(array("WGU.CD_USUARIO = ?" => $params["cd_usuario"]))
                                ->addWhere(array("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY') = ?"   => $params['dt_acesso_ini']))                               
                                ->getWhere();
                                        
        // Busca todos os usu�rios ligados ao grupo
        $select = $gruposUsuarios->select()
                                 ->setIntegrityCheck(false)
                                 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("WGU.CD_USUARIO", 
                                                                                   "U.NO_USUARIO",
                                 												   "WGU.SEQ_VERSAO",	
                                                                                   "TP_USUARIO"    => new Zend_Db_Expr("DECODE(U.TP_USUARIO, 'P', 'PORTO', 'C', 'CLIENTE')"),
                                 												   "DT_ACESSO_INI" => new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY')"), 
                         										  				   "DT_ACESSO_FIM" => new Zend_Db_Expr("TO_CHAR(WGU.DT_ACESSO_FIM, 'DD/MM/YYYY')")))
                                  ->join(array("U"  => "USUARIO"), "WGU.CD_USUARIO = U.CD_USUARIO", array())
                                  ->where($where);
                                 
        // Executa a consulta
        $linha = $gruposUsuarios->fetchRow($select);
                
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($linha, "lower");
        
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
    public function retornaUsuarioXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Captura o c�digo da transa��o pai
            $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
            
            // Instancia o modelo
            $usuarios = new UsuarioModel();
            
            // Define os filtros para a cosulta
            $where = $usuarios->addWhere(array("CD_USUARIO = ?" => $params["cd_usuario"]))
                              ->getWhere();
            
            // Captura o registro
            $select = $usuarios->select()
                               ->from(array("USUARIO"), array("CD_USUARIO", 
                                                              "NO_USUARIO",
                                                              "TP_USUARIO" => new Zend_Db_Expr("DECODE(TP_USUARIO, 'P', 'PORTO', 'C', 'CLIENTE')")))
                               ->where($where);
            
            // Captura o registro                      
            $linha = $usuarios->fetchRow($select);
            
            // Verifica se encontrou o usu�rio
            if($linha->CD_USUARIO != "") {
                $linha = $linha->toArray();
            }
            
            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($linha), true);

            // Limpa os objetos da memoria
            unset($usuarios);
        }

    }
    
    
	/**
     * Verifica os dados do usuario
     *
     * @return JSON
     */
    public function verificaUsuarioXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if($this->_request->isXmlHttpRequest()) {
            
            // Captura os parametros passados por GET
            $params = $this->getRequest()->getParams();
            
            // Captura o c�digo da transa��o pai
            $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
            
            // Instancia o modelo
        	$gruposUsuarios = new WebGrupoUsuarioModel();
            
        	// Captura o c�digo da transa��o pai
	        $pai_cd_grupo = Zend_Registry::get("pai_cd_grupo");
	        
        	// Caso tenha data de ini informado
            if (trim($params['dt_acesso_ini']) != "") {
            	$params['dt_acesso_ini'] = str_replace("_barra_", "/", $params['dt_acesso_ini']);            	
            }
	        
	        // Define os filtros para a cosulta
	        $where = $gruposUsuarios->addWhere(array("WGU.CD_GRUPO   = ?" => $pai_cd_grupo))
	                                ->addWhere(array("WGU.CD_USUARIO = ?" => $params["cd_usuario"]))
	                                ->addWhere(array("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY') = ?"   => $params['dt_acesso_ini']))
	                                ->getWhere();

	        // Busca todos os usu�rios ligados ao grupo
	        $select = $gruposUsuarios->select()
	                                 ->setIntegrityCheck(false)
	                                 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("*"))
	                                 ->join(array("U"  => "USUARIO"), "WGU.CD_USUARIO = U.CD_USUARIO", array())
	                                 ->where($where);		                                 
	        // Executa a consulta
	        $linhas = $gruposUsuarios->fetchAll($select);

	        // Verifica quantos usu�rio tem para o valor informado		        
	        $res = array("qtd"=>0, "qtd_sem_dt_acesso_fim"=>0);
            $qtd = count($linhas);
            if ($qtd > 0) {            	
            	$res["qtd"] = $qtd;
            }
           
            // Define os filtros para a cosulta
	        $where = $gruposUsuarios->addWhere(array("WGU.CD_GRUPO   = ?" => $pai_cd_grupo))
	                                ->addWhere(array("WGU.CD_USUARIO = ?" => $params["cd_usuario"]))
	                                ->getWhere();

            // Busca todos os usu�rios ligados ao grupo
		   	$select = $gruposUsuarios->select()
	                                 ->setIntegrityCheck(false)
	                                 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("TOT" => new Zend_Db_Expr("COUNT(*)")))
	                                 ->join(array("U"  => "USUARIO"), "WGU.CD_USUARIO = U.CD_USUARIO", array())
	                                 ->where($where);

			// Caso tenha as datas informadas
            if (trim($params['dt_acesso_ini']) != "" && trim($params['dt_acesso_fim']) != "") {

	            // Formata o campo para a query
		        $params['dt_acesso_fim'] = str_replace("_barra_", "/", $params['dt_acesso_fim']);
		        
		        // Define os filtros para a cosulta
		        $where = $gruposUsuarios->addWhere(array("WGU.CD_GRUPO   = ?" => $pai_cd_grupo))
	                                	->addWhere(array("WGU.CD_USUARIO = ?" => $params["cd_usuario"]))
	                                	->addWhere(array("TO_CHAR(WGU.DT_ACESSO_INI, 'DD/MM/YYYY') = ?" => $params['dt_acesso_ini'])) 
		        						->addWhere(array("TO_CHAR(WGU.DT_ACESSO_FIM, 'DD/MM/YYYY') = ?" => $params['dt_acesso_fim']))                               
			                            ->getWhere();

				$gruposUsuarios->select()->reset();

	            // Busca todos os usu�rios ligados ao grupo
		        $select = $gruposUsuarios->select()
		                                 ->setIntegrityCheck(false)
		                                 ->from(array("WGU" => "WEB_GRUPO_USUARIO"), array("TOT" => new Zend_Db_Expr("COUNT(*)")))
		                                 ->join(array("U"  => "USUARIO"), "WGU.CD_USUARIO = U.CD_USUARIO", array())
		                                 ->where($where);
            } else {            	
            	$select->where("DT_ACESSO_FIM IS NULL");            
            }

        	// Captura o registro
            $linhas = $gruposUsuarios->fetchAll($select);
            
			// Verifica se tem para o periodo informado mais de um registro
            if ($linhas[0]->TOT > 1) {            	
            	$res["qtd_sem_dt_acesso_fim"] = $linhas[0]->TOT;
            }

            // Limpa os objetos da memoria
            unset($gruposUsuarios);

            // Retorna os dados por json
            $this->_helper->json(Marca_ConverteCharset::converter($res), true);
            
        }
    }  
}