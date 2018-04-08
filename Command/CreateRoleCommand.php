<?php declare(strict_types=1);

namespace TM\RbacBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TM\RbacBundle\Model\PermissionInterface;
use TM\RbacBundle\Repository\RepositoryProvider;

class CreateRoleCommand extends Command
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
            ->setName('tm:rbac:create-role')
            ->setDescription('Create a TM Rbac role')
            ->setDefinition([
                new InputArgument(
                    'name',
                    InputArgument::REQUIRED,
                    'The role name'
                ),
                new InputArgument(
                    'permissions',
                    InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                    'The permissions'
                ),
                new InputOption(
                    'read-only',
                    null,
                    InputOption::VALUE_NONE,
                    'Set the role as read-only'
                ),
            ])
            ->setHelp(<<<EOT
The <info>tm:rbac:create-role</info> command creates a role:

  <info>php %command.full_name% administrator</info>

This interactive shell will first ask you for a list of permissions.

You can alternatively specify the permissions as a second argument delimited by spaces:

  <info>php %command.full_name% administrator permission_1 permission_2 permission_3</info>

You can create an read-only role (can only be deleted using the CLI with --force options):

  <info>php %command.full_name% administrator --read-only</info>
  
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

        $name = $input->getArgument('name');
        $readOnly = (bool) $input->getOption('read-only');
        $permissions = explode(',', $input->getArgument('permissions'));
        $permissions = array_map('trim', $permissions);

        if (null !== $role = $roleRepository->findOneByName($name)) {
            throw new \Exception(sprintf(
                'Role with name "%s" is already used',
                $name
            ));
        }

        $role = $roleRepository->createNew();
        $role->setName($name);
        $role->setReadOnly($readOnly);

        foreach ($permissions as $id) {
            /** @var PermissionInterface $permission */
            if (null === $permission = $permissionRepository->find($id)) {
                throw new \Exception(sprintf(
                    'Permission "%s" does not exist',
                    $id
                ));
            }

            $role->addPermission($permission);
        }

        $roleRepository->save($role);

        $output->writeln(sprintf(
            'Role <comment>%s</comment> created with permissions <comment>%s</comment>',
            $name,
            implode('", "', $permissions)
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questions = [];

        if (!$input->getArgument('name')) {
            $roleRepository = $this->repositoryProvider->getRoleRepository();

            $question = new Question('Enter a role name: ');
            $question->setValidator(function ($name) use ($roleRepository) {
                if (empty($name)) {
                    throw new \Exception('Name can not be empty');
                }

                if (null !== $role = $roleRepository->findOneByName($name)) {
                    throw new \Exception(sprintf(
                        'Role with name "%s" is already used',
                        $name
                    ));
                }

                return $name;
            });

            $questions['name'] = $question;
        }

        if (!$input->getArgument('permissions')) {
            $permissions = $this->repositoryProvider->getPermissionRepository()->getAllPermissionsKeys();

            natcasesort($permissions);

            $question = new ChoiceQuestion(
                'Select permissions to add to role: ',
                $permissions
            );
            $question->setErrorMessage('Permission "%s" is not valid');
            $question->setMultiselect(true);

            $questions['permissions'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);

            if (is_array($answer)) {
                $answer = implode(',', $answer);
            }

            $input->setArgument($name, $answer);
        }
    }
}
