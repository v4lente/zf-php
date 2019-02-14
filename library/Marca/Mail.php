<?php
/**
 * Created on 19/04/2012
 *
 * Classe responsável por mandar e-mails pela aplicação.
 *
 * @filesource
 * @author			David Valente, Márcio Souza Duarte, Bruno Teló
 * @copyright		Copyright 2012 Marca
 * @package			zendframework
 * @subpackage		zendframework.library.marca
 * @version			1.0
 */

class Marca_Mail extends Zend_Mail
{

	protected $_ambiente 	  = null;
	protected $_desenvolvedor = null;
	protected $_email		  = null;

    /**
     * Public constructor
     *
     * @param string $charset
     */
    public function __construct($charset = 'iso-8859-1')
    {
        parent::__construct($charset);

        $config = new Zend_Config_Ini('../application/configs/application.ini', 'email');

        //echo APPLICATION_ENV. '-'. APPLICATION_DEV;

        $this->_ambiente = APPLICATION_ENV;
        $this->_desenvolvedor = APPLICATION_DEV;
        $desenvolvedor = APPLICATION_DEV;
        $this->_email = $config->$desenvolvedor;

        //echo $this->_ambiente.'-'. $this->_desenvolvedor.'-'.$this->_email;

    }

	public function send($transport = null)
	{

//		echo $this->_ambiente.'-'. $this->_desenvolvedor.'-'.$this->_email;

		if (strtolower($this->_ambiente) != 'producao') {

			if (strtolower($this->_ambiente) == 'desenv') {

				$this->clearRecipients();
				$this->addTo($this->_email);
	
				$subject = $this->getSubject();
				$this->clearSubject();
				$this->setSubject('Teste efetuado por '. $this->_desenvolvedor .' - '. $subject);
	
				$this->clearFrom();
				$this->setFrom($this->_email);

			}elseif (strtolower($this->_ambiente) == 'suporte') {

				$recipients = $this->getRecipients();

				$emails = '|';
				foreach ($recipients as $k=>$v){
					$emails .= $v . '|';
				}

				$this->clearRecipients();
				$this->addTo("marca@portoriogrande.com.br");
			
				$subject = $this->getSubject();
				$this->clearSubject();
				$this->setSubject($subject . ". Teste realizado no Suporte (Web). Seria enviado para ".$emails);

				$this->clearFrom();
				$this->setFrom("marca@portoriogrande.com.br");

			}

		}

		parent::send($transport);

	}

}