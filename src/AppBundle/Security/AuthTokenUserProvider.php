<?php


namespace AppBundle\Security;

use AppBundle\Repository\ClientRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use AppBundle\Repository\AuthTokenRepository;

class AuthTokenUserProvider implements UserProviderInterface
{
    protected $authTokenRepository;
    protected $clientRepository;

    public function __construct(AuthTokenRepository $authTokenRepository, ClientRepository $clientRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
        $this->clientRepository = $clientRepository;
    }

    public function getAuthToken($authTokenHeader)
    {
        return $this->authTokenRepository->findOneByValue($authTokenHeader);
    }

    public function loadUserByUsername($email)
    {
        return $this->clientRepository->findByLogin($email);
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return 'AppBundle\Entity\Client' === $class;
    }
}