<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegisterFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/enregistrement", name="user_register",  methods={"GET", "POST"})
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {

        $user = new User();
        $form = $this->createForm(UserRegisterFormType::class, $user)->remove("roles");
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $form->get('password')->getData());
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTime());

            $manager->persist($user);
            $manager->flush();

            $this->addFlash("success", "Vous vous êtes bien inscrit");
            return $this->redirectToRoute('home');
        }

        return $this->render('user/index.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/tout_les_utilisateurs", name="show_all_users")
     * @Security("is_granted('ROLE_CUSTOMER_ADMIN')", 
     * statusCode=403, 
     * message="Vous n'avez pas les droits !")
     */
    public function showAll(UserRepository $userRepository, ?UserInterface $user)
    {
        return $this->render("user/showAll.html.twig", [
            "users" => $this->isGranted("ROLE_ADMIN") ? $userRepository->findAll() : $userRepository->findBy(["customer" => $user->getCustomer()])
        ]);
    }

    /**
     * @Route("/details_utilisateur/{id}", name="show_user")
     */
    public function showUser(User $user)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        return $this->render("user/show.html.twig", [
            "user" => $user
        ]);
    }

    /**
     * @Route("/modifier_utilisateur/{id}", name="modify_user")
     */
    public function modifyUser(User $user, EntityManagerInterface $manager, Request $request)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $form = $this->createForm(UserRegisterFormType::class, $user)->remove("password");
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($user);
            $manager->flush();

            $this->addFlash("success", "Vous vous bien modifié un utilisateur");
            return $this->redirectToRoute('show_all_users');
        }

        return $this->render("user/modify.html.twig", [
            "user" => $user,
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/supprime_utilisateur/{id}", name="delete_user")
     */
    public function deleteUser(User $user, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $manager->remove($user);
        $manager->flush();

        $this->addFlash("success", "Vous vous bien modifié un utilisateur");
        return $this->redirectToRoute('show_all_users');
    }
}