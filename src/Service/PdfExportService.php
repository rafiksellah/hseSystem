<?php

namespace App\Service;

use App\Entity\RapportHSE;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExportService
{
    public function exportRapportsHSE(array $rapports, string $title = 'Rapports HSE'): StreamedResponse
    {
        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        // Génération du HTML
        $html = $this->generateHtml($rapports, $title);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Définir le format de page
        $dompdf->setPaper('A4', 'landscape');

        // Render le PDF
        $dompdf->render();

        // Créer la réponse en streaming
        $response = new StreamedResponse(function () use ($dompdf) {
            echo $dompdf->output();
        });

        $filename = 'rapports_hse_' . date('Y-m-d_H-i-s') . '.pdf';

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function generateHtml(array $rapports, string $title): string
    {
        // Calculer des statistiques
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

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($title) . '</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 9px;
                    margin: 15px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    margin-bottom: 25px;
                    border-bottom: 3px solid #4472C4;
                    padding-bottom: 15px;
                }
                .header h1 {
                    color: #4472C4;
                    margin: 0;
                    font-size: 22px;
                    font-weight: bold;
                }
                .header .subtitle {
                    color: #666;
                    font-size: 12px;
                    margin-top: 5px;
                }
                .header .date {
                    color: #888;
                    font-size: 10px;
                    margin-top: 8px;
                }
                
                .stats-section {
                    background-color: #f8f9fa;
                    padding: 15px;
                    margin-bottom: 20px;
                    border-radius: 5px;
                    border: 1px solid #dee2e6;
                }
                .stats-grid {
                    display: table;
                    width: 100%;
                }
                .stats-item {
                    display: table-cell;
                    width: 33.33%;
                    text-align: center;
                    padding: 10px;
                }
                .stats-number {
                    font-size: 24px;
                    font-weight: bold;
                    color: #4472C4;
                    display: block;
                }
                .stats-label {
                    font-size: 10px;
                    color: #666;
                    margin-top: 5px;
                }
                
                .zones-stats {
                    margin-bottom: 20px;
                }
                .zones-stats h3 {
                    color: #4472C4;
                    font-size: 14px;
                    margin-bottom: 10px;
                    border-bottom: 1px solid #4472C4;
                    padding-bottom: 5px;
                }
                .zones-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 15px;
                }
                .zones-table th, .zones-table td {
                    border: 1px solid #ddd;
                    padding: 6px;
                    text-align: center;
                    font-size: 9px;
                }
                .zones-table th {
                    background-color: #4472C4;
                    color: white;
                    font-weight: bold;
                }
                .zone-simtis {
                    background-color: #e3f2fd;
                    color: #0d47a1;
                }
                .zone-simtis-tissage {
                    background-color: #f3e5f5;
                    color: #4a148c;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                    font-size: 8px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 4px;
                    text-align: left;
                    vertical-align: top;
                }
                th {
                    background-color: #4472C4;
                    color: white;
                    font-weight: bold;
                    font-size: 8px;
                    text-align: center;
                }
                tr:nth-child(even) {
                    background-color: #f9f9f9;
                }
                
                .status-en-cours {
                    background-color: #fff3cd;
                    color: #856404;
                    padding: 2px 4px;
                    border-radius: 3px;
                    font-size: 7px;
                    font-weight: bold;
                }
                .status-cloture {
                    background-color: #d4edda;
                    color: #155724;
                    padding: 2px 4px;
                    border-radius: 3px;
                    font-size: 7px;
                    font-weight: bold;
                }
                
                .page-break {
                    page-break-before: always;
                }
                
                .footer {
                    position: fixed;
                    bottom: 20px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    font-size: 8px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . htmlspecialchars($title) . '</h1>
                <div class="subtitle">Export généré le ' . date('d/m/Y à H:i:s') . '</div>
                <div class="date">Total: ' . $totalRapports . ' rapport(s)</div>
            </div>';

        // Section statistiques
        if ($totalRapports > 0) {
            $html .= '<div class="stats-section">
                        <div class="stats-grid">
                            <div class="stats-item">
                                <span class="stats-number">' . $totalRapports . '</span>
                                <div class="stats-label">Total Rapports</div>
                            </div>
                            <div class="stats-item">
                                <span class="stats-number" style="color: #ffc107;">' . $rapportsEnCours . '</span>
                                <div class="stats-label">En Cours</div>
                            </div>
                            <div class="stats-item">
                                <span class="stats-number" style="color: #28a745;">' . $rapportsClotures . '</span>
                                <div class="stats-label">Clôturés</div>
                            </div>
                        </div>
                    </div>';

            // Statistiques par zone
            if (count($zonesStats) > 1) {
                $html .= '<div class="zones-stats">
                            <h3>Répartition par Zone</h3>
                            <table class="zones-table">
                                <tr>
                                    <th>Zone</th>
                                    <th>Total</th>
                                    <th>En Cours</th>
                                    <th>Clôturés</th>
                                    <th>Taux Clôture</th>
                                </tr>';

                foreach ($zonesStats as $zone => $stats) {
                    $zoneClass = '';
                    if ($zone === 'SIMTIS') {
                        $zoneClass = 'zone-simtis';
                    } elseif ($zone === 'SIMTIS TISSAGE') {
                        $zoneClass = 'zone-simtis-tissage';
                    }

                    $tauxCloture = $stats['total'] > 0 ? round(($stats['clotures'] / $stats['total']) * 100, 1) : 0;

                    $html .= '<tr>
                                <td class="' . $zoneClass . '"><strong>' . htmlspecialchars($zone) . '</strong></td>
                                <td>' . $stats['total'] . '</td>
                                <td>' . $stats['en_cours'] . '</td>
                                <td>' . $stats['clotures'] . '</td>
                                <td>' . $tauxCloture . '%</td>
                              </tr>';
                }

                $html .= '</table></div>';
            }
        }

        // Tableau des rapports
        $html .= '<table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Code Agent</th>
                            <th>Nom</th>
                            <th>Zone</th>
                            <th>Emplacement</th>
                            <th>Statut</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($rapports as $rapport) {
            $statusClass = match ($rapport->getStatut()) {
                'En cours' => 'status-en-cours',
                'Clôturé' => 'status-cloture',
                default => ''
            };

            $description = $rapport->getDescriptionAnomalie() ?? 'Non spécifiée';
            if (strlen($description) > 100) {
                $description = substr($description, 0, 97) . '...';
            }

            $html .= '<tr>
                        <td>' . htmlspecialchars($rapport->getId()) . '</td>
                        <td>' . htmlspecialchars($rapport->getDate() ? $rapport->getDate()->format('d/m/Y') : '') . '</td>
                        <td>' . htmlspecialchars($rapport->getCodeAgt()) . '</td>
                        <td>' . htmlspecialchars($rapport->getNom()) . '</td>
                        <td>' . htmlspecialchars($rapport->getZoneUtilisateur() ?? 'Non définie') . '</td>
                        <td>' . htmlspecialchars($rapport->getEmplacement() ?? 'Non spécifié') . '</td>
                        <td><span class="' . $statusClass . '">' . htmlspecialchars($rapport->getStatut()) . '</span></td>
                        <td>' . nl2br(htmlspecialchars($description)) . '</td>
                      </tr>';
        }

        $html .= '</tbody></table>
            
            <div class="footer">
                Rapport généré par le système HSE - Page <script type="text/php">
                    if (isset($pdf)) {
                        echo $pdf->get_page_number() . " sur " . $pdf->get_page_count();
                    }
                </script>
            </div>
        </body></html>';

        return $html;
    }
}