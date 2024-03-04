<?php

namespace App\Controller\Teacher;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StudentsNotesController extends AbstractController
{
    #[Route('/notes', name: 'teacher_notes')]
    public function notes(EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
        }

        $students = $entityManager->getRepository(User::class)->findUsersByRole("ROLE_STUDENT");

        return $this->render('pages/logged_in/teacher/notes.html.twig', [
            "students" => $students
        ]);
    }
}
