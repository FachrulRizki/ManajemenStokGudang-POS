<?php

namespace App\Exports;

use App\Models\StockIn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockInExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $filters;
    protected int $no = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = StockIn::with(['product.category', 'supplier', 'user']);

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $this->filters['date_to']);
        }

        return $query->latest('transaction_date')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'No. Referensi', 'Tanggal', 'Nama Barang', 'Kategori',
            'Supplier', 'Qty', 'Harga Beli', 'Total', 'No. Invoice', 'Dicatat Oleh', 'Keterangan',
        ];
    }

    public function map($item): array
    {
        $this->no++;
        return [
            $this->no,
            $item->reference_number,
            $item->transaction_date->format('d/m/Y'),
            $item->product?->name,
            $item->product?->category?->name,
            $item->supplier?->name ?? '-',
            $item->quantity,
            number_format($item->purchase_price, 0, ',', '.'),
            number_format($item->total_price, 0, ',', '.'),
            $item->invoice_number ?? '-',
            $item->user?->name,
            $item->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '10B981']]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok Masuk';
    }
}
