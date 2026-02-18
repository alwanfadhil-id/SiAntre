<div class="display-container">
    <div class="header-section">
        <h1 class="main-title">SISTEM ANTRIAN ONLINE (SiAntre)</h1>
        <div class="subtitle">Silahkan menuju loket pendaftaran untuk mendapatkan nomor antrian</div>
    </div>

    <!-- Debug Info - Remove in production -->
    <div class="debug-info">
        <div>Total Services: {{ count($services) }}</div>
        <div>Livewire Component: Ready</div>
        <div>Services Data: {{ json_encode(array_column($services, 'name')) }}</div>
    </div>

    <div class="carousel-container">
        <div class="carousel-wrapper" id="carouselWrapper">
            @if(count($services) > 0)
                <?php
                    // Split services into groups of 4 for each slide
                    $servicesPerSlide = 4;
                    $slides = array_chunk($services, $servicesPerSlide);
                ?>

                @foreach($slides as $slideIndex => $slideServices)
                    <div class="carousel-slide" id="slide-{{ $slideIndex }}" style="display: {{ $slideIndex == 0 ? 'flex' : 'none' }};">
                        <div class="services-table">
                            @foreach($slideServices as $service)
                                <div class="service-row">
                                    <div class="service-info">
                                        <div class="service-name">{{ $service['name'] }}</div>
                                    </div>

                                    <div class="current-number">
                                        <div class="current-number-label">Nomor Antrian</div>
                                        @if(isset($calledQueues[$service['id']]) && $calledQueues[$service['id']])
                                            <div class="current-number-display">{{ $calledQueues[$service['id']] }}</div>
                                        @else
                                            <div class="no-current-number">-</div>
                                        @endif
                                    </div>

                                    <div class="waiting-info">
                                        <div class="waiting-label">Sisa Antrian</div>
                                        <div class="waiting-count-display">{{ $waitingCounts[$service['id']] ?? 0 }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="services-table">
                    <div class="service-row">
                        <div class="service-info">
                            <div class="service-name">Tidak Ada Layanan Aktif</div>
                        </div>
                        <div class="current-number">
                            <div class="current-number-label">Nomor Antrian</div>
                            <div class="no-current-number">-</div>
                        </div>
                        <div class="waiting-info">
                            <div class="waiting-label">Sisa Antrian</div>
                            <div class="waiting-count-display">0</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if(count($services) > 0)
            <div class="carousel-nav">
                @for($i = 0; $i < count($slides); $i++)
                    <div class="carousel-dot {{ $i == 0 ? 'active' : '' }}" onclick="changeSlide({{ $i }})" id="dot-{{ $i }}"></div>
                @endfor
            </div>
        @endif
    </div>

    <!-- Refresh indicator -->
    <div class="refresh-indicator">
        Updated: <span id="update-time">{{ now()->format('H:i:s') }}</span>
    </div>
</div>

<script>
    // Carousel functionality variables
    let currentSlide = 0;
    let totalSlides = 0;
    let slideInterval;

    // Update the displayed time
    function updateRefreshTime() {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' +
                          now.getMinutes().toString().padStart(2, '0') + ':' +
                          now.getSeconds().toString().padStart(2, '0');
        document.getElementById('update-time').textContent = timeString;
    }

    // Function to change slide in carousel
    function changeSlide(slideIndex) {
        // Hide all slides
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');

        slides.forEach((slide, index) => {
            slide.style.display = (index === slideIndex) ? 'flex' : 'none';
        });

        // Update active dot
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === slideIndex);
        });

        currentSlide = slideIndex;
    }

    // Function to go to next slide
    function nextSlide() {
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');

        // Hide current slide
        slides[currentSlide].style.display = 'none';
        dots[currentSlide].classList.remove('active');

        // Move to next slide
        currentSlide = (currentSlide + 1) % slides.length;

        // Show next slide
        slides[currentSlide].style.display = 'flex';
        dots[currentSlide].classList.add('active');
    }

    // Function to animate queue number changes
    function animateQueueNumber(element) {
        element.style.transform = 'scale(1.1)';
        element.style.opacity = '0.8';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
            element.style.opacity = '1';
        }, 300);
    }

    // Function to highlight updated services
    function highlightService(serviceElement) {
        serviceElement.style.boxShadow = '0 0 30px #22c55e';
        setTimeout(() => {
            serviceElement.style.boxShadow = '';
        }, 2000);
    }

    // Auto-refresh every 5 seconds
    setInterval(function() {
        Livewire.emit('queueUpdated');
        updateRefreshTime();

        // Add subtle animation to indicate refresh
        const refreshIndicator = document.querySelector('.refresh-indicator');
        if (refreshIndicator) {
            refreshIndicator.style.transform = 'scale(1.1)';
            setTimeout(() => {
                refreshIndicator.style.transform = 'scale(1)';
            }, 300);
        }
    }, 5000);

    // Also update the time every second for better UX
    setInterval(updateRefreshTime, 1000);

    // Handle Livewire errors gracefully
    window.addEventListener('livewire:init', () => {
        Livewire.on('error', (data) => {
            console.error('Livewire error:', data);
        });

        // Log when Livewire is initialized
        console.log('Livewire initialized for display screen');
    });

    // Listen for Livewire updates to add animations
    document.addEventListener('livewire:update', function() {
        // Add animation when data updates
        const queueNumbers = document.querySelectorAll('.current-number-display');
        queueNumbers.forEach(number => {
            if (number.textContent.trim() !== '-' && number.textContent.trim() !== '') {
                animateQueueNumber(number);
            }
        });

        // Highlight services that have updated
        const services = document.querySelectorAll('.service-row');
        services.forEach(service => {
            highlightService(service);
        });
    });

    // Check if component is loading properly
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Display screen DOM loaded');

        // Initialize carousel
        const slides = document.querySelectorAll('.carousel-slide');
        totalSlides = slides.length;

        if (totalSlides > 1) {
            // Set up automatic slide rotation (every 10 seconds)
            slideInterval = setInterval(nextSlide, 10000);

            // Pause carousel when hovering over it
            const carouselContainer = document.querySelector('.carousel-container');
            carouselContainer.addEventListener('mouseenter', function() {
                clearInterval(slideInterval);
            });

            carouselContainer.addEventListener('mouseleave', function() {
                slideInterval = setInterval(nextSlide, 10000);
            });
        }

        // Add entrance animations
        const services = document.querySelectorAll('.service-row');
        services.forEach((service, index) => {
            service.style.opacity = '0';
            service.style.transform = 'translateY(20px)';

            setTimeout(() => {
                service.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                service.style.opacity = '1';
                service.style.transform = 'translateY(0)';
            }, 150 * index);
        });

        // Additional debug: Check if the component element exists
        setTimeout(() => {
            const container = document.querySelector('.display-container');
            if(container) {
                console.log('Display container found, component rendered');
            } else {
                console.error('Display container not found!');
            }
        }, 1000);
    });
</script>