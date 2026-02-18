<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen IP Whitelist') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold">Daftar IP Whitelist</h3>
                        <a href="{{ route('admin.ip-whitelist.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Tambah IP Address
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kadaluarsa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="ip-whitelist-table">
                                @forelse($ipWhitelists as $ipWhitelist)
                                    <tr id="ip-row-{{ $ipWhitelist->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $ipWhitelist->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <strong>{{ $ipWhitelist->ip_address }}</strong>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $ipWhitelist->description ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($ipWhitelist->category === 'admin') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($ipWhitelist->category) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="ip-status-toggle sr-only peer"
                                                       data-id="{{ $ipWhitelist->id }}"
                                                       {{ $ipWhitelist->is_active ? 'checked' : '' }}>
                                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                                <span class="ml-3 text-sm font-medium text-gray-900">
                                                    {{ $ipWhitelist->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </label>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($ipWhitelist->expires_at)
                                                {{ $ipWhitelist->expires_at->format('d M Y H:i') }}
                                                @if($ipWhitelist->expires_at->isPast())
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 ml-2">
                                                        Kadaluarsa
                                                    </span>
                                                @elseif($ipWhitelist->expires_at->diffInDays() <= 7)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-2">
                                                        Hampir Kadaluarsa
                                                    </span>
                                                @endif
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Tidak Kadarluarsa
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $ipWhitelist->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('admin.ip-whitelist.edit', $ipWhitelist->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>

                                            <form method="POST" action="{{ route('admin.ip-whitelist.destroy', $ipWhitelist->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus IP address ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada IP address ditemukan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $ipWhitelists->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle IP status toggle
            document.querySelectorAll('.ip-status-toggle').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const ipId = this.dataset.id;
                    const isChecked = this.checked;
                    const rowElement = document.getElementById('ip-row-' + ipId);

                    // Add loading state
                    rowElement.classList.add('opacity-50');

                    fetch(`/admin/ip-whitelist/${ipId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the status text
                            const statusText = this.parentElement.querySelector('span');
                            statusText.textContent = isChecked ? 'Aktif' : 'Nonaktif';

                            // Show success message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
                            alertDiv.innerHTML = data.message;
                            document.body.appendChild(alertDiv);

                            setTimeout(() => {
                                if (alertDiv.parentNode) {
                                    alertDiv.parentNode.removeChild(alertDiv);
                                }
                            }, 3000);
                        } else {
                            // Revert the checkbox if there was an error
                            this.checked = !isChecked;
                        }

                        // Remove loading state
                        rowElement.classList.remove('opacity-50');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Revert the checkbox if there was an error
                        this.checked = !isChecked;

                        // Remove loading state
                        rowElement.classList.remove('opacity-50');
                    });
                });
            });
        });
    </script>
</x-app-layout>