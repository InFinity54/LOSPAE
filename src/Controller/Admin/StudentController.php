<?php

namespace App\Controller\Admin;

use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\Promotion;
use App\Entity\School;
use App\Entity\User;
use App\Services\FileUpload\UserAvatarUpload;
use App\Services\StringHandler;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class StudentController extends AbstractController
{
    #[Route('/admin/students', name: 'admin_students')]
    public function students(Request $request, EntityManagerInterface $entityManager): Response
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

        $generatedLetters = [];
        $pageNumber = !is_null($request->query->get("page")) ? $request->query->get("page") : 0;
        $students = $entityManager->getRepository(User::class)->findByRole("ROLE_STUDENT", 20, $pageNumber * 20);
        $studentsCount = $entityManager->getRepository(User::class)->countByRole("ROLE_STUDENT");

        if (count($request->query->all()) > 0 && in_array("generatedLetters", array_keys($request->query->all()))) {
            $generatedLetters = $request->query->all()["generatedLetters"];
        }

        return $this->render('pages/logged_in/admin/students.html.twig', [
            "generatedLetters" => $generatedLetters,
            "students" => $students,
            "totalElements" => $studentsCount,
            "currentPage" => $pageNumber
        ]);
    }

    #[Route('/admin/students/import', name: 'admin_students_import')]
    public function studentsImport(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
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

        if ($request->isMethod("POST")) {
            $newStudents = [];
            $generatedLetters = [];
            $rowNo = 1;

            if (($fp = fopen($request->files->get("csvfile")->getPathName(), "r")) !== FALSE) {
                while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                    if ($rowNo > 1) {
                        $studentLastName = $row[0];
                        $studentFirstName = $row[1];
                        $studentEmail = $row[2];
                        $studentSchool = $row[3];
                        $studentPromotion = $row[4];

                        if (is_null($entityManager->getRepository(User::class)->findOneBy(["email" => $studentEmail]))) {
                            $authorizedSpecialChars = ["#", "@", ".", "/", "!", ",", ":", ";", "?", "%", "*", "-", "+"];
                            $studentPassword = ucfirst(strtolower(substr(StringHandler::remove_accents($studentLastName), 0, 3)));
                            $studentPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
                            $studentPassword .= ucfirst(strtolower(substr(StringHandler::remove_accents($studentFirstName), 0, 3)));
                            $studentPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
                            $studentPassword .= rand(10, 99);

                            $student = new User();
                            $student->setLastName($studentLastName);
                            $student->setFirstName($studentFirstName);
                            $student->setEmail($studentEmail);
                            $student->setPassword($passwordHasher->hashPassword($student, $studentPassword));
                            $student->setRoles(["ROLE_STUDENT"]);
                            $student->setActivated(false);

                            if (!is_null($studentSchool)) {
                                $school = $entityManager->getRepository(School::class)->findOneBy(["uai" => $studentSchool]);

                                if (!is_null($school)) {
                                    if (!is_null($studentPromotion)) {
                                        $promotion = $entityManager->getRepository(Promotion::class)->findOneBy(["name" => $studentPromotion]);

                                        if (!is_null($promotion)) {
                                            $student->setSchool($school);
                                            $student->setPromotion($promotion);
                                        }
                                    }
                                }
                            }

                            $entityManager->persist($student);
                            $entityManager->flush();

                            $newStudents[] = [
                                "lastName" => $studentLastName,
                                "firstName" => $studentFirstName,
                                "email" => $studentEmail,
                                "temporaryPassword" => $studentPassword
                            ];
                        } else {
                            $this->addFlash("warning", "Un compte existe déjà pour l'adresse e-mail suivante : ".$studentEmail);
                        }
                    }

                    $rowNo++;
                }

                fclose($fp);
            }

            if (count($newStudents) > 1) {
                $this->addFlash("success", count($newStudents)." étudiants ont été importés.");
            } else if (count($newStudents) === 1) {
                $this->addFlash("success", "Un étudiant a été importé.");
            } else {
                $this->addFlash("warning", "Aucun étudiant n'a été importé. Il y a peut-être eu un problème durant l'importation.");
            }

            if (count($newStudents) > 0) {
                try {
                    $templateFile = $this->getParameter("kernel.project_dir")."/public/files/users_import_letter_model_student.docx";

                    foreach ($newStudents as $newStudent) {
                        $fileNameWithoutExt = "LOSPAE_NewUserLetter_Student_".$newStudent["lastName"]."_".$newStudent["firstName"]."_".date("Ymd");
                        $pathToSave = $this->getParameter("kernel.project_dir")."/var/";

                        $templateProcessor = new TemplateProcessor($templateFile);
                        $templateProcessor->setValue("firstname", $newStudent["firstName"]);
                        $templateProcessor->setValue("lastname", $newStudent["lastName"]);
                        $templateProcessor->setValue("email", $newStudent["email"]);
                        $templateProcessor->setValue("password", $newStudent["temporaryPassword"]);
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
                    $this->addFlash("danger", "Il n'a pas été possible de générer certaines lettres de notification des étudiants.");
                }
            }

            return $this->redirectToRoute("admin_students", [
                "generatedLetters" => $generatedLetters
            ]);
        }

        return $this->render('pages/logged_in/admin/students_import.html.twig');
    }

    #[Route('/admin/students/configure/{id}', name: 'admin_student_configure')]
    public function studentConfigure(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $student = $entityManager->getRepository(User::class)->find($id);

        if (is_null($student)) {
            $this->addFlash("danger", "L'étudiant demandé est introuvable.");
            return $this->redirectToRoute("admin_students");
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

            $student->setRoles($roles);
            $entityManager->flush();
            $this->addFlash("success", "Les rôles de l'étudiant ciblé ont été modifiés.");
            return $this->redirectToRoute("admin_students");
        }

        return $this->render('pages/logged_in/admin/student_configuration.html.twig', [
            "student" => $student
        ]);
    }

    #[Route('/admin/students/edit/{ids}', name: 'admin_student_edit')]
    public function studentEdit(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $schools = $entityManager->getRepository(School::class)->findBy([], ["name" => "ASC"]);
        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $student;
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        if ($request->isMethod("POST")) {
            foreach ($students as $student) {
                $student->setPromotion($entityManager->getRepository(Promotion::class)->find($request->request->get("promo")));
                $entityManager->flush();
            }

            $this->addFlash("success", "L'affectation des étudiants ciblés ont été modifiées.");
            return $this->redirectToRoute("admin_students");
        }

        return $this->render('pages/logged_in/admin/student_edit.html.twig', [
            "students" => $students,
            "studentsIds" => $ids,
            "schools" => $schools
        ]);
    }

    #[Route('/admin/students/enable/{ids}', name: 'admin_student_enable')]
    public function studentEnable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $student;
            }
        }

        if (count($students) < count(explode(",", $id))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        return $this->render('pages/logged_in/admin/student_enabling_confirm.html.twig', [
            "students" => $students,
            "studentsIds" => $ids
        ]);
    }

    #[Route('/admin/students/enable/{ids}/do', name: 'admin_student_doenable')]
    public function studentDoEnable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $id;

                $student->setActivated(true);
                $entityManager->flush();
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés n'ont pas pu être activés car ils sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        $this->addFlash("success", "Les comptes étudiants sélectionnés ont été activés.");
        return $this->redirectToRoute("admin_students");
    }

    #[Route('/admin/students/disable/{ids}', name: 'admin_student_disable')]
    public function studentDisable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $student;
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        return $this->render('pages/logged_in/admin/student_disabling_confirm.html.twig', [
            "students" => $students,
            "studentsIds" => $ids
        ]);
    }

    #[Route('/admin/students/disable/{ids}/do', name: 'admin_student_dodisable')]
    public function studentDoDisable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            $currentNotes = $entityManager->getRepository(CurrentNote::class)->findBy(["student" => $student]);

            foreach ($currentNotes as $currentNote) {
                $entityManager->remove($currentNote);
            }

            if (!is_null($student)) {
                $students[] = $id;
                $student->setPromotion(null);
                $student->setSchool(null);
                $student->setActivated(false);
                $entityManager->flush();
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés n'ont pas pu être désactivés car ils sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        $this->addFlash("success", "Les comptes étudiants sélectionnés ont été désactivés.");
        return $this->redirectToRoute("admin_students");
    }

    #[Route('/admin/students/remove/{ids}', name: 'admin_student_remove')]
    public function studentRemove(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $student;
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        return $this->render('pages/logged_in/admin/student_removing_confirm.html.twig', [
            "students" => $students,
            "studentsIds" => $ids
        ]);
    }

    #[Route('/admin/students/remove/{ids}/do', name: 'admin_student_doremove')]
    public function studentDoRemove(Request $request, EntityManagerInterface $entityManager, UserAvatarUpload $avatarUpload, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $id;
                $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["student" => $id]);

                if (!is_null($noteChanges) && count($noteChanges) > 0) {
                    foreach ($noteChanges as $noteChange) {
                        $entityManager->remove($noteChange);
                        $entityManager->flush();
                    }
                }

                if ($student->getAvatar() !== "default_avatar.svg") {
                    $studentAvatarFullPath = $avatarUpload->getTargetDirectory().$student->getAvatar();

                    if (file_exists($studentAvatarFullPath)) {
                        unlink($studentAvatarFullPath);
                    }
                }

                $entityManager->remove($student);
                $entityManager->flush();
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés n'ont pas pu être désactivés car ils sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        $this->addFlash("success", "Les comptes étudiants sélectionnés ont été désactivés.");
        return $this->redirectToRoute("admin_students");
    }

    #[Route('/admin/students/reset/{ids}', name: 'admin_student_reset')]
    public function studentReset(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $student;
            }
        }

        if (count($students) < count(explode(",", $id))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        return $this->render('pages/logged_in/admin/student_reset_confirm.html.twig', [
            "students" => $students,
            "studentsIds" => $ids
        ]);
    }

    #[Route('/admin/students/reset/{ids}/do', name: 'admin_student_doreset')]
    public function studentDoReset(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        if (!in_array("ROLE_ADMIN", $this->getStudent()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $students = [];

        foreach (explode(",", $ids) as $id) {
            $student = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($student)) {
                $students[] = $id;

                foreach ($student->getNoteChanges() as $noteChange) {
                    $entityManager->remove($noteChange);
                    $entityManager->flush();
                }

                foreach ($student->getCurrentNotes() as $currentNote) {
                    $currentNote->setNote(20);
                    $entityManager->flush();
                }

                $entityManager->flush();
            }
        }

        if (count($students) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs étudiants parmis ceux demandés n'ont pas pu voir leurs notes être réinitialisées car ils sont introuvables.");
            return $this->redirectToRoute("admin_students");
        }

        $this->addFlash("success", "Les notes des comptes étudiants sélectionnés ont été réinitialisées.");
        return $this->redirectToRoute("admin_students");
    }
}
