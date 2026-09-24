<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - NEXUS COMMUNITY</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #0f172a;
            padding: 24px;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #D4AF37;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .brand {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .sub-brand {
            font-size: 9px;
            color: #b8922e;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .title {
            font-size: 15px;
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
            background: #0f172a;
            color: #D4AF37;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        td {
            padding: 7px 10px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background-color: #fafbfc;
        }
        .bold {
            font-weight: 700;
        }
        .green {
            color: #059669;
            font-weight: 800;
        }
        .gold {
            color: #b8922e;
            font-weight: 800;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 800;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-rose { background: #ffe4e6; color: #9f1239; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-gold { background: #faf6eb; color: #b8922e; border: 1px solid rgba(212, 175, 55, 0.4); }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0f172a; color: #D4AF37; border: 1px solid #D4AF37; border-radius: 8px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="header">
        <div>
            <div class="brand">PT. NEXUS KOMUNITAS BERSAMA</div>
            <div class="sub-brand">NEXUS COMMUNITY MEMBER SYSTEM</div>
            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                User: <strong>{{ $currentUser->name }}</strong> (@{{ $currentUser->username }})
            </div>
        </div>
        <div style="text-align: right;">
            <h1 class="title">{{ $title }}</h1>
            <div class="date">Waktu Cetak: {{ $date }}</div>
        </div>
    </div>

    <table>
        <thead>
            @if($type === 'team')
                <tr>
                    <th>NO</th>
                    <th>NAMA MITRA & USERNAME</th>
                    <th>NO. WHATSAPP</th>
                    <th>GENERASI</th>
                    <th>SPONSOR LANGSUNG</th>
                    <th>MEMBERSHIP</th>
                    <th>TGL DAFTAR</th>
                </tr>
            @elseif($type === 'bonus')
                <tr>
                    <th>KODE</th>
                    <th>NAMA MEMBER</th>
                    <th>JENIS BONUS</th>
                    <th>SUMBER MITRA</th>
                    <th>DESKRIPSI</th>
                    <th>NOMINAL (RP)</th>
                    <th>TANGGAL</th>
                </tr>
            @elseif($type === 'withdrawal')
                <tr>
                    <th>ID WD</th>
                    <th>NAMA MEMBER</th>
                    <th>REKENING TUJUAN</th>
                    <th>NOMINAL WD (RP)</th>
                    <th>STATUS</th>
                    <th>TGL PENGAJUAN</th>
                </tr>
            @elseif($type === 'mutasi')
                <tr>
                    <th>KODE</th>
                    <th>NAMA MEMBER</th>
                    <th>KATEGORI</th>
                    <th>DESKRIPSI</th>
                    <th>NOMINAL (RP)</th>
                    <th>TANGGAL</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                @if($type === 'team')
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="bold">
                            {{ $row['name'] }}<br>
                            <span style="color:#64748b;font-weight:normal;">@{{ $row['username'] }}</span>
                        </td>
                        <td>{{ $row['phone'] }}</td>
                        <td><span class="badge badge-gold">{{ $row['generation'] }}</span></td>
                        <td>{{ $row['sponsor'] }}</td>
                        <td><strong>{{ $row['tier'] }}</strong></td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @elseif($type === 'bonus')
                    <tr>
                        <td class="bold">{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }} (@{{ $row['username'] }})</td>
                        <td><span class="badge badge-green">{{ $row['category'] }}</span></td>
                        <td><strong>{{ $row['source'] }}</strong></td>
                        <td>{{ $row['description'] }}</td>
                        <td class="bold green">+Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @elseif($type === 'withdrawal')
                    <tr>
                        <td class="bold">{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }} (@{{ $row['username'] }})</td>
                        <td>{{ $row['bank_name'] }} - {{ $row['bank_account_number'] }} a.n {{ $row['bank_account_name'] }}</td>
                        <td class="bold">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $row['status'] === 'APPROVED' ? 'badge-green' : ($row['status'] === 'REJECTED' ? 'badge-rose' : 'badge-amber') }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @elseif($type === 'mutasi')
                    <tr>
                        <td class="bold">{{ $row['code'] }}</td>
                        <td>{{ $row['name'] }} (@{{ $row['username'] }})</td>
                        <td><span class="badge badge-gold">{{ $row['category'] }}</span></td>
                        <td>{{ $row['description'] }}</td>
                        <td class="bold {{ $row['type'] === 'MASUK' ? 'green' : '' }}">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                        <td>{{ $row['created_at'] }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:24px;color:#94a3b8;font-style:italic;">
                        Belum ada data tersedia pada laporan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>

