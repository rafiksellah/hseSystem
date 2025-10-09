<?php

namespace App\Command;

use App\Entity\Emplacement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-emplacements',
    description: 'Initialise les emplacements de base dans la base de données par type d\'équipement',
)]
class InitEmplacementsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Emplacements de base par type d'équipement
        // Ces emplacements sont indépendants de la zone
        $emplacementsByType = [
            'extincteur' => [
                // ZONES_SIMTIS
                'Administration',
                'Broderie',
                'Bureaux d\'études',
                'Calandrage',
                'Châles et foulards BC',
                'Chaufferie',
                'Confection Decathlon',
                'Déchets extension',
                'Détortionneuses',
                'Diamantine confection habillement',
                'Emballage BC',
                'Fuel',
                'Grattage',
                'Gravure BC',
                'Groupe électrogène',
                'Impression numérique',
                'Livraison',
                'Préparation',
                'RAM',
                'Rotative',
                'Roulage',
                'SIMI',
                'SIMI - CANTINE',
                'Station lavage',
                'Stock Decathlon',
                'Stock PF',
                'Strass BC',
                'Teinture',
                'Panneaux solaires',
                // ZONES_SIMTIS_TISSAGE
                'Poste électrique',
                'Chaudière',
                'Compresseur tefil',
                'Rentrage',
                'Magasin tefil',
                'Tissage RDC',
                'Les machines jacard',
                'Contrôle qualité',
                'Machine cag',
                'Machine d\'air',
                'Ourdissoir',
                'Cantine homme et femme',
                'Déchets PRATO',
                'Produit fini',
                'Stock beilla',
                'Stock prato',
                'Magasin prato',
                'Stifa RDC',
                'Mezzanine',
                'Compresseur prato',
            ],
            'ram' => [
                'RAM 1',
                'RAM 2',
                'RAM 3',
                'RAM 4',
                'RAM 5',
                'RAM 6',
                'RAM 7',
                'RAM 8',
            ],
            'sirene' => [
                'En face montecharge N°2',
                'Montecharge N°2',
                'Au milieu',
                'CANTINE FEMME',
                'Au fond entre issue de secours et monte charge auxiliaire',
                'Au dessus de l\'issue de secours BED2',
                'Issue de secours T4',
                'Issue de secours T5',
                'A coté de l\'issue de secours T6',
                'Atelier mécanique',
                'Montecharge N°5',
                'En face chaine Serviette',
                'En face de table de lancement',
                'Issue de secours ST.D 02',
                'Au dessus de table de coupe',
                'Porte Montecharge N°5',
                'Porte Montecharge N°6',
                'ISSUE DE SECOURS ( cote escalier)',
                'Entre les machine sde rasage 1 et 2',
                'Au milieu de mur de séparation avec DIMANTINE',
                'A coté W.C',
                'SORTIE RAM SUR DALLE',
                'SORTIE RAM',
                'Au dessus machine biancalani',
                'RAM 3',
                'RAM 2 SUR DALLE',
                'A l\'entrée cuisine rotative',
                'AU DESSUS DES ADOUCISSEURS',
                'DMS1',
                'MACHINES TG',
                'MACHINE SCV',
                'CUISINE COLORANT',
                'A COTE LABO',
                'CANTINE HOMME',
                'Monte charge N°5',
                'En face porte sectionneur N°1',
                'Au milieu du stock',
                'A coté Magasin PDR',
                'A coté Monte charge auxiliaire',
                'MACHINE TEINTURE DE SOIE',
            ],
            'desenfumage' => [
                'LAVAGE A LA CONTINUE',
                'Entre Vaporisateur 1 & 2',
                'ROTATIVE',
            ],
            'monte_charge' => [
                'RDC TISSAGE-RENTRAGE-OURDISSOIR',
                'RDC TISSAGE-MEZZANINE-PRATO',
                'Bâtiment confection',
                'Teinture –préparation-broderie',
                '2eme étage emballage',
                'Zone décathlon',
                'Broderie-la stock PF',
                'Préparation-diamantine-broderie',
            ],
            'ria' => [
                'Bureau études',
                'Salle CAO',
                'Atelier broderie',
                'Zone machines',
                'Couloir principal',
                'Bureau 101',
                'Atelier chales',
                'Atelier foulards',
            ],
            'prise_pompier' => [
                'PORTE TRANSFERT ENTRE RAM ET SIMI 6 COTE RAM',
                'BUREAUX MAINTENANCE',
                'RAM 2 - ENTREE SOUS DALE',
                'EN FACE IMPRESSION ROTATIVE',
                'EN FACE MONTECHARGE N°1',
                'BUREAU LIVRAISON',
                'A COTE ZONE FIOUL',
                'ENTREE GRATTAGE',
                'ENTREE EMBALLAGE',
                'ENTREE BRODERIE',
                'ENTREE PREPARATION',
                'PORTE DE CHARGEMENT',
            ],
            'issue_secours' => [
                'G01',
                'G02',
                'CF.D 01',
                'CF.D 02',
                'ST.D 02',
                'ST.D 03',
                'BR 01',
                'BR 02',
                'PR02',
                'BE 01',
                'BE 02',
                'N01',
                'N02',
                'N03',
                'N04',
                'T01',
                'T02',
                'T03',
                'T04',
                'T05',
                'T06',
                'S01',
                'S02',
                'RAM 01',
                'RAM 02',
                'ST.D 01',
                'ST.D 04',
                'ST.PF 01',
                'ST.PF 02',
                'EMB 01',
                'EMB 02',
                'CF 01',
                'CF 02',
                'CP 01',
                'BR 03',
                'PR01',
                'ADM 01',
                'DIA 01',
            ],
        ];

        $total = 0;
        $added = 0;
        $skipped = 0;

        foreach ($emplacementsByType as $typeEquipement => $listeEmplacements) {
            foreach ($listeEmplacements as $nomEmplacement) {
                $total++;
                
                // Vérifier si l'emplacement existe déjà pour ce type
                $existing = $this->entityManager->getRepository(Emplacement::class)
                    ->findOneBy(['nom' => $nomEmplacement, 'typeEquipement' => $typeEquipement]);
                
                if ($existing) {
                    $skipped++;
                    $io->note("Déjà existant: $nomEmplacement (Type: $typeEquipement)");
                    continue;
                }
                
                // Créer le nouvel emplacement
                $emplacement = new Emplacement();
                $emplacement->setNom($nomEmplacement);
                $emplacement->setTypeEquipement($typeEquipement);
                $emplacement->setZone(null); // Zone nullable car non utilisée pour le filtrage
                
                $this->entityManager->persist($emplacement);
                $added++;
                $io->success("Ajouté: $nomEmplacement (Type: $typeEquipement)");
            }
        }
        
        if ($added > 0) {
            $this->entityManager->flush();
        }

        $io->title('Résumé de l\'initialisation');
        $io->table(
            ['Statistique', 'Nombre'],
            [
                ['Total traité', $total],
                ['Ajoutés', $added],
                ['Déjà existants', $skipped]
            ]
        );

        $io->success("Initialisation terminée avec succès !");

        return Command::SUCCESS;
    }
}
