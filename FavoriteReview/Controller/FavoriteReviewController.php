<?php

// もとはmypageControllerに追記していたものです。

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

namespace Plugin\FavoriteReview\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;
use Eccube\Entity\Product;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\CartException;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CustomerRepository;
use Plugin\FavoriteReview\Repository\FavoriteReviewRepository;
use Eccube\Service\CartService;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseFlow;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;
// ↑いらないものあり。時間のある時に削ってください


class FavoriteReviewController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;
  
    /**
     * @var FavoriteReviewRepository
     */
    protected $favoriteReviewRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var PurchaseFlow
     */
    protected $purchaseFlow;

    /**
     * FavoriteReviewController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param CustomerRepository $customerRepository
     * @param FavoriteReviewRepository $favoriteReviewRepository
     * @param ProductRepository $productRepository
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param CartService $cartService
     * @param BaseInfoRepository $baseInfoRepository
     * @param PurchaseFlow $purchaseFlow
     */
    public function __construct(
        OrderRepository $orderRepository,
        CustomerRepository $customerRepository,
        ProductRepository $productRepository,
        FavoriteReviewRepository $favoriteReviewRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        CartService $cartService,
        BaseInfoRepository $baseInfoRepository,
        PurchaseFlow $purchaseFlow
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->favoriteReviewRepository = $favoriteReviewRepository;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->cartService = $cartService;
        $this->purchaseFlow = $purchaseFlow;
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/favorite", name="mypage_favorite", methods={"GET"})
     * @Template("FavoriteReview/Resource/template/Mypage/favorite.twig")
     */
    public function favorite(Request $request, PaginatorInterface $paginator)
    {
        if (!$this->BaseInfo->isOptionFavoriteProduct()) {
            throw new NotFoundHttpException();
        }
        $Customer = $this->getUser();


        // paginator
        $qb = $this->customerFavoriteProductRepository->getQueryBuilderByCustomer($Customer);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_FAVORITE_SEARCH, $event);

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            $this->eccubeConfig['eccube_search_pmax'],
            ['wrap-queries' => true]
        );
// dump($pagination);
// exit;
            return [
                'pagination' => $pagination,
                'Customer' => $Customer
            ];

        }



    /**
     * お気に入り商品のコメントを編集する.
     *
     * @Route("/mypage/favorite/{id}/update", name="mypage_favorite_update", methods={"GET","POST"},requirements={"id" = "\d+"})
     * @Template("FavoriteReview/Resource/template/Mypage/favorite2.twig")
     */
    public function update(Request $request, $id)
    {
        $form = $this->createFormBuilder()
                ->add('comment',TextType::class,[
                    'constraints' => new Assert\Length(["max" => "30"])
                ])
                // ->add('priority',IntegerType::class)
                ->add('priority',ChoiceType::class,[
                    'choices' => [
                    '最優先' => 4,
                    '優先' => 3,
                    'ほしい' => 2,
                    'そこまでいらない' => 1
                    ]
                    ])
                ->getForm();

        $intId = intval($id);

        //編集中
        // $fp = $this->customerFavoriteProductRepository->find($id)->getProduct();
        // dump($fp);
        // exit;

        // $fpName = $fp->getName();
        // $fpImage = $fp->getImage();
        // $favoriteProduct = $this->productRepository->getProduct();
        // $favoriteProduct= $this->customerFavoriteProductRepository->find($id);



        if($request->getMethod() == "POST"){
            // $CustomerFavoriteProduct2 = $this->customerFavoriteProductRepository->find($id);
            // $FavoriteReview2 = $this->FavoriteReviewRepository->find($CustomerFavoriteProduct2);
            // ここにupdateを書いていたけど、同じテーブルにある今必要ないのか？
            // $fp2 = $this->customerFavoriteProductRepository->find($id);

            // if($fp2){
            //     $this->customerFavoriteProductRepository->delete($id);
            // }

            $form->handleRequest($request);
            $fp = $this->customerFavoriteProductRepository->find($id);
            // $CustomerFavoriteProduct = $this->customerFavoriteProductRepository->find($id);
            $fp->setComment($form->get('comment')->getData())
            ->setPriority($form->get('priority')->getData());

            if($form->isSubmitted() && $form->isValid()){

                $em = $this->getDoctrine()->getManager();
                $em->persist($fp);
                $em->flush();
            }

            return $this->render('FavoriteReview/Resource/template/Mypage/favorite3.twig', [
            ]);
        }

            return [
                'form' => $form->createView(),
                'id' => $id,
            ];
    }

    /**
     * お気に入り商品のコメントの編集を完了する.
     *
     * @Route("/mypage/favorite/{id}/complete", name="mypage_favorite_complete", methods={"GET","POST"},requirements={"id" = "\d+"})
     * @Template("FavoriteReview/Resource/template/Mypage/favorite3.twig")
     */
    public function complete(Request $request, $id)
    {
        $CustomerFavoriteProduct = $this->customerFavoriteProductRepository->find($id);

        if($CustomerFavoriteProduct){
            $this->favoriteReviewRepository->deleteReview($CustomerFavoriteProduct);
        }

        return [];
    }



    /**
     * お気に入り商品を削除する.
     *
     * @Route("/mypage/favorite/{id}/delete", name="mypage_favorite_delete", methods={"DELETE"}, requirements={"id" = "\d+"})
     */
    public function delete(Request $request, Product $Product)
    {
        $this->isTokenValid();

        $Customer = $this->getUser();

        log_info('お気に入り商品削除開始', [$Customer->getId(), $Product->getId()]);

        $CustomerFavoriteProduct = $this->customerFavoriteProductRepository->findOneBy(['Customer' => $Customer, 'Product' => $Product]);

        if ($CustomerFavoriteProduct) {
            $this->customerFavoriteProductRepository->delete($CustomerFavoriteProduct);
        } else {
            throw new BadRequestHttpException();
        }

        $event = new EventArgs(
            [
                'Customer' => $Customer,
                'CustomerFavoriteProduct' => $CustomerFavoriteProduct,
            ], $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_DELETE_COMPLETE, $event);

        log_info('お気に入り商品削除完了', [$Customer->getId(), $CustomerFavoriteProduct->getId()]);

        return $this->redirect($this->generateUrl('mypage_favorite'));
    }

     /**
         * お気に入り商品をシェアする.
         *
         * @Route("/favorite_share/{user_id}", name="favorite_share", methods={"GET"})
         * @Template("FavoriteReview/Resource/template/Mypage/favorite_share.twig")
         */
        public function favoriteShare(Request $request, PaginatorInterface $paginator, $user_id)
        {
            if (!$this->BaseInfo->isOptionFavoriteProduct()) {
                throw new NotFoundHttpException();
            }
            // $Customer = $this->getUser();
            $Customer = $this->customerRepository->find($user_id);

            // paginator
            $qb = $this->customerFavoriteProductRepository->getQueryBuilderByCustomer($Customer);

            $event = new EventArgs(
                [
                    'qb' => $qb,
                    'Customer' => $Customer,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_FAVORITE_SEARCH, $event);

            $pagination = $paginator->paginate(
                $qb,
                $request->get('pageno', 1),
                $this->eccubeConfig['eccube_search_pmax'],
                ['wrap-queries' => true]
            );

            $twitter_share_url = 'https://twitter.com/intent/tweet?url=http://localhost:8091/favorite_share/'.$user_id.'&text=お気に入りリストです！';
            $facebook_share_url = 'https://www.facebook.com/sharer.php?src=bm&u=http://localhost:8091/favorite_share/'.$user_id.'&t=お気に入りリストです！';
    // dump($pagination);
    // exit;
                return [
                    'pagination' => $pagination,
                    'twitter_share_url' => $twitter_share_url,
                    'facebook_share_url' => $facebook_share_url,
                    'Customer' => $Customer
                ];

            }
}
