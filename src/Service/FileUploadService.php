 <?php



 use Symfony\Component\HttpFoundation\File\UploadedFile;
 use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
 /*
class FileUploadService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $picture, ?string $filder ='', ?int $width = 250, ?int $height = 250): string
    {
       // On donne un nouveau nom à l'image
       $fichier = md5(uniqueid(rand(), true));

       // On récupère les infos de l'image
       $pictureInfos = getimagesize($picture);

       if($pictureInfos == false) {
        throw new Exception('Fromat d\'image incorrect');
       }

       // On vérifie le format de l'image
       switch($pictureInfos['mine']) {
        case 'image/png':
            $pictureSource = imagecreatefromwebp($picture);
            break;
        case 'image/jpg':
            $pictureSource = imagecreatefromwebp($picture);
            break;
        case 'image/webp':
            $pictureSource = imagecreatefromwebp($picture);
            break;
        default:
        throw new Exeption('Format d\'image incorrect');
       }

       // On recadre l'image
       // On récupère les dimensions
       $imageWidth = $picture_info[0];
       $imageHeight = $picture_infos[1];

       // On vérifie l'orientation de l'image
       switch ($imageWidth <=> $imageHeight) {
       }
    }
}  
*/