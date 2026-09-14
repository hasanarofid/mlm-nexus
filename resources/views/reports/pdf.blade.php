<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - XSELLER</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #1e293b;
            padding: 24px;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-b: 2px solid #0d131d;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .brand {
            font-size: 20px;
            font-weight: 900;
            color: #0d131d;
            text-transform: uppercase;
        }
        .sub-brand {
            font-size: 10px;
            color: #10b981;
            font-weight: 800;
        }
        .title {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }
        .date {
            font-size: 10px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #0d131d;
            color: #fff;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .bold {
            font-weight: 700;
        }
        .green {
            color: #059669;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: 800;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-rose { background: #ffe4e6; color: #9f1239; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #10b981; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Download PDF
        </button>
    </div>

    <div class="header">
        <div>
            <div class="brand">talenta52.com</div>
            <div class="sub-brand">DUTA SYNERGY BINARY SYSTEM</div>
        </div>
        <div style="text-align: right;">
            <h1 class="title">{{ $title }}</h1>
            <div class="date">Dicetak pada: {{ $date }}</div>
        </div>
    </div>

    <table>
        <thead>
            @if($type === 'member')
                <tr>
                    <th>ID & USERNAME</th>
                    <th>NAMA LENGKAP / EMAIL</th>
                    <th>SPONSOR</th>
                    <th>POIN (KIRI / KANAN)</th>
                    <th>SALDO WALLET</th>
                    <th>TGL DAFTAR</th>
                </tr>
            @elseif($type === 'bonus')
                <tr>
                    <th>NAMA MEMBER</th>
                    <th>JENIS BONUS</th>
                    <th>SUMBER MEMBER</th>
                    <th>DESKRIPSI</th>
                    <th>NOMINAL (RP)</th>
                    <th>TANGGAL</th>
                </tr>
            @elseif($type === 'pencairan')
                <tr>
                    <th>NAMA MEMBER</th>
                    <th>REKENING TUJUAN</th>
                    <th>NOMINAL (RP)</th>
                    <th>STATUS</th>
                    <th>TANGGAL</th>
                </tr>
            @elseif($type === 'topup')
                <tr>
                    <th>NAMA MEMBER</th>
                    <th>KATEGORI</th>
                    <th>DESKRIPSI</th>
                    <th>NOMINAL (RP)</th>
                    <th>TANGGAL</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @foreach($data as $row)
                @if($type === 'member')
                    <tr>
                        <td class="bold">USR{{ str_pad($row['id'], 3, '0', STR_PAD_LEFT) }} ({{ '@' . $row['username'] }})</td>
                        <td>{{ $row['name'] }}<br><small style="color:#64748b;">{{ $row['email'] ?? '-' }}</small></td>
                        <td>{{ $row['sponsor'] }}</td>
                        <td>L: {{ $row['left_count'] }} | R: {{ $row['right_count'] }}</td>
                        <td class="bold green">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @elseif($type === 'bonus')
                    <tr>
                        <td class="bold">{{ $row['name'] }} ({{ '@' . $row['username'] }})</td>
                        <td><span class="badge badge-green">{{ $row['category'] }}</span></td>
                        <td>{{ $row['source'] }}</td>
                        <td>{{ $row['description'] }}</td>
                        <td class="bold green">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @elseif($type === 'pencairan')
                    <tr>
                        <td class="bold">{{ $row['name'] }} ({{ '@' . $row['username'] }})</td>
                        <td>{{ $row['bank_name'] }} - {{ $row['bank_account_number'] }} a.n {{ $row['bank_account_name'] }}</td>
                        <td class="bold">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $row['status'] === 'APPROVED' ? 'badge-green' : ($row['status'] === 'REJECTED' ? 'badge-rose' : 'badge-amber') }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @elseif($type === 'topup')
                    <tr>
                        <td class="bold">{{ $row['name'] }} ({{ '@' . $row['username'] }})</td>
                        <td><span class="badge badge-green">{{ $row['category'] }}</span></td>
                        <td>{{ $row['description'] }}</td>
                        <td class="bold {{ $row['type'] === 'MASUK' ? 'green' : '' }}">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <script>
        window.onload = function() {
            // Auto open print dialog when opened
            window.print();
        };
    </script>
</body>
</html>
