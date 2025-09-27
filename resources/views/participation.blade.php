<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Participação</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 20mm;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #10b981 100%);
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .certificate {
            background: #f8fafc;
            width: 210mm;
            min-height: 148mm;
            border: 8px solid #10b981;
            border-radius: 12px;
            padding: 40px;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            flex-shrink: 0;
        }

        .title {
            color: #1e40af;
            font-size: 32px;
            font-weight: 700;
            line-height: 1.2;
        }

        .logo {
            font-size: 42px;
            font-weight: 900;
            color: #3b82f6;
            letter-spacing: -2px;
        }

        .logo .sinf {
            background: linear-gradient(135deg, #3b82f6, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            color: #3b82f6;
            font-size: 14px;
            font-weight: 500;
            margin-top: 5px;
        }

        .participant-name {
            font-size: 36px;
            font-weight: 600;
            color: #1f2937;
            margin: 20px 0 30px 0;
            text-align: left;
            flex-shrink: 0;
        }

        .content {
            color: #374151;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 40px;
            flex-grow: 1;
        }

        .highlight {
            font-style: italic;
            font-weight: 600;
        }

        .signatures-container {
            margin-top: auto;
            flex-shrink: 0;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 10px;
        }

        .signature {
            text-align: center;
            width: 200px;
        }

        .signature-line {
            border-top: 2px solid #6b7280;
            margin-bottom: 8px;
        }

        .signature-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .signature-title {
            color: #6b7280;
            font-size: 12px;
            text-align: center;
        }

        .decorative-border {
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="decorative-border"></div>
        
        <div class="header">
            <div>
                <div class="title">
                    Certificado de<br>
                    Participação
                </div>
            </div>
            <div>
                <img class="logo" src="{{  public_path('images/sinf_logo.png') }}" alt="Stylized SINF logo">
                <div class="logo">
                    <span class="sinf">SINF</span>
                </div>
                <div class="subtitle">semana_de_informática 2025</div>
            </div>
        </div>

        <div class="participant-name">
            {{ $participant_name }},
        </div>

        <div class="content">
            participou em "<span class="highlight">{{$event_type}}: {{ $event_title }}</span>" no dia {{ $date }}, pelas {{ $time }} horas, na 8ª edição da <span class="highlight">Semana de Informática</span>, organizada pelo <span class="highlight">NIAEFEUP</span>, realizada na Faculdade de Engenharia da Universidade do Porto entre os dias {{ $event_date }}
        </div>

        <div class="signatures-container">
            <div class="signatures">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $coordinators['diogo'] }}</div>
                </div>
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $coordinators['eduarda'] }}</div>
                </div>
            </div>
            <div class="signature-title">
                Coordenadores Gerais da SINF 2025
            </div>
        </div>
    </div>
</body>
</html>