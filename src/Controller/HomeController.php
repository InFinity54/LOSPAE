<?php

namespace App\Controller;

use App\Entity\NoteChange;
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

        if (in_array("ROLE_STUDENT", $this->getUser()->getRoles())) {
            $student = $entityManager->getRepository(User::class)->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
            $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $student->getId()], ["occuredAt" => "DESC"]);

            return $this->render('pages/logged_in/index_student.html.twig', [
                "noteChangesCount" => count($noteChanges),
                "recentNoteChanges" => array_slice($noteChanges, 0, 5)
            ]);
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
                "studentsAverageNote" => (count($students) > 0) ? $studentsNotesSum / count($students) : 20,
                "studentsNotesChangeEventsCount" => 0
            ]);
        }

        return $this->render('pages/logged_in/index.html.twig');
    }
}
