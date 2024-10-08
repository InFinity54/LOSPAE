<?php
namespace App\Services;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;

class EmailSender
{
    private $mailer;
    private $entityManager;

    /**
     * @param MailerInterface $mailer
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     * @param string $recoveryCode
     * @return bool
     */
    /*public function sendPasswordRecoveryCode(User $user, string $recoveryCode): bool
    {
        try {
            $email = new TemplatedEmail();
            $email->to($user->getEmail())
                ->from(new Address($_ENV["LOSPAE_SENDERMAIL"], "LOSPAÉ"))
                ->subject("Code de vérification d'identité")
                ->htmlTemplate("email/content/password_recovery_code.html.twig")
                ->context([
                    "user" => $user,
                    "recoveryCode" => $recoveryCode
                ]);
            $this->mailer->send($email);
            return true;
        }
        catch (TransportExceptionInterface $e) {
            return false;
        }
    }*/
}
