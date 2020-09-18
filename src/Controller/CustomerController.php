<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Customer;
use App\Form\UserRegisterFormType;
use App\Form\CustomerWithUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CustomerController extends AbstractController
{
    /**
     * @Route("/enregistrement_entreprise", name="customer_register",  methods={"GET", "POST"})
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {

        $this->denyAccessUnlessGranted("ROLE_CUSTOMER_ADMIN");

        $user = new User();
        $customer = new Customer();
        $form = $this->createForm(CustomerWithUserFormType::class, ['customer' => $customer, 'user' => $user]);

        $form->handleRequest($request);
        // prise en compte du formulaire
        if($form->isSubmitted() && $form->isValid()) {

            // $user->setPassword($encoder->encodePassword($user, \uniqid('password_bidon')));
            $user->setPassword($encoder->encodePassword($user, "password_bidon"));
            $user->setRoles(['ROLE_USER','ROLE_CUSTOMER_ADMIN', 'ROLE_CUSTOMER']);
            $user->setCreatedAt(new \DateTime());
            $customer->setCreatedAt(new \DateTime());
            $customer->addUser($user);

            $manager->persist($customer); 
            $manager->flush(); 
            $this->addFlash('success', 'Le compte client a bien été créé !');
            return $this->redirectToRoute('home');
        }

        return $this->render('customer/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/enregistrement_sous_compte_entreprise", name="customer_sub_register",  methods={"GET", "POST"})
     */
    public function subRegister(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserRegisterFormType::class, $user, ['type_register' => 'sub_account']);
        $form->handleRequest($request);
        
        // prise en compte du formulaire
        if($form->isSubmitted() && $form->isValid()) {
 
            $user->setCustomer($this->getUser()->getCustomer());
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            $user->setCreatedAt(new \DateTime());
   
            $manager->persist($user); 
            $manager->flush(); 
            $this->addFlash('success', 'Le sous-compte utilisateur a bien été créé !');
            return $this->redirectToRoute('home');
        }
 
        return $this->render('user/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}