<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserService
{
    private EntityManagerInterface $em;

    /**
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    public function __construct(

        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ) {

        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function loginAsUser(UserInterface $user)
    {
        $this->tokenStorage->setToken(null);
        $this->session->clear();

        // Authenticating user
        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $this->session->set('_security_secured_area', serialize($token));

        $this->em->flush();

        return $user;
    }


}
