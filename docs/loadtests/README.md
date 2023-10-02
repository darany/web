# Test de charge de l'application superbowl

## But du test

L'application doit rester performante (c.-à-d. que les pages doivent s'afficher en moins d'une seconde avec une charge de 10 utilisateurs simultanés) avec les scénarios suivants:
 - utilisateurs utilisant l'application mobile pour consulter les matches (avec un pooling de 30s pour afficher les scores du match en pseudo-temps réel).
 - visiteurs faisant un parcours simple sur le site Internet.

## Usage

Ce test de charge utilise [locust](https://locust.io/) pour mesurer la latence des pages, il peut être lancé avec une interface web (l'adresse web est alors fournie par le terminal) :

    $ locust

Ou en ligne de commande (ici on demande le lancement de 10 utilisateurs simultntés et on limite à 360s la durée totale du test):

     locust -f locust_file.py --headless -u 10 -r 5 --run-time 360