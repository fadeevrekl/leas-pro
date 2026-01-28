<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График платежей по договору {{ $deal->deal_number }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        
        body {
            font-family: 'Times-Roman', serif;
            font-size: 12pt;
            line-height: 1.5;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20mm;
            border-bottom: 1pt solid #000;
            padding-bottom: 5mm;
        }
        
        .header h1 {
            font-size: 14pt;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            font-size: 12pt;
            margin-top: 3mm;
            font-weight: bold;
        }
        
        .document-info {
            margin-bottom: 10mm;
        }
        
        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .document-info td {
            padding: 2mm;
            vertical-align: top;
        }
        
        .document-info .label {
            font-weight: bold;
            width: 40%;
        }
        
        .parties-table {
            width: 100%;
            border-collapse: collapse;
            border: 1pt solid #000;
            margin-bottom: 15mm;
        }
        
        .parties-table th, .parties-table td {
            padding: 3mm;
            border: 1pt solid #000;
            vertical-align: top;
        }
        
        .parties-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1pt solid #000;
            margin-top: 10mm;
            margin-bottom: 10mm;
        }
        
        .payment-table th {
            background-color: #f0f0f0;
            color: #000;
            padding: 4mm;
            text-align: center;
            border: 1pt solid #000;
            font-weight: bold;
            font-size: 10pt;
        }
        
        .payment-table td {
            padding: 3mm;
            border: 1pt solid #000;
            text-align: center;
            font-size: 10pt;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .footer {
            margin-top: 10mm;
            font-size: 10pt;
        }
        
        .signatures {
            margin-top: 25mm;
        }
        
        .signature-block {
            width: 45%;
            display: inline-block;
            vertical-align: top;
        }
        
        .signature-line {
            border-top: 1pt solid #000;
            margin-top: 15mm;
            padding-top: 2mm;
            text-align: center;
        }
        
        .page-number {
            position: fixed;
            bottom: 15mm;
            right: 20mm;
            font-size: 10pt;
        }
        
        .section-title {
            font-weight: bold;
            margin: 5mm 0 3mm 0;
            font-size: 12pt;
        }
    </style>
</head>
<body>
    <!-- Заголовок -->
    <div class="header">
        <h1>Приложение №2</h1>
        <div class="subtitle">к договору лизинга № {{ $deal->deal_number }}</div>
        <div class="subtitle">от {{ $deal->contract_signed_date ? $deal->contract_signed_date->format('d.m.Y') : $deal->created_at->format('d.m.Y') }}</div>
    </div>



    <!-- Объект лизинга -->
    <div class="section-title">Объект лизинга:</div>
    <div class="document-info">
        Автомобиль {{ $deal->car->brand }} {{ $deal->car->model }}, {{ $deal->car->year }} г.в.,<br>
        VIN: {{ $deal->car->vin }}, Гос. номер: {{ $deal->car->license_plate }},<br>
        Цвет: {{ $deal->car->color }}
    </div>

    <!-- Таблица платежей -->
    <div class="section-title">График платежей:</div>
    <table class="payment-table">
        <thead>
            <tr>
                <th width="10%">№ п/п</th>
                <th width="40%">Наименование платежа</th>
                <th width="25%">Сумма платежа (руб.)</th>
                <th width="25%">Срок оплаты (дата)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Функция для получения месяца в родительном падеже (правильный падеж для "за январь")
                function getMonthGenitive($monthNumber) {
                    $months = [
                        1 => 'январь', 2 => 'февраль', 3 => 'март', 4 => 'апрель',
                        5 => 'май', 6 => 'июнь', 7 => 'июль', 8 => 'август',
                        9 => 'сентябрь', 10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь'
                    ];
                    
                    return $months[$monthNumber] ?? '';
                }
                
                $totalPaid = 0;
                $rowNumber = 1;
                $paymentCounter = 0;
            @endphp

            <!-- Первоначальный взнос -->
            @if($deal->initial_payment > 0)
                @php
                    $paymentCounter++;
                @endphp
                <tr>
                    <td>{{ $rowNumber++ }}</td>
                    <td><strong>Первоначальный взнос</strong></td>
                    <td>{{ number_format($deal->initial_payment, 2, ',', ' ') }}</td>
                    <td>{{ $deal->start_date->format('d.m.Y') }}</td>
                </tr>
            @endif

            <!-- Регулярные платежи -->
            @foreach($deal->payments->where('payment_number', '>', 0)->sortBy('payment_number') as $payment)
                @php
                    $paymentCounter++;
                    
                    // Используем функцию для правильного падежа
                    $monthName = getMonthGenitive($payment->due_date->month);
                    $paymentName = 'Регулярный платеж';
                    
                    if ($deal->payment_period === 'month') {
                        // Теперь будет: "Платеж за январь 2026 г." (правильный падеж!)
                        $paymentName = 'Платеж за ' . $monthName . ' ' . $payment->due_date->format('Y') . ' г.';
                    } elseif ($deal->payment_period === 'week') {
                        $startWeek = $payment->due_date->copy()->subDays(6);
                        $paymentName = 'Платеж за неделю ' . $startWeek->format('d.m') . '-' . $payment->due_date->format('d.m.Y');
                    } else {
                        $paymentName = 'Платеж за ' . $payment->due_date->format('d.m.Y');
                    }
                @endphp
                <tr>
                    <td>{{ $rowNumber++ }}</td>
                    <td>{{ $paymentName }}</td>
                    <td>{{ number_format($payment->amount, 2, ',', ' ') }}</td>
                    <td>{{ $payment->due_date->format('d.m.Y') }}</td>
                </tr>
            @endforeach

            <!-- Итоговая строка -->
            <tr class="total-row">
                <td colspan="2" style="text-align: right;"><strong>ВСЕГО:</strong></td>
                <td><strong>{{ number_format($deal->total_amount, 2, ',', ' ') }}</strong></td>
                <td></td>
            </tr>
            @if($deal->initial_payment > 0)
            <tr>
                <td colspan="2" style="text-align: right; font-size: 9pt;">в том числе:</td>
                <td colspan="2" style="font-size: 9pt;">
                    • первоначальный взнос: {{ number_format($deal->initial_payment, 2, ',', ' ') }}<br>
                    • регулярные платежи: {{ number_format($deal->total_amount - $deal->initial_payment, 2, ',', ' ') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Подписи сторон -->
<div class="signatures">
<div class="signature-block">
    <div class="signature-line">
        <strong>Лизингодатель</strong><br>
        {{ $deal->car->investor->name ?? 'Инвестор' }}<br>
        ____________________ / {{ $deal->car->investor->name ?? 'Инвестор' }} /
    </div>
</div>
    
    <div class="signature-block" style="float: right;">
        <div class="signature-line">
            <strong>Лизингополучатель</strong><br>
            {{ $deal->client->full_name }}<br>
            ____________________ / {{ $deal->client->last_name }} {{ mb_substr($deal->client->first_name, 0, 1) }}.{{ mb_substr($deal->client->middle_name, 0, 1) }}. /
        </div>
    </div>
    
    <div style="clear: both;"></div>
</div>


</body>
</html>