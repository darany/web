-- Ce fichier est un exemple de transaction SQL pour cloturer une rencontre
-- Il est à adapter en fonction de la base de données utilisée
-- Vous devez exécuter ce fichier dans un client SQL (phpMyAdmin, etc.)
-- ou via la ligne de commande (mysql, psql, etc.)
-- Il est conseillé de tester la transaction sur une copie de la base de données
-- avant de l'exécuter sur la base de données de production
--
-- Mode d'emploi :
-- 1. Remplacer les valeurs entre crochets par les valeurs correspondantes
-- 2. Exécuter le fichier dans un client SQL ou via la ligne de commande
-- 3. Vérifier que la transaction s'est bien déroulée
-- 
-- Exemple d'utilisation :
-- mysql -u root -p < cloture-rencontre.sql
--
START TRANSACTION;

-- ATTENTION : veuillez modifier cette valeur
SET @id_match = [id_match];

-- Clôturer match le match en basculant son statut à Terminé (2) et en mettant à jour la date de fin (maintenant)
UPDATE rencontre SET statut = 2, heure_fin = NOW() WHERE id = @id_match;

-- Déterminer l'équipe gagante
SELECT @equipe_gagnante_id := CASE
                                WHEN score_equipe_a > score_equipe_b THEN r.equipe_a_id
                                WHEN score_equipe_a < score_equipe_b THEN r.equipe_b_id
                                WHEN score_equipe_a = score_equipe_b THEN 0  -- aucun gain pour un match nul
                            END
                            FROM rencontre r
                            WHERE r.id = @id_match;

SELECT @cote_gagnante := CASE
                            WHEN score_equipe_a > score_equipe_b THEN cote_equipe_a
                            WHEN score_equipe_a < score_equipe_b THEN cote_equipe_b
                            WHEN score_equipe_a = score_equipe_b THEN -1
                        END
                        FROM rencontre r
                        WHERE r.id = @id_match;

-- Calculer les gains des paris en fonction de la mise et de la cote des équipes
UPDATE pari 
SET gain = CASE
                WHEN equipe_id = @equipe_gagnante_id THEN ROUND(mise * @cote_gagnante, 2)
                ELSE - mise
            END
WHERE rencontre_id = @id_match;

-- Valider la transaction
COMMIT;
