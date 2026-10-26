<button id="theme-toggle" class="fixed bottom-6 right-6 z-[9999] rounded-full border border-blue-500/50 bg-neutral-900/80 backdrop-blur-md px-4 py-2 text-sm font-bold text-blue-200 shadow-lg transition hover:bg-neutral-800 cursor-pointer">
    ☀️ Modo Claro
</button>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('theme-toggle');
        
        if (localStorage.getItem('theme') === 'light') {
            document.body.classList.add('modo-claro');
            if(themeToggle) themeToggle.textContent = '🌙 Modo Oscuro';
        }

        themeToggle?.addEventListener('click', () => {
            document.body.classList.toggle('modo-claro');
            
            if (document.body.classList.contains('modo-claro')) {
                localStorage.setItem('theme', 'light');
                themeToggle.textContent = '🌙 Modo Oscuro';
            } else {
                localStorage.setItem('theme', 'dark');
                themeToggle.textContent = '☀️ Modo Claro';
            }
        });
    });
</script>