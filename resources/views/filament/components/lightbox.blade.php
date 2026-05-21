{{-- Telegram-style image lightbox using Alpine.js with zoom and pan controls --}}
<div
    x-data="{
        open: false,
        src: '',
        scale: 1,
        offsetX: 0,
        offsetY: 0,
        isDragging: false,
        startX: 0,
        startY: 0,

        resetZoom() {
            this.scale = 1;
            this.offsetX = 0;
            this.offsetY = 0;
        },
        zoomIn() {
            this.scale = Math.min(this.scale + 0.5, 5);
            if (this.scale === 1) this.resetZoom();
        },
        zoomOut() {
            this.scale = Math.max(this.scale - 0.5, 1);
            if (this.scale === 1) this.resetZoom();
        },
        handleWheel(e) {
            const delta = e.deltaY < 0 ? 0.25 : -0.25;
            this.scale = Math.min(Math.max(this.scale + delta, 1), 5);
            if (this.scale === 1) this.resetZoom();
        },
        startDrag(e) {
            if (this.scale > 1) {
                this.isDragging = true;
                const clientX = e.clientX !== undefined ? e.clientX : (e.touches ? e.touches[0].clientX : 0);
                const clientY = e.clientY !== undefined ? e.clientY : (e.touches ? e.touches[0].clientY : 0);
                this.startX = clientX - this.offsetX;
                this.startY = clientY - this.offsetY;
            }
        },
        drag(e) {
            if (this.isDragging && this.scale > 1) {
                const clientX = e.clientX !== undefined ? e.clientX : (e.touches ? e.touches[0].clientX : 0);
                const clientY = e.clientY !== undefined ? e.clientY : (e.touches ? e.touches[0].clientY : 0);
                this.offsetX = clientX - this.startX;
                this.offsetY = clientY - this.startY;
            }
        },
        stopDrag() {
            this.isDragging = false;
        }
    }"
    x-on:lightbox.window="src = $event.detail.src; open = true; resetZoom()"
    x-show="open"
    x-on:click="open = false"
    x-on:keydown.escape.window="open = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 cursor-zoom-out select-none"
    style="display: none;"
>
    {{-- Close button --}}
    <button
        x-on:click="open = false"
        class="absolute top-5 right-5 flex items-center justify-center w-10 h-10
               rounded-full bg-white/10 text-white/80 hover:bg-white/20 hover:text-white
               backdrop-blur-sm transition duration-150 z-[10000]"
    >
        <x-heroicon-m-x-mark class="w-6 h-6" />
    </button>

    {{-- Image --}}
    <img
        :src="src"
        x-on:click.stop
        x-on:dblclick.stop="scale > 1 ? resetZoom() : scale = 2.5"
        x-on:wheel.prevent.stop="handleWheel"

        x-on:mousedown.prevent="startDrag"
        x-on:mousemove="drag"
        x-on:mouseup="stopDrag"
        x-on:mouseleave="stopDrag"

        x-on:touchstart="startDrag"
        x-on:touchmove="drag"
        x-on:touchend="stopDrag"

        class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl
               ring-1 ring-white/10 select-none origin-center"
        :style="`transform: translate(${offsetX}px, ${offsetY}px) scale(${scale}); transition: ${isDragging ? 'none' : 'transform 0.15s cubic-bezier(0.16, 1, 0.3, 1)'}; cursor: ${scale > 1 ? (isDragging ? 'grabbing' : 'grab') : 'default'}`"
    >

    {{-- Controls --}}
    <div
        x-on:click.stop
        class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-3 px-4 py-2
               rounded-full bg-white/10 text-white/80 backdrop-blur-md border border-white/10
               shadow-lg transition-opacity duration-200 z-[10000] select-none"
    >
        {{-- Zoom Out Button --}}
        <button
            type="button"
            x-on:click="zoomOut()"
            :disabled="scale <= 1"
            class="p-1.5 rounded-full hover:bg-white/20 hover:text-white transition disabled:opacity-40 disabled:hover:bg-transparent"
            title="Zoom Out"
        >
            <x-heroicon-m-magnifying-glass-minus class="w-5 h-5" />
        </button>

        {{-- Zoom Percentage --}}
        <span class="text-xs font-semibold w-12 text-center text-white" x-text="`${Math.round(scale * 100)}%`"></span>

        {{-- Zoom In Button --}}
        <button
            type="button"
            x-on:click="zoomIn()"
            :disabled="scale >= 5"
            class="p-1.5 rounded-full hover:bg-white/20 hover:text-white transition disabled:opacity-40 disabled:hover:bg-transparent"
            title="Zoom In"
        >
            <x-heroicon-m-magnifying-glass-plus class="w-5 h-5" />
        </button>

        {{-- Divider --}}
        <div class="w-[1px] h-4 bg-white/20"></div>

        {{-- Reset Button --}}
        <button
            type="button"
            x-on:click="resetZoom()"
            :disabled="scale === 1 && offsetX === 0 && offsetY === 0"
            class="p-1.5 rounded-full hover:bg-white/20 hover:text-white transition disabled:opacity-40 disabled:hover:bg-transparent"
            title="Reset Zoom"
        >
            <x-heroicon-m-arrow-path class="w-5 h-5" />
        </button>
    </div>
</div>

