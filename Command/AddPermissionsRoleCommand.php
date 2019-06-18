<?php 

declare(strict_types=1);

namespace TM\RbacBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Model\RoleInterface;
use TM\RbacBundle\Repository\RepositoryProvider;

class AddPermissionsRoleCommand extends Command
{
    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * AddPermissionsRoleCommand constructor.
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
            ->setName('tm:rbac:add-role-permission')
            ->setDescription('Add a permission to a TM Rbac role')
            ->setDefinition([
                new InputArgument('role', InputArgument::REQUIRED, 'The role'),
                new InputArgument('permission', InputArgument::REQUIRED, 'The permission'),
            ])
            ->setHelp(<<<'EOT'
The <info>tm:rbac:add-role-permission </info> command adds a permission to a role:

  <info>php %command.full_name% juan</info>
  
Permission argument can be an array or a string. Must pass the permission id.  
  
This interactive shell will ask you for a role name.

You can alternatively specify the permission as the second argument:

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
        $roleRepository = $this->repositoryProvider->getRoleRepository();
        $permissionRepository = $this->repositoryProvider->getPermissionRepository();

        /** @var RoleInterface $role */
        if (null === $role = $roleRepository->findOneBy(['name' => $input->getArgument('role')])) {
            throw new \Exception(sprintf(
                'Role with name "%s" can not be found',
                $input->getArgument('role')
            ));
        }

        if (empty($this->getAvailablePermission($role))) {
            $output->writeln(sprintf(
                '"%s" already has all available user roles',
                $role->getName()
            ));

            return 0;
        }

        $permissions = $input->getArgument('permission');

        if (is_array($permissions)) {
            foreach ($permissions as $argument){
                /** @var PermissionInterface $permission */
                if (null === $permission = $permissionRepository->findOneBy(['id' => $argument])) {
                    throw new \Exception(sprintf(
                        'Permission with name "%s" can not be found',
                        $argument
                    ));
                }

                $role->addPermission($permission);
                $roleRepository->save($role);

                $output->writeln(sprintf(
                    'Added permission <comment>%s</comment> to <comment>%s</comment>',
                    $permission->getName(),
                    $role->getName()
                ));
            }
        } else {
            /** @var PermissionInterface $permission */
            if (null === $permission = $permissionRepository->findOneBy(['id' => $argument])) {
                throw new \Exception(sprintf(
                    'Permission with name "%s" can not be found',
                    $input->getArgument('permission')
                ));
            }

            $role->addPermission($permission);
            $roleRepository->save($role);

            $output->writeln(sprintf(
                'Added permission <comment>%s</comment> to <comment>%s</comment>',
                $permission->getName(),
                $role->getName()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $roleRepository = $this->repositoryProvider->getRoleRepository();

        if (!$input->getArgument('role')) {
            $question = new Question('Enter a role: ');
            $question->setValidator(function ($roleName) use ($roleRepository) {
                if(empty($roleName)) {
                    throw new \Exception('Role can not be empty');
                }

                if (null === $role = $roleRepository->findOneBy(['name' => $roleName])) {
                    throw new \Exception(sprintf(
                        'Role with name "%s" can not be found',
                        $roleName
                    ));
                }

                return $roleName;
            });

            $roleName = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('role', $roleName);
        }

        if(!$input->getArgument('permission')) {
            /** @var RoleInterface $role */
            if (null === $role = $roleRepository->findOneBy(['name' => $input->getArgument('role')])) {
                throw new \Exception(sprintf(
                    'Role with name "%s" can not be found',
                    $input->getArgument('role')
                ));
            }

            $availablePermissions = $this->getAvailablePermission($role);

            if (empty($availablePermissions)) {
                $output->writeln(sprintf(
                    '"%s" already has all available role permissions',
                    $role->getName()
                ));

                exit;
            }

            $question = new ChoiceQuestion(
                sprintf('Select a permission to add to "%s:', $role->getName()),
                array_keys($availablePermissions)
            );
            $question->setErrorMessage('Permission "$s" is not valid');
            $question->setMultiselect(true);

            $selectedPermission = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('permission', $selectedPermission);
        }
    }

    /**
     * @param RoleInterface $role
     * @return array|PermissionInterface[]
     */
    private function getAvailablePermission(RoleInterface $role)
    {
        $availablePermissions = array_map(function(PermissionInterface $permission) {
            return $permission->getName();
        }, $this->repositoryProvider->getPermissionRepository()->findAllPermissionsNotUsedByRole($role));

        ksort($availablePermissions);

        return array_combine($availablePermissions, $availablePermissions);
    }
}
