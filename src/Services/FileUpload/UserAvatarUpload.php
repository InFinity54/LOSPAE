<?php
namespace App\Services\FileUpload;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserAvatarUpload
{
    private $targetDirectory;

    public function __construct($avatarsDirectory)
    {
        $this->targetDirectory = $avatarsDirectory;
    }

    public function upload(UploadedFile $file, User $user)
    {
        $fileName = $user->getId().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
            return $fileName;
        } catch (FileException $e) {
            return false;
        }
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}