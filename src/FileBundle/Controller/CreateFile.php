<?php

namespace App\FileBundle\Controller;


use App\FileBundle\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{
    File\UploadedFile,
    Request
};
use Symfony\Component\HttpKernel\{
    Exception\HttpException,
    KernelInterface
};

class CreateFile
{
    public function __invoke(Request $request, KernelInterface $kernel, EntityManagerInterface $em): ?File
    {
        $dir = $kernel->getProjectDir(). '/public/uploads/';

        /** @var UploadedFile $file */
        foreach ($request->files as $file) {

            $name = md5($this->generateRandomString());
            $ext = $file->getClientOriginalExtension();

            $file->move($dir, $name.'.'.$ext);

            /** @var File $objFile */
            $objFile = new File();
            $objFile->name = $name;
            $objFile->path = $name.'.'.$ext;
            $type =$request->get('type');

            if (isset($type)) {
                $objFile->type = $request->get('type');
            } else {
                throw new HttpException(400,'bad request');
            }

            $em->persist($objFile);
            $em->flush();
        }

        if (!isset($objFile)){
            throw new HttpException(400,'bad request');
        }

        return $objFile;
    }

    private function generateRandomString($length = 50)
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
    }
}