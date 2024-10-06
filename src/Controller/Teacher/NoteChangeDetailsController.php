<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use App\Entity\NoteChange;
use App\Entity\TeacherPromotion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteChangeDetailsController extends AbstractController
{
    #[Route('/notes/details', name: 'teacher_details')]
    public function details(EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $teacherPromotions = $entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $this->getUser()]);
        $promotions = [];

        foreach ($teacherPromotions as $teacherPromotion) {
            $students = [];

            foreach ($teacherPromotion->getPromotion()->getStudents() as $student) {
                $students[] = $student;
            }

            $studentsNotesSum = 0;
            $criterias = [];
            $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy([], ["occuredAt" => "DESC"]);
            $globalAddedPoints = 0.0;
            $globalRemovedPoints = 0.0;

            foreach ($students as $student) {
                foreach ($student->getCurrentNotes() as $currentNote) {
                    if ($currentNote->getTeacher() === $this->getUser()) {
                        $studentsNotesSum += $currentNote->getNote();
                    }
                }
            }

            foreach ($entityManager->getRepository(Criteria::class)->findBy([], ["name" => "ASC"]) as $criteria) {
                $criterias[$criteria->getId()] = [
                    "data" => $criteria,
                    "count" => 0
                ];
            }

            foreach ($noteChanges as $noteChange) {
                $criterias[$noteChange->getCriteria()->getId()]["count"]++;

                if ($noteChange->getImpact() > 0) {
                    $globalAddedPoints += $noteChange->getImpact();
                } else {
                    $globalRemovedPoints += ($noteChange->getImpact() * -1);
                }
            }

            foreach ($criterias as $criteria) {
                if ($criteria["count"] === 0) {
                    unset($criterias[$criteria["data"]->getId()]);
                }
            }

            usort($criterias, function($a, $b) {
                return $b["count"] <=> $a["count"];
            });

            $promotions[] = [
                "data" => $teacherPromotion->getPromotion(),
                "studentsAverageNote" => (count($students) > 0) ? $studentsNotesSum / count($students) : 20,
                "studentsNotesChangeEventsCount" => count($noteChanges),
                "globalAddedPoints" => $globalAddedPoints,
                "globalRemovedPoints" => $globalRemovedPoints,
                "criterias" => $criterias
            ];
        }

        return $this->render('pages/logged_in/teacher/details.html.twig', [
            "promotions" => $promotions
        ]);
    }
}
