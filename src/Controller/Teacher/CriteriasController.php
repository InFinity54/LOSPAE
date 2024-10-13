<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CriteriasController extends AbstractController
{
    #[Route('/manage/criterias', name: 'teacher_criterias')]
    public function criterias(EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $criterias = $entityManager->getRepository(Criteria::class)->findBy(["teacher" => $this->getUser()], ["name" => "ASC"]);

        return $this->render('pages/logged_in/teacher/criterias.html.twig', [
            "criterias" => $criterias
        ]);
    }

    #[Route('/manage/criterias/add', name: 'teacher_criteria_add')]
    public function criteriaAdd(Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        if ($request->request->count() > 0) {
            if (!is_null($entityManager->getRepository(Criteria::class)->findOneBy(["teacher" => $this->getUser(), "name" => $request->request->get("name")]))) {
                $this->addFlash("danger", "Ce critère n'a pas été enregistré car un autre critère similaire existe déjà.");
                return $this->redirectToRoute("teacher_criterias");
            }

            $criteria = new Criteria();
            $criteria->setName($request->request->get("name"));
            $criteria->setImpact($request->request->get("impact"));
            $criteria->setModality($request->request->get("modality"));
            $criteria->setTeacher($this->getUser());
            $entityManager->persist($criteria);
            $entityManager->flush();
            $this->addFlash("success", "Le critère a bien été enregistré et est désormais utilisable sur LOSPAÉ.");
            return $this->redirectToRoute("teacher_criterias");
        }

        return $this->render('pages/logged_in/teacher/criteria_add.html.twig');
    }

    #[Route('/manage/criterias/edit/{id}', name: 'teacher_criteria_edit')]
    public function criteriaEdit(string $id, Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $criteria = $entityManager->getRepository(Criteria::class)->find($id);

        if (is_null($criteria)) {
            $this->addFlash("danger", "Le critère demandé n'existe pas.");
            return $this->redirectToRoute("teacher_criterias");
        }

        if ($request->request->count() > 0) {
            $criteria->setTeacher($this->getUser());
            $criteria->setName($request->request->get("name"));
            $criteria->setImpact($request->request->get("impact"));
            $criteria->setModality($request->request->get("modality"));
            $entityManager->persist($criteria);
            $entityManager->flush();
            $this->addFlash("success", "Les modifications apportées au critère ont été enregistrées.");
            return $this->redirectToRoute("teacher_criterias");
        }

        return $this->render('pages/logged_in/teacher/criteria_edit.html.twig', [
            "criteria" => $criteria
        ]);
    }

    #[Route('/manage/criterias/remove/{id}', name: 'teacher_criteria_remove')]
    public function criteriaRemove(string $id, Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $criteria = $entityManager->getRepository(Criteria::class)->find($id);

        if (is_null($criteria)) {
            $this->addFlash("danger", "Le critère demandé n'existe pas.");
            return $this->redirectToRoute("teacher_criterias");
        }

        return $this->render('pages/logged_in/teacher/criteria_removing_confirm.html.twig', [
            "criteria" => $criteria
        ]);
    }

    #[Route('/manage/criterias/remove/{id}/do', name: 'teacher_criteria_doremove')]
    public function criteriaDoRemove(string $id, Request $request, EntityManagerInterface $entityManager): Response
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $criteria = $entityManager->getRepository(Criteria::class)->find($id);

        if (is_null($criteria)) {
            $this->addFlash("danger", "Le critère demandé n'existe pas.");
            return $this->redirectToRoute("teacher_criterias");
        }

        $entityManager->remove($criteria);
        $entityManager->flush();
        $this->addFlash("success", "Le critère a été supprimé.");
        return $this->redirectToRoute("teacher_criterias");
    }
}
