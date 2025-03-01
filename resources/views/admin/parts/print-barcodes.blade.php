<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcodes - {{ $part->name }}</title>
    
    <!-- Use Tailwind CDN instead of app.css -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Include JsBarcode library for client-side barcode generation -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    
    <style>
        @page {
            margin: 0.5cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        
        .barcode-container {
            page-break-inside: avoid;
            display: inline-block;
            border: 1px dashed #ccc;
            margin: 0.2cm;
            padding: 0.3cm;
            min-width: 5.5cm;
            max-width: 7cm;
            min-height: 2.5cm;
            text-align: center;
        }
        
        .barcode-title {
            font-size: 9px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .barcode-number {
            font-size: 11px;
            margin-top: 2px;
            font-weight: bold;
        }
        
        .barcode-image {
            height: 1.5cm;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .print-container {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="no-print bg-gray-100 p-4 mb-4 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold">Barcode Labels - {{ $part->name }}</h1>
            <p class="text-gray-600">Part #: {{ $part->part_number }} - Printing {{ count($serialInstances) }} labels</p>
        </div>
        <div>
            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                Print
            </button>
            <a href="{{ route('admin.parts.show', $part) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Part
            </a>
        </div>
    </div>

    <div class="print-container">
        @foreach($serialInstances as $instance)
            <div class="barcode-container">
                <div class="barcode-title">{{ $part->name }}</div>
                <svg class="barcode-image" id="barcode-{{ $instance->id }}"></svg>
                <div class="barcode-number">{{ $instance->serial_number }}</div>
            </div>
        @endforeach
    </div>

    <script>
        // Generate barcodes using JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            @foreach($serialInstances as $instance)
                JsBarcode("#barcode-{{ $instance->id }}", "{{ $instance->serial_number }}", {
                    format: "CODE128",
                    width: 2,
                    height: 40,
                    displayValue: false
                });
            @endforeach
        });
        
        // Auto-print when the page loads (optional)
        window.onload = function() {
            // Uncomment the line below to enable auto-print
            // window.print();
        };
    </script>
</body>
</html>