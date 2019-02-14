<?php
/**
 * Action Helper para criar uma captcha
 *
 * @uses Zend_View_Helper_Abstract
 *
 * @author David Valente
 *
 * @param array  request
 *
 */

class Zend_View_Helper_Captcha extends Zend_View_Helper_Abstract
{
	public function captcha($patch = "/public/images/captcha/") {

		try {
			// Verifica se esta sendo acessado de dentro da rede interna
			if($_SERVER['REMOTE_ADDR'] === "10.76.1.1") {

				// Instancia a classe
				$captcha = new Zend_Captcha_Image();

				// Seta os parametros para o objeto
				$captcha->setFont("arial.ttf")
				->setImgDir("images/captcha/")
				->setImgUrl($this->view->baseUrl().$patch)
				->setFontSize(33)
				->setWidth("203px");

				// Passa para a view o objeto captcha
				$id = $captcha->generate();

				$retCaptcha = "";
				$retCaptcha .= $captcha->render($this->view);
				$retCaptcha .= "<input type=\"text\"   name=\"captcha[input]\" />";
				$retCaptcha .= "<input type=\"hidden\" name=\"captcha[id]\" value=\"{$id}\" />";

				return $retCaptcha;
			} else {

				return true;
			}

		} catch(Exception $e){

			echo $e->getMessage();
		}
	}
}