<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Services\FileUpload\UserAvatarUpload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvatarsController extends AbstractController
{
    #[Route('/admin/avatars', name: 'admin_avatars')]
    public function avatars(EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $users = $entityManager->getRepository(User::class)->findBy([], ["lastName" => "ASC", "firstName" => "ASC"]);

        return $this->render('pages/logged_in/admin/avatars.html.twig', [
            "users" => $users
        ]);
    }

    #[Route('/admin/avatars/remove/{id}', name: 'admin_avatar_remove')]
    public function avatarRemove(string $id, EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_avatars");
        }

        return $this->render('pages/logged_in/admin/avatar_removing_confirm.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/avatars/remove/{id}/do', name: 'admin_avatar_doremove')]
    public function avatarDoRemove(string $id, EntityManagerInterface $entityManager, UserAvatarUpload $avatarUpload): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_avatars");
        }

        $userAvatarFullPath = $avatarUpload->getTargetDirectory().$user->getAvatar();
        unlink($userAvatarFullPath);

        $user->setAvatar("default_avatar.svg");
        $entityManager->flush();

        $this->addFlash("success", "La photo de profil ciblée a été supprimée de LOSPAÉ.");
        return $this->redirectToRoute("admin_avatars");
    }
}
