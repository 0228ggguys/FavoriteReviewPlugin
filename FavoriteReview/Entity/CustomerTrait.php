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
     * @var boolean
     *
     * @ORM\Column(name="gift", type="boolean", options={"default":false})
     */
    private $gift;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;


    /**
     * Set share.
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

    /**
     * Set gift.
     *
     * @param boolean $gift
     *
     * @return Customer
     */
    public function setGift($gift)
    {
        $this->gift = $gift;

        return $this;
    }

    /**
     * Get gift.
     *
     * @return boolean
     */
    public function getGift()
    {
        return $this->gift;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Customer
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
