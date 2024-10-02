<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use App\Entity\NoteChange;
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

        $students = $entityManager->getRepository(User::class)->findUsersByRole("ROLE_STUDENT");
        $studentsNotesSum = 0;
        $criterias = $entityManager->getRepository(Criteria::class)->findBy([], ["name" => "ASC"]);
        $criteriasCount = [];
        $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy([], ["occuredAt" => "DESC"]);
        $globalAddedPoints = 0.0;
        $globalRemovedPoints = 0.0;

        foreach ($students as $student) {
            if (!is_null($student->getCurrentNote())) {
                $studentsNotesSum += $student->getCurrentNote();
            }
        }

        foreach ($criterias as $criteria) {
            $criteriasCount[$criteria->getId()] = 0;
        }

        foreach ($noteChanges as $noteChange) {
            $criteriasCount[$noteChange->getCriteria()->getId()]++;

            if ($noteChange->getImpact() > 0) {
                $globalAddedPoints += $noteChange->getImpact();
            } else {
                $globalRemovedPoints += ($noteChange->getImpact() * -1);
            }
        }

        return $this->render('pages/logged_in/teacher/details.html.twig', [
            "studentsAverageNote" => (count($students) > 0) ? $studentsNotesSum / count($students) : 20,
            "studentsNotesChangeEventsCount" => count($noteChanges),
            "globalAddedPoints" => $globalAddedPoints,
            "globalRemovedPoints" => $globalRemovedPoints,
            "criterias" => $criterias,
            "criteriasCount" => $criteriasCount
        ]);
    }
}
