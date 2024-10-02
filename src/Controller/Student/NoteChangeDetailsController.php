<?php

namespace App\Controller\Student;

use App\Entity\Criteria;
use App\Entity\NoteChange;
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

        if (!in_array("ROLE_STUDENT", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }
        
        $student = $entityManager->getRepository(User::class)->findOneBy(["email" => $this->getUser()->getUserIdentifier()]);
        $criterias = $entityManager->getRepository(Criteria::class)->findBy([], ["name" => "ASC"]);
        $criteriasCount = [];
        $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $student->getId()], ["occuredAt" => "DESC"]);
        $lastNoteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $student->getId()], ["occuredAt" => "DESC"], 10);
        $noteChangeAvg = "eq";
        $totalAddedPoints = 0.0;
        $totalRemovedPoints = 0.0;
        $lastNotesAddedPoints = 0.0;
        $lastNotesRemovedPoints = 0.0;

        foreach ($criterias as $criteria) {
            $criteriasCount[$criteria->getId()] = 0;
        }

        foreach ($noteChanges as $noteChange) {
            $criteriasCount[$noteChange->getCriteria()->getId()]++;

            if ($noteChange->getImpact() > 0) {
                $totalAddedPoints += $noteChange->getImpact();
            } else {
                $totalRemovedPoints += ($noteChange->getImpact() * -1);
            }
        }

        foreach ($lastNoteChanges as $noteChange) {
            if ($noteChange->getImpact() > 0) {
                $lastNotesAddedPoints += $noteChange->getImpact();
            } else {
                $lastNotesRemovedPoints += ($noteChange->getImpact() * -1);
            }
        }

        if ($lastNotesAddedPoints > $lastNotesRemovedPoints) {
            $noteChangeAvg = "inc";
        } else if ($lastNotesRemovedPoints > $lastNotesAddedPoints) {
            $noteChangeAvg = "dec";
        }

        return $this->render('pages/logged_in/student/details.html.twig', [
            "totalAddedPoints" => $totalAddedPoints,
            "totalRemovedPoints" => $totalRemovedPoints,
            "noteChangeAvg" => $noteChangeAvg,
            "criterias" => $criterias,
            "criteriasCount" => $criteriasCount
        ]);
    }
}
