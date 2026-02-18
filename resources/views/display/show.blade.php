<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian - SiAntre</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow: hidden;
        }

        .display-container {
            height: 100vh;
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        .header-section {
            text-align: center;
            padding: 1rem 0 1.5rem;
            color: white;
        }

        .main-title {
            font-size: clamp(1.5rem, 3vw, 2.5rem);
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: clamp(0.9rem, 1.5vw, 1.1rem);
            opacity: 0.9;
        }

        /* Grid Layout - Dynamic based on service count */
        .services-grid {
            display: grid;
            gap: 1rem;
            flex: 1;
            min-height: 0;
        }

        /* Single service - Full screen */
        .services-grid.mode-single {
            grid-template-columns: 1fr;
            grid-template-rows: 1fr;
        }

        /* 2 services - Side by side */
        .services-grid.mode-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        /* 3-4 services - 2x2 grid */
        .services-grid.mode-4 {
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }

        /* 5-6 services - 2x3 or 3x2 */
        .services-grid.mode-6 {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(2, 1fr);
        }

        /* 7-9 services - 3x3 grid */
        .services-grid.mode-9 {
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
        }

        /* 10+ services - 4x3 grid */
        .services-grid.mode-12 {
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(3, 1fr);
        }

        /* Service Card */
        .service-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1rem;
            padding: clamp(0.75rem, 1.5vw, 1.5rem);
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            min-height: 0;
        }

        .service-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
        }

        .service-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .service-icon {
            width: clamp(2.5rem, 4vw, 3.5rem);
            height: clamp(2.5rem, 4vw, 3.5rem);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: clamp(1rem, 2vw, 1.5rem);
            flex-shrink: 0;
        }

        .service-name {
            font-size: clamp(1rem, 1.8vw, 1.5rem);
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
            flex: 1;
        }

        .service-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            min-height: 0;
        }

        .queue-info {
            text-align: center;
            width: 100%;
        }

        .queue-label {
            font-size: clamp(0.75rem, 1.2vw, 1rem);
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .queue-number {
            font-size: clamp(2rem, 6vw, 5rem);
            font-weight: 900;
            color: #1f2937;
            line-height: 1;
            padding: clamp(0.5rem, 2vw, 1rem) clamp(1rem, 3vw, 2rem);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .waiting-info {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #fef3c7;
            border-radius: 2rem;
            margin-top: 0.5rem;
        }

        .waiting-count {
            font-size: clamp(1.25rem, 2.5vw, 2rem);
            font-weight: 700;
            color: #92400e;
        }

        .waiting-label {
            font-size: clamp(0.625rem, 1vw, 0.75rem);
            color: #78350f;
            text-transform: uppercase;
        }

        .no-queue {
            color: #9ca3af;
            font-size: clamp(1.5rem, 4vw, 3rem);
            font-weight: 700;
        }

        /* Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Refresh indicator */
        .refresh-indicator {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Back button */
        .back-button {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: #4b5563;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
            z-index: 100;
        }

        .back-button:hover {
            background: white;
            transform: translateX(-2px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .services-grid.mode-2,
            .services-grid.mode-4,
            .services-grid.mode-6,
            .services-grid.mode-9,
            .services-grid.mode-12 {
                grid-template-columns: 1fr;
                grid-template-rows: auto;
            }

            .display-container {
                overflow-y: auto;
                height: auto;
                min-height: 100vh;
            }

            body {
                overflow: auto;
            }
        }
    </style>
</head>
<body>
    <a href="{{ route('display.select') }}" class="back-button">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Pilih Display
    </a>

    <div class="display-container">
        <div class="header-section">
            <h1 class="main-title">SISTEM ANTRIAN ONLINE (SiAntre)</h1>
            <p class="subtitle">Silahkan menuju loket pendaftaran untuk mendapatkan nomor antrian</p>
        </div>

        <livewire:multi-service-display 
            :services="$selectedServices" 
            :display-mode="$displayMode" 
        />

        <div class="refresh-indicator">
            Updated: <span id="update-time">{{ now()->format('H:i:s') }}</span>
        </div>
    </div>

    <script>
        // Update time display
        function updateRefreshTime() {
            const now = new Date();
            const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                              now.getMinutes().toString().padStart(2, '0') + ':' +
                              now.getSeconds().toString().padStart(2, '0');
            document.getElementById('update-time').textContent = timeString;
        }

        setInterval(updateRefreshTime, 1000);

        // Listen for Livewire updates to add animations
        document.addEventListener('livewire:update', function() {
            const queueNumbers = document.querySelectorAll('.queue-number');
            queueNumbers.forEach(number => {
                if (number.textContent.trim() !== '-' && number.textContent.trim() !== '') {
                    number.style.transform = 'scale(1.1)';
                    number.style.transition = 'transform 0.3s ease';
                    setTimeout(() => {
                        number.style.transform = 'scale(1)';
                    }, 300);
                }
            });
        });
    </script>
</body>
</html>
