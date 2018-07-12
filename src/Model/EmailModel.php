<?php

namespace App\Model;

use App\Entity\UserAccount;
use Swift_Mailer;
use Swift_Message;
use Twig\Environment;
use Vinorcola\HelperBundle\Model\TranslationModel;

class EmailModel
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var TranslationModel
     */
    private $translationModel;

    /**
     * @var string
     */
    private $sendFromAddress;

    /**
     * @var Environment
     */
    private $templatingEnvironment;

    /**
     * EmailModel constructor.
     *
     * @param Swift_Mailer     $mailer
     * @param TranslationModel $translationModel
     * @param Environment      $templateEnvironment
     * @param string           $sendFromAddress
     */
    public function __construct(
        Swift_Mailer $mailer,
        TranslationModel $translationModel,
        Environment $templateEnvironment,
        string $sendFromAddress
    ) {
        $this->mailer = $mailer;
        $this->translationModel = $translationModel;
        $this->sendFromAddress = $sendFromAddress;
        $this->templatingEnvironment = $templateEnvironment;
    }

    /**
     * Send an email to confirm registration and verify the user's email address.
     *
     * @param UserAccount $userAccount
     */
    public function sendRegistrationEmail(UserAccount $userAccount): void
    {
        $this->send(
            $userAccount->emailAddress,
            $this->translationModel->translate('email.registration.subject'),
            'Email/registration.html.twig',
            [
                'userAccount' => $userAccount,
            ]
        );
    }

    /**
     * Send an email to verify the user's email address after an email address change.
     *
     * @param UserAccount $userAccount
     */
    public function sendEmailAddressVerificationEmail(UserAccount $userAccount): void
    {
        $this->send(
            $userAccount->emailAddress,
            $this->translationModel->translate('email.emailAddressVerification.subject'),
            'Email/emailAddressVerification.html.twig',
            [
                'userAccount' => $userAccount,
            ]
        );
    }

    /**
     * Send an email to authenticate the user through its email address in order to recover the password.
     *
     * @param UserAccount $userAccount
     */
    public function sendPasswordRecoveryEmail(UserAccount $userAccount): void
    {
        $this->send(
            $userAccount->emailAddress,
            $this->translationModel->translate('email.passwordRecovery.subject'),
            'Email/passwordRecovery.html.twig',
            [
                'userAccount' => $userAccount,
            ]
        );
    }

    /**
     * Send an email.
     *
     * @param string $emailAddress
     * @param string $subject
     * @param string $template
     * @param array  $templateParameters
     */
    private function send(string $emailAddress, string $subject, string $template, array $templateParameters = [])
    {
        $email = new Swift_Message($subject);
        $email
            ->setFrom($this->sendFromAddress)
            ->setTo($emailAddress)
            ->setBody(
                $this->templatingEnvironment->render($template, $templateParameters)
            );
        $this->mailer->send($email);
    }
}
