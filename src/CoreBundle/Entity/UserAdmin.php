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

    public function __construct()
    {
        parent::__construct();
    }
}