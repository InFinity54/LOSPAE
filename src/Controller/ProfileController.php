<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\FileUpload\UserAvatarUpload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserAvatarUpload $avatarUploader, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (
            !in_array("ROLE_STUDENT", $this->getUser()->getRoles())
            && !in_array("ROLE_TEACHER", $this->getUser()->getRoles())
            && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())
        ) {
            return $this->redirectToRoute("unconfigured");
        }

        if ($request->files->count() > 0 && $request->files->get("avatar")) {
            $user = $entityManager->getRepository(User::class)->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
            $newAvatar = $avatarUploader->upload($request->files->get("avatar"), $user);

            if ($newAvatar) {
                $user->setAvatar($newAvatar);
                $entityManager->flush();
                $this->addFlash("success", "Votre photo de profil a été modifiée.");
            } else {
                $this->addFlash("danger", "Une erreur est survenue durant l'enregistrement de votre photo de profil.");
            }
        }

        if ($request->request->count() > 0) {
            $user = $entityManager->getRepository(User::class)->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);

            if ($passwordHasher->isPasswordValid($user, $request->request->get("currentpassword"))) {
                if ($request->request->get("email") !== "" || $request->request->get("newpassword") !== "" || $request->request->get("confirmnewpassword") !== "") {
                    if ($request->request->get("email") !== "") {
                        $user->setEmail($request->request->get("email"));
                        $entityManager->flush();
                        $this->addFlash("success", "Votre adresse e-mail a été modifiée.");
                    }

                    if ($request->request->get("newpassword") !== "" && $request->request->get("confirmnewpassword") !== "" && $request->request->get("newpassword") === $request->request->get("confirmnewpassword")) {
                        $user->setPassword($passwordHasher->hashPassword($user, $request->request->get("newpassword")));
                        $entityManager->flush();
                        $this->addFlash("success", "Votre mot de passe a été modifié. Vous avez été deconnecté(e) pour des raisons de sécurité.");
                        return $this->redirectToRoute("_logout_main");
                    } else {
                        $this->addFlash("danger", "Les mots de passe saisis ne sont pas identiques. Votre mot de passe n'a été modifié.");
                    }
                } else {
                    $this->addFlash("info", "Aucune information saisie pour modification. Votre profil est resté intact.");
                }
            } else {
                $this->addFlash("danger", "Le mot de passe actuel est incorrect. Aucune modification n'a été apportée.");
            }
        }

        return $this->render('pages/logged_in/profile.html.twig');
    }
}
