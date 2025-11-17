<?php

/**
 * -------------------------------------------------------------------------
 * Responsibility Terms plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Responsibility Terms.
 *
 * Responsibility Terms is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Responsibility Terms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Responsibility Terms. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2025 by F13 Tecnologia.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/f13-tecnologia/responsibilityterms
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\ResponsibilityTerms;

use CommonDBTM;
use Html;
use Session;
use User;

/**
 * Term class
 * Manages generated responsibility terms (PDFs) for users
 */
class Term extends CommonDBTM
{
    public static $rightname = 'plugin_responsibilityterms_term';
    
    public static function getTable($classname = null)
    {
        return 'glpi_plugin_responsibilityterms_terms';
    }

    public static function getTypeName($nb = 0)
    {
        return _n('Responsibility Term', 'Responsibility Terms', $nb, 'responsibilityterms');
    }

    public static function getIcon()
    {
        return 'ti ti-file-certificate';
    }

    public static function canCreate(): bool
    {
        return Session::haveRight(self::$rightname, CREATE);
    }

    public static function canView(): bool
    {
        return Session::haveRight(self::$rightname, READ);
    }

    public static function canUpdate(): bool
    {
        return Session::haveRight(self::$rightname, UPDATE);
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    public function getTabNameForItem(\CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'User' && self::canView()) {
            $count = countElementsInTable(
                self::getTable(),
                ['users_id' => $item->getID()]
            );
            return self::createTabEntry(self::getTypeName(2), $count);
        }
        return '';
    }

    public static function displayTabContentForItem(\CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'User') {
            self::showForUser($item);
        }
        return true;
    }

    /**
     * Show terms for a specific user
     *
     * @param User $user
     */
    public static function showForUser(User $user)
    {
        global $DB, $CFG_GLPI;

        $user_id = $user->getID();
        $canedit = self::canCreate();

        echo "<div class='center'>";

        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='term_form' method='post' action='" . 
                 $CFG_GLPI['root_doc'] . "/plugins/responsibilityterms/front/term.form.php'>";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='2'>" . __('Generate New Term', 'responsibilityterms') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Template', 'responsibilityterms') . "</td>";
            echo "<td>";
            
            $templates = TermTemplate::getTemplatesForDropdown();
            \Dropdown::showFromArray('templates_id', $templates, [
                'display_emptychoice' => true
            ]);
            
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2' class='center'>";
            echo Html::hidden('users_id', ['value' => $user_id]);
            echo Html::submit(__('Generate PDF', 'responsibilityterms'), ['name' => 'generate']);
            echo "</td>";
            echo "</tr>";

            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }

        // List existing terms
        $iterator = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => ['users_id' => $user_id],
            'ORDER' => 'date_creation DESC'
        ]);

        if (count($iterator) > 0) {
            echo "<table class='tab_cadre_fixehov'>";
            echo "<tr class='tab_bg_1'>";
            echo "<th>" . __('Template', 'responsibilityterms') . "</th>";
            echo "<th>" . __('Status', 'responsibilityterms') . "</th>";
            echo "<th>" . __('Creation Date') . "</th>";
            echo "<th>" . __('Signature Date', 'responsibilityterms') . "</th>";
            echo "<th>" . __('Actions') . "</th>";
            echo "</tr>";

            foreach ($iterator as $data) {
                echo "<tr class='tab_bg_2'>";
                
                $template = new TermTemplate();
                $template->getFromDB($data['templates_id']);
                echo "<td>" . $template->fields['name'] . "</td>";
                
                echo "<td>" . self::getStatusBadge($data['signature_status']) . "</td>";
                echo "<td>" . Html::convDateTime($data['date_creation']) . "</td>";
                echo "<td>" . ($data['date_signature'] ? Html::convDateTime($data['date_signature']) : '-') . "</td>";
                
                echo "<td>";
                // View PDF button
                if (!empty($data['pdf_content'])) {
                    $pdf_url = $CFG_GLPI['root_doc'] . "/plugins/responsibilityterms/front/term.pdf.php?id=" . $data['id'];
                    echo "<button type='button' class='btn btn-sm btn-primary' onclick='showPDFModal(\"" . $pdf_url . "\")'>" .
                         "<i class='fas fa-file-pdf'></i> " . __('View PDF', 'responsibilityterms') .
                         "</button> ";
                }
                
                // Send to signature button
                if ($data['signature_status'] == 'pending' && Config::hasSignatureConfig()) {
                    echo "<a href='" . $CFG_GLPI['root_doc'] . 
                         "/plugins/responsibilityterms/front/term.send.php?id=" . $data['id'] . 
                         "' class='btn btn-sm btn-success'>" .
                         "<i class='fas fa-signature'></i> " . __('Send to Signature', 'responsibilityterms') .
                         "</a>";
                } elseif ($data['signature_status'] == 'pending') {
                    echo "<span class='badge bg-warning'>" . 
                         __('Configure signature URL in Settings', 'responsibilityterms') .
                         "</span>";
                }
                
                echo "</td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "<p class='center'>" . __('No terms generated yet', 'responsibilityterms') . "</p>";
        }

        echo "</div>";
        
        // Modal HTML for PDF viewer
        echo "<div class='modal fade' id='pdfModal' tabindex='-1' role='dialog' aria-hidden='true'>
                <div class='modal-dialog modal-xl' role='document' style='max-width: 90%; height: 90vh;'>
                    <div class='modal-content' style='height: 100%;'>
                        <div class='modal-header'>
                            <h5 class='modal-title'>" . __('View PDF', 'responsibilityterms') . "</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body' style='height: calc(100% - 120px); padding: 0;'>
                            <iframe id='pdfIframe' style='width: 100%; height: 100%; border: none;'></iframe>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>" . __('Close') . "</button>
                        </div>
                    </div>
                </div>
              </div>";
        
        // JavaScript for modal
        echo "<script>
        function showPDFModal(pdfUrl) {
            var iframe = document.getElementById('pdfIframe');
            iframe.src = pdfUrl;
            var modal = new bootstrap.Modal(document.getElementById('pdfModal'));
            modal.show();
        }
        </script>";
    }

    /**
     * Get status badge HTML
     *
     * @param string $status
     * @return string
     */
    public static function getStatusBadge($status)
    {
        $badges = [
            'pending'  => ['class' => 'bg-secondary', 'label' => __('Pending', 'responsibilityterms')],
            'sent'     => ['class' => 'bg-info', 'label' => __('Sent', 'responsibilityterms')],
            'signed'   => ['class' => 'bg-success', 'label' => __('Signed', 'responsibilityterms')],
            'rejected' => ['class' => 'bg-danger', 'label' => __('Rejected', 'responsibilityterms')],
        ];

        $badge = $badges[$status] ?? $badges['pending'];
        return "<span class='badge {$badge['class']}'>{$badge['label']}</span>";
    }

    /**
     * Generate PDF for a term
     *
     * @param int $users_id
     * @param int $templates_id
     * @return bool|int Term ID or false
     */
    public static function generatePDF($users_id, $templates_id)
    {
        global $DB;

        // Get template
        $template = new TermTemplate();
        if (!$template->getFromDB($templates_id)) {
            return false;
        }

        // Get user
        $user = new User();
        if (!$user->getFromDB($users_id)) {
            return false;
        }

        // Get equipment based on template settings
        $equipment = self::getUserEquipment($users_id, $template->fields);

        // Replace placeholders
        $content = self::replacePlaceholders($template->fields['content'], $user->fields, $equipment);

        // Generate PDF (simplified - in production use a proper PDF library like TCPDF or DomPDF)
        $pdf_content = self::createPDF($content, $user->fields);
        $pdf_filename = 'termo_' . $user->fields['name'] . '_' . date('Y-m-d_H-i-s') . '.pdf';

        // Save term
        $term = new self();
        $term_id = $term->add([
            'users_id'     => $users_id,
            'templates_id' => $templates_id,
            'pdf_content'  => $pdf_content,
            'pdf_filename' => $pdf_filename,
            'signature_status' => 'pending',
            'date_creation' => $_SESSION['glpi_currenttime']
        ]);

        if ($term_id) {
            // Link equipment to term
            self::linkEquipment($term_id, $equipment);
        }

        return $term_id;
    }

    /**
     * Get user equipment based on template settings
     *
     * @param int $users_id
     * @param array $template_fields
     * @return array
     */
    private static function getUserEquipment($users_id, $template_fields)
    {
        global $DB;
        
        $equipment = [];

        // Get computers
        if ($template_fields['include_computers']) {
            $computers = $DB->request([
                'FROM'  => 'glpi_computers',
                'WHERE' => [
                    'users_id' => $users_id,
                    'is_deleted' => 0
                ]
            ]);
            foreach ($computers as $comp) {
                $equipment[] = ['type' => 'Computer', 'id' => $comp['id'], 'name' => $comp['name']];
            }
        }

        // Get phones
        if ($template_fields['include_phones']) {
            $phones = $DB->request([
                'FROM'  => 'glpi_phones',
                'WHERE' => [
                    'users_id' => $users_id,
                    'is_deleted' => 0
                ]
            ]);
            foreach ($phones as $phone) {
                $equipment[] = ['type' => 'Phone', 'id' => $phone['id'], 'name' => $phone['name']];
            }
        }

        // Get lines (simplified - assuming a custom itemtype or using phones)
        if ($template_fields['include_lines']) {
            $lines = $DB->request([
                'FROM'  => 'glpi_lines',
                'WHERE' => [
                    'users_id' => $users_id,
                    'is_deleted' => 0
                ]
            ]);
            foreach ($lines as $line) {
                $equipment[] = ['type' => 'Line', 'id' => $line['id'], 'name' => $line['name']];
            }
        }

        return $equipment;
    }

    /**
     * Replace placeholders in content
     *
     * @param string $content
     * @param array $user_fields
     * @param array $equipment
     * @return string
     */
    private static function replacePlaceholders($content, $user_fields, $equipment)
    {
        $replacements = [
            '{USER_NAME}' => $user_fields['firstname'] . ' '. $user_fields['realname'],
            '{USER_EMAIL}' => $user_fields['email'] ?? '',
            '{USER_REGISTRATION}' => $user_fields['registration_number'] ?? '',
            '{DATE}' => date('d/m/Y'),
        ];

        // Build equipment list
        $equipment_list = '';
        foreach ($equipment as $item) {
            $equipment_list .= "- " . $item['type'] . ": " . $item['name'] . "\n";
        }
        $replacements['{EQUIPMENT_LIST}'] = $equipment_list;

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Create PDF content (simplified)
     *
     * @param string $content
     * @param array $user_fields
     * @return string Binary PDF data
     */
    private static function createPDF($content, $user_fields)
    {
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('GLPI - Responsibility Terms');
        $pdf->SetAuthor($user_fields['name']);
        $pdf->SetTitle('Termo de Responsabilidade');
        $pdf->SetSubject('Termo de Responsabilidade de Equipamentos');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 11);

        // Convert content to HTML if needed
        $html = '<div style="text-align: justify; line-height: 1.5;">';
        $html .= nl2br($content);
        $html .= '</div>';

        // Add signature section
        $html .= '<br><br><br>';
        $html .= '<table width="100%" style="margin-top: 50px;">';
        $html .= '<tr>';
        $html .= '<td style="text-align: center; border-top: 1px solid #000; padding-top: 5px;">';
        $html .= '<strong>' . $user_fields['name'] . '</strong><br>';
        $html .= 'Data: ' . date('d/m/Y');
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        // Write HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Return PDF as string
        return $pdf->Output('', 'S');
    }

    /**
     * Link equipment to term
     *
     * @param int $term_id
     * @param array $equipment
     */
    private static function linkEquipment($term_id, $equipment)
    {
        global $DB;

        foreach ($equipment as $item) {
            $DB->insert('glpi_plugin_responsibilityterms_items', [
                'terms_id' => $term_id,
                'items_id' => $item['id'],
                'itemtype' => $item['type']
            ]);
        }
    }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'            => '2',
            'table'         => 'glpi_users',
            'field'         => 'name',
            'name'          => __('User'),
            'datatype'      => 'dropdown',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id'       => '3',
            'table'    => TermTemplate::getTable(),
            'field'    => 'name',
            'name'     => __('Template', 'responsibilityterms'),
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id'       => '4',
            'table'    => self::getTable(),
            'field'    => 'signature_status',
            'name'     => __('Status', 'responsibilityterms'),
            'datatype' => 'specific'
        ];

        return $tab;
    }
}
