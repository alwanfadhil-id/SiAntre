<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sistem Antrian Online (SiAntre)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    <h1 class="text-3xl font-bold mb-6">Selamat Datang di Sistem Antrian Online</h1>
                    <p class="mb-8 text-lg">Silakan pilih layanan untuk mengambil nomor antrian</p>
                    
                    <div class="flex justify-center">
                        <a href="{{ route('patient.services') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Pilih Layanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>