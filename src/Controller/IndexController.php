<?php

namespace App\Controller;

use App\Entity\Commentaires;
use App\Entity\Utilisateur;
use App\Form\SearchType;
use App\Repository\AppartementsRepository;
use App\Repository\BiensRepository;
use App\Repository\ChambresRepository;
use App\Repository\CommentairesRepository;
use App\Repository\MaisonsRepository;
use App\Repository\StudiosRepository;
use App\Service\Helpers;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $repo = new BiensRepository($doctrine);
        $form = $this->createForm(SearchType::class);
        $form->remove('type');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prix = ($form->get('prix')->getData() == '') ? 0 : $form->get('prix')->getData();
            $superficie = ($form->get('superficie')->getData() == '') ? 0 : $form->get('superficie')->getData();
            $ville = $form->get('ville')->getData();
            return $this->redirectToRoute('index.search', [
                'prix' => $prix,
                'ville' => $ville,
                'superficie' => $superficie
            ]);
        }
        $bien = $repo->findByIndex();
        return $this->render('index/index.html.twig', [
            'biens' => $bien,
            'form' => $form->createView()
        ]);
    }

    #[Route('/studio/{page?1}', name: 'index.studio')]
    public function studio(ManagerRegistry $doctrine, Request $request, $page): Response
    {
        $repo = new StudiosRepository($doctrine);
        $form = $this->createForm(SearchType::class);
        $form->remove('type');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prix = ($form->get('prix')->getData() == '') ? 0 : $form->get('prix')->getData();
            $superficie = ($form->get('superficie')->getData() == '') ? 0 : $form->get('superficie')->getData();
            $ville = $form->get('ville')->getData();
            return $this->redirectToRoute('index.search', [
                'prix' => $prix,
                'ville' => $ville,
                'superficie' => $superficie
            ]);
        }
        $nbreBien = $repo->count([]);
        $nbrePage = ceil($nbreBien / 10);
        $studio = $repo->findBy([],[], 10, ($page-1)*10);
        if (!$studio)
            return $this->render('index/studio.html.twig', [
                'studios' => $studio,
                'mess' => 'Aucun studio trouvé',
                'nbre' => 0,
                'nbrePage' => $nbrePage,
                'form' => $form->createView()
            ]);
        return $this->render('index/studio.html.twig', [
            'studios' => $studio,
            'page' => $page,
            'nbrePage' => $nbrePage,
            'form' => $form->createView()
        ]);
    }
    #[Route('/chambre/{page?1}', name: 'index.chambre')]
    public function chambre(ManagerRegistry $doctrine, Request $request, $page): Response
    {
        $repo = new ChambresRepository($doctrine);
        $form = $this->createForm(SearchType::class);
        $form->remove('type');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prix = ($form->get('prix')->getData() == '') ? 0 : $form->get('prix')->getData();
            $superficie = ($form->get('superficie')->getData() == '') ? 0 : $form->get('superficie')->getData();
            $ville = $form->get('ville')->getData();
            return $this->redirectToRoute('index.search', [
                'prix' => $prix,
                'ville' => $ville,
                'superficie' => $superficie
            ]);
        }
        $nbreBien = $repo->count([]);
        $nbrePage = ceil($nbreBien / 10);
        $studio = $repo->findBy([],[], 10, ($page-1)*10);
        if (!$studio)
            return $this->render('index/chambre.html.twig', [
                'studios' => $studio,
                'mess' => 'Aucune chambre trouvée',
                'nbre' => 0,
                'nbrePage' => $nbrePage,
                'form' => $form->createView()
            ]);
        return $this->render('index/chambre.html.twig', [
            'studios' => $studio,
            'page' => $page,
            'nbrePage' => $nbrePage,
            'form' => $form->createView()
        ]);
    }
    #[Route('/appart/{page?1}', name: 'index.appart')]
    public function appart(ManagerRegistry $doctrine, Request $request, $page): Response
    {
        $repo = new AppartementsRepository($doctrine);
        $form = $this->createForm(SearchType::class);
        $form->remove('type');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prix = ($form->get('prix')->getData() == '') ? 0 : $form->get('prix')->getData();
            $superficie = ($form->get('superficie')->getData() == '') ? 0 : $form->get('superficie')->getData();
            $ville = $form->get('ville')->getData();
            return $this->redirectToRoute('index.search', [
                'prix' => $prix,
                'ville' => $ville,
                'superficie' => $superficie
            ]);
        }
        $nbreBien = $repo->count([]);
        $nbrePage = ceil($nbreBien / 10);
        $studio = $repo->findBy([],[], 10, ($page-1)*10);
        if (!$studio)
            return $this->render('index/appart.html.twig', [
                'studios' => $studio,
                'mess' => 'Aucun appartement retrouvé',
                'nbre' => 0,
                'nbrePage' => $nbrePage,
                'form' => $form->createView()
            ]);
        return $this->render('index/appart.html.twig', [
            'studios' => $studio,
            'page' => $page,
            'nbrePage' => $nbrePage,
            'form' => $form->createView()
        ]);
    }
    #[Route('/maison/{page?1}', name: 'index.maison')]
    public function maison(ManagerRegistry $doctrine, Request $request, $page): Response
    {
        $repo = new MaisonsRepository($doctrine);
        $form = $this->createForm(SearchType::class);
        $form->remove('type');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prix = ($form->get('prix')->getData() == '') ? 0 : $form->get('prix')->getData();
            $superficie = ($form->get('superficie')->getData() == '') ? 0 : $form->get('superficie')->getData();
            $ville = $form->get('ville')->getData();
            return $this->redirectToRoute('index.search', [
                'prix' => $prix,
                'ville' => $ville,
                'superficie' => $superficie
            ]);
        }
        $nbreBien = $repo->count([]);
        $nbrePage = ceil($nbreBien / 10);
        $studio = $repo->findBy([],[], 10, ($page-1)*10);
        if (!$studio)
            return $this->render('index/maison.html.twig', [
                'studios' => $studio,
                'mess' => 'Aucune maison retrouvée',
                'nbre' => 0,
                'nbrePage' => $nbrePage,
                'form' => $form->createView()
            ]);
        return $this->render('index/maison.html.twig', [
            'studios' => $studio,
            'page' => $page,
            'nbrePage' => $nbrePage,
            'form' => $form->createView()
        ]);
    }
    #[Route('/carte', name: 'index.carte')]
    public function carte(): Response
    {
        return $this->render('index/carte.html.twig', [
            'carte' => 'carte'
        ]);
    }
    #[
        Route('/contact', name: 'index.contact'),
        IsGranted('ROLE_USER')
    ]
    public function contact(Request $request, Helpers $helpers, ManagerRegistry $doctrine): Response
    {
        // $repo = New CommentairesRepository($this->doctrine);
        $com = new Commentaires();
        $mess = $request->get('mess');
        if ($mess != "") {
            $manager = $doctrine->getManager();
            $com->setContenu($mess)->setUtilisateur($helpers->getUser())->setCreateAt();
            $manager->persist($com);
            $manager->flush();
            $this->addFlash('success', ' Votre commentaire a bien été envoyé aux admins ');
        }
        return $this->render('index/contact.html.twig', [
            'carte' => 'carte'
        ]);
    }
    #[Route('/propos', name: 'index.propos')]
    public function propos(): Response
    {
        return $this->render('index/propos.html.twig', [
            'carte' => 'carte'
        ]);
    }

    #[Route('/q/{page?1}/{prix}/{ville}/{superficie}', name: 'index.search')]
    public function search(ManagerRegistry $doctrine, Request $request, $page, $prix, $ville, $superficie): Response
    {
        $repo = new BiensRepository($doctrine);
        $form = $this->createForm(SearchType::class);
        $form->remove('type');

        $biens = $repo->findWithoutOffset($prix, $superficie, $ville);
        $nbreBien = count($biens);
        $nbrePage = ceil($nbreBien / 10);
        $bien = $repo->findWithOffset($prix, $superficie, $ville, $page);
        if (!$bien) {
            $this->addFlash("error", " Aucun bien trouvé!!! ");
            return $this->redirectToRoute("index");
        }
        return $this->render('index/index.html.twig', [
            'page' => $page,
            'prix' => $prix,
            'ville' => $ville,
            'superficie' => $superficie,
            'nbrePage' => $nbrePage,
            'biens' => $bien,
            'form' => $form->createView()
        ]);
    }
}
