<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pilih Layanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-6">Silakan pilih layanan yang tersedia:</h3>

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('queue.generate') }}" id="queue-form">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($services as $service)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition cursor-pointer service-option"
                                     data-service-id="{{ $service->id }}"
                                     onclick="selectService({{ $service->id }})">
                                    <input type="radio" name="service_id" value="{{ $service->id }}"
                                           id="service_{{ $service->id }}"
                                           class="mr-2 service-radio">
                                    <label for="service_{{ $service->id }}" class="font-medium cursor-pointer">
                                        {{ $service->name }}
                                    </label>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Sisa antrian:
                                        <span class="font-semibold">
                                            {{ $service->waiting_count }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-8">
                                    <p class="text-gray-600">Tidak ada layanan yang tersedia saat ini.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    id="submit-btn"
                                    class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                Ambil Nomor Antrian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedServiceId = null;

        function selectService(serviceId) {
            // Update the selected service ID
            selectedServiceId = serviceId;

            // Update radio button selection
            document.querySelectorAll('.service-radio').forEach(radio => {
                radio.checked = false;
            });

            const radio = document.getElementById(`service_${serviceId}`);
            if (radio) {
                radio.checked = true;
            }

            // Update UI highlighting
            document.querySelectorAll('.service-option').forEach(option => {
                option.classList.remove('border-blue-500', 'bg-blue-50');
            });

            const selectedOption = document.querySelector(`[data-service-id="${serviceId}"]`);
            if (selectedOption) {
                selectedOption.classList.add('border-blue-500', 'bg-blue-50');
            }

            // Enable submit button
            document.getElementById('submit-btn').disabled = false;
        }

        // Form submission with loading state
        document.getElementById('queue-form').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Memproses...';
            submitBtn.classList.add('opacity-75');
        });
    </script>
</x-app-layout>