<?php

namespace OCA\ElectronicSignatures\Commands;

use OCA\ElectronicSignatures\Config;
use OCP\AppFramework\Controller;
use OCP\IUserSession;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMailer;
use OCP\Mail\IMessage;

class SendSigningLinkToEmail extends Controller {
    private $userId;

    /** @var IMailer */
    private $mailer;

    /** @var IUserSession */
    private $session;

    /** @var Config */
    private $config;

    public function __construct(IMailer $mailer, IUserSession $userSession, Config $config, $UserId) {
        $this->userId = $UserId;
        $this->mailer = $mailer;
        $this->session = $userSession;
        $this->config = $config;
    }

    public function sendIfNecessary(string $email, $link): void {
        // We do not need to send the e-mail if OTP is enabled, because in
        // this case, eID Easy will be sending the e-mail instead of us.
        if ($this->config->isOtpEnabled()) {
            return;
        }

        $emailTemplate = $this->mailer->createEMailTemplate('calendar.PublicShareNotification', [
            'link' => $link,
        ]);

        $emailTemplate->setSubject('Document awaits your signature');

        $emailTemplate->addHeader();
        $emailTemplate->addHeading('A document awaits your signature.');
        $emailTemplate->addBodyText('By clicking the button below, you can review it and sign it.');
        $emailTemplate->addBodyButton('Review document', $link);
        $emailTemplate->addFooter();

        $recipient = $email;
        $message = $this->createMessage([$recipient => $recipient], $emailTemplate);

        $this->mailer->send($message);
    }

    /**
     * @param string $from
     * @param array $recipients
     * @param IEMailTemplate $template
     * @return IMessage
     */
    private function createMessage(array $recipients,
                                   IEMailTemplate $template):IMessage {
        $user = $this->session->getUser();
        $message = $this->mailer->createMessage();
        $message->setFrom([$user->getEMailAddress() => $user->getDisplayName()]);
        $message->setTo($recipients);
        $message->useTemplate($template);

        return $message;
    }
}
