<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use App\Entity\NoteChange;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/notes/change', name: 'teacher_notechange')]
    public function noteChange(Request $request, EntityManagerInterface $entityManager): Response
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

        if ($request->isMethod("POST")) {
            $student = $entityManager->getRepository(User::class)->find($request->request->get("student"));
            $criteria = $entityManager->getRepository(Criteria::class)->find($request->request->get("criteria"));

            if ($criteria->getImpact() < 0) {
                $student->getNote()->setCurrentNote($student->getNote()->getCurrentNote() - ($criteria->getImpact() * -1));
            } else {
                $student->getNote()->setCurrentNote($student->getNote()->getCurrentNote() + $criteria->getImpact());
            }

            $noteChange = new NoteChange();
            $noteChange->setStudent($student);
            $noteChange->setCriteria($criteria);
            $noteChange->setImpact($criteria->getImpact());
            $noteChange->setOccuredAt(new DateTime("now"));
            $entityManager->persist($noteChange);
            $entityManager->flush();

            $this->addFlash("success", "La note de l'étudiant sélectionné a bien été modifiée.");
            return $this->redirectToRoute("teacher_notechange");
        }

        $students = $entityManager->getRepository(User::class)->findUsersByRole("ROLE_STUDENT");
        $criterias = $entityManager->getRepository(Criteria::class)->findBy([], ["name" => "ASC"]);

        return $this->render('pages/logged_in/teacher/notechange.html.twig', [
            "students" => $students,
            "criterias" => $criterias
        ]);
    }
}
