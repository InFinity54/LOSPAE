<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\Promotion;
use App\Entity\TeacherPromotion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NoteChangeResetController extends AbstractController
{
    #[Route('/notes/reset', name: 'teacher_notereset')]
    public function reset(EntityManagerInterface $entityManager): Response
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
            $studentCount = count($teacherPromotion->getPromotion()->getStudents());

            $promotions[] = [
                "data" => $teacherPromotion->getPromotion(),
                "studentCount" => $studentCount
            ];
        }

        return $this->render('pages/logged_in/teacher/notereset.html.twig', [
            "promotions" => $promotions
        ]);
    }

    #[Route('/notes/reset/choose', name: 'teacher_notereset_choose', methods: ['POST'])]
    public function resetChoose(Request $request, EntityManagerInterface $entityManager): Response
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

        if ($request->request->count() === 0) {
            $this->addFlash("warning", "Aucune promotion n'a été sélectionnée pour la réinitialisation des notes.");
            return $this->redirectToRoute("teacher_notereset");
        }

        $students = [];

        foreach ($request->request->all() as $id => $value) {
            $promotion = $entityManager->getRepository(Promotion::class)->find($id);

            foreach ($promotion->getStudents() as $student) {
                $students[] = $student;
            }
        }

        usort($students, function($a, $b) {
            if ($a->getLastName() !== $b->getLastName()) {
                return $a->getLastName() <=> $b->getLastName();
            }

            return $a->getFirstName() <=> $b->getFirstName();
        });

        return $this->render('pages/logged_in/teacher/notereset_choose.html.twig', [
            "students" => $students
        ]);
    }

    #[Route('/notes/reset/do', name: 'teacher_notereset_do', methods: ['POST'])]
    public function doReset(Request $request, EntityManagerInterface $entityManager): Response
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

        if ($request->request->count() === 0) {
            $this->addFlash("warning", "Aucun étudiant n'a été sélectionné pour la réinitialisation des notes.");
            return $this->redirectToRoute("teacher_notereset");
        }

        foreach ($request->request->all() as $id => $value) {
            $student = $entityManager->getRepository(User::class)->find($id);
            $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["teacher" => $this->getUser(), "student" => $student]);
            $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $this->getUser(), "student" => $student]);

            foreach ($noteChanges as $noteChange) {
                $entityManager->remove($noteChange);
                $entityManager->flush();
            }

            $currentNote->setNote(20);
            $entityManager->flush();
        }

        $this->addFlash("success", "Les notes des étudiants sélectionnés ont été correctement réinitialisées.");
        return $this->redirectToRoute("teacher_notes");
    }
}
