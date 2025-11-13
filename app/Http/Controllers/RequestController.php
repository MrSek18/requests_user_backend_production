<?php

namespace App\Http\Controllers;

use App\Models\UserRequest;
use App\Models\RequestDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'provider_id' => 'nullable|exists:providers,id',
            'representative_id' => 'nullable|exists:representatives,id',
            'requesting_area' => 'required|string|max:255',
            'date' => 'required|date',
            'justification' => 'required|string',
            'details' => 'required|array|min:1',
            'total' => 'required|numeric|min:0',
        ]);

        $mainRequest = UserRequest::create([
            'user_id' => $request->user()->id,
            'company_id' => $validated['company_id'],
            'provider_id' => $validated['provider_id'],
            'representative_id' => $validated['representative_id'],
            'requesting_area' => $validated['requesting_area'],
            'date' => $validated['date'],
            'justification' => $validated['justification'],
            'total' => $validated['total'],
        ]);

        foreach ($validated['details'] as $detail) {
            RequestDetail::create([
                'request_id' => $mainRequest->id,
                'service_id' => $detail['service_id'],
                'quantity' => $detail['quantity'],
                'unit_id' => $detail['unit_id'],
                'unit_price' => $detail['unit_price'],
                'subtotal' => $detail['subtotal'],
            ]);
        }

        return response()->json([
            'message' => 'Requerimiento registrado exitosamente',
            'request_id' => $mainRequest->id,
        ], 201);
    }
    public function recent()
    {
        $userId = Auth::id();
        $requerimientos = UserRequest::with([
            'company:id,name',
            'details.service:id,name'
        ])
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();
        return response()->json($requerimientos);
    }
}
