<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\TeacherPromotion;
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

            if ($request->query->has("sort") && $request->query->get("sort") === "name") {
                usort($students, function($a, $b) {
                    $lastNameComparison = strcmp($a->getLastName(), $b->getLastName());

                    if ($lastNameComparison === 0) {
                        return strcmp($a->getFirstName(), $b->getFirstName());
                    }

                    return $lastNameComparison;
                });
            } else {
                usort($students, function($a, $b) {
                    $aNote = null;
                    $bNote = null;

                    foreach ($a->getCurrentNotes() as $currentNote) {
                        if ($currentNote->getTeacher() === $this->getUser()) {
                            $aNote = $currentNote->getNote();
                            break;
                        }
                    }

                    foreach ($b->getCurrentNotes() as $currentNote) {
                        if ($currentNote->getTeacher() === $this->getUser()) {
                            $bNote = $currentNote->getNote();
                            break;
                        }
                    }

                    if ($aNote === null && $bNote === null) {
                        return 0;
                    } elseif ($aNote === null) {
                        return 1;
                    } elseif ($bNote === null) {
                        return -1;
                    }

                    return $bNote <=> $aNote; // Tri par ordre décroissant
                });
            }

            $promotions[] = [
                "promo" => $teacherPromotion->getPromotion(),
                "students" => $students
            ];
        }

        return $this->render('pages/logged_in/teacher/notes.html.twig', [
            "promotions" => $promotions
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

        if ($request->isMethod("POST")) {
            $student = $entityManager->getRepository(User::class)->find($request->request->get("student"));
            $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $this->getUser(), "student" => $student]);
            $criteria = $entityManager->getRepository(Criteria::class)->find($request->request->get("criteria"));

            for ($i = 0; $i < $request->request->get("occurrences"); $i++) {
                if ($criteria->getImpact() < 0) {
                    $currentNote->setNote($currentNote->getNote() - ($criteria->getImpact() * -1));
                } else {
                    $currentNote->setNote($currentNote->getNote() + $criteria->getImpact());
                }

                $noteChange = new NoteChange();
                $noteChange->setTeacher($this->getUser());
                $noteChange->setStudent($student);
                $noteChange->setCriteria($criteria);
                $noteChange->setImpact($criteria->getImpact());
                $noteChange->setOccuredAt(new DateTime("now"));
                $entityManager->persist($noteChange);
                $entityManager->flush();
            }

            $this->addFlash("success", "La note de l'étudiant sélectionné a bien été modifiée.");
            return $this->redirectToRoute("teacher_notechange");
        }

        $students = [];
        $criterias = $entityManager->getRepository(Criteria::class)->findBy(["teacher" => $this->getUser()], ["name" => "ASC"]);

        foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $this->getUser()]) as $teacherPromotion) {
            foreach ($teacherPromotion->getPromotion()->getStudents() as $student) {
                $students[] = $student;
            }
        }

        usort($students, function($a, $b) {
            // Comparaison par nom de famille
            $lastNameComparison = strcmp($a->getLastName(), $b->getLastName());

            // Si les noms de famille sont identiques, comparer par prénom
            if ($lastNameComparison === 0) {
                return strcmp($a->getFirstName(), $b->getFirstName());
            }

            return $lastNameComparison;
        });

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

        if ($request->request->count() > 0) {
            if ($request->request->has("criterias")) {
                $students = [];
                $selectedCriterias = [];

                foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $this->getUser()]) as $teacherPromotion) {
                    foreach ($teacherPromotion->getPromotion()->getStudents() as $student) {
                        $students[] = $student;
                    }
                }

                usort($students, function($a, $b) {
                    // Comparaison par nom de famille
                    $lastNameComparison = strcmp($a->getLastName(), $b->getLastName());

                    // Si les noms de famille sont identiques, comparer par prénom
                    if ($lastNameComparison === 0) {
                        return strcmp($a->getFirstName(), $b->getFirstName());
                    }

                    return $lastNameComparison;
                });

                foreach ($request->request->all()["criterias"] as $criteriaId) {
                    $selectedCriterias[] = $entityManager->getRepository(Criteria::class)->find($criteriaId);
                }

                return $this->render('pages/logged_in/teacher/notechange_multiple_step2.html.twig', [
                    "selectedCriterias" => $selectedCriterias,
                    "students" => $students
                ]);
            } else {
                foreach ($request->request->keys() as $noteChangeId) {
                    if ($request->request->get($noteChangeId) > 0) {
                        $noteChangeData = explode("_", $noteChangeId);
                        $student = $entityManager->getRepository(User::class)->find($noteChangeData[0]);
                        $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $this->getUser(), "student" => $student]);
                        $criteria = $entityManager->getRepository(Criteria::class)->find($noteChangeData[1]);

                        for ($i = 0; $i < $request->request->get($noteChangeId); $i++) {
                            if ($criteria->getImpact() < 0) {
                                $currentNote->setNote($currentNote->getNote() - ($criteria->getImpact() * -1));
                            } else {
                                $currentNote->setNote($currentNote->getNote() + $criteria->getImpact());
                            }

                            $noteChange = new NoteChange();
                            $noteChange->setTeacher($this->getUser());
                            $noteChange->setStudent($student);
                            $noteChange->setCriteria($criteria);
                            $noteChange->setImpact($criteria->getImpact());
                            $noteChange->setOccuredAt(new DateTime("now"));
                            $entityManager->persist($noteChange);
                            $entityManager->flush();
                        }
                    }
                }

                $this->addFlash("success", "Les notes des étudiants sélectionnés ont été modifiées.");
                return $this->redirectToRoute("teacher_history");
            }
        } else if ($request->isMethod("POST") && $request->request->count() === 0) {
            $this->addFlash("danger", "Vous devez sélectionner au moins un critère");
        }

        $criterias = $entityManager->getRepository(Criteria::class)->findBy(["teacher" => $this->getUser()], ["name" => "ASC"]);

        return $this->render('pages/logged_in/teacher/notechange_multiple_step1.html.twig', [
            "criterias" => $criterias
        ]);
    }
}
