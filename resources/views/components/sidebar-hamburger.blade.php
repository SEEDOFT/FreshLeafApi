<button 
    x-data="{}" 
    x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()" 
    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors mr-2"
>
    @svg('heroicon-o-bars-3', 'w-6 h-6')
</button>
