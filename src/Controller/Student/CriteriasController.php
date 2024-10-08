<?php

namespace App\Controller\Student;

use App\Entity\Criteria;
use App\Entity\TeacherPromotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CriteriasController extends AbstractController
{
    #[Route('/criterias', name: 'student_criterias')]
    public function criterias(EntityManagerInterface $entityManager): Response
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

        $criterias = [];

        foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["promotion" => $this->getUser()->getPromotion()]) as $promotionTeacher) {
            $teacherCriterias = $entityManager->getRepository(Criteria::class)->findBy(["teacher" => $promotionTeacher->getTeacher()], ["impact" => "ASC"]);

            $criterias[] = [
                "teacher" => $promotionTeacher->getTeacher(),
                "criterias" => $teacherCriterias
            ];
        }

        return $this->render('pages/logged_in/student/criterias.html.twig', [
            "criterias" => $criterias
        ]);
    }
}
