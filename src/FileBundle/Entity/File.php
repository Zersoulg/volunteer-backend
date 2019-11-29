<?php


namespace App\FileBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\BaseBundle\Entity\BaseEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use App\FileBundle\Controller\CreateFile;

/**
 * @ORM\Entity
 * @ApiResource(
 *     collectionOperations={
 *     "get",
 *     "post"={
 *         "method"="POST",
 *         "path"="/files",
 *         "controller"=CreateFile::class,
 *         "defaults"={"_api_receive"=false},
 *         "swagger_context"={
 *              "description"=
 * "Файлы: аватарки пользователей, мероприятий, категорий и иконки достижений.
 * Для каждого есть тип, хранящийся в отдельном поле.",
 *              "responses"={
 *                  "201"={"description"="ok"},
 *                  "404"={"description"="Not Found"},
 *                  "400"={"description"="Bad Request"},
 *                  "409"={"description"="data is used"},
 *              },
 *              "parameters"={
 *                  {
 *                      "name"="file",
 *                      "in"="formData",
 *                      "type"="file"
 *                  },
 *                  {
 *                      "name"="type",
 *                      "in"="formData",
 *                      "type"="string"
 *                  },
 *
 *              },
 *           },
 *        },
 *     },
 *     attributes={
 *       "normalization_context"={"groups"={"GetFile", "GetObjBase"}},
 *       "denormalization_context"={"groups"={"SetFile"}}
 *     }
 * )
 *
 */

class File extends BaseEntity
{
    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     * @Groups({"GetFile"})
     */
    public $name;

    /**
     * @var string $path
     * @ORM\Column(name="path", type="string", nullable=false)
     * @Groups({"GetFile"})
     */
    public $path;

    /**
     * @var string $type
     * @ORM\Column(type="string", name="type", nullable=false)
     * @Groups({"SetFile", "GetFile", "GetObjFile"})
     */
    public $type;
}