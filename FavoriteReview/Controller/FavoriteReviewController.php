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
use Eccube\Repository\PageRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CustomerRepository;
use Plugin\FavoriteReview\Repository\FavoriteReviewRepository;
use Plugin\FavoriteReview\Repository\GiftRepository;
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
     *
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
     * @var PageRepository
     */
    protected $pageRepository;

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
     * @param GiftRepository $giftRepository
     * @param ProductRepository $productRepository
     * @param PageRepository $pageRepository
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
        GiftRepository $giftRepository,
        PageRepository $pageRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        CartService $cartService,
        BaseInfoRepository $baseInfoRepository,
        PurchaseFlow $purchaseFlow
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->favoriteReviewRepository = $favoriteReviewRepository;
        $this->giftRepository = $giftRepository;
        $this->pageRepository = $pageRepository;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->cartService = $cartService;
        $this->purchaseFlow = $purchaseFlow;
    }

    /**
     * お気に入り商品を表示する.
     *
     * @Route("/mypage/favorite", name="mypage_favorite", methods={"GET","POST"})
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
            // 10,
            ['wrap-queries' => true]
        );

        $openCount = $this->customerFavoriteProductRepository->getOpenCount($Customer);
        $share = $this->customerRepository->find($Customer)->getShare();

        $user_url = $this->customerRepository->find($Customer)->geturl();


        $twitter_share_url = 'https://twitter.com/intent/tweet?url=http://localhost:8091/favorite_share/'.$user_url.'&text=お気に入りリストです！';
        $facebook_share_url = 'https://www.facebook.com/sharer.php?src=bm&u=http://localhost:8091/favorite_share/'.$user_url.'&t=お気に入りリストです！';

        return [
            'pagination' => $pagination,
            'Customer' => $Customer,
            'openCount' => $openCount,
            'share' => $share,
            'twitter_share_url' => $twitter_share_url,
            'facebook_share_url' => $facebook_share_url,
        ];

    }


    /**
     * ajax処理で'open'カラムを変えたいidを持ってくる
     *
     * @Route("/mypage/open/status", name="change_open_status", methods={"POST"})
     * @param $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeOpenStatus(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $id = $request->get('id');


        $cfp = $this->customerFavoriteProductRepository->find($id);
        if($cfp->getOpen() == 0){
            $cfp->setOpen(1);
        }else{
            $cfp->setOpen(0);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($cfp);
        $em->flush();

        return $this->json([
            'success' => true
        ]);
    }

    /**
     * ajax処理で'open'カラムを変えたいidを持ってくる
     *
     * @Route("/mypage/lock/status", name="change_lock_status", methods={"POST"})
     * @param $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function changeLockStatus(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        $shareNo = $request->get('no');
        $Customer = $this->getUser();

        $c = $this->customerRepository->find($Customer);
        if($shareNo == 1){
            $c->setShare(0);
        }else{
            $c->setShare(1);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($c);
        $em->flush();

        return $this->json([
            'success' => true,
            'shareNo' => $shareNo
        ]);
    }


    /**
     * お気に入り商品のコメントを編集する.
     *
     * @Route("/mypage/favorite/{id}/update", name="mypage_favorite_update", methods={"GET","POST"},requirements={"id" = "\d+"})
     * @Template("FavoriteReview/Resource/template/Mypage/favorite2.twig")
     */
    public function update(Request $request, PaginatorInterface $paginator, $id)
    {

        $comment = $this->customerFavoriteProductRepository->find($id)->getComment();
        $priority = $this->customerFavoriteProductRepository->find($id)->getPriority();

        $form = $this->createFormBuilder()
                ->add('comment',TextType::class,[
                    'constraints' => new Assert\Length(["max" => "30"]),
                    'data' => $comment
                ])
                // ->add('priority',IntegerType::class)
                ->add('priority',ChoiceType::class,[
                    'choices' => [
                    '4:最優先' => 4,
                    '3:優先' => 3,
                    '2:ほしい' => 2,
                    '1:そこまでいらない' => 1
                    ],
                    'data' => $priority
                    ])
                ->getForm();


        // コメントを編集中の商品の情報をとってくる
        $Customer = $this->getUser();

        // paginator
        $qb = $this->favoriteReviewRepository->getQueryBuilderByReviewId($Customer, $id);

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
        // 商品情報ここまで


        if($request->getMethod() == "POST"){


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
                'pagination' => $pagination
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
     * @Route("/favorite_share/{user_url}", name="favorite_share", methods={"GET","POST"})
     * @Template("FavoriteReview/Resource/template/Mypage/favorite_share.twig")
     */
    public function favoriteShare(Request $request, PaginatorInterface $paginator, $user_url)
    {
        if (!$this->BaseInfo->isOptionFavoriteProduct()) {
            throw new NotFoundHttpException();
        }
        $user_id = $this->customerRepository->getUserFromUrl($user_url);
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
            // $this->eccubeConfig['eccube_search_pmax'], //この３つ目の引数が商品数を超えると（２ページ目が必要な状況になると）エラーが出る
            12,
            ['wrap-queries' => true]
        );
        // $pagination->appends(Input::all());

        $qb2 = $this->giftRepository->getQueryBuilderByTakeUserId($user_id);

        $pagination2 = $paginator->paginate(
            $qb2,
            $request->get('pageno', 1),
            10,
        );

        $share = $Customer->getShare();
        $gift = $Customer->getGift();

        $form = $this->createFormBuilder()
            ->add('share',ChoiceType::class,[
                'data' => $share,
                'choices' => [
                '公開' => true,
                '非公開' => false
                ]
            ])
            ->add('gift',ChoiceType::class,[
                'data' => $gift,
                'choices' => [
                'ON' => true,
                'OFF' => false
                ]
            ])
            ->getForm();

        if($request->getMethod() == "POST") {

            $form->handleRequest($request);
            $c = $this->customerRepository->find($user_id);
            if(!($c->getUrl())){
                $a = sha1(uniqid(mt_rand(), true));
                $c->setUrl($a);
            }
            $c->setShare($form->get('share')->getData());
            $c->setGift($form->get('gift')->getData());


            if($form->isSubmitted() && $form->isValid()){

                $em = $this->getDoctrine()->getManager();
                $em->persist($c);
                $em->flush();
            }


        }

        $twitter_share_url = 'https://twitter.com/intent/tweet?url=http://localhost:8091/favorite_share/'.$user_url.'&text=お気に入りリストです！';
        $facebook_share_url = 'https://www.facebook.com/sharer.php?src=bm&u=http://localhost:8091/favorite_share/'.$user_url.'&t=お気に入りリストです！';

        // 「非公開ページ　かつ　外部ユーザーのアクセス」をはじく
        $user = $this->getUser();               // ページを見ているユーザー
        if($user) {                             // 非ログインなら$userは空
            $auth = $user->getId() == $user_id; // ページを見ている人とページを持つ人が一致していたらtrue
        } else {
            $auth = 0;
        }
        if($share == 0 && $auth == 0) {         // 非公開ページ　かつ　外部ユーザー
            return $this->render('FavoriteReview/Resource/template/Mypage/share_error.twig', [
            ]);
        }

        // 「ログイン済みの別ユーザー」かつ「ギフトがON」なら購入ボタンを出す
        $gift = $this->customerRepository->find($user_id)->getGift();
        $canGift = 0;
        if($user    && $auth == 0 && $gift == 1) {
            // ログイン済 &  別ユーザー   &  ギフトがON
            $canGift = 1;                     // 真なら購入ボタンを表示する
        }


        return [
            'pagination' => $pagination,
            'pagination2' => $pagination2,
            'twitter_share_url' => $twitter_share_url,
            'facebook_share_url' => $facebook_share_url,
            'Customer' => $Customer,
            'user_id' => $user_id,
            'user_url' => $user_url,
            'form' => $form->createView(),
            'auth' => $auth,
            'canGift' => $canGift,
        ];

    }

    /**
     * ギフトを購入する.
     *
     * @Route("/gift_purchase/{id}", name="gift_purchase", methods={"GET","POST"})
     * @Route("/gift_purchase/{id}", name="gift_purchase_confirm", methods={"GET","POST"})
     * @Template("FavoriteReview/Resource/template/Mypage/purchase.twig")
     */
    public function giftPurchase(Request $request, PaginatorInterface $paginator, $id)
    {
        // 受け取る人の商品情報を取得
        $taker_id = $this->customerFavoriteProductRepository->find($id)->getCustomer()->getId();
        $Customer = $this->customerRepository->find($taker_id);

        // paginator
        $qb = $this->favoriteReviewRepository->getQueryBuilderByReviewId($Customer, $id);

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
        // 商品情報ここまで


        $form = $this->createFormBuilder()
        ->add('comment',TextType::class,[])
        ->add('amount',IntegerType::class,[])
        ->add('name',TextType::class,[])
        ->getForm();

        // 送る側のユーザー情報を取得
        $user = $this->getUser();
        $user_id = $user->getId();

            if($request->getMethod() == "POST"){

                $form->handleRequest($request);
                $gift = new \Plugin\FavoriteReview\Entity\Gift();

                $gift->setGiveUserId($user_id)
                ->setTakeUserId($taker_id)
                ->setFavoriteId($id)
                ->setComment($form->get('comment')->getData())
                ->setName($form->get('name')->getData())
                ->setAmount($form->get('amount')->getData());

                if($form->isSubmitted() && $form->isValid()){

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($gift);
                    $em->flush();

                }

                return $this->render(
                    'FavoriteReview/Resource/template/Mypage/gift_purchase_confirm.twig',
                    [
                        'gift' => $gift,
                        'pagination' => $pagination,
                        'form' => $form->createView(),
                        'Page' => $this->pageRepository->getPageByRoute('gift_purchase_confirm')
                    ]
                );

            }

        return [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ];
    }



    /**
     * ギフトの購入を確認する.
     *
     * @Route("/gift_purchase_complete", name="gift_purchase_complete", methods={"GET"})
     * @Template("FavoriteReview/Resource/template/Mypage/gift_purchase_complete.twig")
     */
    public function giftComplete(Request $request)
    {
        return[];
    }


}
