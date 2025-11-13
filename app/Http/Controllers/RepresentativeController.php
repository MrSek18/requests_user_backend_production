<?php

namespace App\Http\Controllers;

use App\Models\Representative;
use App\Models\CompanyRepresentative;
use Illuminate\Http\Request;

class RepresentativeController extends Controller
{
    public function index()
    {
        return response()->json(Representative::all());
    }

    public function byCompany($company_id)
    {
        $repIds = CompanyRepresentative::where('company_id', $company_id)->pluck('representative_id');
        $reps = Representative::whereIn('id', $repIds)->get();
        return response()->json($reps);
    }
}

