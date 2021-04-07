<?php

namespace App\Controller;

use App\Entity\Config;
use App\Repository\BlogRepository;
use App\Repository\CategoryProductRepository;
use App\Repository\ConfigRepository;
use App\Repository\ProductRepository;
use App\Repository\TagRepository;
use App\Repository\VisitaPaisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Controller
 * @Route("/cpanel")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(ProductRepository $productRepository, VisitaPaisRepository $visitaPaisRepository): Response
    {
        $visitaTotal = $visitaPaisRepository->findVisitaTotal();
        $visitaHoy = $visitaPaisRepository->findVisitaHoy(new \DateTime(date('d-m-Y')));
        $visitaAntesSieteDias = $visitaPaisRepository->findVisitaBefore('-7 days');
        $visitaAntesTreintaDias = $visitaPaisRepository->findVisitaBefore('-30 days');

        return $this->render('dashboard/index.html.twig', [
            'products' => $productRepository->findBy([], ['visita' => 'DESC'], 3),
            'visitas' => $visitaPaisRepository->findAll(),
            'visitaTotales' => $visitaTotal,
            'visitaHoy' => $visitaHoy,
            'visitaSiete' => $visitaAntesSieteDias,
            'visitaAntesTreintaDias' => $visitaAntesTreintaDias
        ]);
    }

    /**
     * @param ContainerInterface $container
     * @Route("/make/site-map", name="make_sitemap")
     */
    public function siteMapBuild(Request $request, ContainerInterface $container, ProductRepository $productRepository, BlogRepository $blogRepository, CategoryProductRepository $categoryProductRepository, TagRepository $tagRepository)
    {
        $siteMap = $container->getParameter('kernel.project_dir').'/public/sitemap.xml';
        $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset
      xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"
      xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
      xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9
            thttp://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">
<!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->";
        $content .= "
            <url>
                <loc>".$this->generateUrl('default', [], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>1.00</priority>
            </url>
            <url>
                <loc>".$this->generateUrl('inicio', [], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.95</priority>
            </url>
        ";
        foreach ($productRepository->findBy(['public' => true]) as $item) {
            $content .= "
            <url>
                <loc>".$this->generateUrl('default_product', ['id' => $item->getId(), 'slug' => $item->getSlug()], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.85</priority>
            </url>
            <url>
                <loc>".$this->generateUrl('default_search_product', ['cadena' => $item->getName()], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.80</priority>
            </url>"
            ;
        }

        foreach ($categoryProductRepository->findBy(['active' => true]) as $item){
            $content .= "
            <url>
                <loc>".$this->generateUrl('default_categoria_producto', ['id' => $item->getId(), 'slug' => $item->getSlug()], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.78</priority>
            </url>
            <url>
                <loc>".$this->generateUrl('default_blog_category', ['slug' => $item->getSlug()], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.70</priority>
            </url>
            ";
        }

        foreach ($tagRepository->findAll() as $item) {
            $content .= "
            <url>
                <loc>".$this->generateUrl('default_blog_tag', ['slug' => $item->getSlug()], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.65</priority>
            </url>";
        }

        foreach ($blogRepository->findBy(['public' => true]) as $item) {
            $content .= "
            <url>
                <loc>".$this->generateUrl('default_blog_single', ['slug' => $item->getSlug()], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.60</priority>
            </url>";
        }
        $content .= "
            <url>
                <loc>".$this->generateUrl('default_product_likeless', [],0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.60</priority>
            </url>
            <url>
                <loc>".$this->generateUrl('default_product_pupulate',[],0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.60</priority>
            </url>
            <url>
                <loc>".$this->generateUrl('default_categoria',[], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.60</priority>
            </url>
            <url>
                <loc>".$this->generateUrl('default_blogs',[], 0)."</loc>
                <lastmod>".date("c")."</lastmod>
                <priority>0.60</priority>
            </url>\n";
        ;
        $content .= "</urlset>";

        try {
            file_put_contents($siteMap, $content, LOCK_EX);

            return new JsonResponse('Se ha creado satisfactoriamente!', 200);
        }catch (\Exception $exception){
            return new JsonResponse('Ha ocurrido un error '.$exception->getMessage(), 400);
        }
    }

    /**
     * @param Config $config
     * @Route("/mantenimiento/", name="dashboard_mantenimiento", methods={"GET","POST"})
     */
    public function toggleMantenimiento(Request $request, ConfigRepository $configRepository)
    {
        $config = $configRepository->findOneBy(['nombre' => 'app-mtto']);
        if ($request->getMethod() == "POST") {
            $config->getValor() ? $config->setValor(0) : $config->setValor(1);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($config);
            $entityManager->flush();
        }

        return new JsonResponse($config->getValor(), 200);
    }
}
