<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    // à vrai dire $url devrait s'appeler $name
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     *
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    private $alt;

    /**
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Galerie", cascade={"persist"}, inversedBy="images")
     */
    private $galerie;

    /**
     * @var
     *
     * @Assert\Image(
     *     mimeTypesMessage="Ce fichier n'est pas une image valide.",
     *     maxSize="60M",
     *     maxSizeMessage="Ce fichier est trop gros pour être envoyé. Le fichier doit faire au maximum {{ limit }} {{ suffix }}"
     * )
     *
     */
    private $file;

    // On ajoute cet attribut pour y stocker le nom du fichier temporairement
    private $tempFilename;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        // On vérifie si on avait déjà un fichier pour cette entité
        if (null !== $this->url) {
            // On sauvegarde l'extension du fichier pour le supprimer plus tard
            $this->tempFilename = $this->url;

            // On réinitialise les valeurs des attributs url et alt
            $this->url = null;
            $this->alt = null;
        }
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Image
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set galerie
     *
     * @param \CoreBundle\Entity\Galerie $galerie
     *
     * @return Image
     */
    public function setGalerie(\CoreBundle\Entity\Galerie $galerie = null)
    {
        $this->galerie = $galerie;

        return $this;
    }

    /**
     * Get galerie
     *
     * @return \CoreBundle\Entity\Galerie
     */
    public function getGalerie()
    {
        return $this->galerie;
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate
     */
    public function preUpload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->file) {
            return;
        }

        // On crée un nom de fichier unique et on l'enregistre
        $fileName = md5(uniqid()).'.'.$this->file->guessExtension();
        $this->url = $fileName;

    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
        if (null === $this->file) {
            return;
        }

        // Si on avait déjà un fichier, on le supprime pour éviter les fichiers orphelins
        if(null !== $this->tempFilename){
            $oldFile = $this->getUploadDir().'/'.$this->tempFilename;
            if(file_exists($oldFile)){
                unlink($oldFile);
            }
        }

        // On déplace le fichier envoyé dans le répertoire de notre choix
        $this->file->move($this->getUploadDir(), $this->url);

        // création de la miniature
        $this->creerMin();

    }

    /**
     * @ORM\PreRemove()
     */
    public function preRemoveUpload()
    {
        // On sauvegarde temporairement le nom du fichier
        $this->tempFilename = $this->getUploadDir().'/'.$this->url;
    }

    /**
     * @ORM\PostRemove
     */
    public function removeUpload()
    {
        // En PostRemove, on utilise notre nom sauvegardé
        if (file_exists($this->tempFilename)) {
            // On supprime le fichier
            unlink($this->tempFilename);
            // et sa miniature
            if (file_exists('uploads/thumbs/' . $this->url)) {
                unlink('uploads/thumbs/' . $this->url);
            }
        }
    }

    public function getUploadDir()
    {
        // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
        return 'uploads/images';
    }

    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->url;
    }

    /**
     * Fonction de création d'une miniature durant l'upload d'image
     * @param int $mlargeur
     * @param int $mhauteur
     * @return bool
     */
    private function creerMin($mlargeur=283,$mhauteur=283) {
        $nom = $this->url;
        $dimension = getimagesize($this->getWebPath());

        $extension = strtolower($this->file->getClientOriginalExtension());

        switch ($extension) {
            case 'jpeg':$image = imagecreatefromjpeg($this->getUploadDir().'/'.$nom); break;
            case 'jpg': $image = imagecreatefromjpeg($this->getUploadDir().'/'.$nom); break;
            case 'png': $image = imagecreatefrompng($this->getUploadDir().'/'.$nom); break;
            case 'gif': $image = imagecreatefromgif($this->getUploadDir().'/'.$nom); break;
            default : return false;
        }

        $miniature = imagecreatetruecolor($mlargeur,$mhauteur);

        if($dimension[0]>($mlargeur/$mhauteur)*$dimension[1]) {
            $dimY = $mhauteur;
            $dimX = $mhauteur*$dimension[0]/$dimension[1];
            $decalX = -($dimX-$mlargeur)/2;
            $decalY = 0;
        }
        if($dimension[0]<($mlargeur/$mhauteur)*$dimension[1]) {
            $dimX = $mlargeur;
            $dimY = $mlargeur*$dimension[1]/$dimension[0];
            $decalY = -($dimY-$mhauteur)/2;
            $decalX=0;
        }
        if($dimension[0]==($mlargeur/$mhauteur)*$dimension[1]) {
            $dimX = $mlargeur;
            $dimY = $mhauteur;
            $decalX = 0;
            $decalY = 0;
        }

        imagecopyresampled($miniature, $image, $decalX, $decalY, 0, 0, $dimX, $dimY, $dimension[0], $dimension[1]);

        $chemin = 'uploads/thumbs';
        switch ($extension) {
            case 'jpeg':imagejpeg($miniature, $chemin . "/" . $nom, 100); break;
            case 'jpg': imagejpeg($miniature, $chemin . "/" . $nom, 100); break;
            case 'png': imagepng($miniature, $chemin . "/" . $nom, 100); break;
            case 'gif': imagegif($miniature, $chemin . "/" . $nom); break;
            default : return false;
        }
        return true;
    }

}
