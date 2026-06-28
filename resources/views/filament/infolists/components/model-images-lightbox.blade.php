@php
    $images = $getState();
    if (is_string($images)) {
        $images = json_decode($images, true) ?? [$images];
    }
    $images = is_array($images) ? $images : [];
@endphp

<div 
    x-data="{ 
        lightboxOpen: false, 
        currentIndex: 0, 
        zoomScale: 1,
        panX: 0,
        panY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,
        images: @js(array_map(fn($img) => \Illuminate\Support\Facades\Storage::disk('public')->url($img), $images)),
        
        openLightbox(index) {
            this.currentIndex = index;
            this.lightboxOpen = true;
            this.resetZoom();
        },
        closeLightbox() {
            this.lightboxOpen = false;
        },
        zoomIn() {
            this.zoomScale = Math.min(this.zoomScale + 0.25, 3);
        },
        zoomOut() {
            this.zoomScale = Math.max(this.zoomScale - 0.25, 0.5);
        },
        resetZoom() {
            this.zoomScale = 1;
            this.panX = 0;
            this.panY = 0;
        },
        prev() {
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            this.resetZoom();
        },
        next() {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
            this.resetZoom();
        },
        startDrag(e) {
            if (this.zoomScale === 1) return;
            this.isDragging = true;
            this.startX = e.clientX - this.panX;
            this.startY = e.clientY - this.panY;
        },
        drag(e) {
            if (!this.isDragging) return;
            this.panX = e.clientX - this.startX;
            this.panY = e.clientY - this.startY;
        },
        stopDrag() {
            this.isDragging = false;
        }
    }"
    @keydown.escape.window="closeLightbox()"
    @keydown.arrow-left.window="if (lightboxOpen) prev()"
    @keydown.arrow-right.window="if (lightboxOpen) next()"
    class="w-full"
>
    <!-- Thumbnails Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <template x-for="(img, index) in images" :key="index">
            <div 
                class="relative aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 group cursor-pointer shadow-sm hover:shadow-md transition duration-300"
                @click="openLightbox(index)"
            >
                <img 
                    :src="img" 
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500" 
                    alt="Model Image"
                />
                <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                    <x-heroicon-o-magnifying-glass-plus class="w-6 h-6 text-white" />
                </div>
            </div>
        </template>
    </div>

    <!-- No Images Placeholder -->
    <template x-if="images.length === 0">
        <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400 dark:text-gray-500">
            <x-heroicon-o-photo class="w-12 h-12 mb-2 stroke-1" />
            <span class="text-xs font-medium">No images uploaded for this model</span>
        </div>
    </template>

    <!-- Lightbox Modal -->
    <div 
        x-show="lightboxOpen" 
        class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black/90 backdrop-blur-md select-none"
        style="display: none;"
        @click="closeLightbox()"
    >
        <!-- Close Button (Top Right) -->
        <button 
            type="button" 
            @click.stop="closeLightbox()" 
            class="absolute top-4 right-4 text-white/75 hover:text-white p-2 rounded-full hover:bg-white/10 transition"
        >
            <x-heroicon-o-x-mark class="w-8 h-8" />
        </button>

        <!-- Navigation Arrows (Left/Right) -->
        <template x-if="images.length > 1">
            <div>
                <button 
                    type="button" 
                    @click.stop="prev()" 
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-white/75 hover:text-white p-3 rounded-full hover:bg-white/10 transition"
                >
                    <x-heroicon-o-chevron-left class="w-8 h-8" />
                </button>
                <button 
                    type="button" 
                    @click.stop="next()" 
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white/75 hover:text-white p-3 rounded-full hover:bg-white/10 transition"
                >
                    <x-heroicon-o-chevron-right class="w-8 h-8" />
                </button>
            </div>
        </template>

        <!-- Zoom & Control Tool Bar (Bottom) -->
        <div 
            @click.stop 
            class="absolute bottom-6 flex items-center gap-3 bg-white/10 backdrop-blur border border-white/10 rounded-full px-4 py-2 text-white shadow-lg"
        >
            <button 
                type="button" 
                @click="zoomIn()" 
                class="p-1.5 hover:bg-white/10 rounded-full transition" 
                title="Zoom In"
            >
                <x-heroicon-o-magnifying-glass-plus class="w-5 h-5" />
            </button>
            <button 
                type="button" 
                @click="zoomOut()" 
                class="p-1.5 hover:bg-white/10 rounded-full transition" 
                title="Zoom Out"
            >
                <x-heroicon-o-magnifying-glass-minus class="w-5 h-5" />
            </button>
            <button 
                type="button" 
                @click="resetZoom()" 
                class="p-1.5 hover:bg-white/10 rounded-full transition" 
                title="Reset Zoom"
            >
                <x-heroicon-o-arrow-path class="w-5 h-5" />
            </button>
            <div class="h-4 w-px bg-white/20"></div>
            <span class="text-xs font-semibold px-1" x-text="Math.round(zoomScale * 100) + '%'"></span>
        </div>

        <!-- Main Image Container -->
        <div 
            @click.stop 
            class="max-w-[90%] max-h-[80%] flex items-center justify-center overflow-hidden"
            @mousedown="startDrag"
            @mousemove="drag"
            @mouseup="stopDrag"
            @mouseleave="stopDrag"
        >
            <img 
                :src="images[currentIndex]" 
                class="max-w-full max-h-full object-contain transition-transform duration-100 ease-out"
                :class="zoomScale > 1 ? 'cursor-grab active:cursor-grabbing' : 'cursor-default'"
                :style="`transform: scale(${zoomScale}) translate(${panX}px, ${panY}px)`"
                alt="Model High Resolution Image"
                draggable="false"
            />
        </div>

        <!-- Counter -->
        <div class="absolute bottom-20 text-white/50 text-[10px]" x-text="`Image ${currentIndex + 1} of ${images.length}`"></div>
    </div>
</div>
