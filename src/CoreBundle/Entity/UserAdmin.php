<?php
namespace CoreBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserAdmin
 * @package CoreBundle\Entity
 *
 * @ORM\Table(name="user_admin")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\UserAdminRepository")
 */
class UserAdmin extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="CoreBundle\Entity\Galerie", cascade={"remove"}, mappedBy="user")
     */
    private $galeries;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add galery
     *
     * @param \CoreBundle\Entity\Galerie $galery
     *
     * @return UserAdmin
     */
    public function addGalery(\CoreBundle\Entity\Galerie $galery)
    {
        $this->galeries[] = $galery;
        $galery->setUser($this);

        return $this;
    }

    /**
     * Remove galery
     *
     * @param \CoreBundle\Entity\Galerie $galery
     */
    public function removeGalery(\CoreBundle\Entity\Galerie $galery)
    {
        $this->galeries->removeElement($galery);
        $galery->setUser(null);
    }

    /**
     * Get galeries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGaleries()
    {
        return $this->galeries;
    }

    public function hasGalery($id){
        // prendre la liste des galeries, créer un array des id, faire un in_array avec $id
        $galeries = $this->getGaleries();
        $list = [];
        foreach ($galeries as $galery){
            $list[] = $galery->getId();
        }

        return in_array($id, $list);
    }
}
