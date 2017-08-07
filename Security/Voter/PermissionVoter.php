<?php declare(strict_types=1);

namespace TM\RbacBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use TM\RbacBundle\Model\UserInterface;
use TM\RbacBundle\Repository\RepositoryProvider;
use TM\RbacBundle\TMPermissions;

class PermissionVoter extends Voter
{
    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * @param RepositoryProvider $repositoryProvider
     */
    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        $permissions = $this->repositoryProvider->getPermissionRepository()->getAllPermissionsKeys();

        return in_array($attribute, $permissions);
    }

    /**
     * {@inheritdoc}
     */
    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (null === $user = $this->getUserFromToken($token)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($token->getRoles() as $role) {
            if ($attribute === $role->getRole()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TokenInterface $token
     * @return UserInterface|null
     */
    private function getUserFromToken(TokenInterface $token) /* ?: UserInterface */
    {
        if (null === $user = $token->getUser()) {
            return null;
        }

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user;
    }
}