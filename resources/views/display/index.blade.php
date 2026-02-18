<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display Antrian - SiAntre</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
    <style>
        body {
            background: linear-gradient(135deg, #1e40af, #3b82f6, #60a5fa);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            color: #333;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .display-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 1.5rem;
            gap: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .header-section {
            text-align: center;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .main-title {
            font-size: 3.5rem;
            font-weight: 900;
            color: white;
            text-align: center;
            margin: 0;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        /* Carousel container for services */
        .carousel-container {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            overflow: hidden;
            position: relative;
        }

        .carousel-wrapper {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .carousel-slide {
            min-width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Professional table-like layout for services */
        .services-table {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .service-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1.5rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-row:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .service-info {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-name {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            text-align: center;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .current-number {
            background: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .current-number-label {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            margin-bottom: 1rem;
        }

        .current-number-display {
            font-size: 6rem;
            font-weight: 900;
            color: #0ea5e9;
            text-align: center;
            margin: 0;
            text-shadow: 4px 4px 10px rgba(0, 0, 0, 0.2);
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom, #f0f9ff, #e0f2fe);
            border-radius: 1rem;
            padding: 1rem;
            border: 4px solid #bae6fd;
            width: 100%;
        }

        .waiting-info {
            background: white;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .waiting-label {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            margin-bottom: 1rem;
        }

        .waiting-count-display {
            font-size: 4rem;
            font-weight: 900;
            color: #dc2626;
            text-align: center;
            margin: 0;
            background: rgba(254, 202, 202, 0.3);
            padding: 1rem 2rem;
            border-radius: 1rem;
            border: 3px solid #fecaca;
            min-width: 120px;
        }

        .no-current-number {
            font-size: 5rem;
            font-weight: bold;
            color: #9ca3af;
            text-align: center;
            margin: 0;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.2);
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
            border-radius: 1rem;
            padding: 1rem;
            border: 4px dashed #d1d5db;
            width: 100%;
        }

        .carousel-nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .carousel-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .carousel-dot.active {
            background: white;
        }

        .refresh-indicator {
            position: fixed;
            top: 1rem;
            right: 1rem;
            font-size: 1.2rem;
            color: #ffffff;
            background: rgba(0, 0, 0, 0.4);
            padding: 0.7rem 1.2rem;
            border-radius: 0.75rem;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 1200px) {
            .service-row {
                grid-template-columns: 1fr;
            }

            .main-title {
                font-size: 2.5rem;
            }

            .service-name {
                font-size: 2rem;
            }

            .current-number-display {
                font-size: 4rem;
            }

            .waiting-count-display {
                font-size: 3rem;
            }
        }

        @media (max-width: 768px) {
            .display-container {
                padding: 1rem;
            }

            .main-title {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1.2rem;
            }

            .service-info, .current-number, .waiting-info {
                padding: 1.5rem;
            }

            .service-name {
                font-size: 1.8rem;
            }

            .current-number-display {
                font-size: 3rem;
            }

            .waiting-count-display {
                font-size: 2.5rem;
            }

            .current-number-label, .waiting-label {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body class="bg-blue-50">
    <livewire:display-screen />
</html>