<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-6">Statistik Sistem</h3>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="text-sm text-blue-600">Total Layanan</p>
                            <p class="text-2xl font-bold text-blue-800">{{ $totalServices }}</p>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <p class="text-sm text-green-600">Total Pengguna</p>
                            <p class="text-2xl font-bold text-green-800">{{ $totalUsers }}</p>
                        </div>

                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100">
                            <p class="text-sm text-yellow-600">Antrian Hari Ini</p>
                            <p class="text-2xl font-bold text-yellow-800">{{ $todayQueues }}</p>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                            <p class="text-sm text-purple-600">Selesai Hari Ini</p>
                            <p class="text-2xl font-bold text-purple-800">{{ $todayCompleted }}</p>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                            <p class="text-sm text-red-600">Dibatalkan</p>
                            <p class="text-2xl font-bold text-red-800">{{ $canceledQueues }}</p>
                        </div>
                    </div>

                    <!-- Additional Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="text-sm text-blue-600">Menunggu</p>
                            <p class="text-xl font-bold text-blue-800">{{ $waitingQueues }}</p>
                        </div>

                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-100">
                            <p class="text-sm text-orange-600">Dipanggil</p>
                            <p class="text-xl font-bold text-orange-800">{{ $calledQueues }}</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <p class="text-sm text-gray-600">Tingkat Pembatalan</p>
                            <p class="text-xl font-bold text-gray-800">{{ $cancellationRate }}%</p>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="mb-8">
                        <h4 class="text-lg font-semibold mb-4">Aksi Cepat</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="{{ route('admin.services.index') }}" class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">
                                Kelola Layanan
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center">
                                Kelola Pengguna
                            </a>
                            <a href="{{ route('admin.ip-whitelist.index') }}" class="px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-center">
                                Kelola IP Whitelist
                            </a>
                            <form method="POST" action="{{ route('admin.reset.queue') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 w-full">
                                    Reset Antrian Hari Ini
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Statistics Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <p class="text-sm text-blue-600">Rata-rata Waktu Tunggu</p>
                            <p class="text-2xl font-bold text-blue-800">
                                @if($avgWaitTime)
                                    {{ round($avgWaitTime, 1) }} menit
                                @else
                                    - menit
                                @endif
                            </p>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                            <p class="text-sm text-green-600">Jam Puncak Antrian</p>
                            <p class="text-2xl font-bold text-green-800">
                                @if($peakHour)
                                    {{ $peakHour->hour }}:00 - {{ $peakHour->hour+1 }}:00 ({{ $peakHour->count }} antrian)
                                @else
                                    -
                                @endif
                            </p>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                            <p class="text-sm text-red-600">Tingkat Pembatalan</p>
                            <p class="text-2xl font-bold text-red-800">{{ round($cancellationRate, 1) }}%</p>
                        </div>
                    </div>

                    <!-- Services Overview -->
                    <div class="border rounded-lg p-4">
                        <h4 class="font-bold text-lg mb-4 text-gray-800">Ringkasan Layanan</h4>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menunggu</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dipanggil</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selesai</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($servicesWithQueues as $service)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $service->name }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ $service->queues_count }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ $service->waiting_count }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ $service->called_count }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                {{ $service->done_count }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>