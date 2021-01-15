<?php declare(strict_types=1);

namespace TM\RbacBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Model\UserInterface;
use TM\RbacBundle\Repository\RepositoryProvider;

/**
 * Command for removing a role from a specific user
 *
 * @package TM\RbacBundle\Command
 */
class RemoveUserRoleCommand extends Command
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

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tm:rbac:remove-user-role')
            ->setDescription('Remove a TM Rbac role to a user')
             ->setDefinition([
                new InputArgument(
                    'username',
                    InputArgument::REQUIRED,
                    'The username'
                ),
                new InputArgument(
                    'role',
                    InputArgument::REQUIRED,
                    'The role'
                ),
            ])
           ->setHelp(<<<'EOT'
The <info>tm:rbac:remove-user-role </info> command removes a role from a user:

  <info>php %command.full_name% michael</info>
  
This interactive shell will ask you for a role name.

You can alternatively specify the role as the second argument:

  <info>php %command.full_name% michael administrator</info>

EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userRepository = $this->repositoryProvider->getUserRepository();

        $username = $input->getArgument('username');
        $roleName = $input->getArgument('role');
        $role = false;

        /** @var UserInterface $user */
        if (null === $user = $userRepository->findOneBy(['username' => $username])) {
            throw new \Exception(sprintf(
                'User with username "%s" can not be found',
                $username
            ));
        }

        foreach ($user->getUserRoles() as $userRole) {
            if ($userRole->getName() === $roleName) {
                $role = $userRole;

                break;
            }
        }

        if (!$role) {
            $output->writeln(sprintf(
                '<error>User "%s" does not have role "%s"</error>',
                $username,
                $roleName
            ));

            exit;
        }

        $user->removeUserRole($role);
        $userRepository->save($user);

        $output->writeln(sprintf(
            'Removed role <comment>%s</comment> from <comment>%s</comment>',
            $roleName,
            $username
        ));

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $userRepository = $this->repositoryProvider->getUserRepository();

        if (!$input->getArgument('username')) {
            $question = new Question('Enter a username: ');
            $question->setValidator(function ($username) use ($userRepository) {
                if (empty($username)) {
                    throw new \Exception('User search can not be empty');
                }

                if (null === $userRepository->findOneBy(['username' => $username])) {
                    throw new \Exception(sprintf(
                        'User with username "%s" can not be found',
                        $username
                    ));
                }

                return $username;
            });

            $username = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('username', $username);
        }

        if (!$input->getArgument('role')) {
            /** @var UserInterface $user */
            if (null === $user = $userRepository->findOneBy(['username' => $input->getArgument('username')])) {
                throw new \Exception(sprintf(
                    'User with username "%s" can not be found',
                    $input->getArgument('username')
                ));
            }

            if ($user->getUserRoles()->isEmpty()) {
                throw new \Exception(sprintf(
                    '"%s" has no user roles to remove',
                    $user->getUsername()
                ));
            }

            $question = new ChoiceQuestion(
                sprintf('Select a ' . 'role to remove from "%s": ', $user->getUsername()),
                array_keys($this->getRemovableRoles($user))
            );
            $question->setErrorMessage('Role "%s" is not valid');

            $role = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('role', $role);
        }
    }

    /**
     * @param UserInterface $user
     * @return array|RoleInterface[]
     */
    private function getRemovableRoles(UserInterface $user)
    {
        $removableRoles = array_map(function(RoleInterface $role) {
            return $role->getName();
        }, $user->getUserRoles()->toArray());

        ksort($removableRoles);

        return array_combine($removableRoles, $removableRoles);
    }
}
