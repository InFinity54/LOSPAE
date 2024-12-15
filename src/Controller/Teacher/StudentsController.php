<?php

namespace App\Controller\Teacher;

use App\Entity\Criteria;
use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\TeacherPromotion;
use App\Entity\User;
use App\Services\StringHandler;
use DateTime;
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

class StudentsController extends AbstractController
{
    #[Route('/students', name: 'teacher_students')]
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

        if (!in_array("ROLE_TEACHER", $this->getUser()->getRoles())) {
            $this->addFlash("danger", "Vous n'êtes pas autorisé à accéder à cette page.");
            return $this->redirectToRoute("homepage");
        }

        $generatedLetters = [];
        $teacherPromotions = $entityManager->getRepository(TeacherPromotion::class)->findBy(["teacher" => $this->getUser()]);
        $students = [];

        foreach ($teacherPromotions as $teacherPromotion) {
            foreach ($teacherPromotion->getPromotion()->getStudents() as $student) {
                if ($student->isActivated()) {
                    $students[] = $student;
                }
            }
        }

        usort($students, function($a, $b) {
            // Comparaison par nom de famille
            $lastNameComparison = strcmp($a->getLastName(), $b->getLastName());

            // Si les noms de famille sont identiques, comparer par prénom
            if ($lastNameComparison === 0) {
                return strcmp($a->getFirstName(), $b->getFirstName());
            }

            return $lastNameComparison;
        });

        if (count($request->query->all()) > 0 && in_array("generatedLetters", array_keys($request->query->all()))) {
            $generatedLetters = $request->query->all()["generatedLetters"];
        }

        return $this->render('pages/logged_in/teacher/students.html.twig', [
            "generatedLetters" => $generatedLetters,
            "students" => $students
        ]);
    }

    #[Route('/student/{id}/passwordreset', name: 'teacher_student_passwordreset')]
    public function studentPasswordReset(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, int $id): Response
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

        $student = $entityManager->getRepository(User::class)->find($id);
        $generatedLetters = [];

        if (is_null($student)) {
            $this->addFlash("danger", "L'étudiant demandé n'existe pas.");
            return $this->redirectToRoute("teacher_students");
        }

        $authorizedSpecialChars = ["#", "@", ".", "/", "!", ",", ":", ";", "?", "%", "*", "-", "+"];
        $studentPassword = ucfirst(strtolower(substr(StringHandler::remove_accents($student->getLastName()), 0, 3)));
        $studentPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
        $studentPassword .= ucfirst(strtolower(substr(StringHandler::remove_accents($student->getFirstName()), 0, 3)));
        $studentPassword .= $authorizedSpecialChars[array_rand($authorizedSpecialChars)];
        $studentPassword .= rand(10, 99);

        $student->setPassword($passwordHasher->hashPassword($student, $studentPassword));

        try {
            $templateFile = $this->getParameter("kernel.project_dir")."/public/files/users_import_letter_model_student.docx";

            $fileNameWithoutExt = "LOSPAE_NewUserLetter_Student_".$student->getLastName()."_".$student->getFirstName()."_".date("Ymd");
            $pathToSave = $this->getParameter("kernel.project_dir")."/var/";

            $templateProcessor = new TemplateProcessor($templateFile);
            $templateProcessor->setValue("firstname", $student->getFirstName());
            $templateProcessor->setValue("lastname", $student->getLastName());
            $templateProcessor->setValue("email", $student->getEmail());
            $templateProcessor->setValue("password", $studentPassword);
            $templateProcessor->saveAs($pathToSave.$fileNameWithoutExt.".docx");

            Settings::setPdfRendererName(Settings::PDF_RENDERER_MPDF);
            Settings::setPdfRendererPath($this->getParameter("kernel.project_dir").'/vendor/mpdf/mpdf');

            $reader = IOFactory::load($pathToSave.$fileNameWithoutExt.".docx");

            $writer = IOFactory::createWriter($reader, 'PDF');
            $writer->save($pathToSave.$fileNameWithoutExt.".pdf");

            unlink($pathToSave.$fileNameWithoutExt.".docx");
            $generatedLetters[] = $fileNameWithoutExt.".pdf";
        } catch (Exception $e) {
            $this->addFlash("danger", "Il n'a pas été possible de générer certaines lettres de notification des étudiants.");
        }

        return $this->redirectToRoute("teacher_students", [
            "generatedLetters" => $generatedLetters
        ]);
    }
}
