<?php

namespace App\Controller\Admin;

use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\Promotion;
use App\Entity\School;
use App\Entity\TeacherPromotion;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PromosController extends AbstractController
{
    #[Route('/admin/promos', name: 'admin_promos')]
    public function promos(Request $request, EntityManagerInterface $entityManager): Response
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

        $promos = [];

        foreach ($entityManager->getRepository(Promotion::class)->findBy([], ["name" => "ASC"]) as $promo) {
            $promos[] = [
                "data" => $promo,
                "students" => $promo->getStudents()->count()
            ];
        }

        return $this->render('pages/logged_in/admin/promos.html.twig', [
            "promos" => $promos
        ]);
    }

    #[Route('/admin/promos/add', name: 'admin_promo_add')]
    public function promoAdd(Request $request, EntityManagerInterface $entityManager): Response
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
            $promo = new Promotion();
            $promo->setName($request->request->get("name"));
            $promo->setSchool($entityManager->getRepository(School::class)->find($request->request->get("school")));
            $entityManager->persist($promo);
            $entityManager->flush();

            $this->addFlash("success", "La promotion a été enregistré.");
            return $this->redirectToRoute("admin_promos");
        }

        $schools = $entityManager->getRepository(School::class)->findBy([], ["name" => "ASC"]);

        return $this->render('pages/logged_in/admin/promo_add.html.twig', [
            "schools" => $schools
        ]);
    }

    #[Route('/admin/promos/edit/{id}', name: 'admin_promo_edit')]
    public function promoEdit(Request $request, EntityManagerInterface $entityManager, int $id): Response
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

        $promo = $entityManager->getRepository(Promotion::class)->find($id);

        if (is_null($promo)) {
            $this->addFlash("danger", "La promotion demandée n'existe pas.");
            return $this->redirectToRoute("admin_promos");
        }

        return $this->render('pages/logged_in/admin/promo_edit.html.twig', [
            "promo" => $promo
        ]);
    }

    #[Route('/admin/promos/removestudent/{studentId}', name: 'admin_promo_removestudent')]
    public function promoRemoveStudent(Request $request, EntityManagerInterface $entityManager, int $studentId): Response
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

        $student = $entityManager->getRepository(User::class)->find($studentId);

        if (is_null($student)) {
            $this->addFlash("danger", "L'étudiant demandé n'existe pas.");
            return $this->redirectToRoute("admin_promos");
        }

        $promo = $entityManager->getRepository(Promotion::class)->find($student->getPromotion()->getId());

        if (is_null($promo)) {
            $this->addFlash("danger", "La promotion demandée n'existe pas.");
            return $this->redirectToRoute("admin_promos");
        }

        $student->setPromotion(null);
        $entityManager->persist($student);
        $entityManager->flush();
        $this->addFlash("success", "L'étudiant a été retiré de cette promotion.");
        return $this->redirectToRoute("admin_promo_edit", ["id" => $promo->getId()]);
    }

    #[Route('/admin/promos/remove/{id}', name: 'admin_promo_remove')]
    public function promoRemove(Request $request, EntityManagerInterface $entityManager, string $id): Response
    {
        if (!is_null($this->getUser()) && !$this->getUser()->isActivated()) {
            return $this->redirectToRoute("deactivated");
        }

        if (is_null($this->getUser())) {
            return $this->redirectToRoute("login");
        }

        if (
            !in_array("ROLE_TEACHER", $this->getUser()->getRoles())
            && !in_array("ROLE_TEACHER", $this->getUser()->getRoles())
            && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())
        ) {
            return $this->redirectToRoute("unconfigured");
        }

        if (!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $promo = $entityManager->getRepository(Promotion::class)->find($id);

        if (is_null($promo)) {
            $this->addFlash("danger", "La promotion demandée n'existe pas.");
            return $this->redirectToRoute("admin_promos");
        }

        return $this->render('pages/logged_in/admin/promo_removing_confirm.html.twig', [
            "promo" => $promo
        ]);
    }

    #[Route('/admin/promos/remove/{id}/do', name: 'admin_promo_doremove')]
    public function promoDoRemove(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $promo = $entityManager->getRepository(Promotion::class)->find($id);

        if (is_null($promo)) {
            $this->addFlash("danger", "La promotion demandée n'existe pas.");
            return $this->redirectToRoute("admin_promos");
        }

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
        $entityManager->flush();

        $this->addFlash("success", "La promotion ciblée et toutes les données liées à celle-ci ont été supprimées.");
        return $this->redirectToRoute("admin_promos");
    }
}
