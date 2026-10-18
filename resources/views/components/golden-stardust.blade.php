{{--
    [QoL Golden Stardust Background — Adaptive Colors]
    Ruta: resources/views/components/background/golden-stardust.blade.php

    uso en cualquier panel del dashboard, dentro del @section('content'),
    como primera linea antes del div principal:

        @section('content')
        <x-background.golden-stardust />
        <div class="px-8 py-8">

    lee data-palette y data-theme del <html> cada ~60 frames
    para adaptar colores de estrellas, meteoros y fondo al tema activo.

    el canvas se renderiza con requestAnimationFrame a ~60fps.
    en dispositivos de baja gama el impacto es minimo.
    el fillStyle con alpha bajo actua como limpiador del frame
    en lugar de ctx.clearRect(), reduciendo trabajo del GPU.
--}}

<div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
    <canvas id="golden-stardust" class="w-full h-full"></canvas>
</div>

@once
@push('scripts')
<script>
(function () {
    var canvas = document.getElementById('golden-stardust');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var width, height;

    function resize() {
        width  = canvas.width  = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    // ── Paletas de color por palette ID ─────────────────────────────────────
    var SD_PALETTES = {
        gold:      { stars: ['#FFD700','#FFEEAA','#E8B923','#FFCC66','#F4D03F'], meteor: '#FFDD88', glow: '#FFD700', meteorGlow: '#FFAA00' },
        champagne: { stars: ['#d4af37','#e0c354','#EDD680','#c9a227','#f0d878'], meteor: '#e8d06a', glow: '#d4af37', meteorGlow: '#b8941e' },
        copper:    { stars: ['#b87333','#cc8844','#d4955a','#e0a870','#c07040'], meteor: '#d49060', glow: '#b87333', meteorGlow: '#9a5e20' },
        antique:   { stars: ['#c8971f','#d9aa36','#e0bc50','#b88010','#d4a030'], meteor: '#d4aa40', glow: '#c8971f', meteorGlow: '#a07810' },
        warm:      { stars: ['#e8892a','#f0a050','#f4b468','#e07020','#f09840'], meteor: '#f0a848', glow: '#e8892a', meteorGlow: '#c06818' },
        pearl:     { stars: ['#d4b896','#dfc8a8','#e8d4bc','#c4a880','#d0b088'], meteor: '#dcc0a0', glow: '#c8a878', meteorGlow: '#a08858' },
        blue: {
        stars: ['#3b82f6','#60a5fa','#93c5fd','#1d4ed8','#2563eb'],
        meteor: '#60a5fa',
        glow: '#3b82f6',
        meteorGlow: '#1d4ed8'
},
    };

    // ── Configuracion de fondo por theme ID ──────────────────────────────────
    var SD_THEMES = {
        dark:  { fadeFill: 'rgba(19, 19, 19, 0.2)',   maxOpacity: 0.85, starCount: 150 },
        dim:   { fadeFill: 'rgba(26, 24, 18, 0.22)',   maxOpacity: 0.75, starCount: 140 },
        light: { fadeFill: 'rgba(240, 234, 216, 0.28)', maxOpacity: 0.35, starCount: 90  },
    };

    // ── Defaults cuando no hay data-attrs ───────────────────────────────────
    var DEFAULT_PALETTE = 'blue';
    var DEFAULT_THEME   = 'dark';

    // ── Estado dinamico (leido cada N frames) ────────────────────────────────
    var _frame      = 0;
    var _READ_EVERY = 60; // frames entre lecturas de data-attrs
    var _curPalette = DEFAULT_PALETTE;
    var _curTheme   = DEFAULT_THEME;
    var _pal        = SD_PALETTES[DEFAULT_PALETTE];
    var _thm        = SD_THEMES[DEFAULT_THEME];

    function readTheme() {
        var root = document.documentElement;
        var p = root.getAttribute('data-palette') || DEFAULT_PALETTE;
        var t = root.getAttribute('data-theme')   || DEFAULT_THEME;
        if (p !== _curPalette || t !== _curTheme) {
            _curPalette = p;
            _curTheme   = t;
            _pal = SD_PALETTES[p]  || SD_PALETTES[DEFAULT_PALETTE];
            _thm = SD_THEMES[t]    || SD_THEMES[DEFAULT_THEME];
            // ajustar cantidad de estrellas si cambia el tema
            _adjustStarCount(_thm.starCount);
        }
    }

    // ── Stars ────────────────────────────────────────────────────────────────
    var stars = [];

    function Star() { this.reset(); }
    Star.prototype.reset = function () {
        this.x       = Math.random() * width;
        this.y       = Math.random() * height;
        this.size    = Math.random() * 1.8 + 0.4;
        this.opacity = Math.random() * 0.6 + 0.2;
        this.twinkle = Math.random() * 0.012 + 0.006;
        // indice de color fijo por estrella para evitar parpadeo de color
        this.colorIdx = Math.floor(Math.random() * 5);
    };
    Star.prototype.update = function () {
        this.opacity = 0.2 + Math.sin(Date.now() * this.twinkle) * 0.4;
    };
    Star.prototype.draw = function () {
        var colors = _pal.stars;
        var col    = colors[this.colorIdx % colors.length];
        var alpha  = Math.max(0, Math.min(_thm.maxOpacity, this.opacity));
        ctx.save();
        ctx.globalAlpha = alpha;
        ctx.fillStyle   = col;
        ctx.shadowBlur  = 6;
        ctx.shadowColor = _pal.glow;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
    };

    function _adjustStarCount(target) {
        while (stars.length < target) { stars.push(new Star()); }
        if (stars.length > target)    { stars.length = target; }
    }

    for (var i = 0; i < 150; i++) { stars.push(new Star()); }

    // ── Meteors ──────────────────────────────────────────────────────────────
    var meteors = [];

    function Meteor() { this.reset(); }
    Meteor.prototype.reset = function () {
        this.x       = Math.random() * width * 1.3;
        this.y       = Math.random() * (height * 0.4);
        this.length  = Math.random() * 100 + 60;
        this.speed   = Math.random() * 8 + 5;
        this.angle   = Math.PI / 4 + (Math.random() * 0.3 - 0.15);
        this.opacity = 0.9;
        this.active  = true;
    };
    Meteor.prototype.update = function () {
        this.x += Math.cos(this.angle) * this.speed;
        this.y += Math.sin(this.angle) * this.speed;
        this.opacity -= 0.02;
        if (this.opacity <= 0) this.active = false;
    };
    Meteor.prototype.draw = function () {
        // en light mode los meteoros son casi invisibles — reducir presencia
        var maxA = _curTheme === 'light' ? 0.25 : 0.85;
        ctx.save();
        ctx.globalAlpha = Math.max(0, Math.min(maxA, this.opacity * 0.85));
        ctx.strokeStyle = _pal.meteor;
        ctx.lineWidth   = 2.2;
        ctx.shadowBlur  = 14;
        ctx.shadowColor = _pal.meteorGlow;
        ctx.beginPath();
        ctx.moveTo(this.x, this.y);
        ctx.lineTo(
            this.x - Math.cos(this.angle) * this.length,
            this.y - Math.sin(this.angle) * this.length
        );
        ctx.stroke();
        ctx.restore();
    };

    // ── Loop principal ───────────────────────────────────────────────────────
    function animate() {
        // leer tema cada N frames para no saturar getAttribute
        if (_frame % _READ_EVERY === 0) { readTheme(); }
        _frame++;

        ctx.fillStyle = _thm.fadeFill;
        ctx.fillRect(0, 0, width, height);

        stars.forEach(function (s) { s.update(); s.draw(); });

        // en light mode menos meteoros
        var meteorChance = _curTheme === 'light' ? 0.006 : 0.014;
        if (Math.random() < meteorChance) { meteors.push(new Meteor()); }

        meteors = meteors.filter(function (m) {
            if (m.active) { m.update(); m.draw(); return true; }
            return false;
        });

        requestAnimationFrame(animate);
    }

    animate();
})();
</script>
@endpush
@endonce