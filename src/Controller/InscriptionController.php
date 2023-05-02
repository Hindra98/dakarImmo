<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use App\Form\UtilisateursType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route ('/inscription')]
class InscriptionController extends AbstractController
{
  #[Route('/', name: 'app_inscription')]
  public function addUser(ManagerRegistry $doctrine, Request $request): Response
  {
    $user = new Utilisateurs();
    $form = $this->createForm(UtilisateursType::class, $user);
    $form->remove('date_creation');
    $form->handleRequest($request);
    if ($form->isSubmitted()) {
      $manager = $doctrine->getManager();
      $manager->persist($user);
      $manager->flush();

      $this->addFlash('success', $user->getNom()."a été ajouté avec succès");

      return $this->redirectToRoute("/connexion");
    } else 

    return $this->render('inscription/index.html.twig', [
      'form' => $form->createView()
    ]);
  }
    #[Route('/add', name: 'inscription.add')]
    public function index(): Response
    {
        return $this->render('inscription/detail.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }
}
