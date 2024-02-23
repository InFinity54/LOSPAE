<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\FileUpload\UserAvatarUpload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserAvatarUpload $avatarUploader): Response
    {
        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
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

        return $this->render('pages/logged_in/profile.html.twig');
    }
}
