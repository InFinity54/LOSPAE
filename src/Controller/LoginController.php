<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute("homepage");
        }

        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('pages/login.html.twig', [
            "lastUsername" => $lastUsername,
            "error" => $error
        ]);
    }

    #[Route('/deactivated', name: 'deactivated')]
    public function deactivated(Request $request): Response
    {
        if (!is_null($this->getUser()) && $this->getUser()->isIsActivated()) {
            return $this->redirectToRoute("homepage");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        return $this->render('pages/deactivated.html.twig');
    }
}
