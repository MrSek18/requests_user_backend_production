<?php

namespace App\Http\Controllers;

use App\Models\UserRequest;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ServiceOrderController extends Controller
{
    public function download($id)
    {
        try {
            // Buscar la solicitud con todas las relaciones necesarias
            $request = UserRequest::with([
                'company',
                'provider',
                'representative',
                'user',
                'details.service',
                'details.unit'
            ])->findOrFail($id);

            // Fecha actual formateada
            $now = Carbon::now();
            $dia = $now->format('d');
            $mes = $this->monthNameEs((int)$now->format('m'));
            $anio = $now->format('Y');

            // Armar items para la tabla (con valores por defecto)
            $items = $request->details->map(function ($d) use ($request, $mes, $anio) {
                $descripcion = "Servicio de " . ($d->service->name ?? 'Servicio') .
                    " para " . ($request->company->name ?? 'Empresa') .
                    ", durante {$mes} de {$anio}.";
                $condiciones = "Plazo de ejecución del servicio: hasta " . ($d->duration ?? '0') .
                    " " . ($d->unit->name ?? 'unidad') . ".\n"
                    . "Conformidad del servicio: La conformidad será otorgada por Secretaría General.\n"
                    . "Forma de pago: Según términos de referencia.\n"
                    . "Condiciones del servicio: Según términos de referencia.";
                return [
                    'cantidad'    => $d->quantity ?? 0,
                    'unidad'      => $d->unit->name ?? 'N/A',
                    'descripcion' => $descripcion,
                    'condiciones' => $condiciones,
                    'unitario'    => number_format($d->unit_price ?? 0, 2, '.', ','),
                    'total'       => number_format($d->subtotal ?? 0, 2, '.', ','),
                    'raw_unit'    => (float)($d->unit_price ?? 0),
                    'raw_total'   => (float)($d->subtotal ?? 0),
                ];
            });

            // Totales
            $totalGeneral = $items->sum('raw_total');
            $totalTexto = $this->montoEnLetras($totalGeneral) . " SOLES";

            // Datos para la vista Blade (con fallbacks)
            $data = [
                'titulo'            => 'Orden de servicio',
                'numero'            => $request->id,
                // OJO: DomPDF suele fallar con URLs remotas. Mantén null si da problemas.
                'logo_url'          => $request->company->img_url ?? null,
                'unidad_ejecutora'  => $request->company->name ?? 'N/A',
                'ruc_entidad'       => $request->company->ruc ?? 'N/A',
                'direccion_entidad' => $request->company->address ?? 'N/A',
                'fecha'             => compact('dia', 'mes', 'anio'),
                'proveedor'         => [
                    'name'    => $request->provider->name ?? 'N/A',
                    'ruc'     => $request->provider->ruc ?? 'N/A',
                    'address' => $request->provider->address ?? 'N/A',
                    'area'    => $request->requesting_area ?? 'N/A',
                ],
                'items'             => $items,
                'total_general'     => number_format($totalGeneral, 2, '.', ','),
                'total_texto'       => $totalTexto,
            ];

            // Render de Blade → PDF
            $pdf = Pdf::loadView('pdf.orden_servicio', $data)->setPaper('A4');

            // Descarga
            $filename = "orden_servicio_{$request->id}.pdf";
            return $pdf->download($filename);

        } catch (\Throwable $e) {
            // Log detallado para depurar en Render (usa LOG_CHANNEL=stderr o stack)
            Log::error('Error al generar PDF de orden de servicio', [
                'request_id' => $id,
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error'   => 'No se pudo generar el PDF',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function monthNameEs(int $m): string
    {
        $map = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo',
            6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre',
            10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        return $map[$m] ?? '';
    }

    // Conversor simple de monto a letras (S/ solo enteros + decimales)
    private function montoEnLetras(float $monto): string
    {
        $enteros = (int)floor($monto);
        $dec = (int)round(($monto - $enteros) * 100);
        return strtoupper($this->numeroALetras($enteros)) . " CON " . str_pad((string)$dec, 2, '0', STR_PAD_LEFT) . "/100";
    }

    // Conversión básica usando NumberFormatter
    private function numeroALetras(int $num): string
    {
        $fmt = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
        return $fmt->format($num);
    }
}
