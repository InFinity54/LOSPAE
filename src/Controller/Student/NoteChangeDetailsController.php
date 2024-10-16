<?php

namespace App\Controller\Student;

use App\Entity\Criteria;
use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\TeacherPromotion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteChangeDetailsController extends AbstractController
{
    #[Route('/details', name: 'student_details')]
    public function history(EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_STUDENT", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }
        
        $student = $entityManager->getRepository(User::class)->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
        $criterias = $entityManager->getRepository(Criteria::class)->findBy([], ["name" => "ASC"]);
        $details = [];

        foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["promotion" => $student->getPromotion()]) as $teacherPromotion) {
            $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $teacherPromotion->getTeacher(), "student" => $student]);
            $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $student, "teacher" => $currentNote->getTeacher()]);
            $usedCriterias = [];
            $totalAddedPoints = 0;
            $totalRemovedPoints = 0;
            $noteChangeAvg = "eq";
            $lastNotesAddedPoints = 0.0;
            $lastNotesRemovedPoints = 0.0;

            foreach ($criterias as $criteria) {
                $usedCriterias[$criteria->getId()] = [
                    "data" => $criteria,
                    "count" => 0
                ];
            }

            foreach ($noteChanges as $noteChange) {
                $usedCriterias[$noteChange->getCriteria()->getId()]["count"]++;

                if ($noteChange->getImpact() > 0) {
                    $totalAddedPoints += $noteChange->getImpact();
                } else {
                    $totalRemovedPoints += ($noteChange->getImpact() * -1);
                }
            }

            foreach ($entityManager->getRepository(NoteChange::class)->findBy(["student" => $student, "teacher" => $currentNote->getTeacher()], ["occuredAt" => "DESC"], 10) as $recentNoteChange) {
                if ($recentNoteChange->getImpact() > 0) {
                    $lastNotesAddedPoints += $recentNoteChange->getImpact();
                } else {
                    $lastNotesRemovedPoints += ($recentNoteChange->getImpact() * -1);
                }
            }

            foreach ($usedCriterias as $criteria) {
                if ($criteria["count"] === 0) {
                    unset($usedCriterias[$criteria["data"]->getId()]);
                }
            }

            usort($usedCriterias, function($a, $b) {
                return $b["count"] <=> $a["count"];
            });

            if ($lastNotesAddedPoints > $lastNotesRemovedPoints) {
                $noteChangeAvg = "inc";
            } else if ($lastNotesRemovedPoints > $lastNotesAddedPoints) {
                $noteChangeAvg = "dec";
            }

            $details[] = [
                "teacher" => $currentNote->getTeacher(),
                "currentNote" => $currentNote->getNote(),
                "totalAddedPoints" => $totalAddedPoints,
                "totalRemovedPoints" => $totalRemovedPoints,
                "noteChangeAvg" => $noteChangeAvg,
                "criterias" => $usedCriterias
            ];
        }

        return $this->render('pages/logged_in/student/details.html.twig', [
            "details" => $details
        ]);
    }
}
