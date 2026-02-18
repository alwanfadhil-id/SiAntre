<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Operator') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-6">Daftar Layanan & Antrian Saat Ini</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        @foreach($services as $service)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-bold text-lg mb-3 text-blue-600">{{ $service->name }}</h4>

                                @if(isset($currentQueues[$service->id]) && $currentQueues[$service->id])
                                    <div class="bg-blue-50 p-3 rounded mb-3">
                                        <p class="font-semibold">Nomor Dipanggil:</p>
                                        <p class="text-2xl text-center font-bold text-blue-700">{{ $currentQueues[$service->id]->number }}</p>
                                    </div>
                                @else
                                    <div class="bg-gray-50 p-3 rounded mb-3">
                                        <p class="text-center text-gray-600">Tidak ada antrian dipanggil</p>
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <a href="{{ route('operator.queues', $service) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Kelola Antrian
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Today's History -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-bold text-lg mb-3 text-green-600">Riwayat Hari Ini (10 terakhir)</h4>

                        @if($todayHistory->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                            <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($todayHistory as $queue)
                                            <tr>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $queue->number }}
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $queue->service->name }}
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $queue->updated_at->format('H:i:s') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center text-gray-600">Belum ada antrian yang selesai hari ini</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>