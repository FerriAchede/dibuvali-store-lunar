<html>
<head>
    <title>Resumen del Pedido</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        td, th { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        caption {
            font-weight: bold;
            margin-bottom: 0.5em;
        }
    </style>
</head>
<body>
    <h1>Pedido #{{ $order->reference ?? 'N/A' }}</h1>
    <p>Fecha: {{ $order->placed_at ? \Carbon\Carbon::parse($order->placed_at)->format('d/m/Y') : 'N/A' }}</p>

    <h3>Productos</h3>

    @if ($order->lines && $order->lines->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio unitario</th>
                    <th>Precio total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->lines as $line)
                    <tr>
                        <td>{{ $line->description ?? 'Producto' }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td>
                            €{{ number_format(($line->unit_price->value ?? 0) / 100, 2) }}
                        </td>
                        <td>
                            €{{ number_format(($line->total->value ?? 0) / 100, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No hay productos en este pedido.</p>
    @endif

    <p><strong>Total: </strong> €{{ number_format(($order->total->value ?? 0) / 100, 2) }}</p>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura - Pedido #{{ $order->reference }}</title>
    <style>
        body { font-family: sans-serif; color: #333; padding: 2em; }
        h1, h2, h3 { margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .section { margin-top: 2em; }
        .address-box { border: 1px solid #ccc; padding: 1em; margin-top: 0.5em; }
        .totals td { text-align: right; }
        .totals th { text-align: right; background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Factura</h1>
    <p><strong>Pedido:</strong> #{{ $order->reference }}</p>
    <p><strong>Fecha:</strong> {{ $order->created_at?->format('d/m/Y') ?? 'N/A' }}</p>

    <div class="section">
        <h3>Datos del cliente</h3>
        @php
            $billing = $order->addresses->firstWhere('type', 'billing');
            $shipping = $order->addresses->firstWhere('type', 'shipping');
        @endphp
        <div style="display: flex; gap: 2em;">
{{--             <div>
                <strong>Facturación:</strong>
                <div class="address-box">
                    {{ $billing?->first_name }} {{ $billing?->last_name }}<br>
                    {{ $billing?->line_one }}<br>
                    {{ $billing?->postcode }} {{ $billing?->city }}<br>
                    {{ $billing?->country->name ?? '' }}<br>
                    Email: {{ $billing?->contact_email }}<br>
                    Tel: {{ $billing?->contact_phone }}
                </div>
            </div> --}}

            @if ($shipping)
            <div>
                <strong>Envío:</strong>
                <div class="address-box">
                    {{ $shipping?->first_name }} {{ $shipping?->last_name }}<br>
                    {{ $shipping?->line_one }}<br>
                    {{ $shipping?->postcode }} {{ $shipping?->city }}<br>
                    {{ $shipping?->country->name ?? '' }}<br>
                    Email: {{ $shipping?->contact_email }}<br>
                    Tel: {{ $shipping?->contact_phone }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <h3>Productos</h3>
        @if ($order->lines && $order->lines->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->lines as $line)
                        <tr>
                            <td>{{ $line->description ?? 'Producto' }}</td>
                            <td>{{ $line->quantity }}</td>
                            <td>{{ $line->unit_price->value / 100 }} €</td>
                            <td>{{ $line->total->value / 100 }} €</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay productos en este pedido.</p>
        @endif
    </div>

{{--     @foreach ($order->lines as $line)
    <tr>
        <td>{{ $line->description ?? 'Producto' }}</td>
        <td>{{ $line->quantity }}</td>
        <td>
            €{{ number_format(($line->unit_price->value ?? 0) / 100, 2) }}
        </td>
        <td>
            €{{ number_format(($line->total->value ?? 0) / 100, 2) }}
        </td>
    </tr>
@endforeach --}}


    <div class="section">
        <h3>Resumen</h3>
        <table class="totals">
            <tr>
                <th>Subtotal:</th>
                <td>{{ number_format($order->sub_total->value / 100, 2) }} €</td>
            </tr>
            <tr>
                <th>Envío:</th>
                <td>{{ number_format($order->shipping_total->value / 100, 2) }} €</td>
            </tr>
            <tr>
                <th>Impuestos:</th>
                <td>{{ number_format($order->tax_total->value / 100, 2) }} €</td>
            </tr>
            <tr>
                <th>Total:</th>
                <td><strong>{{ number_format($order->total->value / 100, 2) }} €</strong></td>
            </tr>
        </table>
    </div>

    <div class="section">
        {{-- <p><strong>Estado del pedido:</strong> {{ ucfirst(str_replace('-', ' ', $order->status)) }}</p> --}}
        <p><strong>Método de pago:</strong> {{ $order->status === 'payment-offline' ? 'Pago manual / transferencia' : 'Pago online' }}</p>
    </div>

    <div class="section" style="font-size: 0.9em; color: #666;">
        <p><strong>Contacto:</strong></p>
        <p>
            Email: fercarmar2@alu.edu.gva.es
        </p>
    </div>
</body>
</html>

{{--  <h1>It's on the way!</h1>

<p>Your order with reference {{ $order->reference }} has been dispatched!</p>

<p>{{ $order->total->formatted() }}</p>

@if($content ?? null)
    <h2>Additional notes</h2>
    <p>{{ $content }}</p>
@endif

<ul>
@foreach($order->lines as $line)
    <li>{{ $line->product }} x {{ $line->quantity }}</li>
@endforeach
</ul> --}}