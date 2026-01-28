<!DOCTYPE html>
<html>
<head>
    <title>Структура таблиц</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        table { border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; }
        th { background: #f0f0f0; }
        .exists { color: green; }
        .missing { color: red; }
    </style>
</head>
<body>
    <h1>Структура таблиц базы данных</h1>
    @foreach($structure as $tableName => $columns)
        <h2 class="exists">Таблица: {{ $tableName }}</h2>
        <table>
            <thead><tr><th>Поле</th><th>Тип</th><th>Null</th><th>Ключ</th><th>По умолчанию</th></tr></thead>
            <tbody>
                @foreach($columns as $col)
                <tr>
                    <td><strong>{{ $col->Field }}</strong></td>
                    <td>{{ $col->Type }}</td>
                    <td>{{ $col->Null }}</td>
                    <td>{{ $col->Key }}</td>
                    <td>{{ $col->Default ?? 'NULL' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>