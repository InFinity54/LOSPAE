<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\EmailSender;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class PasswordController extends AbstractController
{
    #[Route('/passwordrecover', name: 'passwordrecover')]
    public function passwordRecover(Request $request, EntityManagerInterface $entityManager, EmailSender $emailSender, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute("homepage");
        }

        if ($request->request->count() === 1 && $request->request->has("email")) {
            // Traitement du formulaire de mot de passe oublié
            try {
                $user = $entityManager->getRepository(User::class)->findOneBy(["email" => $request->request->get("email")]);

                if (!is_null($user)) {
                    if (is_null($user->getRecoveryCode()) || is_null($user->getRecoveryCodeExpireAt()) || new DateTime("now") >= $user->getRecoveryCodeExpireAt()) {
                        // Génération et envoi du code de vérification
                        // (uniquement si pas de code ou si code expiré)
                        $recoveryCode = rand(100000, 999999);
                        $recoveryCodeExpireDateTime = (new DateTime("now"))->add(DateInterval::createFromDateString("30 minutes"));

                        $user->setRecoveryCode($recoveryCode);
                        $user->setRecoveryCodeExpireAt($recoveryCodeExpireDateTime);
                        $entityManager->flush();

                        $emailSender->sendPasswordRecoveryCode($user, $recoveryCode);
                    }

                    return $this->render('pages/password_recover_code.html.twig', [
                        "userId" => $user->getId()
                    ]);
                } else {
                    $this->addFlash("danger", "Aucun compte utilisant l'adresse e-mail saisie n'a été trouvé.");
                }
            } catch (Exception $e) {
                $this->addFlash("danger", "Une erreur est survenue durant la procédure de récupération de compte. Veuillez réessayer plus tard.");
            }
        } else if ($request->request->count() === 3 && $request->request->get("fullcode")) {
            // Traitement du formulaire du code de vérification
            try {
                $user = $entityManager->getRepository(User::class)->find($request->request->get("user"));

                if (!is_null($user)) {
                    if ($request->request->get("fullcode") === $user->getRecoveryCode()) {
                        if (new DateTime("now") < $user->getRecoveryCodeExpireAt()) {
                            return $this->render('pages/password_recover_change.html.twig', [
                                "userId" => $user->getId()
                            ]);
                        } else {
                            $user->setRecoveryCode(null);
                            $user->setRecoveryCodeExpireAt(null);
                            $entityManager->flush();

                            $this->addFlash("danger", "Le code de validation a expiré.");
                        }
                    } else {
                        $this->addFlash("danger", "Le code de vérification est incorrect.");

                        return $this->render('pages/password_recover_code.html.twig', [
                            "userId" => $user->getId()
                        ]);
                    }
                } else {
                    $this->addFlash("danger", "Aucun compte utilisant l'adresse e-mail saisie n'a été trouvé.");
                }
            } catch (Exception $e) {
                $this->addFlash("danger", "Une erreur est survenue durant la vérification d'identité. Veuillez réessayer plus tard.");
            }
        } else if ($request->request->count() === 3 && $request->request->has("confirmpassword")) {
            // Traitement du formulaire de changement de mot de passe
            try {
                $user = $entityManager->getRepository(User::class)->find($request->request->get("user"));

                if (!is_null($user)) {
                    if ($request->request->get("password") === $request->request->get("confirmpassword")) {
                        $user->setPassword($passwordHasher->hashPassword($user, $request->request->get("password")));
                        $user->setRecoveryCode(null);
                        $user->setRecoveryCodeExpireAt(null);
                        $entityManager->flush();
                        $this->addFlash("success", "Votre mot de passe a été modifié.");
                        return $this->redirectToRoute("login");
                    } else {
                        $this->addFlash("danger", "Les mots de passe ne correspondent pas.");

                        return $this->render('pages/password_recover_change.html.twig', [
                            "userId" => $user->getId()
                        ]);
                    }
                } else {
                    $this->addFlash("danger", "Aucun compte utilisant l'adresse e-mail saisie n'a été trouvé.");
                }
            } catch (Exception $e) {
                $this->addFlash("danger", "Une erreur est survenue durant la modification de votre mot de passe. Veuillez réessayer plus tard.");
            }
        }

        return $this->render('pages/password_recover.html.twig');
    }

    #[Route('/passwordrecover/resend/{id}', name: 'passwordrecover_resend')]
    public function passwordRecoverResend(string $id, EntityManagerInterface $entityManager, EmailSender $emailSender): JsonResponse
    {
        try {
            $user = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($user) && (is_null($user->getRecoveryCode()) || !is_null($user->getRecoveryCodeExpireAt() || new DateTime("now") >= $user->getRecoveryCodeExpireAt()))) {
                $recoveryCode = rand(100000, 999999);
                $recoveryCodeExpireDateTime = (new DateTime("now"))->add(DateInterval::createFromDateString("30 minutes"));

                $user->setRecoveryCode($recoveryCode);
                $user->setRecoveryCodeExpireAt($recoveryCodeExpireDateTime);
                $entityManager->flush();
            }

            if (!is_null($user) && !is_null($user->getRecoveryCode()) && !is_null($user->getRecoveryCodeExpireAt()) && new DateTime("now") < $user->getRecoveryCodeExpireAt()) {
                $emailSender->sendPasswordRecoveryCode($user, $recoveryCode);
            }

            return new JsonResponse([], 200);
        } catch (Exception $e) {
            return new JsonResponse([
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
