<?php

namespace App\Controller\Admin;

use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\Promotion;
use App\Entity\School;
use App\Entity\TeacherPromotion;
use App\Entity\User;
use App\Services\FileUpload\UserAvatarUpload;
use App\Services\StringHandler;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class TeacherController extends AbstractController
{
    #[Route('/admin/teachers', name: 'admin_teachers')]
    public function teachers(Request $request, EntityManagerInterface $entityManager): Response
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

        $generatedLetters = [];
        $pageNumber = !is_null($request->query->get("page")) ? $request->query->get("page") : 0;
        $teachers = $entityManager->getRepository(User::class)->findByRole("ROLE_TEACHER", 20, $pageNumber * 20);
        $teachersCount = $entityManager->getRepository(User::class)->countByRole("ROLE_TEACHER");

        if (count($request->query->all()) > 0 && in_array("generatedLetters", array_keys($request->query->all()))) {
            $generatedLetters = $request->query->all()["generatedLetters"];
        }

        return $this->render('pages/logged_in/admin/teachers.html.twig', [
            "generatedLetters" => $generatedLetters,
            "teachers" => $teachers,
            "totalElements" => $teachersCount,
            "currentPage" => $pageNumber
        ]);
    }

    #[Route('/admin/teachers/import', name: 'admin_teachers_import')]
    public function teachersImport(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
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

        if ($request->isMethod("POST")) {
            $newTeachers = [];
            $generatedLetters = [];
            $rowNo = 1;

            if (($fp = fopen($request->files->get("csvfile")->getPathName(), "r")) !== FALSE) {
                while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                    if ($rowNo > 1) {
                        $teacherLastName = $row[0];
                        $teacherFirstName = $row[1];
                        $teacherEmail = $row[2];

                        if (is_null($entityManager->getRepository(User::class)->findOneBy(["email" => $teacherEmail]))) {
                            $authorizedSpecialChars = ["#", "@", ".", "/", "!", ",", ":", ";", "?", "%", "*", "-", "+"];
                            $teacherPassword = ucfirst(strtolower(substr(StringHandler::remove_accents($teacherLastName), 0, 3)));
                            $teacherPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
                            $teacherPassword .= ucfirst(strtolower(substr(StringHandler::remove_accents($teacherFirstName), 0, 3)));
                            $teacherPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
                            $teacherPassword .= rand(10, 99);

                            $teacher = new User();
                            $teacher->setLastName($teacherLastName);
                            $teacher->setFirstName($teacherFirstName);
                            $teacher->setEmail($teacherEmail);
                            $teacher->setPassword($passwordHasher->hashPassword($teacher, $teacherPassword));
                            $teacher->setRoles(["ROLE_TEACHER"]);
                            $teacher->setActivated(false);

                            $entityManager->persist($teacher);
                            $entityManager->flush();

                            $newTeachers[] = [
                                "lastName" => $teacherLastName,
                                "firstName" => $teacherFirstName,
                                "email" => $teacherEmail,
                                "temporaryPassword" => $teacherPassword
                            ];
                        } else {
                            $this->addFlash("warning", "Un compte existe déjà pour l'adresse e-mail suivante : ".$teacherEmail);
                        }
                    }

                    $rowNo++;
                }

                fclose($fp);
            }

            if (count($newTeachers) > 1) {
                $this->addFlash("success", count($newTeachers)." enseignants ont été importés.");
            } else if (count($newTeachers) === 1) {
                $this->addFlash("success", "Un enseignant a été importé.");
            } else {
                $this->addFlash("warning", "Aucun enseignant n'a été importé. Il y a peut-être eu un problème durant l'importation.");
            }

            if (count($newTeachers) > 0) {
                try {
                    $templateFile = $this->getParameter("kernel.project_dir")."/public/files/users_import_letter_model_teacher.docx";

                    foreach ($newTeachers as $newTeacher) {
                        $fileNameWithoutExt = "LOSPAE_NewUserLetter_Teacher_".$newTeacher["lastName"]."_".$newTeacher["firstName"]."_".date("Ymd");
                        $pathToSave = $this->getParameter("kernel.project_dir")."/var/";

                        $templateProcessor = new TemplateProcessor($templateFile);
                        $templateProcessor->setValue("firstname", $newTeacher["firstName"]);
                        $templateProcessor->setValue("lastname", $newTeacher["lastName"]);
                        $templateProcessor->setValue("email", $newTeacher["email"]);
                        $templateProcessor->setValue("password", $newTeacher["temporaryPassword"]);
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
                    $this->addFlash("danger", "Il n'a pas été possible de générer certaines lettres de notification des enseignants.");
                }
            }

            return $this->redirectToRoute("admin_teachers", [
                "generatedLetters" => $generatedLetters
            ]);
        }

        return $this->render('pages/logged_in/admin/teachers_import.html.twig');
    }

    #[Route('/admin/teachers/configure/{id}', name: 'admin_teacher_configure')]
    public function teacherConfigure(Request $request, EntityManagerInterface $entityManager, string $id): Response
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

        $teacher = $entityManager->getRepository(User::class)->find($id);

        if (is_null($teacher)) {
            $this->addFlash("danger", "L'enseignant demandé est introuvable.");
            return $this->redirectToRoute("admin_teachers");
        }

        if ($request->isMethod("POST")) {
            $alreadyAffectedPromotionsIds = [];

            foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $teacher]) as $alreadyAffectedPromotion) {
                //$entityManager->remove($alreadyAffectedPromotion);
                $alreadyAffectedPromotionsIds[] = $alreadyAffectedPromotion->getPromotion()->getId();
            }

            if (!is_null($request->request->all("promotions")) && count($request->request->all("promotions")) > 0) {
                foreach ($alreadyAffectedPromotionsIds as $alreadyAffectedPromotionId) {
                    if (!in_array($alreadyAffectedPromotionId, $request->request->all("promotions"))) {
                        $teacherPromotion = $entityManager->getRepository(TeacherPromotion::class)->findOneBy(["teacher" => $teacher, "promotion" => $alreadyAffectedPromotionId]);

                        foreach ($teacherPromotion->getPromotion()->getStudents() as $student) {
                            $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $teacher, "student" => $student]);
                            $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["teacher" => $teacher, "student" => $student]);

                            if (!is_null($currentNote)) {
                                $entityManager->remove($currentNote);
                            }

                            foreach ($noteChanges as $noteChange) {
                                $entityManager->remove($noteChange);
                            }
                        }

                        $entityManager->remove($teacherPromotion);
                    }
                }

                foreach ($request->request->all("promotions") as $promoId) {
                    $promo = $entityManager->getRepository(Promotion::class)->find($promoId);
                    $teacherPromotion = $entityManager->getRepository(TeacherPromotion::class)->findOneBy(["teacher" => $teacher, "promotion" => $promo]);

                    if (is_null($teacherPromotion)) {
                        $teacherPromotion = new TeacherPromotion();
                        $teacherPromotion->setTeacher($teacher);
                        $teacherPromotion->setPromotion($promo);
                        $entityManager->persist($teacherPromotion);

                        foreach ($promo->getStudents() as $student) {
                            $currentNote = $entityManager->getRepository(CurrentNote::class)->findOneBy(["teacher" => $teacher, "student" => $student]);

                            if (is_null($currentNote)) {
                                $currentNote = new CurrentNote();
                                $currentNote->setStudent($student);
                                $currentNote->setTeacher($teacher);
                                $currentNote->setNote(20);
                                $entityManager->persist($currentNote);
                            }
                        }
                    }
                }
            }

            $entityManager->flush();
            $this->addFlash("success", "L'affectation de l'enseignant ciblé a été modifiée.");
            return $this->redirectToRoute("admin_teachers");
        }

        $teacherPromotions = [];
        $schools = $entityManager->getRepository(School::class)->findBy([], ["name" => "ASC"]);
        $promotions = $entityManager->getRepository(Promotion::class)->findBy([], ["name" => "ASC"]);

        foreach ($entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $teacher]) as $teacherPromotion) {
            $teacherPromotions[] = $teacherPromotion->getPromotion()->getId();
        }

        return $this->render('pages/logged_in/admin/teacher_configuration.html.twig', [
            "teacher" => $teacher,
            "teacherPromotions" => $teacherPromotions,
            "schools" => $schools,
            "promotions" => $promotions
        ]);
    }

    #[Route('/admin/teachers/enable/{ids}', name: 'admin_teacher_enable')]
    public function teacherEnable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $teachers = [];

        foreach (explode(",", $ids) as $id) {
            $teacher = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($teacher)) {
                $teachers[] = $teacher;
            }
        }

        if (count($teachers) < count(explode(",", $id))) {
            $this->addFlash("danger", "Un ou plusieurs enseignants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_teachers");
        }

        return $this->render('pages/logged_in/admin/teacher_enabling_confirm.html.twig', [
            "teachers" => $teachers,
            "teachersIds" => $ids
        ]);
    }

    #[Route('/admin/teachers/enable/{ids}/do', name: 'admin_teacher_doenable')]
    public function teacherDoEnable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $teachers = [];

        foreach (explode(",", $ids) as $id) {
            $teacher = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($teacher)) {
                $teachers[] = $id;

                $teacher->setActivated(true);
                $entityManager->flush();
            }
        }

        if (count($teachers) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs enseignants parmis ceux demandés n'ont pas pu être activés car ils sont introuvables.");
            return $this->redirectToRoute("admin_teachers");
        }

        $this->addFlash("success", "Les comptes enseignants sélectionnés ont été activés.");
        return $this->redirectToRoute("admin_teachers");
    }

    #[Route('/admin/teachers/disable/{ids}', name: 'admin_teacher_disable')]
    public function teacherDisable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $teachers = [];

        foreach (explode(",", $ids) as $id) {
            $teacher = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($teacher)) {
                $teachers[] = $teacher;
            }
        }

        if (count($teachers) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs enseignants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_teachers");
        }

        return $this->render('pages/logged_in/admin/teacher_disabling_confirm.html.twig', [
            "teachers" => $teachers,
            "teachersIds" => $ids
        ]);
    }

    #[Route('/admin/teachers/disable/{ids}/do', name: 'admin_teacher_dodisable')]
    public function teacherDoDisable(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $teachers = [];

        foreach (explode(",", $ids) as $id) {
            $teacher = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($teacher)) {
                $teachers[] = $id;
                $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["teacher" => $id]);
                $teacherPromotions = $entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $teacher]);
                $currentNotes = $entityManager->getRepository(CurrentNote::class)->findBy(["teacher" => $teacher]);

                foreach ($teacherPromotions as $teacherPromotion) {
                    $entityManager->remove($teacherPromotion);
                }

                foreach ($currentNotes as $currentNote) {
                    $entityManager->remove($currentNote);
                }

                foreach ($noteChanges as $noteChange) {
                    $entityManager->remove($noteChange);
                }

                $teacher->setActivated(false);
                $entityManager->flush();
            }
        }

        if (count($teachers) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs enseignants parmis ceux demandés n'ont pas pu être désactivés car ils sont introuvables.");
            return $this->redirectToRoute("admin_teachers");
        }

        $this->addFlash("success", "Les comptes enseignants sélectionnés ont été désactivés.");
        return $this->redirectToRoute("admin_teachers");
    }

    #[Route('/admin/teachers/remove/{ids}', name: 'admin_teacher_remove')]
    public function teacherRemove(Request $request, EntityManagerInterface $entityManager, string $ids): Response
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

        $teachers = [];

        foreach (explode(",", $ids) as $id) {
            $teacher = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($teacher)) {
                $teachers[] = $teacher;
            }
        }

        if (count($teachers) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs enseignants parmis ceux demandés sont introuvables.");
            return $this->redirectToRoute("admin_teachers");
        }

        return $this->render('pages/logged_in/admin/teacher_removing_confirm.html.twig', [
            "teachers" => $teachers,
            "teachersIds" => $ids
        ]);
    }

    #[Route('/admin/teachers/remove/{ids}/do', name: 'admin_teacher_doremove')]
    public function teacherDoRemove(Request $request, EntityManagerInterface $entityManager, UserAvatarUpload $avatarUpload, string $ids): Response
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

        $teachers = [];

        foreach (explode(",", $ids) as $id) {
            $teacher = $entityManager->getRepository(User::class)->find($id);

            if (!is_null($teacher)) {
                $teachers[] = $id;
                $noteChanges = $entityManager->getRepository(NoteChange::class)->findBy(["teacher" => $id]);
                $teacherPromotions = $entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $teacher]);
                $currentNotes = $entityManager->getRepository(CurrentNote::class)->findBy(["teacher" => $teacher]);

                foreach ($teacherPromotions as $teacherPromotion) {
                    $entityManager->remove($teacherPromotion);
                }

                foreach ($currentNotes as $currentNote) {
                    $entityManager->remove($currentNote);
                }

                foreach ($noteChanges as $noteChange) {
                    $entityManager->remove($noteChange);
                }

                if ($teacher->getAvatar() !== "default_avatar.svg") {
                    $teacherAvatarFullPath = $avatarUpload->getTargetDirectory().$teacher->getAvatar();

                    if (file_exists($teacherAvatarFullPath)) {
                        unlink($teacherAvatarFullPath);
                    }
                }

                $entityManager->remove($teacher);
                $entityManager->flush();
            }
        }

        if (count($teachers) < count(explode(",", $ids))) {
            $this->addFlash("danger", "Un ou plusieurs enseignants parmis ceux demandés n'ont pas pu être désactivés car ils sont introuvables.");
            return $this->redirectToRoute("admin_teachers");
        }

        $this->addFlash("success", "Les comptes enseignants sélectionnés ont été désactivés.");
        return $this->redirectToRoute("admin_teachers");
    }
}
