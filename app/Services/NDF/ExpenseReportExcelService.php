<?php

namespace App\Services\NDF;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExpenseReportExcelService
{
    public function export(array $users)
    {
        $templatePath = storage_path('app/templates/expense_report_template.xlsx');
        $spreadsheet = IOFactory::load($templatePath);

        $sheet = $spreadsheet->getSheetByName('Récap') ?? $spreadsheet->getSheet(0);

        // On ne touche plus au header du template
        $this->fillRows($sheet, $users);

        $fileName = 'Fichier NDF STELLANTIS_' . now()->format('Y_m_d_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function fillRows($sheet, array $users): void
    {
        $startRow = 11;

        foreach ($users as $index => $user) {
            $row = $startRow + $index;

            $sheet->setCellValue("A{$row}", $user['prestataire'] ?? '');

            if (!empty($user['dateDebut'])) {
                $sheet->setCellValue("B{$row}", ExcelDate::PHPToExcel(Carbon::parse($user['dateDebut'])));
                $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            }

            if (!empty($user['dateFin'])) {
                $sheet->setCellValue("C{$row}", ExcelDate::PHPToExcel(Carbon::parse($user['dateFin'])));
                $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            }

            $sheet->setCellValue("D{$row}", $user['duree'] ?? '');
            $sheet->setCellValue("E{$row}", (int) ($user['nuitees'] ?? 0));
            $sheet->setCellValue("F{$row}", $user['transport'] ?? '');
            $sheet->setCellValue("G{$row}", (float) ($user['coutTransport'] ?? 0));
            $sheet->setCellValue("H{$row}", $user['pays'] ?? '');
            $sheet->setCellValue("I{$row}", (float) ($user['forfait'] ?? 0));
            $sheet->setCellValue("J{$row}", $user['depensesSpecifiques'] ?? '');
            $sheet->setCellValue("K{$row}", (float) ($user['montantDepenses'] ?? 0));
            $sheet->setCellValue("L{$row}", (float) ($user['total'] ?? 0));
            $sheet->setCellValue("M{$row}", $user['commentaires'] ?? '');
        }
    }
}