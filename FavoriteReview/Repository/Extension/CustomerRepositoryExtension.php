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

use Eccube\Repository\CustomerRepository;

class CustomerRepositoryExtension extends CustomerRepository
{

    /**
     *
     * @param $url
     *
     * @return QueryBuilder
     */
    public function getUserFromUrl($url)
    {
        $qb = $this->createQueryBuilder('c')
        ->select('c.id')
        ->where('c.url = :url')
        ->setParameter('url', $url);

        $id = $qb
            ->getQuery()
            ->getSingleScalarResult();


        return $id;
    }

}
