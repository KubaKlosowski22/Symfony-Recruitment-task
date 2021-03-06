<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
Use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController

{
    //Userlist functions require update - sensitive user data needs to be hidden.

    /**
     * @Route("/api/userslist", name="users_list", methods={"GET"})
     */

    public function usersList(SerializerInterface $serializer):Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        if (!$users) {
            throw $this->createNotFoundException(
                'No users found'
            );
        }
        $jsonData = $serializer->serialize($users, 'json');

        return new JsonResponse($jsonData, Response::HTTP_OK);
    }

    /**
     * @Route("/api/userslist/{id}", name="users_list_by_id", methods={"GET"})
     */
    public function getUserById(SerializerInterface $serializer, $id):Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for this id'
            );
        }
        $jsonData = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonData, Response::HTTP_OK);
    }

    /**
     * @Route("/api/user/{id}", name="user_delete", methods={"DELETE"})
     */
    public function deleteUser(SerializerInterface $serialize, $id):Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $currentUser = $this->getUser();

        if ($user != $currentUser) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        } else {
            return new JsonResponse(Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(Response::HTTP_OK);
    }

    /**
     *
     * @Route("/api/user/{id}", name="email_update", methods={"PUT"})
     */
    public function updateEmail(SerializerInterface $serializer, Request $request, $id):Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager= $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $mail= $request->get('email');
        $user->setEmail($mail);

        $entityManager->flush();
        $serializer->serialize($user, 'json');

        return new JsonResponse($user, Response::HTTP_OK);
    }

    /**
     * @Route("/api/user/changepass/{id}", name="password_update", methods={"PUT"})
     */
    public function updatePassword(SerializerInterface $serializer, Request $request, UserPasswordEncoderInterface $encoder, $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $entityManager= $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        $pass= $request->get('password');
        $encoded = $encoder->encodePassword($user, $pass);
        $user->setPassword($encoded);

        $entityManager->flush();
        $serializer->serialize($user, 'json');

        return new JsonResponse($user, Response::HTTP_OK);
    }
}
