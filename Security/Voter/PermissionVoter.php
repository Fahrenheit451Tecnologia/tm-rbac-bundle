<?php declare(strict_types=1);

namespace TM\RbacBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use TM\RbacBundle\Model\UserInterface;
use TM\RbacBundle\Repository\RepositoryProvider;

class PermissionVoter extends Voter
{
    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * @var array|string[]
     */
    private $permissions;

    /**
     * @param RepositoryProvider $repositoryProvider
     * @param array|string[] $permissions
     */
    public function __construct(RepositoryProvider $repositoryProvider, array $permissions)
    {
        $this->repositoryProvider = $repositoryProvider;
        $this->permissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attribute, $subject)
    {
        return array_key_exists($attribute, $this->permissions);
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