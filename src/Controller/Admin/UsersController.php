<?php

namespace App\Controller\Admin;

use App\Entity\NoteChange;
use App\Entity\StudentNote;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UsersController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    public function users(Request $request, EntityManagerInterface $entityManager): Response
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

        $users = $entityManager->getRepository(User::class)->findBy([], ["lastName" => "ASC", "firstName" => "ASC"]);
        $generatedLetters = [];

        if (count($request->query->all()) > 0 && in_array("generatedLetters", array_keys($request->query->all()))) {
            $generatedLetters = $request->query->all()["generatedLetters"];
        }

        return $this->render('pages/logged_in/admin/users.html.twig', [
            "users" => $users,
            "generatedLetters" => $generatedLetters
        ]);
    }

    #[Route('/admin/users/import', name: 'admin_users_import')]
    public function usersImport(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
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

        if ($request->isMethod("POST")) {
            $newUsers = [];
            $generatedLetters = [];
            $rowNo = 1;

            if (($fp = fopen($request->files->get("csvfile")->getPathName(), "r")) !== FALSE) {
                while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                    if ($rowNo > 1) {
                        $userLastName = $row[0];
                        $userFirstName = $row[1];
                        $userEmail = $row[2];
                        $userType = $row[3];

                        if (is_null($entityManager->getRepository(User::class)->findOneBy(["email" => $userEmail]))) {
                            $authorizedSpecialChars = ["#", "@", ".", "/", "!", ",", ":", ";", "?", "%", "*", "-", "+"];
                            $userPassword = ucfirst(strtolower(substr($userLastName, 0, 3)));
                            $userPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
                            $userPassword .= ucfirst(strtolower(substr($userFirstName, 0, 3)));
                            $userPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
                            $userPassword .= rand(10, 99);

                            $user = new User();
                            $user->setLastName($userLastName);
                            $user->setFirstName($userFirstName);
                            $user->setEmail($userEmail);
                            $user->setPassword($passwordHasher->hashPassword($user, $userPassword));
                            $user->setIsActivated(false);

                            switch ($userType) {
                                case "Enseignant":
                                    $user->setRoles(["ROLE_TEACHER"]);
                                    break;
                                default:
                                    $user->setRoles(["ROLE_STUDENT"]);
                                    break;
                            }

                            $entityManager->persist($user);
                            $entityManager->flush();

                            $newUsers[] = [
                                "lastName" => $userLastName,
                                "firstName" => $userFirstName,
                                "email" => $userEmail,
                                "type" => $userType,
                                "temporaryPassword" => $userPassword
                            ];
                        } else {
                            $this->addFlash("warning", "Un compte existe déjà pour cette adresse e-mail.");
                        }
                    }

                    $rowNo++;
                }

                fclose($fp);
            }

            if (count($newUsers) > 1) {
                $this->addFlash("success", count($newUsers)." utilisateurs ont été importés.");
            } else if (count($newUsers) === 1) {
                $this->addFlash("success", "Un utilisateur a été importé.");
            } else {
                $this->addFlash("warning", "Aucun utilisateur n'a été importé. Il y a peut-être eu un problème durant l'importation.");
            }

            if (count($newUsers) > 0) {
                try {
                    $templateFile = $this->getParameter("kernel.project_dir")."/public/files/users_import_letter_model_student.docx";

                    foreach ($newUsers as $newUser) {
                        $fileNameWithoutExt = "LOSPAE_NewUserLetter_".$newUser["lastName"]."_".$newUser["firstName"]."_".date("Ymd");
                        $pathToSave = $this->getParameter("kernel.project_dir")."/var/";

                        $templateProcessor = new TemplateProcessor($templateFile);
                        $templateProcessor->setValue("firstname", $newUser["firstName"]);
                        $templateProcessor->setValue("lastname", $newUser["lastName"]);
                        $templateProcessor->setValue("email", $newUser["email"]);
                        $templateProcessor->setValue("password", $newUser["temporaryPassword"]);
                        $templateProcessor->saveAs($pathToSave.$fileNameWithoutExt.".docx");

                        Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
                        Settings::setPdfRendererPath($this->getParameter("kernel.project_dir").'/vendor/mpdf/mpdf');

                        $reader = IOFactory::load($pathToSave.$fileNameWithoutExt.".docx");

                        $writer = IOFactory::createWriter($reader, 'PDF');
                        $writer->save($pathToSave.$fileNameWithoutExt.".pdf");

                        unlink($pathToSave.$fileNameWithoutExt.".docx");
                        $generatedLetters[] = $fileNameWithoutExt.".pdf";
                    }
                } catch (Exception $e) {
                    $this->addFlash("danger", "Il n'a pas été possible de générer certaines lettres de notification des utilisateurs.");
                }
            }

            return $this->redirectToRoute("admin_users", [
                "generatedLetters" => $generatedLetters
            ]);
        }

        return $this->render('pages/logged_in/admin/users_import.html.twig');
    }

    #[Route('/admin/users/configure/{id}', name: 'admin_user_configure')]
    public function userConfigure(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_users");
        }

        if ($request->isMethod("POST")) {
            $roles = [];

            if ($request->request->get("roleTeacher") && $request->request->get("roleTeacher") === "on") {
                $roles[] = "ROLE_TEACHER";
            }

            if ($request->request->get("roleStudent") && $request->request->get("roleStudent") === "on") {
                $roles[] = "ROLE_STUDENT";
            }

            if ($request->request->get("roleAdmin") && $request->request->get("roleAdmin") === "on") {
                $roles[] = "ROLE_ADMIN";
            }

            $user->setRoles($roles);
            $entityManager->flush();
            $this->addFlash("success", "Les rôles de l'utilisateur ciblé ont été modifiés.");
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_configuration.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/enable/{ids}', name: 'admin_user_enable')]
    public function userEnable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $users = [];

        foreach (explode(",", $ids) as $id) {
            $user = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($user)) {
                $users[] = $user;
            }
        }

        if (count($users) < count(explode(",", $id))) {
            $this->addFlash("danger", "Un ou plusieurs utilisateurs parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_enabling_confirm.html.twig', [
            "users" => $users,
            "usersIds" => $ids
        ]);
    }

    #[Route('/admin/users/enable/{ids}/do', name: 'admin_user_doenable')]
    public function userDoEnable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $users = [];

        foreach (explode(",", $ids) as $id) {
            $user = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($user)) {
                $users[] = $id;

                if (in_array("ROLE_STUDENT", $user->getRoles()) && is_null($user->getNote())) {
                    $note = new StudentNote();
                    $note->setStudent($user);
                    $note->setCurrentNote(20);
                    $entityManager->persist($note);
                    $entityManager->flush();
                    $user->setNote($note);
                }

                $user->setIsActivated(true);
                $entityManager->flush();
            }
        }

        if (count($users) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs utilisateurs parmis ceux demandés n'ont pas pu être activés car ils sont introuvables.");
            return $this->redirectToRoute("admin_users");
        }

        $this->addFlash("success", "Les comptes utilisateurs sélectionnés ont été activés.");
        return $this->redirectToRoute("admin_users");
    }

    #[Route('/admin/users/disable/{id}', name: 'admin_user_disable')]
    public function userDisable(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_disabling_confirm.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/disable/{id}/do', name: 'admin_user_dodisable')]
    public function userDoDisable(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_users");
        }

        $user->setIsActivated(false);
        $entityManager->flush();

        $this->addFlash("success", "Le compte utilisateur ciblé a été désactivé.");
        return $this->redirectToRoute("admin_users");
    }

    #[Route('/admin/users/remove/{id}', name: 'admin_user_remove')]
    public function userRemove(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_users");
        }

        return $this->render('pages/logged_in/admin/user_removing_confirm.html.twig', [
            "user" => $user
        ]);
    }

    #[Route('/admin/users/remove/{id}/do', name: 'admin_user_doremove')]
    public function userDoRemove(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $user = $entityManager->getRepository(User::class)->find($id);

        if (is_null($user)) {
            $this->addFlash("danger", "L'utilisateur demandé est introuvable.");
            return $this->redirectToRoute("admin_users");
        }

        $note = $entityManager->getRepository(StudentNote::class)->findOneBy(["student" => $id]);

        if (!is_null($note)) {
            $entityManager->remove($note);
            $entityManager->flush();
        }

        $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $id]);

        if (!is_null($noteChanges) && count($noteChanges) > 0) {
            foreach ($noteChanges as $noteChange) {
                $entityManager->remove($noteChange);
                $entityManager->flush();
            }
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash("success", "Le compte utilisateur ciblé a été définitivement supprimé.");
        return $this->redirectToRoute("admin_users");
    }
}
