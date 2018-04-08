<?php declare(strict_types=1);

namespace TM\RbacBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use TM\RbacBundle\Repository\RepositoryProvider;

class DeleteRoleCommand extends Command
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
            ->setName('tm:rbac:delete-role')
            ->setDescription('Delete a TM Rbac role')
            ->setDefinition([
                new InputArgument(
                    'name',
                    InputArgument::REQUIRED,
                    'The role name'
                ),
                new InputOption(
                    'force',
                    null,
                    InputOption::VALUE_NONE,
                    'Force the system to delete a read-only role'
                ),
            ])
            ->setHelp(<<<'EOT'
The <info>tm:rbac:delete-role</info> command deletes a role:

  <info>php %command.full_name% administrator</info>

Read-Only roles can not be deleted by default but you can force them to be deleted:

  <info>php %command.full_name% administrator --force</info>
  

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

        $name = $input->getArgument('name');
        $force = $input->getOption('force');

        if (null === $role = $roleRepository->findOneByName($name)) {
            throw new \Exception(sprintf(
                'Role with name "%s" can not be found',
                $name
            ));
        }

        if ($role->isReadOnly() && !$force) {
            $output->writeln(sprintf(
                '"%s" is read only so can not be deleted and --force option has not been used',
                $role->getName()
            ));

            return 0;
        }

        $wasForced = $role->isReadOnly() && $force;

        $roleRepository->delete($role);

        $output->writeln(sprintf(
            'Deleted role <comment>%s</comment>%s',
            $role->getName(),
            $wasForced ? ' using <comment>force</comment> option' : ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('name')) {
            $roleRepository = $this->repositoryProvider->getRoleRepository();

            $question = new Question('Enter a role name: ');
            $question->setValidator(function ($name) use ($roleRepository) {
                if (empty($name)) {
                    throw new \Exception('Role name can not be empty');
                }

                if (null === $roleRepository->findOneByName($name)) {
                    throw new \Exception(sprintf(
                        'Role with name "%s" can not be found',
                        $name
                    ));
                }

                return $name;
            });

            $name = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument('name', $name);
        }
    }
}