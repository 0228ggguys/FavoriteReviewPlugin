<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\FavoriteReview\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Plugin\FavoriteReview\Entity\Gift')) {
    /**
     * Gift
     *
     * @ORM\Table(name="dtb_gift")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Plugin\FavoriteReview\Repository\GiftRepository")
     */
    class Gift extends \Eccube\Entity\AbstractEntity
    {
        /**
         * @var int
         *
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;


        /**
         * @var integer
         *
         * @ORM\Column(name="give_user_id", type="integer")
         */
        private $give_user_id;

        /**
         * @var integer
         *
         * @ORM\Column(name="take_user_id", type="integer")
         */
        private $take_user_id;

        /**
         * @var integer
         *
         * @ORM\Column(name="favorite_id", type="integer")
         */
        private $favorite_id;

        /**
         * @var string
         *
         * @ORM\Column(name="comment", type="string", length=255)
         */
        private $comment;

        /**
         * @var string
         *
         * @ORM\Column(name="name", type="string", length=255)
         */
        private $name;

        /**
         * @var integer
         *
         * @ORM\Column(name="amount", type="integer")
         */
        private $amount;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="create_date", type="datetimetz")
         */
        private $create_date;

        /**
         * @var \DateTime
         *
         * @ORM\Column(name="update_date", type="datetimetz")
         */
        private $update_date;



        /**
         * Set id.
         *
         * @param int $id
         *
         * @return Gift
         */
        public function setId($id)
        {
            $this->id = $id;

            return $this;
        }

        /**
         * Get id.
         *
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Set giveUserId.
         *
         * @param int $giveUserId
         *
         * @return Gift
         */
        public function setGiveUserId($giveUserId)
        {
            $this->give_user_id = $giveUserId;

            return $this;
        }

        /**
         * Get giveUserId.
         *
         * @return int
         */
        public function getGiveUserId()
        {
            return $this->give_user_id;
        }

        /**
         * Set takeUserId.
         *
         * @param int $takeUserId
         *
         * @return Gift
         */
        public function setTakeUserId($takeUserId)
        {
            $this->take_user_id = $takeUserId;

            return $this;
        }

        /**
         * Get takeUserId.
         *
         * @return int
         */
        public function getTakeUserId()
        {
            return $this->take_user_id;
        }

        /**
         * Set favoriteId.
         *
         * @param int $favoriteId
         *
         * @return Gift
         */
        public function setFavoriteId($favoriteId)
        {
            $this->favorite_id = $favoriteId;

            return $this;
        }

        /**
         * Get favoriteId.
         *
         * @return int
         */
        public function getFavoriteId()
        {
            return $this->favorite_id;
        }

        /**
         * Set comment.
         *
         * @param string $comment
         *
         * @return Gift
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
         * Set name.
         *
         * @param string $name
         *
         * @return Gift
         */
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        /**
         * Get name.
         *
         * @return string
         */
        public function getName()
        {
            return $this->name;
        }

        /**
         * Set amount.
         *
         * @param int $amount
         *
         * @return Gift
         */
        public function setAmount($amount)
        {
            $this->amount = $amount;

            return $this;
        }

        /**
         * Get amount.
         *
         * @return int
         */
        public function getAmount()
        {
            return $this->amount;
        }

        /**
         * Set createDate.
         *
         * @param \DateTime $createDate
         *
         * @return ProductStock
         */
        public function setCreateDate($createDate)
        {
            $this->create_date = $createDate;

            return $this;
        }

        /**
         * Get createDate.
         *
         * @return \DateTime
         */
        public function getCreateDate()
        {
            return $this->create_date;
        }

        /**
         * Set updateDate.
         *
         * @param \DateTime $updateDate
         *
         * @return ProductStock
         */
        public function setUpdateDate($updateDate)
        {
            $this->update_date = $updateDate;

            return $this;
        }

        /**
         * Get updateDate.
         *
         * @return \DateTime
         */
        public function getUpdateDate()
        {
            return $this->update_date;
        }
    }
}
