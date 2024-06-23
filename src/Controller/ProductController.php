<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\AddProductHistory;
use App\Form\AddProductHistoryType;
use App\Form\PoductUpdateType;


#[Route('/editor/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,SluggerInterface $sluggerInterface): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $sluggerInterface->slug($originalName);
            $newFileName = $safeFileName . '-' . uniqid() . '.' . $image->guessExtension();

            try {
                $image->move(
                $this->getParameter('image_dir'),
                $newFileName
                );
                $product->setImage($newFileName);
            } catch (FileException $e) {
                $product->setImage($newFileName);
            }
            }

            $entityManager->persist($product);
            $entityManager->flush();


            $stockHistory = new AddProductHistory();
            $stockHistory->setQte($product->getStock());
            $stockHistory->setProduct($product);
            $stockHistory->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($stockHistory);
            $entityManager->flush();


            $this->addFlash('success', 'votre produit a été ajouté!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager,SluggerInterface $sluggerInterface): Response
    {
        $form = $this->createForm(PoductUpdateType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $image = $form->get('image')->getData();

            if ($image) {
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFileName = $sluggerInterface->slug($originalName);
            $newFileName = $safeFileName . '-' . uniqid() . '.' . $image->guessExtension();

            try {
                $image->move(
                $this->getParameter('image_dir'),
                $newFileName
                );
                $product->setImage($newFileName);
            } catch (FileException $e) {
                $product->setImage($newFileName);
            }
            }



            $entityManager->flush();
            $this->addFlash('success', 'votre produit a ete modifie!');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($product);
            $this->addFlash('success', 'votre produit a ete supprime!');
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/add/product/{id}/stock', name: 'app_product_stock_add', methods: ['POST','GET'])]
    public function addStock($id,EntityManagerInterface $entityManager,Request $request,ProductRepository $productRepository): Response
    {
        
        $addStock = new AddProductHistory();
        $form = $this->createForm(AddProductHistoryType::class, $addStock);
        $form->handleRequest($request);
        $procduct = $productRepository->find($id);

        if($form->isSubmitted() && $form->isValid()){
            if($addStock->getQte() > 0){
               
            $newQte = $procduct->getStock() + $addStock->getQte();
            $procduct->setStock($newQte);
            $addStock->setCreatedAt(new \DateTimeImmutable());
            $addStock->setProduct($procduct);
            $entityManager->persist($addStock);
            $entityManager->flush();
            $this->addFlash('success', 'votre stock a ete ajoute!');
            return $this->redirectToRoute('app_product_index');
            }else{
                $this->addFlash('danger', 'votre stock doit etre superieur a 0!',['id'=>$procduct->getId()]);
            }
        }
        return $this->render('product/add_stock.html.twig', [
            'form' => $form->createView(),
            'product' => $procduct,
        ]);

    }
    #[Route('/add/product/{id}/stock/history', name: 'app_product_stock_add-history', methods: ['POST','GET'])]
    public function stockHistory($id,ProductRepository $productRepository,AddProductHistoryRepository $addProductHistoryRepository): Response
    {
        $product = $productRepository->find($id);
        $productRepository = $addProductHistoryRepository->findBy(['product'=>$product],['id'=>'DESC']);
        return $this->render('product/added_stock_history.html.twig', [
            'productRepository' => $productRepository,
        ]);
    }
}
