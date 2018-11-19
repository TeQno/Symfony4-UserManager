<?php
/**
 * Created by PhpStorm.
 * User: corentinboutillier
 * Date: 19/11/2018
 * Time: 13:24
 */

namespace App\Security;


use Twig\Environment;

class SecurityMailer
{

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $renderer;

    private $senderEmail;

    /**
     * SecurityMailer constructor.
     * @param \Swift_Mailer $mailer
     * @param Environment $renderer
     */
    public function __construct(\Swift_Mailer $mailer, Environment $renderer) {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
        $this->senderEmail = 'donotreply@example.com';
    }

    /**
     * @param $targetEmail
     * @param $tokenUrl
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function sendResetPasswordMail($targetEmail, $tokenUrl) {

        $message = (new \Swift_Message('Mot de passe oublié ?'))
            ->setFrom($this->senderEmail)
            ->setTo($targetEmail)
            ->setBody($this->renderer->render('email/reset-password.html.twig', [
                'url' => $tokenUrl
            ]));

        $this->mailer->send($message);
    }

    public function sendEmailConfirmation($targetEmail, $tokenUrl) {

        $message = (new \Swift_Message('Confirmer votre adresse mail'))
            ->setFrom($this->senderEmail)
            ->setTo($targetEmail)
            ->setBody($this->renderer->render('email/email-confirmation.html.twig', [
                'url' => $tokenUrl
            ]));

        $this->mailer->send($message);
    }
}