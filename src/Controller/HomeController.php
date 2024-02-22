<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        return $this->render('pages/logged_in/index.html.twig');
    }
}
