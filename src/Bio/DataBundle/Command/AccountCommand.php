<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Bio\UserBundle\Entity\User;
use Bio\DataBundle\Objects\Database;

class AccountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:create:account')
            ->setDescription('Creates an account')
            ->addOption(
                'username',
                null,
                InputArgument::OPTIONAL,
                'Username?'
            )
            ->addOption(
                'password',
                null,
                InputArgument::OPTIONAL,
                'Password?'
            )
            ->addOption(
                'role',
                null,
                InputArgument::OPTIONAL,
                'Email?'
            )
            ->addOption(
                'email',
                null,
                InputArgument::OPTIONAL,
                'Role?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!($username = $input->getOption('username'))) {
            $username = $dialog->ask(
                $output,
                'Username: ',
                null
            );
        }
        if (!($password = $input->getOption('password'))) {
            $password = $dialog->askHiddenResponse(
                    $output,
                    'Password: ',
                    false
                );
        }
        if (!($email = $input->getOption('email'))) {
            $email = $dialog->ask(
                    $output,
                    'Email: ',
                    false
                );
        }

        $roles = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_SETUP');
        if (!($role = $input->getOption('role'))) {
            $role = $roles[$dialog->select(
                    $output,
                    'Role: ',
                    $roles,
                    1
                )];
        }

        $db = new Database($this->getContainer(), 'BioUserBundle:User');

        $factory = $this->getContainer()->get('security.encoder_factory');
        $user = new User();

        $encoder = $factory->getEncoder($user);
        $pwd = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($pwd)
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles(array($role));

        $db->add($user);
        $db->close();
    }
}