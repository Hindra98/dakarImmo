<?php

namespace App\Controller;

use App\Entity\Appartements;
use App\Entity\Biens;
use App\Entity\Chambres;
use App\Entity\Favoris;
use App\Entity\Maisons;
use App\Entity\PhotosBiens;
use App\Entity\Proprietaires;
use App\Entity\Studios;
use App\Entity\Utilisateur;
use App\Form\BienPhotoType;
use App\Form\BiensType;
use App\Form\SearchType;
use App\Repository\BiensRepository;
use App\Repository\ProprietairesRepository;
use App\Service\FileUploader;
use App\Service\Helpers;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/FI')]
class BiensController extends AbstractController
{

    private BiensRepository $biens;
    private ManagerRegistry $doctrine;
    private $tab_bien = [
        0 => 'Chambres',
        0 => 'Studios',
        0 => 'Appartements',
        0 => 'Maisons'
    ];

  public function __construct(BiensRepository $biens, ManagerRegistry $doctrine) {
      $this->biens = $biens;
      $this->doctrine = $doctrine;
  }
    #[
        Route('/p/{page?1}', name: 'biens'),
        IsGranted('ROLE_PROPRIO')
    ]
    public function index($page, Helpers $helpers): Response
    {

        $biensUser = $this->biens->findByUser($helpers->getUser()->getProprio()->current());
        $nbreBien = count($biensUser);
        $nbrePage = ceil($nbreBien / 10);
        $biens = $this->biens->findByUserWithOfset($helpers->getUser()->getProprio(), $page);
        return $this->render('biens/index.html.twig', [
            'page' => $page,
            'nbrePage' => $nbrePage,
            'biens' => $biens
        ]);
    }

    #[
        Route('/create', name: 'biens.create'),
        IsGranted('ROLE_PROPRIO')
    ]
    public function newBien(Request $request, FileUploader $fileUploader, Helpers $helpers): Response
    {
        $biens = new Biens();
        $form = $this->createForm(BienPhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $tof = $form->get('photo')->getData();
            $to = $tof[0];
            $dir = $this->getParameter('repertoire_images_biens');
            $newName = $fileUploader->upload($to, $dir);
            $adresse = $form->get('numero')->getData().' '.$form->get('rue')->getData().', ';
            $adresse .= $form->get('code')->getData().' '.$form->get('ville')->getData();
            $description = $form->get('description')->getData();
            $biens->setPhoto($newName)
                ->setPrix($form->get('prix')->getData())
                ->setSuperficie($form->get('superficie')->getData())
                ->setNom($form->get('nom')->getData())
                ->setProprietaires($helpers->getUser()->getProprio()->current())
                ->setAdresse($adresse)->setDescription($description);
            $biens->setEtat(1)->setDateCreation(new DateTime());
            $manager = $this->doctrine->getManager();
            $manager->persist($biens);
            $type = $form->get('type')->getData();
            switch ($type) {
                case 0:
                    $ch = new Chambres();
                    $ch->setBiens($biens)->setType("Kot");
                    $manager->persist($ch);
                    $manager->flush();
                    break;
                case 1:
                    $ch = new Studios();
                    $ch->setBiens($biens);
                    $manager->persist($ch);
                    $manager->flush();
                    break;
                case 3:
                    $ch = new Appartements();
                    $ch->setBiens($biens)->setEtage(true)->setAscenceur(false)->setNbrePieces(4)->setGarage(true);
                    $manager->persist($ch);
                    $manager->flush();
                    break;
                case 4:
                    $ch = new Maisons();
                    $ch->setBiens($biens)->setNbrePieces(4)->setGrenier(true);
                    $manager->persist($ch);
                    $manager->flush();
                    break;
            }

            $photoFiles = $form->get('photo');
            if ($photoFiles) {
                $directory = $this->getParameter('repertoire_images_biens');
                //dd($photoFiles->getData());
                foreach($photoFiles->getData() as $photoFile) {
                    $nouveauNom = $fileUploader->upload($photoFile, $directory);
                    $photo = new PhotosBiens();
                    $photo->setNom($nouveauNom)->setCreateAt()->setBiens($biens);
                    $managers = $this->doctrine->getManager();
                    $managers->persist($photo);
                    $managers->flush();
                }
                // dd($photoFiles);
            }
            $manager->flush();
            return $this->render('biens/type.html.twig', [
                'biens' => $biens,
                'form' => $form->createView(),
                'type' => $type
            ]);
            $this->addFlash("success",$biens->getNom()." a été ajouté avec succès");
            return $this->redirectToRoute("biens");
        }
        return $this->render('biens/create.html.twig', [
            'biens' => $biens,
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'biens.detail')]
    public function detail($id, Helpers $helpers) : Response
    {
        $repo = $this->doctrine->getRepository(Biens::class);
        $repoPhoto = $this->doctrine->getRepository(PhotosBiens::class);
        $bien = $repo->find($id);
        $photos = $repoPhoto->findByBien($bien);
        $repoFav = $this->doctrine->getRepository(Favoris::class);
        $fav = $repoFav->findByBiens($id, $bien->getProprietaires()->getUtilisateur());
        if (!$bien) {
            $this->addFlash('error', "Le bien n'existe pas");
            return $this->redirectToRoute("index");
        }
        if (!$fav) {
            return $this->render('biens/detail.html.twig', [
                'bien' => $bien,
                'fav' => $fav,
                'favs' => 0,
                'photos' => $photos
            ]);
        }
        return $this->render('biens/detail.html.twig', [
            'bien' => $bien,
            'fav' => $fav,
            'photos' => $photos
        ]);

    }

    #[
        Route('/edit/{id}', name: 'biens.edit', methods: 'GET|POST'),
        IsGranted('ROLE_PROPRIO')
    ]
    public function edit(Biens $biens, Request $request, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(BiensType::class, $biens);
        $form->handleRequest($request);
        $manager = $this->doctrine->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            // Upload de photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $directory = $this->getParameter('repertoire_images_biens');
                $nouveauNom = $fileUploader->upload($photoFile, $directory);
                $biens->setPhoto($nouveauNom);
            }
            $manager->persist($biens);
            $manager->flush();
            $this->addFlash("success",$biens->getNom()." a été modifié avec succès");
            return $this->redirectToRoute("biens");
        }
        return $this->render('biens/edit.html.twig', [
            'biens' => $biens,
            'form' => $form->createView()
        ]);
    }

    #[
        Route('/delete/{id}', name: 'biens.delete'),
        IsGranted('ROLE_PROPRIO')
    ]
    public function delete(Biens $biens, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$biens->getId(), $request->get('_token'))) {
            $nom = $biens->getNom();
            $manager = $this->doctrine->getManager();
            $manager->remove($biens);
            $manager->flush();
            $this->addFlash("success",$nom." a été supprimé avec succès");
        }
        return $this->redirectToRoute("biens");
    }
    #[
        Route('/fav/{id}/{user}', name: 'biens.fav'),
        IsGranted('ROLE_USER')
    ]
    public function favori(Biens $biens, Utilisateur $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('fav'.$biens->getId(), $request->get('_token'))) {
            $manager = $this->doctrine->getManager();
            $favori = new Favoris();
            $favori->setBiens($biens)->setUtilisateur($user);

            // $biens->addFavori($favori);
            $manager->persist($favori);
            $manager->flush();
        }
        return $this->redirectToRoute("biens.detail", [
            'id' => $biens->getId()
        ]);
    }
}
