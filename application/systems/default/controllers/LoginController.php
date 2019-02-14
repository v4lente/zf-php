<?php

/**
 *
 * Classe LoginController
 *
 * Esta classe é responsável pela autenticação
 * do usuário no sistema.
 *
 * @category   Marca Sistemas
 * @package    IndexController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 * @version    $Id: LoginController.php 0 2009-11-10 17:25:00 marcio $
 */

/**
 * @category   Marca Sistemas
 * @package    IndexController
 * @copyright  Copyright (c) 1991-2009 Marca Sistemas. (http://www.marca.com.br)
 * @license    http://marca.com.br/licenca/
 */
class LoginController extends Marca_Controller_Abstract_Comum {

    /**
     * Objeto com os dados do usuário autenticado
     *
     * @var Zend_Auth
     */
    protected $_autenticacao = null;
    private $_data = null;

    /**
     * 
     * Enter description here ...
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     */
    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

        parent::__construct($request, $response, $invokeArgs);
    }

    /**
     * (non-PHPdoc)
     * @see library/Zend/Controller/Zend_Controller_Action#init()
     */
    public function init() {

        Zend_Loader::loadClass('Marca_Auth_Adapter_Autentica');
        Zend_Loader::loadClass("UsuarioModel");

        // Inicializa a classe de autenticação
        $this->_autenticacao = Marca_Auth::getInstance();

        $captcha = new Marca_Captcha_Image();

        $captcha->setFont("arial.ttf")
                ->setImgDir("images/captcha/")
                ->setImgUrl($this->view->baseUrl("/public/images/captcha/"))
                ->setWidth("72px")
                ->setHeight("40px")
                ->setFontSize('15')
                ->setDotNoiseLevel('1')
                ->setLineNoiseLevel('1')
                ->setWordlen(4);

        $this->view->captcha = $captcha;

        Zend_Registry::set("captcha", $captcha);

        // Define o layout a ser utilizado
        $this->_helper->layout->setLayout("layout-login");
    }

    /**
     * Metodo index
     * metodo do padrão da zend
     * objetivo: é chamado caso nenhuma action seja definida
     */
    public function indexAction() {

        // Captura os dados da requisição
        $params = $this->_request->getParams();

        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Caso exista uma sessão aberta
        if ($this->_autenticacao->getIdentity() != "" &&
            $this->_autenticacao->getIdentity() != "PUBLICO" &&
            isset($sessao->_menuSistemas)) {

            if ($sessao->linkUrl != "") {

                $linkUrl = $sessao->linkUrl;
                // Redireciona para o menu
                $this->_redirect($sessao->linkUrl);
                unset($sessao->linkUrl);
				
            } else {
                // Redireciona para o menu
                $this->_redirect("menu/index");
            }
        }
    }

    /**
     * Método que faz a autenticação
     *
     * Este método vai verificar se o usuário está logado ou não, mantendo-o na tela
     * de login ou jogando-o para o menu principal
     *
     * @return void
     */
    public function logarAction() {

        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Caso exista uma sessão aberta
        if ($this->_autenticacao->getIdentity() != "" &&
                $this->_autenticacao->getIdentity() != "PUBLICO" &&
                isset($sessao->_menuSistemas)) {

			if (isset($sessao->linkUrl)){
				// Redireciona para o menu
				$this->_redirect($sessao->linkUrl);
				unset ($sessao->linkUrl);
			}else{
				// Redireciona para o menu
				$this->_redirect("menu/index");
			}
			
        } else {

            // Captura os dados da requisição
            $params = $this->_request->getParams();

            // Trata os dados de retorno
            $cd_usuario = strtoupper(trim($params['cd_usuario']));

            // Se a senha não for passada, pressupõe-se que o usuário é publico
            if (isset($params['senha']) && $params['senha'] != "") {
                $senha = strtoupper(trim($params['senha']));
            } else {
                $senha = "P";
                $this->_request->setParam("senha", $senha);
            }

            // Captura o captcha
            if (isset($params['captcha']['input'])) {
                $params['captcha']['input'] = trim($params['captcha']['input']);
            }

            // Desabilitado o captcha problema com o IEXPLORE
            //$params['captcha']['input'] = true;
            // Se for um usuário publico não precisa passar captcha
            if (($cd_usuario != "" && $senha != "" && $params['captcha']['input'] != "") ||
                    ($cd_usuario == "PUBLICO" && $senha == "P" )) {

                // Desabilita momentaneamente o captcha para os navegadores IE
                if (strpos(strtoupper($_SERVER["HTTP_USER_AGENT"]), "MSIE") === false) {

                    // Se for passado o captcha, valida ele
                    if (isset($params['captcha']['input']) && $params['captcha']['input'] != "") {

                        $retorno = Zend_Registry::get("captcha")->isValid($params['captcha']['input'], $params['captcha']);

                        if (!$retorno) {
                            $captcha = false;
                        } else {
                            $captcha = true;
                        }
                    } else {
                        $captcha = true;
                    }
                } else {
                    $captcha = true;
                }

                // Desabilitado por problema com o IEXPLORE
                //$captcha = true;

                if ($cd_usuario != "" && $senha != "" && $captcha) {

                    // Passa o login e a senha para logar no banco
                    $resultado = $this->_autenticacao->authenticate(new
                            Marca_Auth_Adapter_Autentica($cd_usuario, $senha));

                    // Retorna o código de validação
                    switch ($resultado->getCode()) {

                        case Marca_Auth_Result::FAILURE_CREDENTIAL_INVALID:

                        case Marca_Auth_Result::FAILURE_ACCOUNT_USER_LOCKED:

                        case Marca_Auth_Result::FAILURE_UNCATEGORIZED:

                            // Pega do registro a fila
                            $mensagemSistema = Zend_Registry::get("mensagemSistema");

                            // Parametro para ser gerada a mensagem
                            $msg = array("msg" => $resultado->getMessages(),
                                "titulo" => "ATENÇÃO",
                                "tipo" => $resultado->getTipo());
                            // Gera a mensagem
                            $mensagemSistema->send(serialize($msg));

                            // Remove o usuário da sessão
                            $this->_autenticacao->clearIdentity();

                            break;

                        default:

                            // Instancia o usuário
                            $usuario = new UsuarioModel();
                            $usuarioLinha = $usuario->find(strtoupper($this->_autenticacao->getIdentity()));
                            $usuarioLogado = $usuarioLinha->current();

                            // Grava a autenticação do usuário em sessão
                            $sessao->_autenticacao = $this->_autenticacao;

                            // Retorna o perfil do usuário
                            $sessao->perfil = $usuarioLogado->getPerfil();

                            // Grava a senha do usuario no perfil
                            $sessao->perfil->SENHA = md5($senha);
                            $sessao->perfil->SENHA_REAL = $senha;

                            // Seta o parametro para a view
                            $this->view->contaExpirada = 0;


                            // Se a conta do usuário expirou
                            if ($usuarioLogado->verificaContaExpirada()) {

                                // Seta o parametro para a view
                                $this->view->contaExpirada = 1;

                                // Remove o usuário da sessão
                                $this->_autenticacao->clearIdentity();

                                break;
                            }

                            // Verifica se o usuário esta ATIVO no sistema
                            if ($usuarioLogado->FL_ATIVO == 0) {

                                // Pega do registro a fila
                                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                                // Parametro para ser gerada a mensagem
                                $msg = array("msg" => array("Usuário encontra-se inativo. Contate o CPD."),
                                    "titulo" => "ATENÇÃO",
                                    "tipo" => 3);
                                // Gera a mensagem
                                $mensagemSistema->send(serialize($msg));

                                // Remove o usuário da sessão
                                $this->_autenticacao->clearIdentity();

                                break;
                            }

                            // Se a ultima vez que usuário acessou o sistema e passou o período permitido
                            $vcto_acesso = $usuarioLogado->verificaPeriodoLimiteAcesso();
                            if ($vcto_acesso !== false) {

                                // Atualiza a data do ultimo acesso do usuário
                                $usuario->setShowMessage(false);
                                $usuario->update(array("FL_ATIVO" => 0), "CD_USUARIO = '{$cd_usuario}'");

                                // Pega do registro a fila
                                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                                // Parametro para ser gerada a mensagem
                                $msg = array("msg" => array("Seu usuário não acessou o sistema nos últimos {$vcto_acesso} dias e por questões de segurança foi bloqueado. Entre em contato com o setor de Informática da SUPRG para realizar o desbloqueio."),
                                    "titulo" => "ATENÇÃO",
                                    "tipo" => 3);
                                // Gera a mensagem
                                $mensagemSistema->send(serialize($msg));

                                // Remove o usuário da sessão
                                $this->_autenticacao->clearIdentity();

                                break;
                            }

                            // Atualiza a data do ultimo acesso do usuário                        
                            $usuarioLogado->DT_ULT_ACESSO = new Zend_Db_Expr("sysdate");
                            $usuarioLogado->save();

                            // Verifica o login do usuário, se não tem alguma sessão em aberto para o mesmo CD_USUARIO
                            try {
                                // Executa as operações para o registro na tabela log sessão
                                $this->_helper->LogSessao->registraLogin();
                            } catch (Zend_Db_Exception $e) {
                                echo $e->getMessage();
                                die;
                            }

                            // Controla o número de páginas
                            if (is_null($sessao->perfil->QT_LINHAS) || $sessao->perfil->QT_LINHAS == 0) {
                                $sessao->perfil->QT_LINHAS = 10;
                            }

                            // Controla o idioma
                            if (is_null($sessao->perfil->CD_IDIOMA) || $sessao->perfil->CD_IDIOMA == "") {
                                $sessao->perfil->CD_IDIOMA = 'POR';
                            }

                            // Criptografa os parametros
                            $cd_usuario64 = base64_encode($cd_usuario);
                            $senha64 = base64_encode($senha);
                            $baseUrl = $this->getRequest()->getBaseUrl();
                            $baseUrl64 = base64_encode($baseUrl);
							
                            // Se existir o módulo vai diretamente para ele.
							// A variável crypt ficou como herança de outros desenvolvimentos,
							// mas que será usada para este propósito.
                            if ($params["_module"] != "") {
								$crypt = "&crypt=" . $params["_module"] . "/" . $params["_controller"] . "/" . $params["_action"] . "/";
								// Captura os parâmetros extras
								foreach($params as $indice => $valor) {
									if(strpos($indice, "param_") === 0 && $valor != "") {
										$indice = substr($indice, 6, strlen($indice));
										$valor  = str_replace("/","_barra_", $valor);
										$crypt .= $indice."/".$valor."/";
									}
								}
							}
							
							// Limpa o crypt para não extourar o tamanho 
							// permitido para dados passados por get
							if($sessao->linkUrl != "") {
								// Redireciona para o sistema antigo
								$caminhoSistemaAntigo = "Location: {$baseUrl}/../login_act_zend.php" .
										"?baseUrl=" . $baseUrl64 .
										"&cd_usuario=" . $cd_usuario64 .
										"&senha=" . $senha64 .
										"&ambiente=" . APPLICATION_ENV .
										"&linkUrl=" . $sessao->linkUrl .
										"&crypt=0";
							} else {
								// Redireciona para o sistema antigo
								$caminhoSistemaAntigo = "Location: {$baseUrl}/../login_act_zend.php" .
										"?baseUrl=" . $baseUrl64 .
										"&cd_usuario=" . $cd_usuario64 .
										"&senha=" . $senha64 .
										"&ambiente=" . APPLICATION_ENV .
										$crypt;
							}
							
                            // Veridica se tem paralisações para o usuário que pertence ao grupo RFB                                                                     	
                            if ($usuarioLogado->verificaParalisacoes()) {

                                // Parametro para ser gerada a mensagem
                                $msg = array("msg" => array("Existem paralisações programadas dos Sistemas Informatizados."),
                                    "titulo" => "ATENÇÃO",
                                    "tipo" => 4);

                                // Registra em sessão para pegar no controller menu
                                // E gerar a mensagem na tela
                                $sessao->mensagemParalisacao = $msg;
                            }

                            // Verifica se o usuário tem permissão para acessar o sistema antigo, 
                            // caso contrário entra somente nas transações novas
                            //if($usuarioLogado->verificaPermissaoSistemaAntigo()) {
                            header($caminhoSistemaAntigo);
                            //} else {
                            //$this->_redirect("menu/index"); 
                            //}	
                            break;
                    }

                    // Reenvia os valores para o formulário
                    $this->_helper->RePopulaFormulario->repopular($params, "lower");
                } else {

                    // Pega do registro a fila
                    $mensagemSistema = Zend_Registry::get("mensagemSistema");

                    // Parametro para ser gerada a mensagem
                    $msg = array("msg" => array("Dados de acesso inválidos."),
                        "titulo" => "ATENÇÃO",
                        "tipo" => 4);

                    // Registra a mensagem do sistema
                    $mensagemSistema->send(serialize($msg));

                    // Reenvia os valores para o formulário
                    $this->_helper->RePopulaFormulario->repopular($params, "lower");

                    // Desloga do sistema
                    $this->_forward('index');
                }
            } else {

                // Redireciona para a página inicial
                $this->_redirect('login/sair');
            }
        }
    }

    /**
     * Método que faz a autenticação pegando os dados do sistema antigo
     *
     * Este método vai verificar se o usuário está logado ou não
     *
     * @return void
     */
    public function logarAdapterAction() {

        // Desabilita o layout padrão
        $this->_helper->layout->disableLayout();

        // Não deixa a view renderizar
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // Inicializa a classe de autenticação
        $this->_autenticacao = Zend_Auth::getInstance();

        if (isset($_SESSION['CD_USUARIO'])) {

            // Passa o login e a senha para logar no banco
            $resultado = $this->_autenticacao->authenticate(
                    new Marca_Auth_Adapter_Autentica($_SESSION['CD_USUARIO'], $_SESSION['SENHA'])
            );

            // Captura a sessão
            $sessao = new Zend_Session_Namespace('portoweb');
            $config = new Zend_Config($sessao->arrayconfig);

            $db = Zend_Db::factory($config->database);
            $db->getConnection();

            // Seta o modo de retorno das consultas
            $db->setFetchMode(Zend_Db::FETCH_OBJ);

            Zend_Db_Table::setDefaultAdapter($db);
            Zend_Registry::set('db', $db);

            $usuario = new UsuarioModel();
            $usuarioLinha = $usuario->find(strtoupper($this->_autenticacao->getIdentity()));
            $usuarioLogado = $usuarioLinha->current();

            // Retorna o perfil do usuário
            $sessao->perfil = $usuarioLogado->getPerfil();

            // Executa as operações para o registro na tabela log sessão
            $this->_helper->LogSessao->registraLogin();

            // Controla o número de páginas
            if (is_null($sessao->perfil->QT_LINHAS) || $sessao->perfil->QT_LINHAS == 0) {
                $sessao->perfil->QT_LINHAS = 10;
            }

            // Controla o idioma
            if (is_null($sessao->perfil->CD_IDIOMA) || $sessao->perfil->CD_IDIOMA == "") {
                $sessao->perfil->CD_IDIOMA = 'POR';
            }
        }

        /* -------------------------------------------------------------------------------- */
        // Executa a consulta
        $menu = $usuarioLogado->getMenuUsuario();

        // Pega os controllers que o usuário tem permissão para acessar.
        $grupoController = array();
        foreach ($menu as $linha) {
            if (trim($linha->NO_CONTROLLER) != '') {
                $grupoController[] = trim($linha->NO_CONTROLLER);
            }
        }
        // Grava na sessão o perfil do usuário
        $sessao = new Zend_Session_Namespace('portoweb');

        // Registra o array com os nomes dos controllers em sessão.
        $sessao->grupo_controller = $grupoController;
        /* ------------------------------------------------------------------------------------ */

        // Pega o base url e separa pelas barras
        $baseUrl = Zend_Controller_Front::getInstance()->getRequest()->getBaseUrl();
        $divBaseUrl = explode("/", $baseUrl);
        $this->_redirect('http://' . $_SERVER["HTTP_HOST"] . '/' . $divBaseUrl[1] . '/menu.php');
    }

    /**
     * Desloga do banco
     *
     * @return void
     */
    public function sairAction() {

        // Captura a sessão
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura os dados da requisição
        $params = $this->_request->getParams();
                
        // Se existir na sessão carrega o plugin
        if(isset($sessao->db)) {
            
            try {
                // Executa as operações para o registro na tabela log sessão
                $this->_helper->LogSessao->registraLogoff();

                // Captura a instância do banco
                $db = Zend_Registry::get("db");

                // Mata a conexão do banco
                $db->closeConnection();

                // Verifica se o usuário foi forçado a sair do sistema,
                // devido ao seu logim estar sendo utilizado em outra estação de trabalho
                // ou seu tempo de inatividade tenha excedido
                if (isset($params['msg-sessao'])) {

                    // Pega uma das mensagens de tratamento da sessao do usuário
                    switch ($params['msg-sessao']) {
                        // EXPIRADA: tempo de inatividade esgotado
                        case "expirada" :
                            $texto = array("Sessão finalizada, tempo de inatividade expirou.");
                            break;
                        // SAIDA-FORÇADA: O mesmo usuário se logou em estações de trabalho diferentes
                        case "saida-forcada" :
                            $texto = array("Sessão desconectada, seu usuário está sendo utilizado em outra estação de trabalho.");
                            break;
                    }

                    // Pega do registro a fila
                    $mensagemSistema = Zend_Registry::get("mensagemSistema");

                    // Parametro para ser gerada a mensagem
                    $msg = array("msg"      => $texto,
                                 "titulo"   => "ATENÇÃO",
                                 "tipo"     => 3,
                                 "callback" => "Base.sair();");
                    // Gera a mensagem
                    $mensagemSistema->send(serialize($msg));
                }
            } catch (Zend_Exception $e) {
                //echo $e->getMessage(); die;
            }
			
			if($sessao->linkUrl != "") {
				$linkUrl = $sessao->linkUrl;
			}
			
            // Remove o usuário da sessão
            $this->_autenticacao->clearIdentity();
			unset($sessao->_autenticacao);
			$sessao->unsetAll();
			unset($sessao);
            			
			// Captura a sessão
			$sessao = new Zend_Session_Namespace('portoweb');
			
			// Cria novamente a url
			$sessao->linkUrl = $linkUrl;

        }

    }

    /**
     * 
     * Altera a senha do usuário que esta com a senha bloqueada
     * via ajax
     * 
     * @return void
     */
    public function alteraSenhaXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if ($this->_request->isXmlHttpRequest()) {
            
            // Recupera a instância da base de dados
            // Inicializa a classe de autenticação
            $this->_autenticacao = Zend_Auth::getInstance();

            // Passa o login e a senha para logar no banco
            $resultado = $this->_autenticacao->authenticate(
                    new Marca_Auth_Adapter_Autentica("autenticacao", "a")
            );

            // Instancia o banco de dados
            $db = Zend_Registry::get("db");

            $retorno = array(false, "Operação não realizada.");

            // Captura a sessão
            $sessao = new Zend_Session_Namespace('portoweb');

            // Pega os parametros da requisição
            $params = $this->_request->getParams();

            if (trim($params["_cd_usuario"]) != "") {

                // Compara a senha atual com a passada
                if ($sessao->perfil->SENHA == md5(strtoupper($params["_senha"]))) {

                    if (strtoupper($params["nova_senha"]) == strtoupper($params["nova_senha2"])) {

                        try {
                            $usuario = strtoupper($params["_cd_usuario"]);

                            // Inicia a transação.
                            $db->beginTransaction();

                            // Altera a senha do usuário
                            // Aqui é possível executar mais de uma query
                            $sql = "ALTER USER {$usuario} IDENTIFIED BY {$params["nova_senha"]}";

                            $db->query($sql);
                            $db->query("UPDATE USUARIO SET DT_ALT_SENHA = sysdate WHERE CD_USUARIO = '{$usuario}'");

                            // Executa as transações
                            $db->commit();

                            $retorno = array(true, "Operação realizada com sucesso.");
                        } catch (Zend_Db_Exception $e) {

                            // será desfeito a query e será mostrado o erro
                            $db->rollBack();

                            switch ($e->getCode()) {
                                case 20001:
                                case 20002:
                                case 20003:
                                    $retorno = array(false, "Senha não foi alterada. \n" . $e->getMessage());
                                    break;
                                case 988:
                                    $retorno = array(false, "Formato da senha inválido.");
                                    break;
                                default:
                                    $retorno = array(false, "Senha já foi utilizada\n tente uma senha diferente.");
                            }
                        }
                    } else {
                        $retorno = array(false, "As Senhas devem ser identicas.");
                    }
                } else {
                    $retorno = array(false, "Senha atual inválida.");
                }
            } else {
                $retorno = array(false, "Nome de usuário não informado");
            }

            $this->_helper->json(Marca_ConverteCharset::converter($retorno), true);
        }
    }

    /**
     *
     * Gera o captcha para os ip fora do dominio da rede
     * @param string $ip
     */
    protected function captcha($ip = null) {

        // Verifica o dominio do ip de origem
        if ($_SERVER['REMOTE_ADDR'] !== "10.76.1.1") {

            // Cria o objeto CAPTCHA
            $captcha = new Zend_Captcha_Image();

            // Seta as configurações
            $captcha->setFont("arial.ttf")
                    ->setImgDir("images/captcha/")
                    ->setImgUrl("{$this->view->baseUrl()}/public/images/captcha/")
                    ->setFontSize(30);

            $this->view->captcha = $captcha;

            $params = $this->_request->getParams();

            if ($this->getRequest()->isPost()) {
                if ($captcha->isValid($params['captcha'])) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Metodo novo
     * Ação da tela para solicitar nova senha
     */
    public function novoAction() {
        
        // captura os parâmetros da requisição
        $params = $this->_request->getParams();
        
        // Reenvia os valores para o formulário
        $this->_helper->RePopulaFormulario->repopular($params, "lower");
    }

    /**
     * Metodo aviso_email
     * Exibe as mensagens de email não cadastrado ou de email enviado com sucesso
     */
    public function avisoAction() {
        
    }
    /**
     * Metodo avisose der erro na alteracao de senha
     * Exibe as mensagens de email não cadastrado ou de email enviado com sucesso
     */
    public function avisoerroAction() {
        
    }
    
   

    /**
     *  verifica se o usuario que esta solicitando tem e-mail cadastrado
        se tiver altera a senha e envia e-mail para futura alteração
     *
     * @return void
     */
    public function esquecisenhaAction() {

       
        // Recupera a instância da base de dados
        // Inicializa a classe de autenticação
        $this->_autenticacao = Zend_Auth::getInstance();
        
        // Passa o login e a senha para logar no banco
        $resultado = $this->_autenticacao->authenticate(
                new Marca_Auth_Adapter_Autentica("autenticacao", "a")
        );

        
        // Instancia o banco de dados
        $db = Zend_Registry::get("db");

        // Recupera os parametros da requisição
        $params = $this->_request->getParams();

        // Instancia a classe modelo
        $usuario = new UsuarioModel();

        // Define os filtros para a consulta
        $where = $usuario->addWhere(array("U.CD_USUARIO = ?" => $params["cd_usuario"]))
                ->addWhere(array("U.FL_ATIVO = 1"))
                ->addWhere(array("U.CPF = ?" => $params["cpf"]))
                ->getWhere();

        // Monta query
        $select = $usuario->select()
                ->setIntegrityCheck(false)
                ->from(array("U" => "USUARIO"), array("U.NO_USUARIO, U.EMAIL"))
                ->where($where);

        // Executa a query
        $resPesquisa = $usuario->fetchRow($select);
        $novo_valor = "";
        $digitos = "ABCDEFGHJKLMNPQRSTUVWYZ123456789";

        srand((double) microtime() * 1000000);
        for ($i = 0; $i < 8; $i++) {
            $novo_valor.= $digitos[rand() % strlen($digitos)];
        }

        // Senha Temporária
        $senha_pura = strtoupper($novo_valor);
        $erro = 0;
       
        if (count($resPesquisa) > 0 && $resPesquisa->EMAIL !="") {

            try {

                $usuario = strtoupper($params["cd_usuario"]);

                // Inicia a transação.
                $db->beginTransaction();

                // Altera a senha do usuário
                // Altero a senha do usuário no Banco de Dados
                $sql = "ALTER USER {$usuario} IDENTIFIED BY ". $senha_pura;

                $db->query($sql);
                
                //atualiza a data de alteração de senha, para fazer que quando o usuário log com a nova senha já entre na tela de alteração de senha 
                $db->query("UPDATE USUARIO SET DT_ALT_SENHA = sysdate -370 WHERE CD_USUARIO = '{$usuario}'");

               

               
            } catch (Zend_Db_Exception $e) {
                // será desfeito a query e será mostrado o erro
                $erro = 1;
               
            }
        
       
            try {

                $baseUrl = $this->getRequest()->getBaseUrl();
                $baseUrl = "http://" . $_SERVER["SERVER_NAME"] . $baseUrl;

                $link = "<a href='{$baseUrl}/login/'>Clique aqui</a> para acessar o sistema com sua nova senha e aproveite para trocá-la.";

                // Função de envio de email
                $mail = new Marca_Mail();

                 //seta o e-mail do usuario
                $mail->setFrom("portoriogrande@portoriogrande.com.br");
				//seta o e-mail do usuario
                $mail->addTo(trim($resPesquisa->EMAIL));
                
                // Assunto do e-mail
                $mail->setSubject("Senha Temporária " . date("d/m/Y"));

                // Corpo do email
                $mail->setBodyHtml("Olá " . $resPesquisa->NO_USUARIO . " <br /> <br /> Segue abaixo os seus dados para recuperar o acesso. <br /> <br />  Usuário: " . $params["cd_usuario"] . " <br /> <br /> Senha Atual: ".$senha_pura."<br /> <br />" . $link);

                $envia = $mail->send();
                // Executa as transações
                $db->commit();
                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                $msg = array("msg" => array("Email enviado com sucesso!"),
                    "titulo" => "ATENÇÃO",
                    "tipo" => 2);

                // Mostra mensagem para o usuário
                $mensagemSistema->send(serialize($msg));
                 // Mostra mensagem para o usuário
                
                 $this->_forward("aviso", null, null, $params);
           
            } catch (Exception $e) {
                 $db->rollBack();
                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                $msg = array("msg" => array("Problemas de configuração do servidor de Saída de Emails (SMTP) ao enviar email. Entre em contato com o pessoal de Suporte CPD!"),
                    "titulo" => "ATENÇÃO",
                    "tipo" => 3);

                // Mostra mensagem para o usuário
                $mensagemSistema->send(serialize($msg));
            }
            
        }else{
             $db->rollBack();
             $mensagemSistema = Zend_Registry::get("mensagemSistema");

                $msg = array("msg" => array(" Senha não foi alterada. Entre em contato com o pessoal do Suporte CPD!"),
                    "titulo" => "ATENÇÃO",
                    "tipo" => 3);

                // Mostra mensagem para o usuário
                $mensagemSistema->send(serialize($msg));
                $this->_forward("avisoerro", null, null, $params);
        }
        
       
        // Libera a memória
        unset($usuario);
        unset($mail);
    }

    /**
     * Verifica se o usuario tem email para recuperar a senha
     * caso não tenha não será permitido a recuperação de senha automatico
     *
     * @return JSON
     */
    public function verificaEmailUsuarioXAction() {

        // Verifica se arrequisição foi passada por Ajax
        if ($this->_request->isXmlHttpRequest()) {


            try {

                // Inicializa a classe de autenticação
                $this->_autenticacao = Zend_Auth::getInstance();

                // Passa o login e a senha para logar no banco
                $resultado = $this->_autenticacao->authenticate(
                        new Marca_Auth_Adapter_Autentica("autenticacao", "a")
                );

                //$db = Zend_Registry::get("db");
                // Captura a sessão
               // $sessao = new Zend_Session_Namespace("portoweb");

                // Captura os parametros passados por GET
                $params = $this->getRequest()->getParams();

                // "Corrige" os valores dos parâmetros caso possuem caracteres fora do escopo do UTF8
                foreach ($params as &$value) {
                    $value = utf8_decode($value);
                }

                // Array de caracteres especiais
                $var = array(" ", "_", "/", "-", ":", ".");

                $params["cpf"] = str_replace($var, "", $params["cpf"]);

                // Instancia a classe modelo
                $usuario = new UsuarioModel();

                // Define os filtros para a cosulta
                $where = $usuario->addWhere(array("U.CD_USUARIO = ?" => $params["cd_usuario"]))
                        ->addWhere(array("U.CPF = ?" => $params["cpf"]))
                         ->addWhere(array("U.FL_ATIVO = 1"))
                        ->getWhere();

                // Monta a busca
                $select = $usuario->select()
                        ->setIntegrityCheck(false)
                        ->from(array("U" => "USUARIO"), array("U.EMAIL", "U.CD_USUARIO"))
                        ->where($where);

                // Executa a query
                $resConsulta = $usuario->fetchRow($select);

                $linha = array();

                if (count($resConsulta) > 0) {
                    // Retorna o email do usuário
                    $linha["EMAIL"] = $resConsulta->EMAIL;
                    $linha["CD_USUARIO"] = $resConsulta->CD_USUARIO;
                }

                // Retorna os dados por json
                $this->_helper->json(Marca_ConverteCharset::converter($linha), true);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }
        }
    }

}