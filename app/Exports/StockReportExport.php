<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected array $filters;
    protected int $no = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Product::with(['category', 'unit', 'supplier']);

        if (! empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (! empty($this->filters['status'])) {
            if ($this->filters['status'] === 'low') {
                $query->whereColumn('stock', '<=', 'min_stock');
            } elseif ($this->filters['status'] === 'out') {
                $query->where('stock', '<=', 0);
            }
        }

        return $query->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'No', 'Kode', 'Nama Barang', 'Kategori', 'Satuan', 'Supplier',
            'Stok', 'Min Stok', 'Status', 'Harga Beli', 'Harga Jual', 'Nilai Stok', 'Lokasi Rak',
        ];
    }

    public function map($product): array
    {
        $this->no++;
        return [
            $this->no,
            $product->code,
            $product->name,
            $product->category?->name,
            $product->unit?->symbol,
            $product->supplier?->name ?? '-',
            $product->stock,
            $product->min_stock,
            $product->stock_status === 'out' ? 'Habis' : ($product->stock_status === 'low' ? 'Menipis' : 'Normal'),
            number_format($product->purchase_price, 0, ',', '.'),
            number_format($product->selling_price, 0, ',', '.'),
            number_format($product->stock * $product->purchase_price, 0, ',', '.'),
            $product->rack_location ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '6366F1']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laporan Stok';
    }
}
