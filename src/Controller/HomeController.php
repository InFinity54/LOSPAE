<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $students = $entityManager->getRepository(User::class)->findUsersByRole("ROLE_STUDENT");
            $studentsNotesSum = 0;

            foreach ($students as $student) {
                if (!is_null($student->getNote())) {
                    $studentsNotesSum += $student->getNote()->getCurrentNote();
                }
            }

            return $this->render('pages/logged_in/index_teacher.html.twig', [
                "studentsCount" => count($students),
                "studentsAverageNote" => $studentsNotesSum / count($students),
                "studentsNotesChangeEventsCount" => 0
            ]);
        }

        return $this->render('pages/logged_in/index.html.twig');
    }
}
