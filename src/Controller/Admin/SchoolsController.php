<?php

namespace App\Controller\Admin;

use App\Entity\School;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SchoolsController extends AbstractController
{
    #[Route('/admin/schools', name: 'admin_schools')]
    public function schools(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $schools = $entityManager->getRepository(School::class)->findBy([], ["name" => "ASC"]);

        return $this->render('pages/logged_in/admin/schools.html.twig', [
            "schools" => $schools
        ]);
    }
}
