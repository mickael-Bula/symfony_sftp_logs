# Transfert SFTP, logger et mailer

J'ajoute la gestion des logs à mon script de transfert SFTP.

Pour faciliter la chose, je suis parti d'une application Symfony et non plus d'un simple composant Symfony.
Ceci me permet de bénéficier du composant DependencyInjection déjà configuré.

Ne reste ensuite qu'à ajouter le Logger et à l'injecter dans le constructeur de ma commande.

```bash
$ symfony new symfony_sftp_logs
$ cd symfony_sftp_logs
$ composer require symfony/monolog-bundle
$ composer require --dev symfony/maker-bundle
$ php bin/console make:command SftpDownload
$ composer require phpseclib/phpseclib
```

## Configuration du logger

Dans le fichier `monolog.yaml`, j'ai ajouté un `channel` et un `handler` sftp.
Dans le constructeur, il me suffit alors d'injecter `LoggerInterface` en le nommant `sftpLogger` pour que le `handler` sftp soit appelé.

Dès lors, les logs seront dirigés vers le fichier `var/sftp.log`.

## Lancement du script

```bash
$ php bin/console sftp:download
```