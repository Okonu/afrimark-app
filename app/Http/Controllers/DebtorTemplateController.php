<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Exports\DebtorTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class DebtorTemplateController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new DebtorTemplateExport(), 'debtors-import-template.xlsx');
    }
}
