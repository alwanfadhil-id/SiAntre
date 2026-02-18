<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit IP Whitelist Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-6">Edit IP Address dalam Whitelist</h3>

                    <form action="{{ route('admin.ip-whitelist.update', $ipWhitelist->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat IP *</label>
                                <input type="text" name="ip_address" id="ip_address"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('ip_address') border-red-500 @enderror"
                                       value="{{ old('ip_address', $ipWhitelist->ip_address) }}" required>
                                @error('ip_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Masukkan alamat IPv4 atau IPv6 yang valid</p>
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                                <select name="category" id="category" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="admin" {{ old('category', $ipWhitelist->category) == 'admin' ? 'selected' : '' }}>Akses Admin</option>
                                    <option value="operator" {{ old('category', $ipWhitelist->category) == 'operator' ? 'selected' : '' }}>Akses Operator</option>
                                </select>
                                @error('category')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Pilih tingkat akses untuk IP ini</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                       value="1" {{ old('is_active', $ipWhitelist->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="ml-2 block text-sm text-gray-900">Aktif</label>
                                @error('is_active')
                                    <p class="ml-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kadaluarsa (Opsional)</label>
                                <input type="date" name="expires_at" id="expires_at"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('expires_at') border-red-500 @enderror"
                                       value="{{ old('expires_at', $ipWhitelist->expires_at ? $ipWhitelist->expires_at->format('Y-m-d') : '') }}">
                                @error('expires_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Biarkan kosong untuk tidak ada kadaluarsa</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi (Opsional)</label>
                            <textarea name="description" id="description"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                      rows="3">{{ old('description', $ipWhitelist->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Deskripsi singkat tentang alamat IP ini</p>
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Perbarui Entri Whitelist
                            </button>
                            <a href="{{ route('admin.ip-whitelist.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>