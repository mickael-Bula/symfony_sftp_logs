<?php

namespace App\Command;

use Exception;
use phpseclib3\Net\SFTP;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SftpDownloadCommand extends Command
{
    protected static $defaultName = 'sftp:download';
    protected static $defaultDescription = 'Téléchargement SFTP et envoi de mail en cas d\'échec';

    /** @var LoggerInterface */
    private $logger;

    /**@var MailerInterface */
    private $mailer;

    public function __construct(LoggerInterface $sftpLogger, MailerInterface $mailer)
    {
        $this->logger = $sftpLogger;
        $this->mailer = $mailer;

        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sftp = new SFTP('192.168.1.93', 2222);
        $sftp_login = $sftp->login('tester', 'password');

        if (!$sftp_login) {
            $this->logger->error('Impossible de se connecter au serveur sftp !');
            $output->writeln(print('Erreur de connexion' . PHP_EOL));

            return Command::FAILURE;
        }
        // se déplace dans un répertoire distant
        $sftp->chdir('download');

        // télécharge un fichier et spécifie le chemin où il sera déposé
        try {
            $sftp->get('test2.txt', './var/log/local_file.txt');
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de la récupération d'un fichier : {message}", ['message' => $e->getMessage()]);
            print($e->getMessage());
        }
        $output->writeln(print('Commande exécutée' . PHP_EOL));

        $this->sendEmail($this->mailer);

        return Command::SUCCESS;
    }

    /**
     * @param MailerInterface $mailer
     * @return void
     * @throws TransportExceptionInterface
     */
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            ->subject("mail de l'application Symfony SFTP logs")
            ->text("Le fichier a bien été téléchargé")
            ->html("<p>Reste à voir les styles disponibles pour l'envoi de mail.</p>");

        // envoi d'un mail
        $mailer->send($email);
        $this->logger->info("Mail envoyé");
    }
}
