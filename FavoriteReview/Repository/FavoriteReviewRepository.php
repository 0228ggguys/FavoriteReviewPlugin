<?php

// 元はCustomerFavoriteProductRepositoryに追記した内容です

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

namespace Plugin\FavoriteReview\Repository;

use Doctrine\ORM\QueryBuilder;
use Eccube\Entity\CustomerFavoriteProduct;
use Eccube\Repository\AbstractRepository;
// use Customize\Entity\FavoriteReview;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * FavoriteReviewRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FavoriteReviewRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomerFavoriteProduct::class);
    }

    /**
     * お気に入りのレビューを削除します.
     *
     * @param \Eccube\Entity\CustomerFavoriteProduct $CustomerFavoriteProduct
     */
    public function deleteReview($CustomerFavoriteProduct)
    {
        $CustomerFavoriteProduct->setComment(NULL)
        ->setPriority(NULL);

        $em = $this->getEntityManager();
        $em->persist($CustomerFavoriteProduct);
        $em->flush();
    }
}