<?php

namespace App\Service;

use App\Entity\RapportHSE;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    public function exportRapportsHSE(array $rapports, string $title = 'Rapports HSE'): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définir le titre de la feuille
        $sheet->setTitle($title);

        // En-têtes des colonnes
        $headers = [
            'A' => 'ID',
            'B' => 'Code Agent',
            'C' => 'Nom',
            'D' => 'Date',
            'E' => 'Heure',
            'F' => 'Zone',
            'G' => 'Emplacement',
            'H' => 'Équipement/Produit concerné',
            'I' => 'Description anomalie',
            'J' => 'Cause probable',
            'K' => 'Photo constat',
            'L' => 'Action clôturée',
            'M' => 'Date clôture',
            'N' => 'Heure action',
            'O' => 'Photo action clôturée',
            'P' => 'Statut',
            'Q' => 'Date création',
            'R' => 'Utilisateur'
        ];

        // Appliquer les en-têtes
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '1', $header);
        }

        // Style pour les en-têtes
        $headerRange = 'A1:R1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
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
            $sheet->setCellValue('F' . $row, $rapport->getZone() ?? '');
            $sheet->setCellValue('G' . $row, $rapport->getEmplacement() ?? '');
            $sheet->setCellValue('H' . $row, $rapport->getEquipementProduitConcerne() ?? '');
            $sheet->setCellValue('I' . $row, $rapport->getDescriptionAnomalie() ?? '');
            $sheet->setCellValue('J' . $row, $rapport->getCauseProbable() ?? '');
            $sheet->setCellValue('K' . $row, $rapport->getPhotoConstat() ?? '');
            $sheet->setCellValue('L' . $row, $rapport->getActionCloturee() ?? '');
            $sheet->setCellValue('M' . $row, $rapport->getDateCloture() ? $rapport->getDateCloture()->format('d/m/Y') : '');
            $sheet->setCellValue('N' . $row, $rapport->getHeureAction() ? $rapport->getHeureAction()->format('H:i') : '');
            $sheet->setCellValue('O' . $row, $rapport->getPhotoActionCloturee() ?? '');
            $sheet->setCellValue('P' . $row, $rapport->getStatut() ?? '');
            $sheet->setCellValue('Q' . $row, $rapport->getDateCreation() ? $rapport->getDateCreation()->format('d/m/Y H:i') : '');
            $sheet->setCellValue('R' . $row, $rapport->getUser() ? $rapport->getUser()->getNom() . ' ' . $rapport->getUser()->getPrenom() : '');

            $row++;
        }

        // Ajuster la largeur des colonnes
        foreach (range('A', 'R') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Appliquer des bordures à tous les données
        $dataRange = 'A1:R' . ($row - 1);
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
                $sheet->getStyle('A' . $i . ':R' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2'],
                    ],
                ]);
            }
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
}
