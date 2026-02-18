<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Layanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-6">Pilih Layanan untuk Mengelola Antrian</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($services as $service)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-bold text-lg mb-3 text-blue-600">{{ $service->name }}</h4>
                                
                                <div class="mb-3">
                                    <p class="text-sm text-gray-600">
                                        Antrian menunggu: 
                                        <span class="font-semibold">
                                            {{ \App\Models\Queue::where('service_id', $service->id)
                                                ->where('status', 'waiting')
                                                ->whereDate('created_at', now()->toDateString())
                                                ->count() }}
                                        </span>
                                    </p>
                                </div>
                                
                                <a href="{{ route('operator.queues', $service) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Lihat Antrian
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>