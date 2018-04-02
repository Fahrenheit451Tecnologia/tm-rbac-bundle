<?php declare(strict_types=1);

namespace TM\RbacBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Repository\RepositoryProvider;

class InitializePermissionsCommand extends Command
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
     * @param array $permissions
     */
    public function __construct(
        RepositoryProvider $repositoryProvider,
        array $permissions
    ) {
        $this->repositoryProvider = $repositoryProvider;
        $this->permissions = $permissions;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tm:rbac:initialize-permissions')
            ->setDescription('Initialize the TM Rbac permissions ** Required after any change in permissions **')
            ->setHelp(<<<EOT
The <info>tm:rbac:initialize-permissions</info> command initialize the permissions from the app/config/config.yml
file into the tm_rbac permission table
  
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
        $this->permissions = $this->getContainer()->getParameter('tm_rbac.permissions');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->purgeUnusedPermissions($output);
        $this->createNewPermissions($output);
    }

    /**
     * @param OutputInterface $output
     * @retun void
     */
    private function purgeUnusedPermissions(OutputInterface $output) /* : void */
    {
        $permissionsRepository = $this->repositoryProvider->getPermissionRepository();
        $currentPermissions = $permissionsRepository->findAll();

        /** @var PermissionInterface $permission */
        foreach ($currentPermissions as $permission) {
            if (array_key_exists($permission->getId(), $this->permissions)) {
                continue;
            }

            $id = $permission->getId();
            $name = $permission->getName();
            $permissionsRepository->delete($permission);


            $output->writeln(sprintf(
                'Permission <comment>%s</comment> (%s) was deleted',
                $name,
                $id
            ));
        }
    }

    /**
     * @param OutputInterface $output
     * @retun void
     */
    private function createNewPermissions(OutputInterface $output) /* : void */
    {
        $permissionsRepository = $this->repositoryProvider->getPermissionRepository();

        foreach ($this->permissions as $id => $name) {
            if ($permissionsRepository->find($id)) {
                continue;
            }

            $permission = $permissionsRepository->createNew($id, $name);
            $permissionsRepository->save($permission);

            $output->writeln(sprintf(
                'Permission <comment>%s</comment> (%s) was created',
                $name,
                $id
            ));
        }
    }
}
