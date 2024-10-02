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
    public function notes(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        if ($request->query->has("sort") && $request->query->get("sort") === "name") {
            $students = $entityManager->getRepository(User::class)->findUsersByRole("ROLE_STUDENT");
        } else {
            $students = $entityManager->getRepository(User::class)->findUsersByRoleOrderedByNote("ROLE_STUDENT");
        }

        return $this->render('pages/logged_in/teacher/notes.html.twig', [
            "students" => $students
        ]);
    }

    #[Route('/notes/change', name: 'teacher_notechange')]
    public function noteChange(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        if ($request->isMethod("POST")) {
            $student = $entityManager->getRepository(User::class)->find($request->request->get("student"));
            $criteria = $entityManager->getRepository(Criteria::class)->find($request->request->get("criteria"));

            if ($criteria->getImpact() < 0) {
                $student->setCurrentNote($student->getCurrentNote() - ($criteria->getImpact() * -1));
            } else {
                $student->setCurrentNote($student->getCurrentNote() + $criteria->getImpact());
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

    #[Route('/notes/change/multiple', name: 'teacher_notemultiplechange')]
    public function noteMultipleChange(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        if ($request->request->count() > 0) {
            if ($request->request->has("criterias")) {
                $selectedCriterias = [];
                $students = $entityManager->getRepository(User::class)->findUsersByRole("ROLE_STUDENT");

                foreach ($request->request->all()["criterias"] as $criteriaId) {
                    $selectedCriterias[] = $entityManager->getRepository(Criteria::class)->find($criteriaId);
                }

                return $this->render('pages/logged_in/teacher/notechange_multiple_step2.html.twig', [
                    "selectedCriterias" => $selectedCriterias,
                    "students" => $students
                ]);
            } else {
                foreach ($request->request->keys() as $noteChangeId) {
                    $noteChangeData = explode("_", $noteChangeId);
                    $student = $entityManager->getRepository(User::class)->find($noteChangeData[0]);
                    $criteria = $entityManager->getRepository(Criteria::class)->find($noteChangeData[1]);

                    if ($criteria->getImpact() < 0) {
                        $student->setCurrentNote($student->getCurrentNote() - ($criteria->getImpact() * -1));
                    } else {
                        $student->setCurrentNote($student->getCurrentNote() + $criteria->getImpact());
                    }

                    $noteChange = new NoteChange();
                    $noteChange->setStudent($student);
                    $noteChange->setCriteria($criteria);
                    $noteChange->setImpact($criteria->getImpact());
                    $noteChange->setOccuredAt(new DateTime("now"));
                    $entityManager->persist($noteChange);
                    $entityManager->flush();
                }

                $this->addFlash("success", "Les notes des étudiants sélectionnés ont été modifiées.");
                return $this->redirectToRoute("teacher_history");
            }
        } else if ($request->isMethod("POST") && $request->request->count() === 0) {
            $this->addFlash("danger", "Vous devez sélectionner au moins un critère");
        }

        $criterias = $entityManager->getRepository(Criteria::class)->findBy([], ["name" => "ASC"]);

        return $this->render('pages/logged_in/teacher/notechange_multiple_step1.html.twig', [
            "criterias" => $criterias
        ]);
    }
}
