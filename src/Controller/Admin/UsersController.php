<?php

namespace App\Controller\Admin;

use App\Entity\StudentNote;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UsersController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function users(EntityManagerInterface $entityManager): Response
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

        return $this->render('pages/logged_in/admin/users.html.twig', [
            "users" => $users
        ]);
    }

    #[Route('/admin/users/configure/{id}', name: 'admin_user_configure')]
    public function userConfigure(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        if ($request->isMethod("POST")) {
            $roles = [];

            if ($request->request->get("roleTeacher") && $request->request->get("roleTeacher") === "on") {
                $roles[] = "ROLE_TEACHER";
            }

            if ($request->request->get("roleStudent") && $request->request->get("roleStudent") === "on") {
                $roles[] = "ROLE_STUDENT";
            }

            if ($request->request->get("roleAdmin") && $request->request->get("roleAdmin") === "on") {
                $roles[] = "ROLE_ADMIN";
            }

            $user->setRoles($roles);
            $entityManager->flush();
            $this->addFlash("success", "Les rôles de l'utilisateur ciblé ont été modifiés.");
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_configuration.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/enable/{id}', name: 'admin_user_enable')]
    public function userEnable(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_enabling_confirm.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/enable/{id}/do', name: 'admin_user_doenable')]
    public function userDoEnable(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        if (in_array("ROLE_STUDENT", $user->getRoles()) && is_null($user->getNote())) {
            $note = new StudentNote();
            $note->setStudent($user);
            $note->setCurrentNote(20);
            $entityManager->persist($note);
            $entityManager->flush();
        }

        $user->setIsActivated(true);
        $user->setNote($note);
        $entityManager->flush();

        $this->addFlash("success", "Le compte utilisateur ciblé a été activé.");
        return $this->redirectToRoute("admin_users");
    }

    #[Route('/admin/users/disable/{id}', name: 'admin_user_disable')]
    public function userDisable(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_disabling_confirm.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/disable/{id}/do', name: 'admin_user_dodisable')]
    public function userDoDisable(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        $user->setIsActivated(false);
        $entityManager->flush();

        $this->addFlash("success", "Le compte utilisateur ciblé a été désactivé.");
        return $this->redirectToRoute("admin_users");
    }

    #[Route('/admin/users/remove/{id}', name: 'admin_user_remove')]
    public function userRemove(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_removing_confirm.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/remove/{id}/do', name: 'admin_user_doremove')]
    public function userDoRemove(Request $request, EntityManagerInterface $entityManager, string $id): Response
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
            return $this->redirectToRoute("admin_users");
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash("success", "Le compte utilisateur ciblé a été définitivement supprimé.");
        return $this->redirectToRoute("admin_users");
    }
}
