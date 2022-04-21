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

namespace Plugin\FavoriteReview\Repository\Extension;

use Eccube\Repository\CustomerFavoriteProductRepository;

class CustomerFavoriteProductRepositoryExtension extends CustomerFavoriteProductRepository
{

    /**
     * @param  \Eccube\Entity\Customer $Customer
     *
     * @return QueryBuilder
     */
    public function getOpenCount($Customer)
    {
        $qb = $this->createQueryBuilder('cfp')
        ->select('COUNT(cfp.id)')
        ->andWhere('cfp.Customer = :Customer AND cfp.open = 1')
        ->setParameter('Customer', $Customer);

        $count = $qb
            ->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

}
