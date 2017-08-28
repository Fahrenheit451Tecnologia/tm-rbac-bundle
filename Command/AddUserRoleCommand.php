<?php declare(strict_types=1);

namespace TM\RbacBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Model\UserInterface;
use TM\RbacBundle\Repository\RepositoryProvider;

class AddUserRoleCommand extends ContainerAwareCommand
{
    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tm:rbac:add-user-role')
            ->setDescription('Add a TM Rbac role to a user')
             ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('role', InputArgument::REQUIRED, 'The role'),
            ])
           ->setHelp(<<<'EOT'
The <info>tm:rbac:add-user-role </info> command adds a role to a user:

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
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->repositoryProvider = $this->getContainer()->get('tm_rbac.provider.repository');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userRepository = $this->repositoryProvider->getUserRepository();
        $roleRepository = $this->repositoryProvider->getRoleRepository();

        /** @var UserInterface $user */
        if (null === $user = $userRepository->findOneBy(['username' => $input->getArgument('username')])) {
            throw new \Exception(sprintf(
                'User with username "%s" can not be found',
                $input->getArgument('username')
            ));
        }

        if (empty($this->getAvailableRoles($user))) {
            $output->writeln(sprintf(
                '"%s" already has all available user roles',
                $user->getUsername()
            ));

            return 0;
        }

        /** @var RoleInterface $role */
        if (null === $role = $roleRepository->findOneBy(['name' => $input->getArgument('role')])) {
            throw new \Exception(sprintf(
                'Role with name "%s" can not be found',
                $input->getArgument('role')
            ));
        }

        $user->addUserRole($role);
        $userRepository->save($user);

        $output->writeln(sprintf(
            'Added role <comment>%s</comment> to <comment>%s</comment>',
            $role->getName(),
            $user->getUsername()
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $userRepository = $this->repositoryProvider->getUserRepository();

        if (!$input->getArgument('username')) {
            $question = new Question('Enter a username: ');
            $question->setValidator(function($username) use ($userRepository) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                if (null === $user = $userRepository->findOneBy(['username' => $username])) {
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

            $availableRoles = $this->getAvailableRoles($user);

            if (empty($availableRoles)) {
                $output->writeln(sprintf(
                    '"%s" already has all available user roles',
                    $user->getUsername()
                ));

                exit;
            }

            $question = new ChoiceQuestion(
                sprintf('Select a role to to add to "%s": ', $user->getUsername()),
                array_keys($availableRoles)
            );
            $question->setErrorMessage('Role "%s" is not valid');

            $selectedRole = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('role', $selectedRole);
        }
    }

    /**
     * @param UserInterface $user
     * @return array|RoleInterface[]
     */
    private function getAvailableRoles(UserInterface $user)
    {
        $availableRoles = array_map(function(RoleInterface $role) {
            return $role->getName();
        }, $this->repositoryProvider->getRoleRepository()->findAllRolesNotUsedByUser($user));

        ksort($availableRoles);

        return array_combine($availableRoles, $availableRoles);
    }
}