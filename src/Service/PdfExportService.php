<?php

namespace App\Service;

use App\Entity\RapportHSE;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExportService
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function exportRapportsHSE(array $rapports, string $title = 'Rapports HSE'): StreamedResponse
    {
        // Configuration de Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('enable_php', true);
        $options->set('chroot', realpath($this->parameterBag->get('kernel.project_dir')));

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

    /**
     * Méthode pour exporter un seul rapport HSE avec images détaillées
     */
    public function exportSingleRapportHSE($rapport, bool $includeImages = true): StreamedResponse
    {
        return $this->exportRapportsHSE([$rapport], 'Rapport HSE - ' . $rapport->getCodeAgt(), $includeImages);
    }

    /**
     * Convertit une image en base64 pour l'intégration dans le PDF
     */
    private function getImageAsBase64(string $imagePath): ?string
    {
        $fullPath = $this->parameterBag->get('uploads_directory') . '/' . $imagePath;

        if (!file_exists($fullPath)) {
            return null;
        }

        $imageData = file_get_contents($fullPath);
        if ($imageData === false) {
            return null;
        }

        $imageInfo = getimagesize($fullPath);
        if ($imageInfo === false) {
            return null;
        }

        $mimeType = $imageInfo['mime'];
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }

    private function generateHtml(array $rapports, string $title, bool $includeImages = true): string
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
                
                /* Styles pour les rapports détaillés avec images */
                .rapport-detail {
                    page-break-after: always;
                    margin-bottom: 30px;
                }
                .rapport-detail:last-child {
                    page-break-after: auto;
                }
                
                .rapport-header {
                    background-color: #4472C4;
                    color: white;
                    padding: 10px;
                    margin-bottom: 20px;
                    border-radius: 5px;
                }
                .rapport-header h2 {
                    margin: 0;
                    font-size: 16px;
                }
                
                .info-section {
                    margin-bottom: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    padding: 10px;
                }
                .info-section h3 {
                    color: #4472C4;
                    font-size: 12px;
                    margin: 0 0 10px 0;
                    border-bottom: 1px solid #4472C4;
                    padding-bottom: 5px;
                }
                
                .info-grid {
                    display: table;
                    width: 100%;
                }
                .info-row {
                    display: table-row;
                }
                .info-cell {
                    display: table-cell;
                    padding: 5px;
                    width: 50%;
                }
                .info-label {
                    font-weight: bold;
                    color: #666;
                }
                
                .description-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #4472C4;
                    padding: 10px;
                    margin: 10px 0;
                    border-radius: 0 5px 5px 0;
                }
                
                .photos-section {
                    margin-top: 20px;
                }
                .photo-container {
                    text-align: center;
                    margin: 15px 0;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .photo-title {
                    font-weight: bold;
                    color: #4472C4;
                    margin-bottom: 10px;
                    font-size: 10px;
                }
                .photo-img {
                    max-width: 300px;
                    max-height: 200px;
                    border: 2px solid #ddd;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

        // Si c'est un export d'un seul rapport avec images détaillées
        if ($totalRapports === 1 && $includeImages) {
            $rapport = $rapports[0];
            $html .= $this->generateDetailedRapportHtml($rapport);
        } else {
            // Export liste avec statistiques
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

            $html .= '</tbody></table>';
        }

        $html .= '<div class="footer">
                Rapport généré par le système HSE - Page <script type="text/php">
                    if (isset($pdf)) {
                        echo $pdf->get_page_number() . " sur " . $pdf->get_page_count();
                    }
                </script>
            </div>
        </body></html>';

        return $html;
    }

    /**
     * Génère le HTML détaillé pour un rapport unique avec images
     */
    private function generateDetailedRapportHtml(RapportHSE $rapport): string
    {
        $html = '<div class="rapport-detail">
                    <div class="rapport-header">
                        <h2>Rapport HSE #' . $rapport->getId() . ' - ' . htmlspecialchars($rapport->getCodeAgt()) . '</h2>
                        <div>Agent: ' . htmlspecialchars($rapport->getNom()) . ' | Zone: ' . htmlspecialchars($rapport->getZoneUtilisateur() ?? 'Non définie') . '</div>
                    </div>';

        // Informations générales
        $html .= '<div class="info-section">
                    <h3>Informations Générales</h3>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Date:</span> ' . ($rapport->getDate() ? $rapport->getDate()->format('d/m/Y') : 'Non définie') . '
                            </div>
                            <div class="info-cell">
                                <span class="info-label">Heure:</span> ' . ($rapport->getHeure() ? $rapport->getHeure()->format('H:i') : 'Non définie') . '
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Zone de travail:</span> ' . htmlspecialchars($rapport->getZone() ?? 'Non spécifiée') . '
                            </div>
                            <div class="info-cell">
                                <span class="info-label">Emplacement:</span> ' . htmlspecialchars($rapport->getEmplacement() ?? 'Non spécifié') . '
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell">
                                <span class="info-label">Équipement concerné:</span> ' . htmlspecialchars($rapport->getEquipementProduitConcerne() ?? 'Non spécifié') . '
                            </div>
                            <div class="info-cell">
                                <span class="info-label">Statut:</span> <strong>' . htmlspecialchars($rapport->getStatut()) . '</strong>
                            </div>
                        </div>
                    </div>
                </div>';

        // Description de l'anomalie
        if ($rapport->getDescriptionAnomalie()) {
            $html .= '<div class="info-section">
                        <h3>Description de l\'Anomalie</h3>
                        <div class="description-box">
                            ' . nl2br(htmlspecialchars($rapport->getDescriptionAnomalie())) . '
                        </div>';

            if ($rapport->getCauseProbable()) {
                $html .= '<div>
                            <span class="info-label">Cause probable:</span> ' . htmlspecialchars($rapport->getCauseProbable()) . '
                        </div>';
            }
            $html .= '</div>';
        }

        // Informations de clôture
        if ($rapport->getActionCloturee() === 'oui') {
            $html .= '<div class="info-section">
                        <h3>Action de Clôture</h3>
                        <div class="info-grid">
                            <div class="info-row">
                                <div class="info-cell">
                                    <span class="info-label">Date de clôture:</span> ' . ($rapport->getDateCloture() ? $rapport->getDateCloture()->format('d/m/Y') : 'Non définie') . '
                                </div>
                                <div class="info-cell">
                                    <span class="info-label">Heure de l\'action:</span> ' . ($rapport->getHeureAction() ? $rapport->getHeureAction()->format('H:i') : 'Non définie') . '
                                </div>
                            </div>
                        </div>
                    </div>';
        }

        // Section photos
        $hasPhotos = $rapport->getPhotoConstat() || $rapport->getPhotoActionCloturee();
        if ($hasPhotos) {
            $html .= '<div class="photos-section">
                        <div class="info-section">
                            <h3>Photos du Rapport</h3>';

            // Photo du constat
            if ($rapport->getPhotoConstat()) {
                $photoBase64 = $this->getImageAsBase64($rapport->getPhotoConstat());
                if ($photoBase64) {
                    $html .= '<div class="photo-container">
                                <div class="photo-title">Photo du Constat</div>
                                <img src="' . $photoBase64 . '" alt="Photo du constat" class="photo-img">
                                <div style="margin-top: 5px; font-size: 8px; color: #666;">
                                    Prise le ' . ($rapport->getDate() ? $rapport->getDate()->format('d/m/Y') : '') . ' à ' . ($rapport->getHeure() ? $rapport->getHeure()->format('H:i') : '') . '
                                </div>
                            </div>';
                }
            }

            // Photo d'action
            if ($rapport->getPhotoActionCloturee()) {
                $photoBase64 = $this->getImageAsBase64($rapport->getPhotoActionCloturee());
                if ($photoBase64) {
                    $html .= '<div class="photo-container">
                                <div class="photo-title">Photo d\'Action Clôturée</div>
                                <img src="' . $photoBase64 . '" alt="Photo d\'action clôturée" class="photo-img">
                                <div style="margin-top: 5px; font-size: 8px; color: #666;">
                                    Action clôturée le ' . ($rapport->getDateCloture() ? $rapport->getDateCloture()->format('d/m/Y') : '') . '
                                </div>
                            </div>';
                }
            }

            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
    }
}
