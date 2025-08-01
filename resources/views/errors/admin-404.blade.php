<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Admin Tidak Ditemukan - Smart Village Bayan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }
        
        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }
        
        .admin-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.7;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            opacity: 0.4;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            background: linear-gradient(45deg, #f59e0b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .error-title {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .error-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
            color: #cbd5e1;
        }
        
        .warning-box {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #fecaca;
        }
        
        .warning-box strong {
            color: #fca5a5;
        }
        
        .buttons-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            border: 2px solid rgba(59, 130, 246, 0.5);
            transition: all 0.3s ease;
            font-weight: 500;
            min-width: 140px;
        }
        
        .back-button:hover {
            background: linear-gradient(45deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }
        
        .login-button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(45deg, #059669, #047857);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            border: 2px solid rgba(5, 150, 105, 0.5);
            transition: all 0.3s ease;
            font-weight: 500;
            min-width: 140px;
        }
        
        .login-button:hover {
            background: linear-gradient(45deg, #047857, #065f46);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
        }
        
        .decorative-element {
            position: absolute;
            opacity: 0.05;
            pointer-events: none;
        }
        
        .gear-1 {
            top: 15%;
            left: 15%;
            font-size: 3rem;
            animation: rotate 20s linear infinite;
        }
        
        .gear-2 {
            top: 20%;
            right: 20%;
            font-size: 2rem;
            animation: rotate 15s linear infinite reverse;
        }
        
        .gear-3 {
            bottom: 25%;
            left: 20%;
            font-size: 2.5rem;
            animation: rotate 25s linear infinite;
        }
        
        .shield {
            bottom: 20%;
            right: 15%;
            font-size: 3rem;
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.05; transform: scale(1); }
            50% { opacity: 0.1; transform: scale(1.1); }
        }
        
        .floating-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 10s linear infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating particles -->
    <div class="floating-particles">
        <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
        <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
        <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
        <div class="particle" style="left: 40%; animation-delay: 1s;"></div>
        <div class="particle" style="left: 50%; animation-delay: 3s;"></div>
        <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
        <div class="particle" style="left: 70%; animation-delay: 2.5s;"></div>
        <div class="particle" style="left: 80%; animation-delay: 4.5s;"></div>
        <div class="particle" style="left: 90%; animation-delay: 1.5s;"></div>
    </div>

    <!-- Decorative elements -->
    <div class="decorative-element gear-1">⚙️</div>
    <div class="decorative-element gear-2">⚙️</div>
    <div class="decorative-element gear-3">⚙️</div>
    <div class="decorative-element shield">🛡️</div>
    
    <div class="container">
        <div class="admin-icon">🔐</div>
        <div class="error-code">404</div>
        <h1 class="error-title">Area Admin Tidak Ditemukan</h1>
        <p class="error-message">
            Halaman admin yang Anda cari tidak tersedia atau telah dipindahkan.
            Pastikan Anda memiliki akses yang sesuai dan URL yang benar.
        </p>
        
        <div class="warning-box">
            <strong>⚠️ Catatan Keamanan:</strong><br>
            Akses ke area admin memerlukan otentikasi yang valid. 
            Jika Anda adalah administrator yang sah, silakan login terlebih dahulu.
        </div>
        
        <div class="buttons-container">
            <a href="{{ url('/admin') }}" class="login-button">
                🔑 Login Admin
            </a>
            <a href="https://kecamatanbayan.id" class="back-button">
                ← Beranda Utama
            </a>
        </div>
    </div>

    <script>
        // Add some interactive particles on mouse move
        document.addEventListener('mousemove', function(e) {
            if (Math.random() > 0.95) {
                const particle = document.createElement('div');
                particle.style.position = 'absolute';
                particle.style.left = e.clientX + 'px';
                particle.style.top = e.clientY + 'px';
                particle.style.width = '2px';
                particle.style.height = '2px';
                particle.style.background = 'rgba(59, 130, 246, 0.5)';
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.zIndex = '5';
                document.body.appendChild(particle);
                
                setTimeout(() => {
                    particle.style.transition = 'all 1s ease-out';
                    particle.style.transform = 'scale(10)';
                    particle.style.opacity = '0';
                }, 10);
                
                setTimeout(() => {
                    document.body.removeChild(particle);
                }, 1000);
            }
        });
    </script>
</body>
</html>