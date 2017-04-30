<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Page
 *
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PageRepository")
 */
class Page
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
     * @ORM\Column(name="tags", type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=200, minMessage="Les tags ne doivent pas excéder {{ limit }} caractères.")
     */
    private $tags;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(message="La page a besoin d'un titre.")
     */
    private $title;

    /**
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Lien", cascade={"persist", "remove"}, inversedBy="page")
     */
    private $lien;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     *
     * @Assert\NotBlank(message="Une page vide ne sert à rien ! Ecrivez quelques mots...")
     */
    private $content;

    /**
     * @var bool
     *
     * @ORM\Column(name="publication", type="boolean")
     */
    private $publication;


    public function __construct(){
        $this->setPublication(false);
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
     * Set tags
     *
     * @param string $tags
     *
     * @return Page
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

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Page
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set publication
     *
     * @param boolean $publication
     *
     * @return Page
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get publication
     *
     * @return bool
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Page
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
     * Set lien
     *
     * @param \CoreBundle\Entity\Lien $lien
     *
     * @return Page
     */
    public function setLien(\CoreBundle\Entity\Lien $lien = null)
    {
        $this->lien = $lien;
        $lien->setTitle($this->getTitle());
        $lien->setType('PAGE');
        $lien->setPage($this);

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
}
