<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\Proprietaires;
use App\Form\MessageType;
use App\Repository\MessagesRepository;
use PhpParser\Node\Expr\New_;
use SevenGps\PayUnit;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use App\Service\FileUploader;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private UtilisateurRepository $users;
    private ManagerRegistry $doctrine;
    public function __construct(UtilisateurRepository $users, ManagerRegistry $doctrine) {
    $this->users = $users;
    $this->doctrine = $doctrine;
}
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash("warning"," Deconnectez vous avant d'essayer de vous reconnecter'");
            return $this->redirectToRoute('index');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request, FileUploader $fileUploader, UserPasswordHasherInterface $hasher): Response
    {
        if ($this->getUser()) {
            $this->addFlash("warning"," Deconnectez vous avant de pouvoir créer un compte");
            return $this->redirectToRoute('index');
        }

        $user = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->remove('create_at');
        $form->remove('update_at');
        $form->remove('roles');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {// Upload de photo
            $photoFile = $form->get('profil')->getData();
            if ($photoFile) {
                $directory = $this->getParameter('repertoire_profil');
                $nouveauNom = $fileUploader->upload($photoFile, $directory);
                $user->setProfil($nouveauNom);
            }
            $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
            $user->setCreateAt();
            $user->setUpdateAt();
            // dd($user);
            $manager = $this->doctrine->getManager();
            $manager->persist($user);
            $manager->flush();
            $this->addFlash("success",$user->getEmail()." a été ajouté avec succès");
            return $this->redirectToRoute("app_login");
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[
        Route('/profil/compte/{id}', name: 'app_edit'),
        IsGranted('ROLE_USER')
    ]
    public function edit(Utilisateur $user, Request $request, FileUploader $fileUploader, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->remove('create_at');
        $form->remove('update_at');
        $form->remove('roles');
        $form->handleRequest($request);
        $manager = $this->doctrine->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            // Upload de photo
            $photoFile = $form->get('profil')->getData();
            if ($photoFile) {
                $directory = $this->getParameter('repertoire_profil');
                $nouveauNom = $fileUploader->upload($photoFile, $directory);
                $user->setProfil($nouveauNom);
            }
            if ($form->get('password')->getData() == "")
                $user->setPassword($user->getPassword());
            else
                $user->setPassword($hasher->hashPassword($user, $form->get('password')->getData()));
            $manager->persist($user);
            $manager->flush();
            $this->addFlash("success",$user->getEmail()." a été modifié avec succès");
            return $this->redirectToRoute("index");
        }
        return $this->render('security/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
    #[
        Route('/profil/message/{id}/', name: 'app_message'),
        IsGranted('ROLE_USER')
    ]
    public function message(Utilisateur $user, Request $request): Response
    {
        $repo = New UtilisateurRepository($this->doctrine);
        $users = $repo->findUser($user->getId());

        if (!$users) {
            return $this->render('security/message.html.twig', [
                'user' => $user,
                'retour' => 'Aucun message pour l\'instant'
            ]);
        }
        return $this->render('security/message.html.twig', [
            'user' => $user,
            'users' => $users
        ]);
    }
    #[
        Route('/profil/message/{id}/{receive?0}', name: 'app_message.send'),
        IsGranted('ROLE_USER')
    ]
    public function send(Utilisateur $user, Utilisateur $receive, Request $request): Response
    {
        $repo = New MessagesRepository($this->doctrine);
        $message = new Messages();
        $mess = $request->get('mess');
        if ($mess != "") {
            $manager = $this->doctrine->getManager();
            $message->setContenu($mess)->setCreateAt()->setUtilisateurSend($user)->setUtilisateurReceive($receive);
            $manager->persist($message);
            $manager->flush();
        }

        $msg = $repo->findMessage($user->getId(), $receive);
        if (!$msg) {
            return $this->render('security/message.html.twig', [
                'user' => $user,
                'receive' => $receive,
                'retour' => 'Aucun message pour l\'instant'
            ]);
        }
        return $this->render('security/message.html.twig', [
            'user' => $user,
            'receive' => $receive,
            'message' => $msg
        ]);
    }
    #[
        Route('/profil/compte/pay/{id}', name: 'app_payement'),
        IsGranted('ROLE_USER')
    ]
    public function payement(Utilisateur $user, Request $request): Response
    {
        if ($this->isCsrfTokenValid('pay'.$user->getId(), $request->get('_token'))) {
            $nom = $user->getNom();
            $payement = [
                'title' => 'Abonnement Franck Immo',
                'subtitle' => 'Devenez proprietaire sur la plateforme et vendez/louer vos biens comme bon vous semble',
                'description' => 'Devenez proprietaire sur la plateforme et vendez/louer vos biens comme bon vous semble',
                'amount' => '20.00'
            ];
            $clientId = 'AVgLbBvJdNa7HI2ZywRuyoGkenqzGvBRhfGPIAO74CW7IgjjTOBUBjPrYJKh8kBkRdF9ck26-f9gzkTy';
            $order = json_encode([
                'purchase_units' => [
                    [
                        'title' => $payement['title'],
                        'description' => $payement['description'],
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => number_format($payement['amount'], 2, '.', ""),
                        ],
                    ],
                ],
            ]);return <<<HTML
        <script src="https://www.paypal.com/sdk/js?client-id={$clientId}&currency=EUR&intent=authorize"></script>
        <div id="paypal-button-container"></div>
        <script>
          paypal.Buttons({
            createOrder: (data, actions) => {
              return actions.order.create({$order});
            },
            onApprove: async (data, actions) => {
              const authorization = await actions.order.authorize()
              const authorizationId = authorization.purchase_units[0].payments.authorizations[0].id
              await fetch('/paypal.php', {
                method: 'post',
                headers: {
                  'content-type': 'application/json'
                },
                body: JSON.stringify({authorizationId})
              })
              alert('Paiement effectué')
              // Action a effectuer quand le payement reussit
              // Enregister l'utilisateur dans la bd
              // code de redirection redirection
              // window.location.href = "redirection"
            },
            onCancel: (data, actions) => {
              // Action a effectuer si l'utilisateur annule le payement
              // window.location.href = "redirection"
            },
            onError: (err) => {
              // Action a effectuer si une erreur survient lors du payement
              // window.location.href = "redirection"
            },
          }).render('#paypal-button-container');
        </script>
    HTML;
            /*
            $proprio = new Proprietaires();
            $proprio->setUtilisateur($user);
            $proprio->setTel($request->get('phone'));

            $manager = $this->doctrine->getManager();
            $user->setRoles(['ROLE_PROPRIO']);
            $manager->persist($proprio);
            $manager->flush();
            $this->addFlash("success",$nom." a été ajouté avec succes comme proprio");
            */
        }
        return new Response("
            <div id='paypal-button-container'></div>
        ");
    }

    #[
        Route(path: '/profil/pay/success/{id}', name: 'app_pay_success'),
        IsGranted('ROLE_USER')
    ]
    public function paySucces(Utilisateur $user, Request $request): Response
    {
        dd($request);
        $proprio = new Proprietaires();
        $proprio->setUtilisateur($user);
        $proprio->setTel($request->get('phone'));

        $manager = $this->doctrine->getManager();
        $user->setRoles(['ROLE_PROPRIO']);
        $manager->persist($proprio);
        $manager->flush();
        $this->addFlash("success",$nom." a été ajouté avec succes comme proprio");
        return $this->redirectToRoute("app_edit", [
            'id' => $user->getId()
        ]);

    }
    #[
        Route(path: '/logout', name: 'app_logout'),
        IsGranted('ROLE_USER')
    ]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
