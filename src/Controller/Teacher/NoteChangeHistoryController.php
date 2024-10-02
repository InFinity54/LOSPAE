<?php

namespace App\Controller\Teacher;

use App\Entity\NoteChange;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteChangeHistoryController extends AbstractController
{
    #[Route('/notes/history', name: 'teacher_history')]
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy([], ["occuredAt" => "DESC"]);

        return $this->render('pages/logged_in/teacher/history.html.twig', [
            "noteChanges" => $noteChanges
        ]);
    }

    #[Route('/notes/history/cancel/{id}', name: 'teacher_history_cancel')]
    public function historyCancel(string $id, EntityManagerInterface $entityManager): Response
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

        $noteChange = $entityManager->getRepository(NoteChange::class)->find($id);

        return $this->render('pages/logged_in/teacher/history_canceling_confirm.html.twig', [
            "noteChange" => $noteChange
        ]);
    }

    #[Route('/notes/history/cancel/{id}/do', name: 'teacher_history_docancel')]
    public function historyDoCancel(string $id, Request $request, EntityManagerInterface $entityManager): Response
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

        $noteChange = $entityManager->getRepository(NoteChange::class)->find($id);

        if ($noteChange->getImpact() < 0) {
            $noteChange->getStudent()->getNote()->setCurrentNote($noteChange->getStudent()->getNote()->getCurrentNote() + ($noteChange->getImpact() * -1));
        } else {
            $noteChange->getStudent()->getNote()->setCurrentNote($noteChange->getStudent()->getNote()->getCurrentNote() - $noteChange->getImpact());
        }

        $entityManager->remove($noteChange);
        $entityManager->flush();

        $this->addFlash("success", "La modification ciblée a été correctement annulée.");
        return $this->redirectToRoute("teacher_history");
    }
}
