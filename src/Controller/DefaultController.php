<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\CategoryProduct;
use App\Entity\Product;
use App\Entity\Tag;
use App\Entity\VisitaPais;
use App\Repository\BlogRepository;
use App\Repository\CategoryProductRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductViewImageRepository;
use App\Repository\TagRepository;
use App\Repository\VisitaPaisRepository;
use App\Service\Mtto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController
 * @package App\Controller
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function default()
    {
        return $this->redirectToRoute('inicio');
    }
    
    /**
     * @Route("/inicio/", name="inicio")
     */
    public function index(CategoryProductRepository $categoryProductRepository, ProductRepository $productRepository, BlogRepository $blogRepository): Response
    {
        return $this->render('default/index.html.twig', [
            'categories' => $categoryProductRepository->findBy(['active' => true], ['name' => 'ASC']),
            'productos' => $productRepository->findBy(['public' => true], ['visita' => 'DESC'], 8),
            'blogs' => $blogRepository->findBy([ 'public' => true ], ['id' => 'DESC'])
        ]);
    }

    public function menuCategory(CategoryProductRepository $categoryProductRepository)
    {
        return $this->render('includes/default_category.html.twig', [
            'categories' => $categoryProductRepository->findBy(['active' => true], ['name' => 'ASC'])
        ]);
    }

    /**
     * @Route("/categoria/producto/{id}/{slug}/{page}", name="default_categoria_producto", defaults={"page"=1})
     * @param CategoryProduct $categoryProduct
     * @param ProductRepository $productRepository
     * @param $page
     * @return Response
     */
    public function categoriaProductos(CategoryProduct $categoryProduct, ProductRepository $productRepository, $page)
    {
        $productsTotal = $productRepository->findBy(['categoryProduct' => $categoryProduct, 'public' => true]);

        $setMaxResults    = Product::page;
        $cantTotal        = count($productsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;

        $products = $productRepository->findBy(['categoryProduct' => $categoryProduct, 'public' => true], ['id' => 'DESC'],  $setMaxResults, ($page-1) * $setMaxResults);

        return $this->render('default/category_products.html.twig', [
            'products' => $products,
            'categoria' => $categoryProduct,
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param ProductRepository $productRepository
     * @param $page
     * @Route("/producto/pupulares/{page}", name="default_product_pupulate", defaults={"page"=1})
     */
    public function productPopulate(ProductRepository $productRepository, $page)
    {
        $productsTotal = $productRepository->findBy(['public' => true], ['visita' => 'DESC']);
        $setMaxResults    = Product::page;
        $cantTotal        = count($productsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;
        $products = $productRepository->findBy(['public' => true], ['visita' => 'DESC'], $setMaxResults, ($page-1) * $setMaxResults);

        return $this->render('default/_products_populate.html.twig', [
            'products' => $products,
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param ProductRepository $productRepository
     * @param $page
     * @Route("/producto/los-mas-gustado/{page}", name="default_product_likeless", defaults={"page"=1})
     */
    public function productLikeLess(ProductRepository $productRepository, $page)
    {
        $productsTotal = $productRepository->findBy(['public' => true], ['likeCount' => 'DESC']);
        $setMaxResults    = Product::page;
        $cantTotal        = count($productsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;
        $products = $productRepository->findBy(['public' => true], ['likeCount' => 'DESC'], $setMaxResults, ($page-1) * $setMaxResults);

        return $this->render('default/_products_likeless.html.twig', [
            'products' => $products,
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param CategoryProductRepository $categoryProductRepository
     * @Route("/productos/", name="default_categoria")
     */
    public function categoriaProductoAll(CategoryProductRepository $categoryProductRepository)
    {
        return $this->render('default/_categoria_productos.html.twig', [
            'categorias' => $categoryProductRepository->findBy(['active' => true])
        ]);
    }

    /**
     * @Route("/resultado/buscador/{cadena}/{page}", name="default_search_product", defaults={"cadena"="","page"=1})
     * @param ProductRepository $productRepository
     * @param $cadena
     * @param $page
     * @return Response
     */
    public function productSearch(ProductRepository $productRepository, $cadena, $page)
    {
        $productsTotal = $productRepository->findBuscador($cadena);
        $setMaxResults    = Product::page;
        $cantTotal        = count($productsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;

        $products = $productRepository->findBuscador($cadena, ($page-1) * $setMaxResults, $setMaxResults);

        return $this->render('default/_product_search.html.twig', [
            'products' => $products,
            'cadena' => $cadena,
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param Request $request
     * @param Product $product
     * @Route("/like/product/{id}", name="default_like_product")
     */
    public function likePlus(Request $request, Product $product)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }

        $likeCount = $product->getLikeCount() ?? 0;
        $product->setLikeCount($likeCount + 1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);

        try {
            $entityManager->flush();

            return new JsonResponse(1);
        }catch (\Exception $exception){
            return new JsonResponse($exception->getMessage());
        }
    }

    /**
     * @param Product $product
     * @Route("/producto/{id}/{slug}", name="default_product")
     */
    public function product(Product $product, ProductViewImageRepository $productViewImageRepository)
    {
        $product->setVisita($product->getVisita()+1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->render('default/product.html.twig', [
            'product' => $product,
            'views' => $productViewImageRepository->findBy(['product' => $product])
        ]);
    }

    public function menuTop(CategoryProductRepository $categoryProductRepository, BlogRepository $blogRepository)
    {
        return $this->render('includes/default_menu_top.html.twig', [
            'categoryProducts' => $categoryProductRepository->findCategoryProduct(),
            'blogs' => count($blogRepository->findBy(['public' => true]))
        ]);
    }

    /**
     * @Route("/visita/pais", name="default_visita_pais", methods={"POST"})
     *
     * @param Request $request
     * @param Session $session
     * @param VisitaPaisRepository $visitaPaisRepository
     * @param Mtto $mtto
     * @return RedirectResponse|Response
     */
    public function visitaPais(Request $request, Session $session, VisitaPaisRepository $visitaPaisRepository, Mtto $mtto)
    {
        if ($mtto->statusSite() === 1){
            return $this->redirectToRoute('default_mantenimiento');
        }

        $ipCliente = substr($request->getClientIp(), 0, strripos($request->getClientIp(), ".")).'.0';
        $ch = curl_init("https://api.ipgeolocationapi.com/geolocate/".$ipCliente);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

//        $arrayJson = '{"continent":"North America","address_format":"{{recipient}}\n{{street}}\n{{city}} {{region_short}} {{postalcode}}\n
//          {{country}}","alpha2":"US","alpha3":"USA","country_code":"1","international_prefix":"011","ioc":"USA","gec":"US",
//          "name":"United States of America","national_destination_code_lengths":[3],"national_number_lengths":[10],
//          "national_prefix":"1","number":"840","region":"Americas","subregion":"Northern America","world_region":"AMER",
//          "un_locode":"US","nationality":"American","postal_code":true,"unofficial_names":["United States","Vereinigte Staaten von Amerika",
//          "États-Unis","Estados Unidos","アメリカ合衆国","Verenigde Staten"],"languages_official":["en"],"languages_spoken":["en"],
//          "geo":{"latitude":37.09024,"latitude_dec":"39.44325637817383","longitude":-95.712891,"longitude_dec":"-98.95733642578125",
//          "max_latitude":71.5388001,"max_longitude":-66.885417,"min_latitude":18.7763,"min_longitude":170.5957,
//          "bounds":{"northeast":{"lat":71.5388001,"lng":-66.885417},"southwest":{"lat":18.7763,"lng":170.5957}}},"currency_code":"USD",
//          "start_of_week":"sunday"}';
        $visitaPais = $session->get('visita_pais');

        if($visitaPais === 'hello'){
            return new Response('');
        }

        $session->start();
        $session->set('visita_pais', 'hello');
        $session->save();

        $json = json_decode($result, true);

        if ($json){
            $visitaPaisExisteneHoy = $visitaPaisRepository->findOneBy(['ioc' => $json['ioc'], 'date' => new DateTime()]);
            $entityManager = $this->getDoctrine()->getManager();

            if (!$visitaPaisExisteneHoy) {
                $visitaPais = new VisitaPais();
                $visitaPais->setContinente($json['continent'])
                    ->setGec($json['gec'])
                    ->setIoc($json['ioc'])
                    ->setPais($json['name'])
                    ->setTcpIp($ipCliente)
                    ->setCantidad(1);
                $entityManager->persist($visitaPais);
            }else{
                $visitaPaisExisteneHoy->setCantidad($visitaPaisExisteneHoy->getCantidad()+1);
                $entityManager->persist($visitaPaisExisteneHoy);
            }

            $entityManager->flush();
        }else{
            trigger_error(curl_error($ch));
            exit;
        }

        curl_close($ch);

        return new Response('');
    }

    /**
     * @param BlogRepository $blogRepository
     * @Route("/blogs/{page}", name="default_blogs", defaults={"page"=1})
     */
    public function blogs(BlogRepository $blogRepository, CategoryProductRepository $categoryProductRepository, TagRepository $tagRepository, $page)
    {
        $blogsTotal = $blogRepository->findBy(['public' => true], ['id' => 'DESC']);

        $setMaxResults    = Blog::page;
        $cantTotal        = count($blogsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;

        $blogs = $blogRepository->findBy(['public' => true], ['id' => 'DESC'], $setMaxResults, ($page-1) * $setMaxResults);

        return $this->render('default/blogs.html.twig', [
            'blogs' => $blogs,
            'categories' => $categoryProductRepository->findAll(),
            'tags' => $tagRepository->findAll(),
            'lastBlogs' => $blogRepository->findBy(['public' => true], ['date' => 'DESC'], 5, 5),
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param CategoryProduct $categoryProduct
     * @param BlogRepository $blogRepository
     * @param CategoryProductRepository $categoryProductRepository
     * @param TagRepository $tagRepository
     * @return Response
     * @Route("/blogs-categoria/{slug}/{page}", name="default_blog_category", defaults={"page"=1})
     */
    public function blogCategory(CategoryProduct $categoryProduct, BlogRepository $blogRepository, CategoryProductRepository $categoryProductRepository, TagRepository $tagRepository, $page)
    {
        $blogsTotal = $blogRepository->findBy(['category' => $categoryProduct, 'public' => true], ['id' => 'DESC']);

        $setMaxResults    = Blog::page;
        $cantTotal        = count($blogsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;

        $blogs = $blogRepository->findBy(['category' => $categoryProduct, 'public' => true], ['id' => 'DESC'], $setMaxResults, ($page-1) * $setMaxResults);


        return $this->render('default/blogs.html.twig', [
            'blogs' => $blogs,
            'categories' => $categoryProductRepository->findAll(),
            'tags' => $tagRepository->findAll(),
            'lastBlogs' => $blogRepository->findBy(['category' => $categoryProduct, 'public' => true], ['date' => 'DESC'], 5, 5),
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param Tag $tag
     * @param BlogRepository $blogRepository
     * @param TagRepository $tagRepository
     * @param CategoryProductRepository $categoryProductRepository
     * @return Response
     * @Route("/blog/tag/{slug}/{page}", name="default_blog_tag", defaults={"page"=1})
     */
    public function blogTag(Tag $tag, BlogRepository $blogRepository, TagRepository $tagRepository, CategoryProductRepository $categoryProductRepository, $page)
    {
        $blogsTotal = $blogRepository->findBlogTag($tag->getSlug());

        $setMaxResults    = Blog::page;
        $cantTotal        = count($blogsTotal);

        $ultimaPagina     = $cantTotal ? ceil($cantTotal/$setMaxResults) : 1;

        $blogs = $blogRepository->findBlogTag($tag->getSlug(),  ($page-1) * $setMaxResults, $setMaxResults);

        return $this->render('default/blogs.html.twig', [
            'blogs' => $blogs,
            'categories' => $categoryProductRepository->findAll(),
            'tags' => $tagRepository->findAll(),
            'lastBlogs' => $blogRepository->findBy(['public' => true], ['date' => 'DESC'], 5, 5),
            'total' => $ultimaPagina,
            'current' => $page
        ]);
    }

    /**
     * @param Blog $blog
     * @Route("/blog/{slug}", name="default_blog_single")
     */
    public function blogDetail(Blog $blog, BlogRepository $blogRepository)
    {
        $blog->setVisit($blog->getVisit()+1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($blog);
        $entityManager->flush();

        return $this->render('default/blog_single.html.twig', [
            'blog' => $blog,
            'blogs' => $blogRepository->findBlogRelacionado($blog->getId(), $blog->getCategory()->getSlug())
        ]);
    }

    /**
     * @param Request $request
     * @param Blog $blog
     * @Route("/blog/like/{slug}", name="default_like_blog", methods={"POST"})
     */
    public function likeBlog(Request $request, Blog $blog)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }

        $blog->setLikeless($blog->getLikeless()+1);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($blog);
        $entityManager->flush();

        return new JsonResponse('');
    }

    /**
     * @param ProductRepository $productRepository
     * @Route("/footer/products", name="default_footer_product", methods={"POST"})
     */
    public function footerProducto(Request $request, ProductRepository $productRepository)
    {
        if (!$request->isXmlHttpRequest()){
            throw $this->createNotFoundException();
        }

        $list = [];

        $products = $productRepository->findBy(['public' => true], ['visita' => 'DESC'], 6);

        foreach ($products as $product) {
            $list [] = [
                'id' => $product->getId(),
                'slug' => $product->getSlug(),
                'name' => $product->getName(),
                'image' => '/images/'.$product->getImage(),
                'url' => $this->generateUrl('default_product', [ 'id' => $product->getId(), 'slug' => $product->getSlug() ])
            ];
        }

        return new JsonResponse($list, 200);
    }
}
