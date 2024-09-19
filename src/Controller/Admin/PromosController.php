<?php

namespace App\Controller\Admin;

use App\Entity\Promo;
use App\Entity\School;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PromosController extends AbstractController
{
    #[Route('/admin/promos', name: 'admin_promos')]
    public function promos(Request $request, EntityManagerInterface $entityManager): Response
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

        $promos = [];

        foreach ($entityManager->getRepository(Promo::class)->findBy([], ["name" => "ASC"]) as $promo) {
            $promos[] = [
                "data" => $promo,
                "students" => 0
            ];
        }

        return $this->render('pages/logged_in/admin/promos.html.twig', [
            "promos" => $promos
        ]);
    }
}
