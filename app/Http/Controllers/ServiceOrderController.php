<?php

namespace App\Http\Controllers;

use App\Models\Request as ServiceRequest;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ServiceOrderController extends Controller
{
    public function download(ServiceRequest $request, Request $http)
    {

        // Carga relaciones necesarias
        $request->load(['company', 'provider', 'user', 'details.service']);

        // Fecha actual formateada
        $now = Carbon::now();
        $dia = $now->format('d');
        $mes = $this->monthNameEs((int)$now->format('m'));
        $anio = $now->format('Y');

        // Armar items para tabla
        $items = $request->details->map(function ($d) use ($request, $mes, $anio) {
            $descripcion = "Servicio de {$d->service->name} para {$request->company->name}, durante {$mes} de {$anio}.";
            $condiciones = "Plazo de ejecución del servicio: El plazo de presentación del servicio es de hasta {$d->duration_value} {$d->duration_unit}.\n"
                . "Conformidad del servicio: La conformidad será otorgada por Secretaría General, adjuntando los documentos correspondientes.\n"
                . "Forma de pago: Según términos de referencia.\n"
                . "Condiciones del servicio: Según términos de referencia.";
            return [
                'cantidad'    => $d->quantity,
                'unidad'      => 'SER',
                'descripcion' => $descripcion,
                'condiciones' => $condiciones,
                'unitario'    => number_format($d->unit_price, 2, '.', ','),
                'total'       => number_format($d->subtotal, 2, '.', ','),
                'raw_unit'    => (float)$d->unit_price,
                'raw_total'   => (float)$d->subtotal,
            ];
        });

        // Totales
        $totalGeneral = $items->sum('raw_total');
        $totalTexto = $this->montoEnLetras($totalGeneral) . " SOLES";

        // Datos del encabezado y proveedor
        $data = [
            'titulo'           => 'Orden de servicio',
            'numero'           => $request->id, // request_id
            'logo_url'         => $request->company->img_url ?? null,
            'unidad_ejecutora' => $request->company->name,
            'ruc_entidad'      => $request->company->ruc,
            'direccion_entidad' => $request->company->address,
            'fecha'            => compact('dia', 'mes', 'anio'),
            'proveedor'        => [
                'name'     => $request->provider->name,
                'ruc'      => $request->provider->ruc,
                'address'  => $request->provider->address,
                'area'     => $request->requesting_area, // área usuaria
            ],
            'items'            => $items,
            'total_general'    => number_format($totalGeneral, 2, '.', ','),
            'total_texto'      => $totalTexto,
        ];

        // Render de Blade → PDF
        $pdf = Pdf::loadView('pdf.orden_servicio', $data)->setPaper('A4');

        // Descarga
        $filename = "orden_servicio_{$request->id}.pdf";
        return $pdf->download($filename);
    }

    
    private function monthNameEs(int $m): string
    {
        $map = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
        return $map[$m] ?? '';
    }

    // Conversor simple de monto a letras (S/ solo enteros + decimales)
    private function montoEnLetras(float $monto): string
    {
        $enteros = floor($monto);
        $dec = round(($monto - $enteros) * 100);
        return strtoupper($this->numeroALetras($enteros)) . " CON " . str_pad((string)$dec, 2, '0', STR_PAD_LEFT) . "/100";
    }

    // Conversión básica (puedes reemplazar por una librería si prefieres)
    private function numeroALetras(int $num): string
    {
        // Simplificado: usa una librería si necesitas casos grandes
        $fmt = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
        return $fmt->format($num);
    }
}
