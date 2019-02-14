<?php

/**
 *
 * Classe responsável por controlar dados referente a configuração do sistema web
 *
 * @author     Márcio Souza Duarte
 * @category   Marca Sistemas
 * @copyright  Copyright (c) 1991-2010 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class ConfiguracoesSistema_ConfiguracaoController extends Marca_Controller_Abstract_Operacao {

    /**
     * (non-PHPdoc)
     * @see library/Marca/Controller/Abstract/Marca_Controller_Abstract_Operacao#init()
     */
    public function init() {
        parent::init();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Carrega os modelos
        Zend_Loader::loadClass("UsuarioModel");
        
        // Carrega a classe de tradução
        Zend_Loader::loadClass("Marca_Controller_Action_Helper_Traduz");
        
        // Instancia o objeto de tradução
        $traducao = new Marca_Controller_Action_Helper_Traduz();
        
        // Monta a tradução dos campos.
        // Nessessário para reduzir a quantidade de acessos ao banco,  
        // diminuindo assim o tempo de carregamento da página.
        $this->view->traducao = $traducao->traduz(array("LB_SENHA_NOVA",
                                                        "LB_SENHA_CONF",
                                                        "LB_QT_LINHAS",
                                                        "LB_SENHA_ATUAL"), $sessao->perfil->CD_IDIOMA);
        
    }
    
    /**
     * Metodo index
     * objetivo: Método principal da classe
     */
    public function indexAction() { 
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Joga para a tela a quantidade de linhas que aparecerão na pesquisa do usuário
        $this->view->qt_linhas = $sessao->perfil->QT_LINHAS;
        $this->view->no_usuario = $sessao->perfil->NO_USUARIO;
        $this->view->email = $sessao->perfil->EMAIL;
        
        
    }
    
     /**
     * Metodo novo
     * objetivo: utilizado para formularios de cadastro
     */
    public function novoAction() { }

    /**
     * Metodo salvar
     * objetivo: utilizado para as operações de INSERT/UPDATE
     */
    public function salvarAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
        
        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');
        
        // Recupera o controle de mensagens
        $mensagemSistema = Zend_Registry::get("mensagemSistema");
        
        // Instancia o modelo de dados
        $usuarios = new UsuarioModel();
        
        // Inicializa as mensagens
        $msg = array();
        
        // Verifica se o usuário quer trocar a senha ou apenas atualizar 
        // algum outro campo do perfil dele
        if($params["senha"] != "") {
        	
            // Compara a senha atual com a passada
            if($sessao->perfil->SENHA == md5(strtoupper($params["asenha"]))) {
                
                // Verifica se a senha está correta
                if(strtoupper($params["senha"]) === strtoupper($params["rsenha"])) {
                	                	
                	try {
                		// Troca a senha do usuário logado
                		$usuarios->trocarSenhaUsuarioLogado(strtoupper($params['senha']));
                		
                	} catch (Zend_Db_Exception $e) {
                		
						// Trata os erros retornados pela excepetion                		
                		switch ($e->getCode()) {
                			                			
                			case "28003" :  $msgFim = strpos(substr ($e->getMessage(), 83), "*");
                							$erro   = substr ($e->getMessage(), 83, $msgFim);
                				break;
                			default      : $erro = $e->getMessage();                			                			
                		}
                		
                		// Monta a mensagem
                		$msg = array("msg"    => array($erro),
                		             "titulo" => "ERRO",
                		             "tipo"   => 3);
                		
                		// Lança a mensagem do sistema
                		$mensagemSistema->send(serialize($msg));
                	}
                	                	
                	
                } else {
                    
                    // Monta a mensagem
                    $msg = array("msg" => array("Senhas incompatíveis."), 
                                 "titulo" => "Alerta",
                                 "tipo"   => 4);
                    
                    // Lança a mensagem do sistema
                    $mensagemSistema->send(serialize($msg));
                }
                
            } else {
                
                // Monta a mensagem
                $msg = array("msg" => array("Senha atual incorreta."), 
                             "titulo" => "Alerta",
                             "tipo"   => 4);
                
                // Lança a mensagem do sistema
                $mensagemSistema->send(serialize($msg));
            }
        }
        
        // Verifica se não deu erro ao trocar a senha
        if(count($msg) == 0) {
            // Inicializa a classe de autenticação
            $autenticacao = Zend_Auth::getInstance();
    
            // Captura e identidade do usuário
            $identidade_usuario = $autenticacao->getIdentity();
            
            // Define os filtros para a atualização
            $where = $usuarios->addWhere(array("CD_USUARIO = ?" => $identidade_usuario))
                              ->getWhere();
            
            // Verifica se esta sendo passado alguma quantidade de linhas
            $params['qt_linhas'] = $params['qt_linhas'] != "" ? $params['qt_linhas'] : '15';  
            
            $dados = array(
                "QT_LINHAS" => $params['qt_linhas'] ,
                "NO_USUARIO" => $params['no_usuario'],
                "EMAIL" => $params['email']
            );
                              
            // Atualiza o número de linhas do usuário
            $update = $usuarios->update($dados, $where);
            
            // Se ocorreu tudo bem, altera os dados da sessão
            if($update) {
                $sessao->perfil->QT_LINHAS = $params['qt_linhas'];
                $sessao->perfil->NO_USUARIO = $params['no_usuario'];
                $sessao->perfil->EMAIL = $params['email'];
                 
            }
            $params['no_usuario'] = $sessao->perfil->NO_USUARIO;
            $params['email'] = $sessao->perfil->EMAIL;
        }
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params);
        
    }

    /**
     * Metodo excluir
     * objetivo: utilizado para a operação de DELETE
     */
    public function excluirAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
    }

    /**
     * Metodo pesquisar
     * objetivo: utilizado para executar pesquisas
     */
    public function pesquisarAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
    }

    /**
     * Metodo selecionar
     * objetivo: utilizado para selecionar um registro a partir de uma listagem
     */
    public function selecionarAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
    }

    /**
     * Metodo relatorio
     * objetivo: utilizado para gerar um relatorio a partir de uma listagem
     */
    public function relatorioAction() { 
        // Recupera os parametros da requisição
        $params = $this->_request->getParams();
    }

}