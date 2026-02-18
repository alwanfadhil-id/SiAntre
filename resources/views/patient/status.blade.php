<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Status Antrian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h3 class="text-2xl font-semibold mb-6">Nomor Antrian Anda</h3>

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 max-w-md mx-auto">
                        <div class="text-6xl font-bold text-blue-700 mb-4">{{ $queue->number }}</div>
                        <div class="text-lg mb-2">Layanan: <span class="font-semibold">{{ $queue->service->name }}</span></div>
                        <div class="text-lg mb-4">Status:
                            <span class="px-3 py-1 rounded-full text-white
                                @if($queue->status == 'waiting') bg-yellow-500
                                @elseif($queue->status == 'called') bg-blue-500
                                @elseif($queue->status == 'done') bg-green-500
                                @else bg-red-500 @endif">
                                {{ ucfirst($queue->status) }}
                            </span>
                        </div>

                        <div class="mt-6">
                            <p class="text-gray-600">Harap tunggu giliran Anda dipanggil</p>

                            @if($queue->status === 'waiting')
                                <p class="mt-2 text-lg text-blue-600 font-semibold">
                                    @if($queue->people_ahead > 0)
                                        Masih ada {{ $queue->people_ahead }} orang di depan Anda
                                    @else
                                        Giliran Anda akan segera dipanggil
                                    @endif
                                </p>

                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">
                                        Perkiraan waktu tunggu: ~{{ $queue->getEstimatedWaitTime() }} menit
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Posisi Anda: {{ $queue->getPositionInQueue() }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                        <a href="{{ route('patient.home') }}" class="px-6 py-3 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Kembali ke Beranda
                        </a>
                        <a href="{{ route('patient.services') }}" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Pilih Layanan Lain
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>