<?php

namespace App\Controller\Student;

use App\Entity\NoteChange;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteChangeHistoryController extends AbstractController
{
    #[Route('/history', name: 'student_history')]
    public function history(EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
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
        $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $student->getId()], ["occuredAt" => "DESC"]);

        return $this->render('pages/logged_in/student/history.html.twig', [
            "noteChanges" => $noteChanges
        ]);
    }
}
