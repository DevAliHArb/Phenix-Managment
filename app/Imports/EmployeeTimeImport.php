<?php

namespace App\Imports;

use App\Models\EmployeeTime;
use Maatwebsite\Excel\Concerns\ToModel;

class EmployeeTimeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new EmployeeTime([
            //
        ]);
    }
}
