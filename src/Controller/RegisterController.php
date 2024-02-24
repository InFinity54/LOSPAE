<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if (!is_null($this->getUser())) {
            return $this->redirectToRoute("homepage");
        }

        if ($request->request->count() > 0) {
            try {
                if (is_null($entityManager->getRepository(User::class)->findOneBy(["email" => $request->request->get("email")]))) {
                    $user = new User();
                    $user->setLastName($request->request->get("lastname"));
                    $user->setFirstName($request->request->get("firstname"));
                    $user->setEmail($request->request->get("email"));
                    $user->setPassword($passwordHasher->hashPassword($user, $request->request->get("password")));
                    $user->setIsActivated(false);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    return $this->render('pages/register_success.html.twig');
                } else {
                    $this->addFlash("warning", "Un compte existe déjà pour cette adresse e-mail.");
                }

                return $this->redirectToRoute("login");
            } catch (Exception $e) {
                $this->addFlash("danger", "Une erreur est survenue durant votre inscription. Veuillez réessayer plus tard.");
            }
        }

        return $this->render('pages/register.html.twig');
    }
}
