<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Galerie
 *
 * @ORM\Table(name="galerie")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\GalerieRepository")
 */
class Galerie
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     *
     * @Assert\Length(min=5, minMessage="Le titre doit faire au moins {{ limit }} caractères.")
     * @Assert\NotBlank(message="Il faut donner un titre à la galerie")
     */
    private $title;

    /**
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Lien", cascade={"persist", "remove"}, inversedBy="galerie")
     */
    private $lien;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="private", type="boolean")
     */
    private $private;

    /**
     * @ORM\OneToMany(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"}, mappedBy="galerie")
     *
     * @Assert\Valid()
     */
    private $images;

    /**
     * @var string
     *
     * @ORM\Column(name="tags", type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=200, minMessage="Les tags ne doivent pas excéder {{ limit }} caractères.")
     */
    private $tags;


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
     * Set title
     *
     * @param string $title
     *
     * @return Galerie
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Galerie
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set private
     *
     * @param boolean $private
     *
     * @return Galerie
     */
    public function setPrivate($private)
    {
        $this->private = $private;

        return $this;
    }

    /**
     * Get private
     *
     * @return bool
     */
    public function getPrivate()
    {
        return $this->private;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * Add image
     *
     * @param \CoreBundle\Entity\Image $image
     *
     * @return Galerie
     */
    public function addImage(\CoreBundle\Entity\Image $image)
    {
        $this->images[] = $image;
        $image->setGalerie($this);

        return $this;
    }

    /**
     * Remove image
     *
     * @param \CoreBundle\Entity\Image $image
     */
    public function removeImage(\CoreBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
        $image->removeUpload();
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set lien
     *
     * @param \CoreBundle\Entity\Lien $lien
     *
     * @return Galerie
     */
    public function setLien(\CoreBundle\Entity\Lien $lien = null)
    {
        $this->lien = $lien;
        $lien->setTitle($this->getTitle());
        $lien->setType('GALERIE');
        $lien->setGalerie($this);

        return $this;
    }

    /**
     * Get lien
     *
     * @return \CoreBundle\Entity\Lien
     */
    public function getLien()
    {
        return $this->lien;
    }

    /**
     * Set tags
     *
     * @param string $tags
     *
     * @return Galerie
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Get tags
     *
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }
}
