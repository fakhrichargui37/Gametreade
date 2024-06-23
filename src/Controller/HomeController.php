<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpCache\SubRequestHandler;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(ProductRepository $productRepository,CategoryRepository $categoryRepository,Request $request,PaginatorInterface $paginationInterface): Response
    {
        $data = $productRepository->findBy([],['id'=>'DESC']);
        $products = $paginationInterface->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'categories' => $categoryRepository->findAll()
        ]);
    }
    #[Route('/product/{id}/show', name: 'app_home_product_show', methods: ['GET'])]
    public function show(Product $product,ProductRepository $productRepository,categoryRepository $categoryRepository): Response
    {
        $lasProducts= $productRepository->findBy([],['id'=>'DESC'],4);
        return $this->render('home/show.html.twig', [
            'product' => $product,
            'products' => $lasProducts,
            'categories' => $categoryRepository->findAll()
        ]);
    }
    #[Route('/product/subcategory/{id}/filter', name: 'app_home_product_filter', methods: ['GET'])]
    public function filter($id,SubCategoryRepository $subCategoryRepository,categoryRepository $categoryRepository): Response
    {
        $products = $subCategoryRepository->find($id)->getProducts();
        $subCategory=$subCategoryRepository->find($id);
        return $this->render('home/filter.html.twig', [
            'products' => $products,
            'subCategory' => $subCategory,
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
