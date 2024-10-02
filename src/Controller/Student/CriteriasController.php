<?php

namespace App\Controller\Student;

use App\Entity\Criteria;
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

        if (!in_array("ROLE_STUDENT", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $criterias = $entityManager->getRepository(Criteria::class)->findBy([], ["impact" => "ASC"]);

        return $this->render('pages/logged_in/student/criterias.html.twig', [
            "criterias" => $criterias
        ]);
    }
}
