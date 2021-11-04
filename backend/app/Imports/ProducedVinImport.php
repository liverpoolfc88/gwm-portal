<?php

namespace App\Imports;

use App\ProducedTempVin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProducedVinImport implements ToModel, WithHeadingRow,WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $user = auth('api')->user();
        $excel = [
            'skd_plant' => $row['skd_plant'],
            'vin_gm' => $row['vin_gm'],
            'vin_local' => $row['vin_local'],
            'model_code' => $row['model_code'],
            'model_year' => $row['model_year'],
            'engine' => $row['engine'],
            'full_option' => $row['full_option'],
            'produced_date' =>  $this->transformDate($row['produced_date']),
            'to_dealer' => $row['to_dealer'],
            'sold_date' => $this->transformDate($row['sold_date']),
            'user_id' =>  $user->id,
        ];

        return new ProducedTempVin($excel);
    }
    public function rules(): array {
        return [
          '*.model_code' => 'string|min:5|max:5',
          '*.vin_local' => 'string|min:17|max:17',
          '*.vin_gm' => 'string|min:17|max:17',
        ];
    }
    public function transformDate($value, $format = 'Y-m-d')
    {
        try {
            return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
        } catch (\ErrorException $e) {
            return \Carbon\Carbon::createFromFormat($format, $value);
        }
    }
}
