<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode - {{ $part->name }}</title>

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
            <p class="text-gray-600">
                Part #: {{ $part->part_number }} - Printing {{ $quantity }} {{ $quantity === 1 ? 'label' : 'labels' }}
            </p>
        </div>
        <div class="flex items-center">
            {{-- Reprint with a different count without going back to the list. --}}
            <form method="GET" class="inline-flex items-center mr-2">
                <label for="quantity" class="text-gray-600 mr-2">Labels:</label>
                <input type="number" id="quantity" name="quantity" value="{{ $quantity }}" min="1" max="200"
                       class="border border-gray-300 rounded px-2 py-1 w-20 mr-2">
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Update
                </button>
            </form>

            <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                Print
            </button>
            <a href="{{ route('admin.parts.show', $part) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Part
            </a>
        </div>
    </div>

    <div class="print-container">
        @for($i = 1; $i <= $quantity; $i++)
            <div class="barcode-container">
                <div class="barcode-title">{{ $part->name }}</div>
                <svg class="barcode-image" id="barcode-{{ $i }}"></svg>
                <div class="barcode-number">{{ $part->part_number }}</div>
            </div>
        @endfor
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Every label encodes the part number: the units are interchangeable.
            const partNumber = @json($part->part_number);

            for (let i = 1; i <= {{ $quantity }}; i++) {
                JsBarcode('#barcode-' + i, partNumber, {
                    format: "CODE128",
                    width: 2,
                    height: 40,
                    displayValue: false
                });
            }
        });
    </script>
</body>
</html>
