<?php 

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    private $params; 
 
    /**
     * Constructeur de la classe
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * Méthode de la classe permettant d'ajouter une image en fonction de différents cas
     * Gestion du format, de la taille et du type d'image
     * @param UploadedFile $picture
     * @param string|null $folder
     * @param integer|null $width
     * @param integer|null $height
     * @return void
     */
    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250):string
    {
        $fichier = md5(uniqid(rand(), true)) . '.png';

        $picture_infos = getimagesize($picture);

        if($picture_infos === false)
            throw new Exception('Format d\'image incorrect');

        // Vérifier le format de l'image
        switch($picture_infos['mime'])
        {
            case 'image/png': 
                $source_picture = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $source_picture = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $source_picture = imagecreatefromwebp($picture);
                break;
            default:
                throw new Exception('Format d\'image incorrect');
        }

        $imageWidth = $picture_infos[0];
        $imageHeight = $picture_infos[1];

        switch($imageWidth <=> $imageHeight)
        {
            
            case -1 : // portrait
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = ($imageHeight - $squareSize) / 2;
                break;
            case 0 : // carré
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1 : // paysage
                $squareSize = $imageHeight;
                $src_x = ($imageWidth - $squareSize) / 2;
                $src_y = 0;
                break;
        }
        
        $resize_picture = imagecreatetruecolor($width, $height);
        imagecopyresampled($resize_picture,$source_picture,0,0,$src_x,$src_y,$width,$height,$squareSize,$squareSize);

        $path = $this->params->get('images_directory') . $folder;

        if (!is_dir($path)) 
        {
            mkdir($path, 0777, true);
        }
        
        imagepng($resize_picture, $path . '/' . $width . 'x' . $height . '-' . $fichier);

        $picture->move($path . '/', $fichier); 

        return $fichier;
    }

    /**
     * Méthode de la classe permettant de supprimer l'image en fonction du dossier et du nom
     * @param string $fichier
     * @param string|null $folder
     * @param integer|null $width
     * @param integer|null $height
     * @return void
     */
    public function delete(string $fichier, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if($fichier !== 'default.png')
        {
            $success = false;
            $path = $this->params->get('image_directory') . $folder; 

            $file = $path . $width . 'x' . $height . '-' . $fichier; 

            if(file_exists($file))
            {
                unlink($file);
                $success = true;
            }

            $original = $path . '/' . $fichier;

            if(file_exists($original))
            {
                unlink($original);
                $success = true;
            }

            return $success;
        }

        return false;
    }
}
?> 