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
        if (!is_null($this->getUser()) && $this->getUser()->isActivated()
            && (
                in_array("ROLE_STUDENT", $this->getUser()->getRoles())
                || in_array("ROLE_TEACHER", $this->getUser()->getRoles())
                || in_array("ROLE_ADMIN", $this->getUser()->getRoles())
            )
        ) {
            return $this->redirectToRoute("homepage");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        return $this->render('pages/deactivated.html.twig');
    }

    #[Route('/unconfigured', name: 'unconfigured')]
    public function unconfigured(Request $request): Response
    {
        if (!is_null($this->getUser()) && $this->getUser()->isActivated()
            && (
                in_array("ROLE_STUDENT", $this->getUser()->getRoles())
                || in_array("ROLE_TEACHER", $this->getUser()->getRoles())
                || in_array("ROLE_ADMIN", $this->getUser()->getRoles())
            )
        ) {
            return $this->redirectToRoute("homepage");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        return $this->render('pages/unconfigured.html.twig');
    }
}
