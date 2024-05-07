<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    #[Route('/admin/user/{id}/to/editor', name: 'app_user-to-editor')]
    public function changeRole(EntityManagerInterface $entityManagerInterface,User $user): Response
    {
        $user->setRoles(['ROLE_EDITOR',"ROLE_USER"]);
        $entityManagerInterface->flush();
        $this->addFlash('success','User Role Changed Successfully');
        return $this->redirectToRoute('app_user');
    }
    #[Route('/admin/user/{id}/remove/editor/role', name: 'app_user-to-remove-editor-role')]
    public function editorRoleRemove(EntityManagerInterface $entityManagerInterface,User $user): Response
    {
        $user->setRoles([]);
        $entityManagerInterface->flush();
        $this->addFlash('danger','le role de editeur a été retire avec succès');
        return $this->redirectToRoute('app_user');
    }
    #[Route('/admin/user/{id}/to/delete', name: 'app_user-to-delete')]
    public function deleteUser(EntityManagerInterface $entityManagerInterface,$id,User $user,UserRepository $userRepository): Response
    {
        $userfind=$userRepository->find($id);
        $entityManagerInterface->remove($userfind);
        $entityManagerInterface->flush();
        $this->addFlash('danger','User Deleted Successfully');
        return $this->redirectToRoute('app_user');
    }
}
