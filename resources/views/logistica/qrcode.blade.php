<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir QR Code - {{ $tracking->qrcode_token }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #1e293b;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .ticket {
            border: 2px dashed #cbd5e1;
            padding: 2.5rem;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .ticket-title {
            font-size: 1.25rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        .ticket-subtitle {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 2rem;
        }
        .qrcode-container {
            margin: 1.5rem 0;
        }
        .qrcode-image {
            width: 200px;
            height: 200px;
            border: 1px solid #e2e8f0;
            padding: 0.5rem;
            border-radius: 8px;
        }
        .info-grid {
            margin-top: 2rem;
            text-align: left;
            font-size: 0.875rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1.5rem;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            font-weight: 600;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 2rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        
        @media print {
            body {
                padding: 0;
                min-height: auto;
            }
            .ticket {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="ticket">
        <h1 class="ticket-title">Identificação de Operação</h1>
        <p class="ticket-subtitle">Cole este QR Code no volume correspondente</p>

        <div class="qrcode-container">
            <img class="qrcode-image" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $tracking->qrcode_token }}" alt="QR Code Rastreamento">
        </div>

        <div class="info-grid">
            <div class="info-row">
                <span class="info-label">Tipo:</span>
                <span class="info-value" style="text-transform: uppercase;">{{ $tracking->type }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nº Pedido:</span>
                <span class="info-value">{{ $tracking->order_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Transporte:</span>
                <span class="info-value" style="text-transform: capitalize;">{{ $tracking->transport_type ?? 'A definir' }}</span>
            </div>
            <div class="info-row" style="margin-bottom: 0;">
                <span class="info-label">Código QR:</span>
                <span class="info-value" style="font-family: monospace;">{{ $tracking->qrcode_token }}</span>
            </div>
        </div>
    </div>

    <button class="btn-print" onclick="window.print()">
        <svg style="width:16px;height:16px;vertical-align:middle;margin-right:0.5rem;fill:currentColor" viewBox="0 0 24 24"><path d="M18 3H6v4h12M6 10.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3m-3 11H8v-5h8z"/></svg>
        Imprimir Etiqueta
    </button>

</body>
</html>
