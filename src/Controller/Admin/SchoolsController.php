<?php

namespace App\Controller\Admin;

use App\Entity\School;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        $schools = [];

        foreach ($entityManager->getRepository(School::class)->findBy([], ["name" => "ASC"]) as $school) {
            $studentsCount = 0;

            foreach ($school->getPromos() as $promo) {
                $studentsCount += $promo->getStudents()->count();
            }

            $schools[] = [
                "data" => $school,
                "promos" => $school->getPromos()->count(),
                "students" => $studentsCount
            ];
        }

        return $this->render('pages/logged_in/admin/schools.html.twig', [
            "schools" => $schools
        ]);
    }

    #[Route('/admin/schools/add', name: 'admin_school_add')]
    public function schoolAdd(Request $request, EntityManagerInterface $entityManager): Response
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

        if ($request->request->count() > 0) {
            $school = new School();
            $school->setName($request->request->get("name"));
            $school->setAddress($request->request->get("address"));
            $school->setAddressExtension($request->request->get("addressExtension"));
            $school->setPostalCode($request->request->get("postalCode"));
            $school->setCity($request->request->get("city"));
            $entityManager->persist($school);
            $entityManager->flush();

            $this->addFlash("success", "L'établissement scolaire a été enregistré.");
            return $this->redirectToRoute("admin_schools");
        }

        return $this->render('pages/logged_in/admin/school_add.html.twig');
    }

    #[Route('/admin/school/{id}/promos', name: 'admin_school_promoslist')]
    public function schoolPromosList(EntityManagerInterface $entityManager, string $id): JsonResponse
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isIsActivated()) {
            return new JsonResponse([], 403);
        }

        if (is_null($this->getUser())) {
            return new JsonResponse([], 403);
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            return new JsonResponse([], 403);
        }

        $school = $entityManager->getRepository(School::class)->find($id);
        $promos = [];

        if (!is_null($school)) {
            foreach ($school->getPromos() as $promo) {
                $promos[] = [
                    "id" => $promo->getId(),
                    "name" => $promo->getName()
                ];
            }

            return new JsonResponse($promos, 200);
        }

        return new JsonResponse([], 404);
    }
}
