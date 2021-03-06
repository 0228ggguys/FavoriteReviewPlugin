<?php

namespace Plugin\FavoriteReview\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\CustomerFavoriteProduct")
 */
trait CustomerFavoriteProductTrait
{

    /**
     * @var string|null
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var int|null
     *
     * @ORM\Column(name="priority", type="integer", nullable=true)
     */
    private $priority;

    /**
     * @var boolean|null
     *
     * @ORM\Column(name="open", type="boolean", options={"default":false}, nullable=true)
     */
    private $open;

    /**
     * Set comment.
     *
     *
     * @param string $comment
     *
     * @return CustomerFavoriteProduct
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return CustomerFavoriteProduct
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priotity.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set open.
     *
     * @param boolean $open
     *
     * @return Customer
     */
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open.
     *
     * @return boolean
     */
    public function getOpen()
    {
        return $this->open;
    }

}
