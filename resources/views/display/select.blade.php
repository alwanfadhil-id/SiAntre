<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Display Antrian - SiAntre</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .service-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .service-card.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        .service-card.selected .check-icon {
            opacity: 1;
        }
        .check-icon {
            opacity: 0;
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <span class="text-blue-600">Si</span>Antre
            </h1>
            <p class="text-gray-600 text-lg">Pilih Poli/Layanan untuk Ditampilkan</p>
        </div>

        <form action="{{ route('display.show') }}" method="GET" id="displayForm">
            <!-- Service Selection -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Pilih Layanan ({{ count($services) }} tersedia)
                </h2>

                @if($services->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($services as $service)
                            <label class="service-card relative bg-gray-50 rounded-xl p-4 border-2 border-gray-200 hover:border-blue-400 flex items-center space-x-3">
                                <input type="checkbox" 
                                       name="services[]" 
                                       value="{{ $service->id }}" 
                                       class="service-checkbox hidden"
                                       data-service-name="{{ $service->name }}">
                                <div class="check-icon absolute top-2 right-2 text-blue-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <span class="text-blue-600 font-bold text-lg">{{ substr($service->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">{{ $service->name }}</h3>
                                            <p class="text-sm text-gray-500">Prefix: {{ $service->prefix }}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p>Belum ada layanan yang tersedia.</p>
                    </div>
                @endif
            </div>

            <!-- Display Mode Selection -->
            <div class="bg-white rounded-2xl shadow-xl p-6 mb-6" id="modeSection" style="display: none;">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                    </svg>
                    Mode Tampilan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="mode-card cursor-pointer">
                        <input type="radio" name="mode" value="single" class="hidden" checked>
                        <div class="border-2 border-gray-200 rounded-xl p-4 text-center hover:border-blue-400 transition-all mode-option">
                            <div class="text-3xl mb-2">📋</div>
                            <h3 class="font-semibold text-gray-800">Single</h3>
                            <p class="text-sm text-gray-500 mt-1">Satu layanan per layar</p>
                        </div>
                    </label>
                    <label class="mode-card cursor-pointer">
                        <input type="radio" name="mode" value="grid" class="hidden">
                        <div class="border-2 border-gray-200 rounded-xl p-4 text-center hover:border-blue-400 transition-all mode-option">
                            <div class="text-3xl mb-2">⊞</div>
                            <h3 class="font-semibold text-gray-800">Grid</h3>
                            <p class="text-sm text-gray-500 mt-1">Beberapa layanan (2-4)</p>
                        </div>
                    </label>
                    <label class="mode-card cursor-pointer">
                        <input type="radio" name="mode" value="list" class="hidden">
                        <div class="border-2 border-gray-200 rounded-xl p-4 text-center hover:border-blue-400 transition-all mode-option">
                            <div class="text-3xl mb-2">☰</div>
                            <h3 class="font-semibold text-gray-800">List</h3>
                            <p class="text-sm text-gray-500 mt-1">Banyak layanan (5+)</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button type="button" 
                        onclick="selectAll()" 
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-all">
                    Pilih Semua
                </button>
                <button type="button" 
                        onclick="clearSelection()" 
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-300 transition-all">
                    Hapus Pilihan
                </button>
                <button type="submit" 
                        id="submitBtn"
                        disabled
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                    Tampilkan Display
                </button>
            </div>
        </form>

        <!-- Quick Access Info -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-sm text-blue-800">
                <strong>💡 Tips:</strong> Pilih 1 layanan untuk tampilan fokus, atau pilih beberapa layanan untuk tampilan gabungan.
            </p>
        </div>
    </div>

    <script>
        const checkboxes = document.querySelectorAll('.service-checkbox');
        const modeSection = document.getElementById('modeSection');
        const submitBtn = document.getElementById('submitBtn');
        const modeCards = document.querySelectorAll('.mode-option');

        // Update UI based on selection
        function updateUI() {
            const selectedCount = document.querySelectorAll('.service-checkbox:checked').length;
            
            checkboxes.forEach(cb => {
                const card = cb.closest('.service-card');
                if (cb.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            });

            // Show mode section if more than 1 selected
            if (selectedCount > 1) {
                modeSection.style.display = 'block';
                // Auto-select mode based on count
                const radioValue = selectedCount <= 4 ? 'grid' : 'list';
                document.querySelector(`input[name="mode"][value="${radioValue}"]`).checked = true;
                updateModeCards();
            } else {
                modeSection.style.display = 'none';
                document.querySelector('input[name="mode"][value="single"]').checked = true;
                updateModeCards();
            }

            // Enable/disable submit button
            submitBtn.disabled = selectedCount === 0;
        }

        // Update mode cards styling
        function updateModeCards() {
            const selectedMode = document.querySelector('input[name="mode"]:checked').value;
            modeCards.forEach(card => {
                const input = card.previousElementSibling;
                if (input && input.checked) {
                    card.classList.add('border-blue-500', 'bg-blue-50');
                    card.classList.remove('border-gray-200');
                } else {
                    card.classList.add('border-gray-200');
                    card.classList.remove('border-blue-500', 'bg-blue-50');
                }
            });
        }

        // Select all
        function selectAll() {
            checkboxes.forEach(cb => cb.checked = true);
            updateUI();
        }

        // Clear selection
        function clearSelection() {
            checkboxes.forEach(cb => cb.checked = false);
            updateUI();
        }

        // Event listeners
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateUI);
        });

        document.querySelectorAll('input[name="mode"]').forEach(radio => {
            radio.addEventListener('change', updateModeCards);
        });

        // Initialize
        updateUI();
    </script>
</body>
</html>
