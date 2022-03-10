<?php

namespace Plugin\FavoriteReview\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{

    /**
     * @var boolean
     *
     * @ORM\Column(name="share", type="boolean", options={"default":false})
     */
    private $share;

    /**
     * Set share.
     *
     *
     * @param boolean $share
     *
     * @return Customer
     */
    public function setShare($share)
    {
        $this->share = $share;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return boolean
     */
    public function getShare()
    {
        return $this->share;
    }


}
