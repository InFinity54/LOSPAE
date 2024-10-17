<?php

namespace App\Controller\Admin;

use App\Entity\Academy;
use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\Promotion;
use App\Entity\School;
use App\Entity\TeacherPromotion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SchoolsController extends AbstractController
{
    #[Route('/admin/schools', name: 'admin_schools')]
    public function schools(Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $schools = [];
        $schoolsCount = $entityManager->getRepository(School::class)->count();
        $pageNumber = !is_null($request->query->get("page")) ? $request->query->get("page") : 0;

        foreach ($entityManager->getRepository(School::class)->findBy([], ["name" => "ASC"], 20, $pageNumber * 20) as $school) {
            $studentsCount = 0;

            foreach ($school->getPromotions() as $promo) {
                $studentsCount += $promo->getStudents()->count();
            }

            $schools[] = [
                "data" => $school,
                "promos" => $school->getPromotions()->count(),
                "students" => $studentsCount
            ];
        }

        return $this->render('pages/logged_in/admin/schools.html.twig', [
            "schools" => $schools,
            "totalElements" => $schoolsCount,
            "currentPage" => $pageNumber
        ]);
    }

    #[Route('/admin/schools/add', name: 'admin_school_add')]
    public function schoolAdd(Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        if ($request->request->count() > 0) {
            $school = new School();
            $school->setUai($request->request->get("uai"));
            $school->setName($request->request->get("name"));
            $school->setAddress($request->request->get("address"));
            $school->setPostalCode($request->request->get("postalCode"));
            $school->setCity($request->request->get("city"));
            $school->setAcademy($entityManager->getRepository(Academy::class)->find($request->request->get("academy")));
            $entityManager->persist($school);
            $entityManager->flush();

            $this->addFlash("success", "L'établissement scolaire a été enregistré.");
            return $this->redirectToRoute("admin_schools");
        }

        return $this->render('pages/logged_in/admin/school_add.html.twig', [
            "academies" => $entityManager->getRepository(Academy::class)->findAll()
        ]);
    }

    #[Route('/admin/school/{id}/promos', name: 'admin_school_promoslist')]
    public function schoolPromosList(EntityManagerInterface $entityManager, string $id): JsonResponse
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isActivated()) {
            return new JsonResponse([], 403);
        }

        if (is_null($this->getUser())) {
            return new JsonResponse([], 403);
        }

        if (
            !in_array("ROLE_STUDENT", $this->getUser()->getRoles())
            && !in_array("ROLE_TEACHER", $this->getUser()->getRoles())
            && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())
        ) {
            return new JsonResponse([], 403);
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            return new JsonResponse([], 403);
        }

        $school = $entityManager->getRepository(School::class)->find($id);
        $promos = [];

        if (!is_null($school)) {
            foreach ($school->getPromotions() as $promo) {
                $promos[] = [
                    "id" => $promo->getId(),
                    "name" => $promo->getName()
                ];
            }

            return new JsonResponse($promos, 200);
        }

        return new JsonResponse([], 404);
    }

    #[Route('/admin/schools/remove/{ids}', name: 'admin_school_remove')]
    public function schoolRemove(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $schools = [];

        foreach (explode(",", $ids) as $id) {
            $school = $entityManager->getRepository(School::class)->find($id);

            if (!is_null($school)) {
                $schools[] = $school;
            }
        }

        if (count($schools) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs établissements parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_schools");
        }

        return $this->render('pages/logged_in/admin/school_removing_confirm.html.twig', [
            "schools" => $schools,
            "schoolsIds" => $ids
        ]);
    }

    #[Route('/admin/schools/remove/{ids}/do', name: 'admin_school_doremove')]
    public function schoolDoRemove(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $schools = [];

        foreach (explode(",", $ids) as $id) {
            $school = $entityManager->getRepository(School::class)->find($id);

            if (!is_null($school)) {
                $schools[] = $id;
                $promos = $entityManager->getRepository(Promotion::class)->findBy(["school" => $id]);

                foreach ($promos as $promo) {
                    foreach ($promo->getStudents() as $student) {
                        foreach ($entityManager->getRepository(NoteChange::class)->findBy(["student" => $student]) as $noteChange) {
                            $entityManager->remove($noteChange);
                        }

                        foreach ($entityManager->getRepository(CurrentNote::class)->findBy(["student" => $student]) as $currentNote) {
                            $entityManager->remove($currentNote);
                        }

                        $student->setPromotion(null);
                        $student->setSchool(null);
                    }

                    foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["promotion" => $promo]) as $teacherPromotion) {
                        $entityManager->remove($teacherPromotion);
                    }

                    $entityManager->remove($promo);
                }

                $entityManager->remove($school);
                $entityManager->flush();
            }
        }

        if (count($schools) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs établissements parmis ceux demandés n'ont pas pu être supprimés car ils sont introuvables.");
            return $this->redirectToRoute("admin_schools");
        }

        $this->addFlash("success", "Les établissements sélectionnés, ainsi que les promotions et les étudiants rattachés, ont été supprimés.");
        return $this->redirectToRoute("admin_schools");
    }
}
