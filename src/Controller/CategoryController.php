<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories=$categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories'=>$categories
        ]);
    }
    #[Route('/admin/category/new', name: 'app_category/new')]
    public function addcategory(EntityManagerInterface $entityManager,HttpFoundationRequest $request): Response
    {
        $category=new Category();
        $form=$this->createForm(CategoryFormType::class,$category) ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success','Category Created Successfully');
            return $this->redirectToRoute('app_category');
            
        }
        return $this->render('category/new.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/admin/category/{id}/update', name: 'app_category-update')]
    public function updatecategory(Category $category,EntityManagerInterface $entityManager,HttpFoundationRequest $request): Response
    {
        $form=$this->createForm(CategoryFormType::class,$category) ;
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();
            $this->addFlash('success','Category Updated Successfully');
            return $this->redirectToRoute('app_category');
            
        }
        return $this->render('category/update.html.twig', ['form' => $form->createView()]);
    }
    #[Route('/admin/category/{id}/delete', name: 'app_category-delete')]
    public function Deletecategory(Category $category,EntityManagerInterface $entityManager,HttpFoundationRequest $request): Response
    {
        $entityManager->remove($category);
        $entityManager->flush();
        $this->addFlash('danger', 'Category Deleted Successfully');
        return $this->redirectToRoute('app_category');

    }
}
