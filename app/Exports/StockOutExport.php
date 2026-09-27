<?php

namespace App\Exports;

use App\Models\StockOut;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockOutExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $filters;
    protected int $no = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = StockOut::with(['product.category', 'user']);

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $this->filters['date_to']);
        }
        if (! empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        return $query->latest('transaction_date')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'No. Referensi', 'Tanggal', 'Nama Barang', 'Kategori',
            'Tipe', 'Pelanggan', 'Qty', 'Harga Jual', 'Total', 'Dicatat Oleh', 'Keterangan',
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
            $item->type_label,
            $item->customer_name ?? '-',
            $item->quantity,
            number_format($item->selling_price, 0, ',', '.'),
            number_format($item->total_price, 0, ',', '.'),
            $item->user?->name,
            $item->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'EF4444']]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok Keluar';
    }
}
