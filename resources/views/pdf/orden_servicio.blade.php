<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de servicio</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { display: flex; justify-content: space-between; }
        .logo { height: 60px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; }
        .right { text-align: right; }
        .firmas { width: 100%; margin-top: 50px; text-align: center; }
        .firmas td { border: 1px solid #000; width: 30%; height: 120px; vertical-align: bottom; padding: 10px; }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="header">
        <div>
            <h2>ORDEN DE SERVICIO</h2>
            <p><strong>N°:</strong> {{ $numero }}</p>
            <p><strong>Unidad Ejecutora:</strong> {{ $unidad_ejecutora ?? 'N/A' }}</p>
            <p><strong>RUC:</strong> {{ $ruc_entidad ?? 'N/A' }}</p>
            <p><strong>Dirección:</strong> {{ $direccion_entidad ?? 'N/A' }}</p>
        </div>
        <div>
            {{-- Logo (comenta si da error) --}}
            {{--@if(!empty($logo_url))
                <img class="logo" src="{{ $logo_url }}" alt="Logo">
            @endif --}}
            <table class="table">
                <tr><th>Día</th><th>Mes</th><th>Año</th></tr>
                <tr>
                    <td>{{ $fecha['dia'] ?? '' }}</td>
                    <td>{{ $fecha['mes'] ?? '' }}</td>
                    <td>{{ $fecha['anio'] ?? '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Datos del proveedor --}}
    <h3>Datos del proveedor</h3>
    <table class="table">
        <tr><th>Señor(es)</th><td>{{ $proveedor['name'] ?? 'N/A' }}</td></tr>
        <tr><th>RUC N°</th><td>{{ $proveedor['ruc'] ?? 'N/A' }}</td></tr>
        <tr><th>Dirección</th><td>{{ $proveedor['address'] ?? 'N/A' }}</td></tr>
        <tr><th>Área usuaria</th><td>{{ $proveedor['area'] ?? 'N/A' }}</td></tr>
    </table>

    {{-- Detalle de servicios --}}
    <table class="table" style="margin-top:12px;">
        <thead>
            <tr>
                <th>Cant.</th>
                <th>Unidad medida</th>
                <th>Descripción y condiciones contractuales</th>
                <th class="right">Unitario</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $it)
            <tr>
                <td>{{ $it['cantidad'] }}</td>
                <td>{{ $it['unidad'] ?? 'SER' }}</td>
                <td>
                    {{ $it['descripcion'] }}<br><br>
                    <strong>Condiciones contractuales:</strong><br>
                    {!! nl2br(e($it['condiciones'])) !!}
                </td>
                <td class="right">S/. {{ $it['unitario'] }}</td>
                <td class="right">S/. {{ $it['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right"><strong>Total general</strong></td>
                <td class="right"><strong>S/. {{ $total_general ?? '0.00' }}</strong></td>
            </tr>
            <tr>
                <td colspan="5"><strong>SON:</strong> {{ $total_texto ?? '' }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Firmas --}}
    <table class="firmas">
        <tr>
            <td>Firma Área Usuaria</td>
            <td>Firma Proveedor</td>
            <td>Firma Secretaría General</td>
        </tr>
    </table>

    <p style="margin-top:16px; font-size:11px;">
        <strong>Nota importante:</strong> La orden de servicio es válida con firmas y sellos de las oficinas competentes.
    </p>

</body>
</html>
