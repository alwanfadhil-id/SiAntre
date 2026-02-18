<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Antrian untuk ') . $service->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Current Queue -->
                        <div class="border rounded-lg p-4 bg-blue-50">
                            <h3 class="font-bold text-lg mb-2 text-blue-700">Antrian Sedang Diproses</h3>
                            @if($currentQueue)
                                <div class="text-center">
                                    <p class="text-4xl font-bold text-blue-700">{{ $currentQueue->number }}</p>
                                    <p class="mt-2 text-sm text-gray-600">Status: <span class="font-semibold">{{ ucfirst($currentQueue->status) }}</span></p>
                                </div>
                            @else
                                <p class="text-center text-gray-600">Tidak ada antrian sedang diproses</p>
                            @endif
                        </div>
                        
                        <!-- Next Queue -->
                        <div class="border rounded-lg p-4 bg-green-50">
                            <h3 class="font-bold text-lg mb-2 text-green-700">Antrian Berikutnya</h3>
                            @if($nextQueue)
                                <div class="text-center">
                                    <p class="text-4xl font-bold text-green-700">{{ $nextQueue->number }}</p>
                                    <p class="mt-2 text-sm text-gray-600">Status: <span class="font-semibold">{{ ucfirst($nextQueue->status) }}</span></p>

                                    <form method="POST" action="{{ route('operator.queue.call', $nextQueue) }}" class="mt-4" id="call-form-{{ $nextQueue->id }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" onclick="playCallSound()">
                                            Panggil Antrian
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-center text-gray-600">Tidak ada antrian menunggu</p>
                            @endif
                        </div>

                        <!-- Navigation Controls -->
                        <div class="border rounded-lg p-4 bg-purple-50">
                            <h3 class="font-bold text-lg mb-2 text-purple-700">Navigasi Antrian</h3>
                            <div class="space-y-3">
                                <a href="{{ route('operator.queues', $service) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    Muat Ulang Halaman
                                </a>

                                @if($currentQueue)
                                    <form method="POST" action="{{ route('operator.queue.call', $currentQueue) }}" class="mt-2">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                            Panggil Ulang
                                        </button>
                                    </form>
                                @endif

                                <!-- Specific queue number form -->
                                <div class="mt-4">
                                    <form method="GET" action="{{ route('operator.queues', $service) }}" class="flex space-x-2">
                                        <input type="number" name="queue_number" placeholder="Nomor Antrian"
                                               class="flex-1 rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                            Cari
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Waiting Count -->
                        <div class="border rounded-lg p-4 bg-yellow-50">
                            <h3 class="font-bold text-lg mb-2 text-yellow-700">Jumlah Antrian Menunggu</h3>
                            <div class="text-center">
                                <p class="text-4xl font-bold text-yellow-700">
                                    {{ $queues->where('status', 'waiting')->count() }}
                                </p>
                                <p class="mt-2 text-sm text-gray-600">orang</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- All Queues Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($queues as $queueItem)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $queueItem->number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($queueItem->status == 'waiting') bg-yellow-100 text-yellow-800
                                                @elseif($queueItem->status == 'called') bg-blue-100 text-blue-800
                                                @elseif($queueItem->status == 'done') bg-green-100 text-green-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($queueItem->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $queueItem->created_at->format('H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($queueItem->status === 'waiting')
                                                <form method="POST" action="{{ route('operator.queue.call', $queueItem) }}" class="inline" id="call-form-{{ $queueItem->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900 mr-2" onclick="playCallSound()">Panggil</button>
                                                </form>
                                            @elseif($queueItem->status === 'called')
                                                <form method="POST" action="{{ route('operator.queue.done', $queueItem) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-2">Selesai</button>
                                                </form>
                                                <form method="POST" action="{{ route('operator.queue.cancel', $queueItem) }}" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Batal</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada antrian untuk layanan ini hari ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio element for notifications -->
    <audio id="call-audio" preload="auto">
        <source src="https://assets.mixkit.co/sfx/preview/mixkit-doorbell-single-press-333.mp3" type="audio/mpeg">
    </audio>

    <script>
        function playCallSound() {
            const audio = document.getElementById('call-audio');
            audio.currentTime = 0;
            audio.play().catch(e => console.log("Audio play prevented by browser: ", e));
        }

        // Play sound when page loads if there's a success message
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                playCallSound();
            @endif
        });
    </script>
</x-app-layout>