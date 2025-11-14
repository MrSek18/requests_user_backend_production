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

            // Armar items para la tabla
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

            // Datos para la vista Blade (sin logo_url)
            $data = [
                'titulo'            => 'Orden de servicio',
                'numero'            => $request->id,
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

    // Conversor simple de monto a letras sin NumberFormatter
    private function montoEnLetras(float $monto): string
    {
        $enteros = (int)floor($monto);
        $dec = (int)round(($monto - $enteros) * 100);

        $textoEnteros = $this->numeroALetrasBasico($enteros);

        return strtoupper($textoEnteros) . " CON " . str_pad((string)$dec, 2, '0', STR_PAD_LEFT) . "/100";
    }

    // Conversión básica sin intl (soporta hasta miles)
    private function numeroALetrasBasico(int $num): string
    {
        $unidades = [
            0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro',
            5 => 'cinco', 6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve',
            10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce',
            15 => 'quince', 20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta',
            50 => 'cincuenta', 60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta',
            90 => 'noventa', 100 => 'cien'
        ];

        if ($num <= 20) {
            return $unidades[$num];
        } elseif ($num < 100) {
            $decenas = (int)floor($num / 10) * 10;
            $unidad = $num % 10;
            return $unidades[$decenas] . ($unidad > 0 ? ' y ' . $unidades[$unidad] : '');
        } elseif ($num < 1000) {
            $centenas = (int)floor($num / 100);
            $resto = $num % 100;
            $texto = $centenas == 1 ? 'ciento' : $unidades[$centenas] . 'cientos';
            return $texto . ($resto > 0 ? ' ' . $this->numeroALetrasBasico($resto) : '');
        } elseif ($num < 1000000) {
            $miles = (int)floor($num / 1000);
            $resto = $num % 1000;
            $texto = $miles == 1 ? 'mil' : $this->numeroALetrasBasico($miles) . ' mil';
            return $texto . ($resto > 0 ? ' ' . $this->numeroALetrasBasico($resto) : '');
        }

        return (string)$num; // fallback para números grandes
    }
}
