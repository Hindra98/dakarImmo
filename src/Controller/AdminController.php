<?php

namespace App\Controller;

use App\Entity\Commentaires;
use App\Entity\Utilisateur;
use App\Repository\BiensRepository;
use App\Repository\CommentairesRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Helpers;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private UtilisateurRepository $user;
    private CommentairesRepository $com;
    private ManagerRegistry $doctrine;

    public function __construct(UtilisateurRepository $user, CommentairesRepository $com, ManagerRegistry $doctrine) {
        $this->user = $user;
        $this->com = $com;
        $this->doctrine = $doctrine;
    }
    #[
        Route('/p/{page?1}', name: 'app_admin'),
        IsGranted('ROLE_ADMIN')
    ]
    public function index($page, Helpers $helpers): Response
    {
        $nbreBien = $this->user->count([]);
        $nbrePage = ceil($nbreBien / 10);
        $user = $this->user->findUser($helpers->getUser()->getId(), $page);
        return $this->render('admin/index.html.twig', [
            'page' => $page,
            'nbrePage' => $nbrePage,
            'user' => $user
        ]);
    }

    #[
        Route('/comment/{page?1}', name: 'app_admin_comment'),
        IsGranted('ROLE_ADMIN')
    ]
    public function comment($page): Response
    {
        $nbreBien = $this->com->count([]);
        $nbrePage = ceil($nbreBien / 10);
        $user = $this->com->findBy([],[], 10, ($page-1)*10);
        return $this->render('admin/comment.html.twig', [
            'page' => $page,
            'nbrePage' => $nbrePage,
            'user' => $user
        ]);
    }

    #[
        Route('/delete/{id}', name: 'app_admin_delete'),
        IsGranted('ROLE_ADMIN')
    ]
    public function delete(Utilisateur $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->get('_token'))) {
            $nom = $user->getNom();
            $manager = $this->doctrine->getManager();
            $manager->remove($user);
            $manager->flush();
            $this->addFlash("success",$nom." a été supprimé avec succès");
        }
        return $this->render('admin/index.html.twig');
    }
    #[
        Route('com/del/{id}', name: 'app_admin_del_com'),
        IsGranted('ROLE_ADMIN')
    ]
    public function del_com(Commentaires $com, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$com->getId(), $request->get('_token'))) {
            $manager = $this->doctrine->getManager();
            $manager->remove($com);
            $manager->flush();
        }
        return $this->render('admin/comment.html.twig');
    }
}
