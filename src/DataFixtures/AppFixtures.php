<?php

namespace App\DataFixtures;

use App\Entity\Commentaire;
use App\Entity\Equipe;
use App\Entity\Joueur;
use App\Entity\Rencontre;
use App\Entity\User;
use App\Entity\Pari;
use Faker;

use App\Service\GestionPari;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private GestionPari $gestionPari;

    public function __construct(UserPasswordHasherInterface $hasher, GestionPari $gestionPari)
    {
        $this->hasher = $hasher;
        $this->gestionPari = $gestionPari;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();   // Outil de génération de données aléatoires

        // Créer des utilisateurs
        $visiteurs = [];  // à remplir pour faire des paris
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $username = mb_strtolower(mb_substr($faker->firstName(), 0, 1) . $faker->lastName());
            $user->setEmail($username . '@example.org');
            $password = $this->hasher->hashPassword($user, $username);
            $user->setPassword($password);
            $manager->persist($user);
            array_push($visiteurs, $user);
        }

        // Créer les utilisateurs de référence
        $admin = new User();
        $admin->setEmail('admin@example.org');
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $commentateur = new User();
        $commentateur->setEmail('commentateur@example.org');
        $commentateur->setPassword($this->hasher->hashPassword($commentateur, 'commentateur'));
        $commentateur->setRoles(['ROLE_COMMENTATEUR']);
        $manager->persist($commentateur);

        $visiteur = new User();
        $visiteur->setEmail('visiteur@example.org');
        $visiteur->setPassword($this->hasher->hashPassword($visiteur, 'visiteur'));
        $manager->persist($visiteur);
        array_push($visiteurs, $visiteur);

        // Créer équipes et joueurs
        $nomsEquipe = ['Kansas City Chiefs', 'Los Angeles Rams', 'Tampa Bay Buccaneers',
            'Philadelphia Eagles', 'Denver Broncos', 'Seattle Seahawks', 'Baltimore Ravens', 
            'Green Bay Packers', 'New Orleans Saints', 'Pittsburgh Steelers', 'Indianapolis Colts',
            'Dallas Cowboys', 'San Francisco 49ers', 'Washington Redskins', 'Chicago Bears', 
            'Oakland Raiders', 'Miami Dolphins', 'Baltimore Colts', 'New York Jets', 'Cincinnati Bengals',
            'Atlanta Falcons', 'Carolina Panthers', 'Arizona Cardinals', 'Tennessee Titans', 
            'Buffalo Bills', 'Minnesota Vikings', 'New England Patriots', 'St. Louis Rams',
            'New York Giants', 'Los Angeles Raiders', 'San Diego Chargers', 'Old Grumpy Cats',
            'Fake Team A', 'Fake Team B', 'Fake Team C', 'Fake Team D', 'Fake Team E',
            'Fake Team F', 'Fake Team G', 'Fake Team H', 'Fake Team I', 'Fake Team J'];
        $equipes = [];  // à remplir pour faire des matches
        foreach ($nomsEquipe as $nomEquipe) {
            $equipe = new Equipe();
            $equipe->setNom($nomEquipe);
            $equipe->setPays("USA");
            $manager->persist($equipe);
            array_push($equipes, $equipe);
            // Chaque équipe peut aligner sur la feuille de match jusqu'à 45 joueurs.
            // Créer un tableau de 99 valeurs parmi lesquelles nous choisirons un numéro de 
            // maillot unique et aléatoire pour chaque joueur
            $numeros = range(1, 99);
            shuffle($numeros);
            for ($ii=0; $ii<45; $ii++) {
                $joueur = new Joueur();
                $joueur->setNom($faker->lastName());
                $joueur->setPrenom($faker->firstNameMale());
                $joueur->setNumero(array_pop($numeros));
                $joueur->setEquipe($equipe);
                $manager->persist($joueur);
            }
        }
        
        // Créer des rencontres et éventuellement des commentaires
        shuffle($equipes);
        $rencontres = [];  // à remplir pour faire des paris
        for ($ii=0; $ii<21; $ii++) {
            $rencontre = new Rencontre();
            $rencontre->setEquipeA(array_pop($equipes));
            $rencontre->setEquipeB(array_pop($equipes));
            // Une date entre la semaine dernière et dans un mois
            $dateRencontre = $faker->dateTimeBetween('-1 week', '+1 month');
            // Avoir des rencontres aujourd'hui (à des fins de test IHM)
            if (str_contains($rencontre->getEquipeA()->getNom(), 'Fake') ||
                str_contains($rencontre->getEquipeB()->getNom(), 'Fake')) {
                $dateRencontre = new \DateTime();
            }
            $dateRencontre->setTime($faker->numberBetween(13, 20), 0);
            $rencontre->setHeureDebut($dateRencontre);
            $heureFin = clone $dateRencontre;
            $rencontre->setHeureFin($heureFin->add(new \DateInterval('PT1H')));
            // Forcer le statut du match sans réelle logique temporelle afin d'avoir un jeu d'essai complet
            $rencontre->setStatut($faker->randomElement([
                Rencontre::STATUT_A_VENIR,
                Rencontre::STATUT_EN_COURS,
                Rencontre::STATUT_TERMINE
            ]));
            $rencontre->setMeteo($faker->randomElement([
                "Ensoleillé", "Couvert", "Nuages épars", "Pluies rares", "Pluvieux", "Orageux", "Neige"
            ]));
            $rencontre->setCoteEquipeA($faker->randomFloat(2, 1, 10));
            $rencontre->setCoteEquipeB($faker->randomFloat(2, 1, 10));
            $rencontre->setScoreEquipeA($faker->numberBetween(0, 80));
            $rencontre->setScoreEquipeB($faker->numberBetween(0, 80));
            $manager->persist($rencontre);
            array_push($rencontres, $rencontre);
            // Ajouter des commentaires si le match est en cours ou terminé
            if ($rencontre->getStatut() == Rencontre::STATUT_EN_COURS || $rencontre->getStatut() == Rencontre::STATUT_TERMINE) {
                $nbCommentaires = $faker->numberBetween(10, 30);
                $dateCommentaire = clone $dateRencontre;
                for ($kk=0; $kk<$nbCommentaires; $kk++) {
                    $commentaire = new Commentaire();
                    $dateCommentaire = clone $dateCommentaire->add(new \DateInterval('PT1M'));
                    $commentaire->setDateHeure($dateCommentaire);
                    $commentaire->setTexte($faker->paragraph());
                    $commentaire->setCommentateur($commentateur);
                    $commentaire->setRencontre($rencontre);
                    $manager->persist($commentaire);
                }
            }

            // Ajouter des paris misés par les visiteurs. Calculer le gain
            $nbParis = $faker->numberBetween(1, 20);
            for ($jj=0; $jj<$nbParis; $jj++) {
                $pari = new Pari();
                $pari->setDate($faker->dateTimeBetween('-1 week', '+1 month'));
                $pari->setMise($faker->biasedNumberBetween(10, 2000, function($x) { return 1 - sqrt($x); }));
                $pari->setUser($faker->randomElement($visiteurs));
                $pari->setEquipe($faker->randomElement([$rencontre->getEquipeA(), $rencontre->getEquipeB()]));
                $pari->setRencontre($rencontre);
                $pari->setGain($this->gestionPari->calculerGain($pari));
                $manager->persist($pari);
            }

        }

        $manager->flush();
    }
}
