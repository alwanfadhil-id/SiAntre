<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Layanan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-6">Detail Layanan</h3>

                    <div class="mb-6">
                        <p class="text-gray-700"><span class="font-medium">ID:</span> {{ $service->id }}</p>
                        <p class="text-gray-700"><span class="font-medium">Nama:</span> {{ $service->name }}</p>
                        <p class="text-gray-700"><span class="font-medium">Awalan:</span> {{ $service->prefix }}</p>
                        <p class="text-gray-700"><span class="font-medium">Tanggal Dibuat:</span> {{ $service->created_at->format('d M Y H:i') }}</p>
                        <p class="text-gray-700"><span class="font-medium">Tanggal Diubah:</span> {{ $service->updated_at->format('d M Y H:i') }}</p>
                    </div>

                    <div class="flex items-center">
                        <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 mr-2">
                            Kembali
                        </a>
                        <a href="{{ route('admin.services.edit', $service) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>