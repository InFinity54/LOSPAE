<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TeacherController extends AbstractController
{
    #[Route('/admin/teachers', name: 'admin_teachers')]
    public function teachers(Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $generatedLetters = [];
        $pageNumber = !is_null($request->query->get("page")) ? $request->query->get("page") : 0;
        $teachers = $entityManager->getRepository(User::class)->findByRole("ROLE_TEACHER", 20, $pageNumber * 20);
        $teachersCount = $entityManager->getRepository(User::class)->countByRole("ROLE_TEACHER");

        if (count($request->query->all()) > 0 && in_array("generatedLetters", array_keys($request->query->all()))) {
            $generatedLetters = $request->query->all()["generatedLetters"];
        }

        return $this->render('pages/logged_in/admin/teachers.html.twig', [
            "generatedLetters" => $generatedLetters,
            "teachers" => $teachers,
            "totalElements" => $teachersCount,
            "currentPage" => $pageNumber
        ]);
    }
}
