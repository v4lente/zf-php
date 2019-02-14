<?php

/**
 *
 * Classe LoginController
 *
 * Esta classe � respons�vel pela autentica��o
 * do usu�rio no sistema.
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
     * Objeto com os dados do usu�rio autenticado
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

        // Inicializa a classe de autentica��o
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
     * metodo do padr�o da zend
     * objetivo: � chamado caso nenhuma action seja definida
     */
    public function indexAction() {

        // Captura os dados da requisi��o
        $params = $this->_request->getParams();

        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Caso exista uma sess�o aberta
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
     * M�todo que faz a autentica��o
     *
     * Este m�todo vai verificar se o usu�rio est� logado ou n�o, mantendo-o na tela
     * de login ou jogando-o para o menu principal
     *
     * @return void
     */
    public function logarAction() {

        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Caso exista uma sess�o aberta
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

            // Captura os dados da requisi��o
            $params = $this->_request->getParams();

            // Trata os dados de retorno
            $cd_usuario = strtoupper(trim($params['cd_usuario']));

            // Se a senha n�o for passada, pressup�e-se que o usu�rio � publico
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
            // Se for um usu�rio publico n�o precisa passar captcha
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

                    // Retorna o c�digo de valida��o
                    switch ($resultado->getCode()) {

                        case Marca_Auth_Result::FAILURE_CREDENTIAL_INVALID:

                        case Marca_Auth_Result::FAILURE_ACCOUNT_USER_LOCKED:

                        case Marca_Auth_Result::FAILURE_UNCATEGORIZED:

                            // Pega do registro a fila
                            $mensagemSistema = Zend_Registry::get("mensagemSistema");

                            // Parametro para ser gerada a mensagem
                            $msg = array("msg" => $resultado->getMessages(),
                                "titulo" => "ATEN��O",
                                "tipo" => $resultado->getTipo());
                            // Gera a mensagem
                            $mensagemSistema->send(serialize($msg));

                            // Remove o usu�rio da sess�o
                            $this->_autenticacao->clearIdentity();

                            break;

                        default:

                            // Instancia o usu�rio
                            $usuario = new UsuarioModel();
                            $usuarioLinha = $usuario->find(strtoupper($this->_autenticacao->getIdentity()));
                            $usuarioLogado = $usuarioLinha->current();

                            // Grava a autentica��o do usu�rio em sess�o
                            $sessao->_autenticacao = $this->_autenticacao;

                            // Retorna o perfil do usu�rio
                            $sessao->perfil = $usuarioLogado->getPerfil();

                            // Grava a senha do usuario no perfil
                            $sessao->perfil->SENHA = md5($senha);
                            $sessao->perfil->SENHA_REAL = $senha;

                            // Seta o parametro para a view
                            $this->view->contaExpirada = 0;


                            // Se a conta do usu�rio expirou
                            if ($usuarioLogado->verificaContaExpirada()) {

                                // Seta o parametro para a view
                                $this->view->contaExpirada = 1;

                                // Remove o usu�rio da sess�o
                                $this->_autenticacao->clearIdentity();

                                break;
                            }

                            // Verifica se o usu�rio esta ATIVO no sistema
                            if ($usuarioLogado->FL_ATIVO == 0) {

                                // Pega do registro a fila
                                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                                // Parametro para ser gerada a mensagem
                                $msg = array("msg" => array("Usu�rio encontra-se inativo. Contate o CPD."),
                                    "titulo" => "ATEN��O",
                                    "tipo" => 3);
                                // Gera a mensagem
                                $mensagemSistema->send(serialize($msg));

                                // Remove o usu�rio da sess�o
                                $this->_autenticacao->clearIdentity();

                                break;
                            }

                            // Se a ultima vez que usu�rio acessou o sistema e passou o per�odo permitido
                            $vcto_acesso = $usuarioLogado->verificaPeriodoLimiteAcesso();
                            if ($vcto_acesso !== false) {

                                // Atualiza a data do ultimo acesso do usu�rio
                                $usuario->setShowMessage(false);
                                $usuario->update(array("FL_ATIVO" => 0), "CD_USUARIO = '{$cd_usuario}'");

                                // Pega do registro a fila
                                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                                // Parametro para ser gerada a mensagem
                                $msg = array("msg" => array("Seu usu�rio n�o acessou o sistema nos �ltimos {$vcto_acesso} dias e por quest�es de seguran�a foi bloqueado. Entre em contato com o setor de Inform�tica da SUPRG para realizar o desbloqueio."),
                                    "titulo" => "ATEN��O",
                                    "tipo" => 3);
                                // Gera a mensagem
                                $mensagemSistema->send(serialize($msg));

                                // Remove o usu�rio da sess�o
                                $this->_autenticacao->clearIdentity();

                                break;
                            }

                            // Atualiza a data do ultimo acesso do usu�rio                        
                            $usuarioLogado->DT_ULT_ACESSO = new Zend_Db_Expr("sysdate");
                            $usuarioLogado->save();

                            // Verifica o login do usu�rio, se n�o tem alguma sess�o em aberto para o mesmo CD_USUARIO
                            try {
                                // Executa as opera��es para o registro na tabela log sess�o
                                $this->_helper->LogSessao->registraLogin();
                            } catch (Zend_Db_Exception $e) {
                                echo $e->getMessage();
                                die;
                            }

                            // Controla o n�mero de p�ginas
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
							
                            // Se existir o m�dulo vai diretamente para ele.
							// A vari�vel crypt ficou como heran�a de outros desenvolvimentos,
							// mas que ser� usada para este prop�sito.
                            if ($params["_module"] != "") {
								$crypt = "&crypt=" . $params["_module"] . "/" . $params["_controller"] . "/" . $params["_action"] . "/";
								// Captura os par�metros extras
								foreach($params as $indice => $valor) {
									if(strpos($indice, "param_") === 0 && $valor != "") {
										$indice = substr($indice, 6, strlen($indice));
										$valor  = str_replace("/","_barra_", $valor);
										$crypt .= $indice."/".$valor."/";
									}
								}
							}
							
							// Limpa o crypt para n�o extourar o tamanho 
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
							
                            // Veridica se tem paralisa��es para o usu�rio que pertence ao grupo RFB                                                                     	
                            if ($usuarioLogado->verificaParalisacoes()) {

                                // Parametro para ser gerada a mensagem
                                $msg = array("msg" => array("Existem paralisa��es programadas dos Sistemas Informatizados."),
                                    "titulo" => "ATEN��O",
                                    "tipo" => 4);

                                // Registra em sess�o para pegar no controller menu
                                // E gerar a mensagem na tela
                                $sessao->mensagemParalisacao = $msg;
                            }

                            // Verifica se o usu�rio tem permiss�o para acessar o sistema antigo, 
                            // caso contr�rio entra somente nas transa��es novas
                            //if($usuarioLogado->verificaPermissaoSistemaAntigo()) {
                            header($caminhoSistemaAntigo);
                            //} else {
                            //$this->_redirect("menu/index"); 
                            //}	
                            break;
                    }

                    // Reenvia os valores para o formul�rio
                    $this->_helper->RePopulaFormulario->repopular($params, "lower");
                } else {

                    // Pega do registro a fila
                    $mensagemSistema = Zend_Registry::get("mensagemSistema");

                    // Parametro para ser gerada a mensagem
                    $msg = array("msg" => array("Dados de acesso inv�lidos."),
                        "titulo" => "ATEN��O",
                        "tipo" => 4);

                    // Registra a mensagem do sistema
                    $mensagemSistema->send(serialize($msg));

                    // Reenvia os valores para o formul�rio
                    $this->_helper->RePopulaFormulario->repopular($params, "lower");

                    // Desloga do sistema
                    $this->_forward('index');
                }
            } else {

                // Redireciona para a p�gina inicial
                $this->_redirect('login/sair');
            }
        }
    }

    /**
     * M�todo que faz a autentica��o pegando os dados do sistema antigo
     *
     * Este m�todo vai verificar se o usu�rio est� logado ou n�o
     *
     * @return void
     */
    public function logarAdapterAction() {

        // Desabilita o layout padr�o
        $this->_helper->layout->disableLayout();

        // N�o deixa a view renderizar
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // Inicializa a classe de autentica��o
        $this->_autenticacao = Zend_Auth::getInstance();

        if (isset($_SESSION['CD_USUARIO'])) {

            // Passa o login e a senha para logar no banco
            $resultado = $this->_autenticacao->authenticate(
                    new Marca_Auth_Adapter_Autentica($_SESSION['CD_USUARIO'], $_SESSION['SENHA'])
            );

            // Captura a sess�o
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

            // Retorna o perfil do usu�rio
            $sessao->perfil = $usuarioLogado->getPerfil();

            // Executa as opera��es para o registro na tabela log sess�o
            $this->_helper->LogSessao->registraLogin();

            // Controla o n�mero de p�ginas
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

        // Pega os controllers que o usu�rio tem permiss�o para acessar.
        $grupoController = array();
        foreach ($menu as $linha) {
            if (trim($linha->NO_CONTROLLER) != '') {
                $grupoController[] = trim($linha->NO_CONTROLLER);
            }
        }
        // Grava na sess�o o perfil do usu�rio
        $sessao = new Zend_Session_Namespace('portoweb');

        // Registra o array com os nomes dos controllers em sess�o.
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

        // Captura a sess�o
        $sessao = new Zend_Session_Namespace('portoweb');

        // Captura os dados da requisi��o
        $params = $this->_request->getParams();
                
        // Se existir na sess�o carrega o plugin
        if(isset($sessao->db)) {
            
            try {
                // Executa as opera��es para o registro na tabela log sess�o
                $this->_helper->LogSessao->registraLogoff();

                // Captura a inst�ncia do banco
                $db = Zend_Registry::get("db");

                // Mata a conex�o do banco
                $db->closeConnection();

                // Verifica se o usu�rio foi for�ado a sair do sistema,
                // devido ao seu logim estar sendo utilizado em outra esta��o de trabalho
                // ou seu tempo de inatividade tenha excedido
                if (isset($params['msg-sessao'])) {

                    // Pega uma das mensagens de tratamento da sessao do usu�rio
                    switch ($params['msg-sessao']) {
                        // EXPIRADA: tempo de inatividade esgotado
                        case "expirada" :
                            $texto = array("Sess�o finalizada, tempo de inatividade expirou.");
                            break;
                        // SAIDA-FOR�ADA: O mesmo usu�rio se logou em esta��es de trabalho diferentes
                        case "saida-forcada" :
                            $texto = array("Sess�o desconectada, seu usu�rio est� sendo utilizado em outra esta��o de trabalho.");
                            break;
                    }

                    // Pega do registro a fila
                    $mensagemSistema = Zend_Registry::get("mensagemSistema");

                    // Parametro para ser gerada a mensagem
                    $msg = array("msg"      => $texto,
                                 "titulo"   => "ATEN��O",
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
			
            // Remove o usu�rio da sess�o
            $this->_autenticacao->clearIdentity();
			unset($sessao->_autenticacao);
			$sessao->unsetAll();
			unset($sessao);
            			
			// Captura a sess�o
			$sessao = new Zend_Session_Namespace('portoweb');
			
			// Cria novamente a url
			$sessao->linkUrl = $linkUrl;

        }

    }

    /**
     * 
     * Altera a senha do usu�rio que esta com a senha bloqueada
     * via ajax
     * 
     * @return void
     */
    public function alteraSenhaXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if ($this->_request->isXmlHttpRequest()) {
            
            // Recupera a inst�ncia da base de dados
            // Inicializa a classe de autentica��o
            $this->_autenticacao = Zend_Auth::getInstance();

            // Passa o login e a senha para logar no banco
            $resultado = $this->_autenticacao->authenticate(
                    new Marca_Auth_Adapter_Autentica("autenticacao", "a")
            );

            // Instancia o banco de dados
            $db = Zend_Registry::get("db");

            $retorno = array(false, "Opera��o n�o realizada.");

            // Captura a sess�o
            $sessao = new Zend_Session_Namespace('portoweb');

            // Pega os parametros da requisi��o
            $params = $this->_request->getParams();

            if (trim($params["_cd_usuario"]) != "") {

                // Compara a senha atual com a passada
                if ($sessao->perfil->SENHA == md5(strtoupper($params["_senha"]))) {

                    if (strtoupper($params["nova_senha"]) == strtoupper($params["nova_senha2"])) {

                        try {
                            $usuario = strtoupper($params["_cd_usuario"]);

                            // Inicia a transa��o.
                            $db->beginTransaction();

                            // Altera a senha do usu�rio
                            // Aqui � poss�vel executar mais de uma query
                            $sql = "ALTER USER {$usuario} IDENTIFIED BY {$params["nova_senha"]}";

                            $db->query($sql);
                            $db->query("UPDATE USUARIO SET DT_ALT_SENHA = sysdate WHERE CD_USUARIO = '{$usuario}'");

                            // Executa as transa��es
                            $db->commit();

                            $retorno = array(true, "Opera��o realizada com sucesso.");
                        } catch (Zend_Db_Exception $e) {

                            // ser� desfeito a query e ser� mostrado o erro
                            $db->rollBack();

                            switch ($e->getCode()) {
                                case 20001:
                                case 20002:
                                case 20003:
                                    $retorno = array(false, "Senha n�o foi alterada. \n" . $e->getMessage());
                                    break;
                                case 988:
                                    $retorno = array(false, "Formato da senha inv�lido.");
                                    break;
                                default:
                                    $retorno = array(false, "Senha j� foi utilizada\n tente uma senha diferente.");
                            }
                        }
                    } else {
                        $retorno = array(false, "As Senhas devem ser identicas.");
                    }
                } else {
                    $retorno = array(false, "Senha atual inv�lida.");
                }
            } else {
                $retorno = array(false, "Nome de usu�rio n�o informado");
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

            // Seta as configura��es
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
     * A��o da tela para solicitar nova senha
     */
    public function novoAction() {
        
        // captura os par�metros da requisi��o
        $params = $this->_request->getParams();
        
        // Reenvia os valores para o formul�rio
        $this->_helper->RePopulaFormulario->repopular($params, "lower");
    }

    /**
     * Metodo aviso_email
     * Exibe as mensagens de email n�o cadastrado ou de email enviado com sucesso
     */
    public function avisoAction() {
        
    }
    /**
     * Metodo avisose der erro na alteracao de senha
     * Exibe as mensagens de email n�o cadastrado ou de email enviado com sucesso
     */
    public function avisoerroAction() {
        
    }
    
   

    /**
     *  verifica se o usuario que esta solicitando tem e-mail cadastrado
        se tiver altera a senha e envia e-mail para futura altera��o
     *
     * @return void
     */
    public function esquecisenhaAction() {

       
        // Recupera a inst�ncia da base de dados
        // Inicializa a classe de autentica��o
        $this->_autenticacao = Zend_Auth::getInstance();
        
        // Passa o login e a senha para logar no banco
        $resultado = $this->_autenticacao->authenticate(
                new Marca_Auth_Adapter_Autentica("autenticacao", "a")
        );

        
        // Instancia o banco de dados
        $db = Zend_Registry::get("db");

        // Recupera os parametros da requisi��o
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

        // Senha Tempor�ria
        $senha_pura = strtoupper($novo_valor);
        $erro = 0;
       
        if (count($resPesquisa) > 0 && $resPesquisa->EMAIL !="") {

            try {

                $usuario = strtoupper($params["cd_usuario"]);

                // Inicia a transa��o.
                $db->beginTransaction();

                // Altera a senha do usu�rio
                // Altero a senha do usu�rio no Banco de Dados
                $sql = "ALTER USER {$usuario} IDENTIFIED BY ". $senha_pura;

                $db->query($sql);
                
                //atualiza a data de altera��o de senha, para fazer que quando o usu�rio log com a nova senha j� entre na tela de altera��o de senha 
                $db->query("UPDATE USUARIO SET DT_ALT_SENHA = sysdate -370 WHERE CD_USUARIO = '{$usuario}'");

               

               
            } catch (Zend_Db_Exception $e) {
                // ser� desfeito a query e ser� mostrado o erro
                $erro = 1;
               
            }
        
       
            try {

                $baseUrl = $this->getRequest()->getBaseUrl();
                $baseUrl = "http://" . $_SERVER["SERVER_NAME"] . $baseUrl;

                $link = "<a href='{$baseUrl}/login/'>Clique aqui</a> para acessar o sistema com sua nova senha e aproveite para troc�-la.";

                // Fun��o de envio de email
                $mail = new Marca_Mail();

                 //seta o e-mail do usuario
                $mail->setFrom("portoriogrande@portoriogrande.com.br");
				//seta o e-mail do usuario
                $mail->addTo(trim($resPesquisa->EMAIL));
                
                // Assunto do e-mail
                $mail->setSubject("Senha Tempor�ria " . date("d/m/Y"));

                // Corpo do email
                $mail->setBodyHtml("Ol� " . $resPesquisa->NO_USUARIO . " <br /> <br /> Segue abaixo os seus dados para recuperar o acesso. <br /> <br />  Usu�rio: " . $params["cd_usuario"] . " <br /> <br /> Senha Atual: ".$senha_pura."<br /> <br />" . $link);

                $envia = $mail->send();
                // Executa as transa��es
                $db->commit();
                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                $msg = array("msg" => array("Email enviado com sucesso!"),
                    "titulo" => "ATEN��O",
                    "tipo" => 2);

                // Mostra mensagem para o usu�rio
                $mensagemSistema->send(serialize($msg));
                 // Mostra mensagem para o usu�rio
                
                 $this->_forward("aviso", null, null, $params);
           
            } catch (Exception $e) {
                 $db->rollBack();
                $mensagemSistema = Zend_Registry::get("mensagemSistema");

                $msg = array("msg" => array("Problemas de configura��o do servidor de Sa�da de Emails (SMTP) ao enviar email. Entre em contato com o pessoal de Suporte CPD!"),
                    "titulo" => "ATEN��O",
                    "tipo" => 3);

                // Mostra mensagem para o usu�rio
                $mensagemSistema->send(serialize($msg));
            }
            
        }else{
             $db->rollBack();
             $mensagemSistema = Zend_Registry::get("mensagemSistema");

                $msg = array("msg" => array(" Senha n�o foi alterada. Entre em contato com o pessoal do Suporte CPD!"),
                    "titulo" => "ATEN��O",
                    "tipo" => 3);

                // Mostra mensagem para o usu�rio
                $mensagemSistema->send(serialize($msg));
                $this->_forward("avisoerro", null, null, $params);
        }
        
       
        // Libera a mem�ria
        unset($usuario);
        unset($mail);
    }

    /**
     * Verifica se o usuario tem email para recuperar a senha
     * caso n�o tenha n�o ser� permitido a recupera��o de senha automatico
     *
     * @return JSON
     */
    public function verificaEmailUsuarioXAction() {

        // Verifica se arrequisi��o foi passada por Ajax
        if ($this->_request->isXmlHttpRequest()) {


            try {

                // Inicializa a classe de autentica��o
                $this->_autenticacao = Zend_Auth::getInstance();

                // Passa o login e a senha para logar no banco
                $resultado = $this->_autenticacao->authenticate(
                        new Marca_Auth_Adapter_Autentica("autenticacao", "a")
                );

                //$db = Zend_Registry::get("db");
                // Captura a sess�o
               // $sessao = new Zend_Session_Namespace("portoweb");

                // Captura os parametros passados por GET
                $params = $this->getRequest()->getParams();

                // "Corrige" os valores dos par�metros caso possuem caracteres fora do escopo do UTF8
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
                    // Retorna o email do usu�rio
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