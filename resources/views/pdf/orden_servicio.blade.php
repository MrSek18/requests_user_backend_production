<h1>Orden de Servicio #{{ $numero }}</h1>
<p>Empresa: {{ $unidad_ejecutora ?? 'N/A' }}</p>
<p>Proveedor: {{ $proveedor['name'] ?? 'N/A' }}</p>
<p>Total: {{ $total_general ?? '0.00' }}</p>
