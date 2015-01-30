<?php

namespace RocketShip\Utils;

use RocketShip\Base;
use RocketShip\Configuration;

class Mail extends Base
{
    /**
     *
     * The PHPMailer instance for custom configurations
     *
     * @var \PHPMailer
     *
     */
    public $mailer;

    private $host;
    private $user;
    private $pass;
    private $type   = 'smtp';
    private $secure = false;

    public function __construct()
    {
        parent::__construct();

        $this->mailer = new \PHPMailer;
        $conf         = Configuration::get('configuration', 'emailing');

        $this->type = $conf->mode;

        switch ($conf->mode)
        {
            case "sendgrid":
                $this->mailer->SMTPSecure = '';
                $this->mailer->Host       = 'smtp.sendgrid.net';
                $this->mailer->Port       = 587;
                $this->mailer->SMTPAuth   = true;
                $this->mailer->Username   = $conf->user;
                $this->mailer->Password   = $conf->password;

                $this->mailer->isMail(false);
                $this->mailer->isSMTP(true);
                break;

            case "smtp":
                $this->mailer->SMTPSecure = ($conf->ssl == 'yes') ? 'ssl' : '';
                $this->mailer->Host       = $conf->host;
                $this->mailer->Port       = $conf->port;
                $this->mailer->SMTPAuth   = true;
                $this->mailer->Username   = $conf->user;
                $this->mailer->Password   = $conf->password;

                $this->mailer->isMail(false);
                $this->mailer->isSMTP(true);
                break;

            case "mail":
                $this->mailer->isSMTP(false);
                $this->mailer->isMail(true);
                break;

            case "gmail":
                $this->mailer->SMTPSecure = 'ssl';
                $this->mailer->Host       = 'smtp.gmail.com';
                $this->mailer->Port       = 465;
                $this->mailer->SMTPAuth   = true;
                $this->mailer->Username   = $conf->user;
                $this->mailer->Password   = $conf->password;

                $this->mailer->isMail(false);
                $this->mailer->isSMTP(true);
                break;
        }

        $this->mailer->CharSet = 'UTF-8';

        /* Anti Spam technique */
        $this->mailer->AddReplyTo("no-reply@" . $conf->domain, 'No Reply');
    }

    /**
     *
     * Send the email
     *
     * @param   string  the email to email it to
     * @param   string  sent from (format: "Name <email@addr.com>"
     * @param   string  the subject
     * @param   string  the alternate text version
     * @param   string  the html data
     * @return  bool    success/failure
     * @access  public
     * @throws  \RuntimeException
     *
     */
    public function send($target, $from, $subject, $txt, $html)
    {
        list($name, $mail) = explode('<', $from);

        $this->mailer->setFrom(substr($mail, 0, strlen($mail) - 1), trim($name));

        $this->mailer->Subject = $subject;
        $this->mailer->AltBody = $txt;
        $this->mailer->MsgHTML($html);
        $this->mailer->isHTML(true);
        $this->mailer->AddAddress($target);

        //print_r($this);
        //die();

        if (empty($this->mailer->ErrorInfo)) {
            if ($this->mailer->send()) {
                return true;
            } else {
                return false;
            }
        } else {
            throw new \RuntimeException('There is a problem with the email settings: ' . $this->mailer->ErrorInfo);
        }
    }
}
