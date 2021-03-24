<?php


namespace App\Service;


use App\Entity\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use function get_class;

class ImageCropUpload
{
    private $imagesPath;
    /**
     * @var FlashBag
     */
    private $flashBag;

    /**
     * ImageCropUpload constructor.
     * @param $imagesPath
     * @param FlashBag $flashBag
     */
    public function __construct($imagesPath, FlashBag $flashBag)
    {
        $this->imagesPath = $imagesPath;
        $this->flashBag = $flashBag;
    }

    public function subirImagen($imageString, $imageName, $entidad, EntityManager $entityManager): JsonResponse
    {
        $data = $imageString->imageData ?? null;

        $imageData = preg_replace('/data:image\/(png|jpeg);base64,/','',$data);
        $imageData = base64_decode($imageData);
        if ($imageData) {
            //$img = @imagecreatefromstring($imageData);
            if (!function_exists('imagecreatefromstring') || !$img = @imagecreatefromstring($imageData)) {
                return new JsonResponse('Imagen no soportada. Debe ser JPG o PNG',400);
            }

            [$width, $height, $image_type] = getimagesizefromstring($imageData);

            $basePath = $this->imagesPath.DIRECTORY_SEPARATOR;

            $imageName = preg_replace('/.(png|jpg)/','',$imageName);

            switch ($image_type)
            {
                case 2:
                    $prevImage = $basePath.$imageName.'.png';
                    $fileName = $imageName.substr(md5(uniqid('_',false)), 0, 10).'.jpg';
                    imagejpeg($img, $basePath.$fileName, 100);
                    break;

                case 3:
                    $prevImage = $basePath.$imageName.'.jpg';
                    $fileName = $imageName.substr(md5(uniqid('_',false)), 0, 10).'.png';
                    imagesavealpha($img, true);
                    $trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
                    imagefill($img, 0, 0, $trans_colour);
                    imagepng($img, $basePath.$fileName,0);
                    break;

                default:
                    return new JsonResponse('Imagen no soportada. Debe ser JPG o PNG',400);
            }

            if (file_exists($prevImage)) {
                @unlink($prevImage);
            }

            $entidad = $entidad->setImage($fileName);

            $entityManager->persist($entidad);
            $entityManager->flush();

            $this->flashBag->add('success','Foto de "'.$entidad.'" guardada correctamente.' );
            return new JsonResponse(null, 200);
        }

        return new JsonResponse('Datos no encontrados',400);
    }
}