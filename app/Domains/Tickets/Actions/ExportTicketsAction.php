<?php

namespace App\Domains\Tickets\Actions;

use App\Domains\Clients\Company;
use App\Domains\Tickets\Ticket;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportTicketsAction
{
    public function execute(Request $request): StreamedResponse
    {
        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'ticket_type_id' => $request->get('ticket_type_id'),
            'date_from' => $request->get('date_from', now()->startOfMonth()->format('Y-m-d')),
            'date_to' => $request->get('date_to', now()->endOfMonth()->format('Y-m-d')),
        ];

        $user = $request->user();
        $query = Ticket::with('company', 'ticketType', 'creator', 'requester', 'assignee')
            ->with('comments', fn ($q) => $q->reorder()->oldest()->with('user'));

        if ($user->hasRole('super-admin')) {
        } elseif ($user->can('tickets.view-all')) {
            $companyIds = $user->companies()->pluck('companies.id')->toArray();
            if ($user->company_id) {
                $companyIds[] = $user->company_id;
            }
            $companyIds = array_unique($companyIds);
            $query->where(function ($q) use ($user, $companyIds) {
                if (!empty($companyIds)) {
                    $q->whereIn('company_id', $companyIds);
                }
                $q->orWhere('creator_id', $user->id)
                  ->orWhere('requester_id', $user->id);
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                  ->orWhere('requester_id', $user->id);
            });
        }

        if ($search = $filters['search']) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('uuid', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status']) {
            $query->where('status', $status);
        }

        if ($ticketTypeId = $filters['ticket_type_id']) {
            $query->where('ticket_type_id', $ticketTypeId);
        }

        $query->whereBetween('requested_at', [
            $filters['date_from'] . ' 00:00:00',
            $filters['date_to'] . ' 23:59:59',
        ]);

        $query->orderBy('requested_at', 'desc');

        $tickets = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Solicitudes');

        $statusLabels = [
            'open' => 'Abierto',
            'in_progress' => 'En progreso',
            'resolved' => 'Resuelto',
            'closed' => 'Cerrado',
        ];

        $headers = [
            'UUID',
            'Compañía',
            'Tipo',
            'Descripción',
            'Estado',
            'Solicitante',
            'Asignado',
            'Fecha Solicitud',
            'Fecha Actualización',
            'Fecha Creación',
            'Fecha Cierre',
            'Tiempo Resolución',
            'Tiempo Invertido (min)',
            'Comentarios',
        ];

        $headerCol = 1;
        foreach ($headers as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($headerCol);
            $sheet->setCellValue($colLetter . '1', $header);
            $headerCol++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '059669']]],
        ];
        $sheet->getStyle('A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1')
            ->applyFromArray($headerStyle);

        $row = 2;
        foreach ($tickets as $ticket) {
            $commentsText = '';
            foreach ($ticket->comments as $comment) {
                $userName = $comment->user?->name ?? 'Sistema';
                $date = $comment->created_at->format('Y-m-d H:i');
                $commentsText .= "[{$userName} - {$date}]\n{$comment->content}\n\n";
            }
            $commentsText = trim($commentsText);

            $resolutionTime = $ticket->closed_at
                ? $ticket->created_at->diffForHumans($ticket->closed_at, true)
                : '—';

            $sheet->setCellValue('A' . $row, $ticket->uuid);
            $sheet->setCellValue('B' . $row, $ticket->company?->name ?? '—');
            $sheet->setCellValue('C' . $row, $ticket->ticketType?->name ?? '—');
            $sheet->setCellValue('D' . $row, $ticket->description);
            $sheet->setCellValue('E' . $row, $statusLabels[$ticket->status] ?? $ticket->status);
            $sheet->setCellValue('F' . $row, $ticket->requester?->name ?? '—');
            $sheet->setCellValue('G' . $row, $ticket->assignee?->name ?? '—');
            $sheet->setCellValue('H' . $row, $ticket->requested_at?->format('Y-m-d H:i') ?? '—');
            $sheet->setCellValue('I' . $row, $ticket->updated_at->format('Y-m-d H:i'));
            $sheet->setCellValue('J' . $row, $ticket->created_at->format('Y-m-d H:i'));
            $sheet->setCellValue('K' . $row, $ticket->closed_at?->format('Y-m-d H:i') ?? '—');
            $sheet->setCellValue('L' . $row, $resolutionTime);
            $sheet->setCellValue('M' . $row, $ticket->total_time_spent_minutes);
            $sheet->setCellValue('N' . $row, $commentsText);

            $row++;
        }

        foreach (range(1, count($headers)) as $col) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $sheet->getStyle('D2:D' . ($row - 1))
            ->getAlignment()
            ->setWrapText(true);

        $sheet->getStyle('N2:N' . ($row - 1))
            ->getAlignment()
            ->setWrapText(true);

        $sheet->getStyle('A2:N' . ($row - 1))
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP);

        $sheet->freezePane('A2');

        $now = now()->format('Ymd_His');
        $filename = "solicitudes_{$now}.xlsx";

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
