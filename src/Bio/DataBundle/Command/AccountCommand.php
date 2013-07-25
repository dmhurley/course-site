<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\User\UserInterface;
use Bio\UserBundle\Entity\User;
use Bio\DataBundle\Objects\Database;

class AccountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:create:account')
            ->setDescription('Creates an account')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username?'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password?'
            )
            ->addArgument(
                'role',
                InputArgument::OPTIONAL,
                'Role?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');
        if (!$role) {
            $role = 'ROLE_ADMIN';
        }

        $db = new Database($this->getContainer(), 'BioUserBundle:User');

        $factory = $this->getContainer()->get('security.encoder_factory');
        $user = new User();

        $encoder = $factory->getEncoder($user);
        $pwd = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($pwd);
        $user->setUsername($username);
        $user->setRoles(array($role));

        $db->add($user);
        $db->close();
    }
}