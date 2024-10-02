<?php

namespace App\Controller;

use App\Entity\Criteria;
use App\Entity\CurrentNote;
use App\Entity\NoteChange;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class VersionTwoImportController extends AbstractController
{
    #[Route('/v2/data/import', name: 'v2_data_import')]
    public function DataImportFromLospaeV2(EntityManagerInterface $entityManager): JsonResponse
    {
        $originalUsers = [];
        $originalCriterias = [];

        if (($fp = fopen($this->getParameter("kernel.project_dir")."/import/user.csv", "r")) !== FALSE) {
            $rowNo = 1;

            while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                if ($rowNo > 1) {
                    $userEmail = $row[1];
                    $userRoles = json_decode($row[2]);
                    $userPassword = $row[3];
                    $userLastName = $row[4];
                    $userFirstName = $row[5];
                    $userAvatar = $row[6];
                    $userIsActivated = $row[7];

                    $user = new User();
                    $user->setEmail($userEmail);
                    $user->setRoles($userRoles);
                    $user->setPassword($userPassword);
                    $user->setLastName($userLastName);
                    $user->setFirstName($userFirstName);
                    $user->setAvatar($userAvatar);
                    $user->setActivated($userIsActivated);

                    //$entityManager->persist($user);
                    //$entityManager->flush();

                    $originalUsers[$row[0]] = $userEmail;
                }

                $rowNo++;
            }

            fclose($fp);
        }

        if (($fp = fopen($this->getParameter("kernel.project_dir")."/import/criteria.csv", "r")) !== FALSE) {
            $rowNo = 1;

            while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                if ($rowNo > 1) {
                    $criteriaName = $row[1];
                    $criteriaImpact = $row[2];
                    $criteriaModality = $row[3];

                    $criteria = new Criteria();
                    $criteria->setName($criteriaName);
                    $criteria->setImpact(floatval($criteriaImpact));
                    $criteria->setModality($criteriaModality != "" ? $criteriaModality : null);

                    //$entityManager->persist($criteria);
                    //$entityManager->flush();

                    $originalCriterias[$row[0]] = $criteriaName;
                }

                $rowNo++;
            }

            fclose($fp);
        }

        if (($fp = fopen($this->getParameter("kernel.project_dir")."/import/note_change.csv", "r")) !== FALSE) {
            $rowNo = 1;
            $teacher = $entityManager->getRepository(User::class)->findOneBy(["email" => "corentin.kreicher@ac-nancy-metz.fr"]);

            while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                if ($rowNo > 1) {
                    $noteChangeStudent = $row[1];
                    $noteChangeCriteria = $row[2];
                    $noteChangeImpact = $row[3];
                    $noteChangeOccuredAt = $row[4];

                    $noteChange = new NoteChange();
                    $noteChange->setTeacher($teacher);
                    $noteChange->setStudent($entityManager->getRepository(User::class)->findOneBy(["email" => $originalUsers[$noteChangeStudent]]));
                    $noteChange->setCriteria($entityManager->getRepository(Criteria::class)->findOneBy(["name" => $originalCriterias[$noteChangeCriteria]]));
                    $noteChange->setImpact(floatval($noteChangeImpact));
                    $noteChange->setOccuredAt(DateTimeImmutable::createFromFormat("Y-m-d H:i:s", $noteChangeOccuredAt));

                    /*$entityManager->persist($noteChange);
                    $entityManager->flush();*/
                }

                $rowNo++;
            }

            fclose($fp);
        }

        if (($fp = fopen($this->getParameter("kernel.project_dir")."/import/student_note.csv", "r")) !== FALSE) {
            $rowNo = 1;
            $teacher = $entityManager->getRepository(User::class)->findOneBy(["email" => "corentin.kreicher@ac-nancy-metz.fr"]);

            while (($row = fgetcsv($fp, 1000, ";")) !== FALSE) {
                if ($rowNo > 1) {
                    $studentNoteStudent = $row[1];
                    $studentNoteCurrent = $row[2];

                    $studentNote = new CurrentNote();
                    $studentNote->setTeacher($teacher);
                    $studentNote->setStudent($entityManager->getRepository(User::class)->findOneBy(["email" => $originalUsers[$studentNoteStudent]]));
                    $studentNote->setNote(floatval($studentNoteCurrent));

                    $entityManager->persist($studentNote);
                    $entityManager->flush();
                }

                $rowNo++;
            }

            fclose($fp);
        }

        return new JsonResponse([], 200);
    }
}
