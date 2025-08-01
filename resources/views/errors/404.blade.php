<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemukan - Smart Village Bayan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            opacity: 0.3;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .error-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .back-button:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .decorative-element {
            position: absolute;
            opacity: 0.1;
            pointer-events: none;
        }
        
        .circle-1 {
            top: 10%;
            left: 10%;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
        }
        
        .circle-2 {
            top: 70%;
            right: 10%;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
        }
        
        .triangle {
            bottom: 20%;
            left: 15%;
            width: 0;
            height: 0;
            border-left: 50px solid transparent;
            border-right: 50px solid transparent;
            border-bottom: 86px solid white;
        }
    </style>
</head>
<body>
    <div class="decorative-element circle-1"></div>
    <div class="decorative-element circle-2"></div>
    <div class="decorative-element triangle"></div>
    
    <div class="container">
        <div class="error-code">404</div>
        <h1 class="error-title">
            @if(isset($message) && $message === 'Village not found')
                Desa Tidak Ditemukan
            @else
                Halaman Tidak Ditemukan
            @endif
        </h1>
        <p class="error-message">
            @if(isset($message) && $message === 'Village not found')
                Maaf, subdomain desa yang Anda cari tidak tersedia di sistem Smart Village Bayan. 
                Pastikan alamat website yang Anda masukkan sudah benar.
            @else
                Maaf, halaman yang Anda cari tidak dapat ditemukan. 
                Halaman mungkin telah dipindahkan atau tidak tersedia.
            @endif
        </p>
        <a href="{{ url('/') }}" class="back-button">
            ← Kembali ke Beranda Utama
        </a>
    </div>
</body>
</html>
