Lodel 
=====

<img src="https://github.com/OpenEdition/lodel/blob/master/share/images/lodel_couleur.png" width="100">

Logiciel d'édition Électronique

Home page (doc générale): http://www.lodel.org

Documentation technique: https://github.com/OpenEdition/lodel/wiki

English readers: for a presentation in English, see https://lodel.org/666 . The rest of the documentation and the wiki are currently only in French. If you are interested in Lodel, [please contact us](lodel@lodel.org) !  
    
E-Mail: lodel@lodel.org

Résumé
-------

Lodel est un logiciel d'édition électronique. Il permet de publier en ligne des articles issus d'un traitement de texte.


Licence
-------

Lodel est un logiciel libre sous licence GPL Version 2. Lisez la licence dans [le fichier COPYING](https://github.com/OpenEdition/lodel/blob/master/COPYING).


Lodel - Logiciel d'édition Électronique
----------------------------------------

Lodel est un logiciel d'édition électronique. Il est simple d'utilisation et
facile à adapter à des usages particuliers.

Les documents à publier peuvent être préparés dans un traitement de texte (Word,
OpenOffice.org, etc) ou édités directement en ligne.

Le design du site est défini par des gabarits écrits dans le langage Lodelscript.

Installation
------------

Notez qu'une version pré-installée de Lodel (et OTX, l’application de conversion Word/Office vers XML/TEI) en tant qu’image de machine virtuelle linux Debian est téléchargeable à l’adresse : http://lodel.org/downloads/vms/2017/


Pré-requis:
  - Serveur HTTP (nginx, apache) avec PHP
  - Serveur MySQL/MariaDb
    - pour être utilisé avec OTX, il faut une valeur de max_allowed_packet et key_buffer très grande (16 M)


Marche à suivre:
  - Cloner de préférence la dernière version tagguée
  - Faire pointer le virtual host sur la racine de l'installation lodel.
  - L'utilisateur du serveur HTTP doit avoir les droits de lecture sur tous les fichiers.
  - Créer une base de donnée et un utilisateur ayant les droits de modification sur cette base.
  - Aller à l'adresse configurée avec un navigateur web, suivre les instructions.
  - Il faudra donner temporairement les droits d'écriture sur le dossier d'une instance de site.
  - Vérifer qu'à l'intérieur du dossier d'un site l'utilisateur du serveur HTTP a bien les droits d'écriture sur les dossiers:
      upload, docannexe, docannexe/file, docannexe/image, lodel/sources, lodel/icons
      
Informations complémentaires :
  - la requête exacte pour donner les droits à l'utilisateur de la base de 
donnée est "GRANT ALL ON `lodeldbname%`.* TO admin", contrairement à ce qui est
indiqué via l'interface graphique durant l'installation (où `lodeldbname` est le nom spécifié lors de l'installation) ;
      
      

### Avec Docker (pour développement / tests) ###

Lodel est disponible en version docker (Nginx + Mysql + PHP-FPM). Le code 
source lui n'est pas disponible dans un conteneur mais est partagé entre la machine
d'accueil et le conteneur de PHP-FPM (ceci étant automatiquement effectué lors de la
composition qui suit).

L'installation de Lodel via des conteneurs Docker présuppose que ce dernier soit 
déjà installé. Voir [l'aide à l'installation officiel](https://docs.docker.com/engine/installation/)
si ce n'est pas le cas.

Une fois le répositoire cloné, placez-vous à la raçine du code source en ligne de 
commande, puis effectuez :
  - modifier le valeurs "environment" du fichier compose.yml afin (notamment le
    LOCAL_USER_ID qui est votre UID d'utilisateur Linux) ;
  - docker-compose up --build ;
  - lors de l'initialisation de la base de donnée, l'adresse IP du conteneur est 172.30.0.30 ; 
  
  Une fois l'opération terminé, ouvrez votre navigateur et rendez vous à l'adresse 
  suivant : http://localhost:9009 . L'interface d'installation de Lodel devrait alors
  apparaître !
  
Remarque, les adresses IP des différents conteneurs sont les suivants (tel que
configurées dans les fichier "docker-compose.yml") :
  - NginX > 172.30.0.10
  - PHP-FPM > 172.30.0.20
  - MySQL > 172.30.0.30 
