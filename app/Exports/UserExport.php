<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UserExport implements FromCollection, WithMapping, WithColumnFormatting, WithHeadings, Responsable, ShouldAutoSize
{
    use Exportable;

    /**
     * It's required to define the fileName within
     * the export class when making use of Responsable.
     */
    public $fileName = 'users.xlsx';

    /**
     * Optional Writer Type
     */
    private $writerType = Excel::XLSX;

    /**
     * Optional headers
     */
    private $headers = [
        'Content-Type' => 'text/csv',
    ];


    public function collection()
    {
        return User::all();
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->full_name,
            $user->email,
            $user->phone_number,
            $user->created_at,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function headings(): array
    {
        return [
            'Id',
            'Full name',
            'Email',
            'Phone number',
            'Added date',
        ];
    }
}
