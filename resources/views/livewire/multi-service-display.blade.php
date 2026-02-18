<div class="services-grid mode-{{ $displayMode }} mode-{{ count($services) }}" wire:poll.3s="refreshData">
    @if(count($serviceData) > 0)
        @foreach($serviceData as $service)
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon">
                        {{ substr($service['name'], 0, 1) }}
                    </div>
                    <div class="service-name">{{ $service['name'] }}</div>
                </div>

                <div class="service-content">
                    <div class="queue-info">
                        <div class="queue-label">Nomor Antrian</div>
                        @if($service['called_number'])
                            <div class="queue-number">
                                {{ $service['prefix'] }}-{{ str_pad($service['called_number'], 4, '0', STR_PAD_LEFT) }}
                            </div>
                        @else
                            <div class="no-queue">-</div>
                        @endif

                        @if($service['waiting_count'] > 0)
                            <div class="waiting-info">
                                <span class="waiting-count">{{ $service['waiting_count'] }}</span>
                                <span class="waiting-label">Antrian</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="service-card">
            <div class="service-header">
                <div class="service-icon">ℹ</div>
                <div class="service-name">Informasi</div>
            </div>
            <div class="service-content">
                <div class="no-queue">Belum Ada Antrian</div>
            </div>
        </div>
    @endif
</div>
