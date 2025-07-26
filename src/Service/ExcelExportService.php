<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    public function exportRapportsHSE(array $rapports, string $title = 'Rapports HSE'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définir le titre de la feuille
        $sheet->setTitle('Rapports HSE');

        // En-têtes des colonnes
        $headers = [
            'A' => 'ID',
            'B' => 'Code Agent',
            'C' => 'Nom Complet',
            'D' => 'Date',
            'E' => 'Heure',
            'F' => 'Zone Travail',
            'G' => 'Zone Utilisateur',
            'H' => 'Emplacement',
            'I' => 'Équipement/Produit',
            'J' => 'Description Anomalie',
            'K' => 'Cause Probable',
            'L' => 'Photo Constat',
            'M' => 'Action Clôturée',
            'N' => 'Date Clôture',
            'O' => 'Heure Action',
            'P' => 'Photo Action',
            'Q' => 'Statut',
            'R' => 'Date Création',
            'S' => 'Créé Par'
        ];

        // Appliquer les en-têtes
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '1', $header);
        }

        // Style pour les en-têtes
        $headerRange = 'A1:S1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Remplir les données
        $row = 2;
        foreach ($rapports as $rapport) {
            $sheet->setCellValue('A' . $row, $rapport->getId());
            $sheet->setCellValue('B' . $row, $rapport->getCodeAgt());
            $sheet->setCellValue('C' . $row, $rapport->getNom());
            $sheet->setCellValue('D' . $row, $rapport->getDate() ? $rapport->getDate()->format('d/m/Y') : '');
            $sheet->setCellValue('E' . $row, $rapport->getHeure() ? $rapport->getHeure()->format('H:i') : '');
            $sheet->setCellValue('F' . $row, $rapport->getZone() ?? 'Non spécifiée');
            $sheet->setCellValue('G' . $row, $rapport->getZoneUtilisateur() ?? 'Non définie');
            $sheet->setCellValue('H' . $row, $rapport->getEmplacement() ?? 'Non spécifié');
            $sheet->setCellValue('I' . $row, $rapport->getEquipementProduitConcerne() ?? 'Non spécifié');
            $sheet->setCellValue('J' . $row, $rapport->getDescriptionAnomalie() ?? 'Non spécifiée');
            $sheet->setCellValue('K' . $row, $rapport->getCauseProbable() ?? 'Non spécifiée');
            $sheet->setCellValue('L' . $row, $rapport->getPhotoConstat() ? 'Oui' : 'Non');
            $sheet->setCellValue('M' . $row, $rapport->getActionCloturee() === 'oui' ? 'Oui' : 'Non');
            $sheet->setCellValue('N' . $row, $rapport->getDateCloture() ? $rapport->getDateCloture()->format('d/m/Y') : '');
            $sheet->setCellValue('O' . $row, $rapport->getHeureAction() ? $rapport->getHeureAction()->format('H:i') : '');
            $sheet->setCellValue('P' . $row, $rapport->getPhotoActionCloturee() ? 'Oui' : 'Non');
            $sheet->setCellValue('Q' . $row, $rapport->getStatut() ?? 'En cours');
            $sheet->setCellValue('R' . $row, $rapport->getDateCreation() ? $rapport->getDateCreation()->format('d/m/Y H:i') : '');
            $sheet->setCellValue('S' . $row, $rapport->getUser() ? $rapport->getUser()->getNom() . ' ' . $rapport->getUser()->getPrenom() : 'Utilisateur supprimé');

            // Coloration conditionnelle selon le statut
            $statutRange = 'Q' . $row;
            if ($rapport->getStatut() === 'Clôturé') {
                $sheet->getStyle($statutRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D4EDDA'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '155724'],
                        'bold' => true,
                    ],
                ]);
            } elseif ($rapport->getStatut() === 'En cours') {
                $sheet->getStyle($statutRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF3CD'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '856404'],
                        'bold' => true,
                    ],
                ]);
            }

            // Coloration selon la zone utilisateur
            $zoneRange = 'G' . $row;
            if ($rapport->getZoneUtilisateur() === 'SIMTIS') {
                $sheet->getStyle($zoneRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E3F2FD'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '0D47A1'],
                        'bold' => true,
                    ],
                ]);
            } elseif ($rapport->getZoneUtilisateur() === 'SIMTIS TISSAGE') {
                $sheet->getStyle($zoneRange)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3E5F5'],
                    ],
                    'font' => [
                        'color' => ['rgb' => '4A148C'],
                        'bold' => true,
                    ],
                ]);
            }

            $row++;
        }

        // Ajuster la largeur des colonnes
        foreach (range('A', 'S') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Appliquer des bordures à toutes les données
        $dataRange = 'A1:S' . ($row - 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Alternance des couleurs de ligne
        for ($i = 2; $i < $row; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':S' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Figer la première ligne
        $sheet->freezePane('A2');

        // Ajouter des filtres automatiques
        $sheet->setAutoFilter('A1:S' . ($row - 1));

        // Ajouter une feuille de statistiques si on a des données
        if (count($rapports) > 0) {
            $this->addStatisticsSheet($spreadsheet, $rapports);
        }

        // Créer la réponse en streaming
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $filename = 'rapports_hse_' . date('Y-m-d_H-i-s') . '.xlsx';

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * Méthode pour exporter un seul rapport HSE
     */
    public function exportSingleRapportHSE($rapport): StreamedResponse
    {
        return $this->exportRapportsHSE([$rapport], 'Rapport HSE - ' . $rapport->getCodeAgt());
    }

    private function addStatisticsSheet(Spreadsheet $spreadsheet, array $rapports): void
    {
        // Créer une nouvelle feuille pour les statistiques
        $statsSheet = $spreadsheet->createSheet();
        $statsSheet->setTitle('Statistiques');

        // Calculer les statistiques
        $totalRapports = count($rapports);
        $rapportsEnCours = count(array_filter($rapports, fn($r) => $r->getStatut() === 'En cours'));
        $rapportsClotures = count(array_filter($rapports, fn($r) => $r->getStatut() === 'Clôturé'));

        // Statistiques par zone
        $zonesStats = [];
        foreach ($rapports as $rapport) {
            $zone = $rapport->getZoneUtilisateur() ?? 'Non définie';
            if (!isset($zonesStats[$zone])) {
                $zonesStats[$zone] = ['total' => 0, 'en_cours' => 0, 'clotures' => 0];
            }
            $zonesStats[$zone]['total']++;
            if ($rapport->getStatut() === 'En cours') {
                $zonesStats[$zone]['en_cours']++;
            } elseif ($rapport->getStatut() === 'Clôturé') {
                $zonesStats[$zone]['clotures']++;
            }
        }

        // En-têtes de la feuille statistiques
        $statsSheet->setCellValue('A1', 'STATISTIQUES DES RAPPORTS HSE');
        $statsSheet->mergeCells('A1:E1');
        $statsSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Ajouter la date de génération
        $statsSheet->setCellValue('A2', 'Généré le : ' . date('d/m/Y à H:i:s'));
        $statsSheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $statsSheet->mergeCells('A2:E2');

        // Statistiques générales
        $row = 4;
        $statsSheet->setCellValue('A' . $row, 'RÉSUMÉ GÉNÉRAL');
        $statsSheet->getStyle('A' . $row)->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4472C4']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4472C4']]],
        ]);

        $row++;
        $statsSheet->setCellValue('A' . $row, 'Total des rapports:');
        $statsSheet->setCellValue('B' . $row, $totalRapports);
        $row++;
        $statsSheet->setCellValue('A' . $row, 'Rapports en cours:');
        $statsSheet->setCellValue('B' . $row, $rapportsEnCours);
        $row++;
        $statsSheet->setCellValue('A' . $row, 'Rapports clôturés:');
        $statsSheet->setCellValue('B' . $row, $rapportsClotures);
        $row++;
        $statsSheet->setCellValue('A' . $row, 'Taux de clôture:');
        $statsSheet->setCellValue('B' . $row, $totalRapports > 0 ? round(($rapportsClotures / $totalRapports) * 100, 2) . '%' : '0%');

        // Style pour les statistiques générales
        $statsSheet->getStyle('A5:A8')->applyFromArray([
            'font' => ['bold' => true],
        ]);
        $statsSheet->getStyle('B5:B8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Statistiques par zone (seulement s'il y a plusieurs zones)
        if (count($zonesStats) > 1) {
            $row += 2;
            $statsSheet->setCellValue('A' . $row, 'STATISTIQUES PAR ZONE');
            $statsSheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '4472C4']],
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '4472C4']]],
            ]);
            $row++;

            // En-têtes du tableau des zones
            $statsSheet->setCellValue('A' . $row, 'Zone');
            $statsSheet->setCellValue('B' . $row, 'Total');
            $statsSheet->setCellValue('C' . $row, 'En cours');
            $statsSheet->setCellValue('D' . $row, 'Clôturés');
            $statsSheet->setCellValue('E' . $row, 'Taux clôture');

            $statsSheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6C757D']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $row++;

            // Données des zones
            $startZoneRow = $row;
            foreach ($zonesStats as $zone => $stats) {
                $taux = $stats['total'] > 0 ? round(($stats['clotures'] / $stats['total']) * 100, 2) : 0;

                $statsSheet->setCellValue('A' . $row, $zone);
                $statsSheet->setCellValue('B' . $row, $stats['total']);
                $statsSheet->setCellValue('C' . $row, $stats['en_cours']);
                $statsSheet->setCellValue('D' . $row, $stats['clotures']);
                $statsSheet->setCellValue('E' . $row, $taux . '%');

                // Coloration selon la zone
                if ($zone === 'SIMTIS') {
                    $statsSheet->getStyle('A' . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
                        'font' => ['color' => ['rgb' => '0D47A1'], 'bold' => true],
                    ]);
                } elseif ($zone === 'SIMTIS TISSAGE') {
                    $statsSheet->getStyle('A' . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3E5F5']],
                        'font' => ['color' => ['rgb' => '4A148C'], 'bold' => true],
                    ]);
                }

                $row++;
            }

            // Bordures pour le tableau des zones
            $zoneTableRange = 'A' . ($startZoneRow - 1) . ':E' . ($row - 1);
            $statsSheet->getStyle($zoneTableRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // Ajuster les largeurs des colonnes pour les statistiques
        foreach (range('A', 'E') as $column) {
            $statsSheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Ajouter une note en bas
        $noteRow = $row + 3;
        $statsSheet->setCellValue('A' . $noteRow, 'Note: Ce rapport a été généré automatiquement par le système HSE.');
        $statsSheet->getStyle('A' . $noteRow)->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6C757D']],
        ]);
        $statsSheet->mergeCells('A' . $noteRow . ':E' . $noteRow);

        // Définir la feuille des rapports comme feuille active
        $spreadsheet->setActiveSheetIndex(0);
    }
}
