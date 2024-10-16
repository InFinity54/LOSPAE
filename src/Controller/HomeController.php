<?php

namespace App\Controller;

use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\TeacherPromotion;
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

        if (in_array("ROLE_STUDENT", $this->getUser()->getRoles())) {
            $student = $entityManager->getRepository(User::class)->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
            $notes = [];

            foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["promotion" => $student->getPromotion()]) as $teacherPromotion) {
                $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $teacherPromotion->getTeacher(), "student" => $student]);
                $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $student, "teacher" => $currentNote->getTeacher()], ["occuredAt" => "DESC"]);

                $notes[] = [
                    "teacher" => $currentNote->getTeacher(),
                    "currentNote" => $currentNote->getNote(),
                    "totalChanges" => count($noteChanges),
                    "recentChanges" => array_slice($noteChanges, 0, 10)
                ];
            }

            return $this->render('pages/logged_in/index_student.html.twig', [
                "notes" => $notes
            ]);
        }

        if (in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $schools = [];
            $promos = [];
            $students = [];
            $noteChanges = [];
            $studentsNotesSum = 0;

            foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $this->getUser()]) as $teacherPromotions) {
                $promos[] = $teacherPromotions->getPromotion();

                if (!in_array($teacherPromotions->getPromotion()->getSchool(), $schools, true)) {
                    $schools[] = $teacherPromotions->getPromotion()->getSchool();
                }

                foreach ($teacherPromotions->getPromotion()->getStudents() as $student) {
                    $students[] = $student;
                    $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $this->getUser(), "student" => $student]);
                    $studentsNotesSum += $currentNote->getNote();

                    foreach ($student->getNoteChanges() as $noteChange) {
                        $noteChanges[] = $noteChange;
                    }
                }
            }

            return $this->render('pages/logged_in/index_teacher.html.twig', [
                "schools" => $schools,
                "promotions" => $promos,
                "studentsCount" => count($students),
                "studentsAverageNote" => (count($students) > 0) ? $studentsNotesSum / count($students) : 20,
                "studentsNotesChangeEventsCount" => count($noteChanges),
            ]);
        }

        return $this->render('pages/logged_in/index.html.twig');
    }
}
