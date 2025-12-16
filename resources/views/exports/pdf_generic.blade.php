<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        h1 { font-size: 16pt; margin-bottom: 5px; }
        .meta { color: #666; font-size: 9pt; margin-bottom: 20px; }
        .logo { float: right; height: 40px; }
    </style>
</head>
<body>
    @if(!empty($globalCompanyLogo) && file_exists(public_path($globalCompanyLogo)))
        <img src="{{ public_path($globalCompanyLogo) }}" class="logo">
    @endif
    
    <h1>{{ $globalCompanyName ?? 'Elite Waste Management' }}</h1>
    <div class="meta">{{ $title }} • Generated on {{ date('Y-m-d H:i') }}</div>

    <table>
        <thead>
            <tr>
                @foreach($columns as $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    @foreach($columns as $key => $label)
                        <td>{{ data_get($item, $key) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
