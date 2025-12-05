<!DOCTYPE html>
<html lang="cs" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZMT Projekt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="https://fel.cvut.cz/favicon/favicon-16x16.png">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;700&display=swap');

        :root {
            --forward: #00f0ff; /* Cyan */
            --backward: #ff004c; /* Red */
        }

        body {
            background-color: #050505;
            color: #e0e0e0;
            font-family: 'Space Grotesk', sans-serif;
            overflow-x: hidden;
            cursor: default; /* Custom cursor handling */
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            display: none
        }
        ::-webkit-scrollbar-track {
            background: #000;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--backward);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--forward);
        }

        /* Custom Cursor */
        /*
        .cursor-dot,
        .cursor-outline {
            position: fixed;
            top: 0;
            left: 0;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            z-index: 9999;
            pointer-events: none;
        }
        .cursor-dot {
            width: 8px;
            height: 8px;
            background-color: white;
        }
        .cursor-outline {
            width: 40px;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            transition: width 0.2s, height 0.2s, background-color 0.2s;
        }*/

        strong.mark{
            color: var(--backward);
        }

        /* Glitch Effect for Titles */
        .glitch-text {
            position: relative;
        }
        .glitch-text::before,
        .glitch-text::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #050505;
        }
        .glitch-text::before {
            left: 2px;
            text-shadow: -1px 0 var(--backward);
            animation: glitch-anim-1 2s infinite linear alternate-reverse;
        }
        .glitch-text::after {
            left: -2px;
            text-shadow: -1px 0 var(--forward);
            animation: glitch-anim-2 3s infinite linear alternate-reverse;
        }

        @keyframes glitch-anim-1 {
            0% { clip-path: inset(20% 0 80% 0); }
            20% { clip-path: inset(60% 0 10% 0); }
            40% { clip-path: inset(40% 0 50% 0); }
            60% { clip-path: inset(80% 0 5% 0); }
            80% { clip-path: inset(10% 0 70% 0); }
            100% { clip-path: inset(30% 0 60% 0); }
        }
        @keyframes glitch-anim-2 {
            0% { clip-path: inset(10% 0 60% 0); }
            20% { clip-path: inset(30% 0 20% 0); }
            40% { clip-path: inset(70% 0 40% 0); }
            60% { clip-path: inset(5% 0 80% 0); }
            80% { clip-path: inset(50% 0 10% 0); }
            100% { clip-path: inset(20% 0 30% 0); }
        }

        /* Image Duality Effect */
        .duality-img {
            filter: grayscale(100%) contrast(1.2);
            transition: filter 0.5s ease, transform 0.5s ease;
        }
        .duality-img:hover {
            filter: grayscale(0%) contrast(1) drop-shadow(4px 4px 0 var(--forward)) drop-shadow(-4px -4px 0 var(--backward));
            transform: scale(1.02);
        }

        /* Reveal Animation Classes */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Magnetic Button */
        .magnetic-btn {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Background Grid */
        .bg-grid {
            background-size: 40px 40px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        }
    </style>
</head>
<body class="antialiased">

<header class="relative h-screen flex flex-col items-center justify-center overflow-hidden bg-grid">
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-[#050505]"></div>

    <h1 class="text-7xl md:text-9xl font-bold tracking-tighter uppercase text-center leading-none z-10 glitch-text mb-4" data-text="INVERZE">
        INVERZE
    </h1>

    <div class="absolute w-[1px] h-screen bg-gradient-to-b from-transparent via-red-600 to-transparent left-1/4 opacity-20"></div>
    <div class="absolute w-[1px] h-screen bg-gradient-to-t from-transparent via-cyan-400 to-transparent right-1/4 opacity-20"></div>
</header>
<!--
<section class="max-w-8xl mx-auto px-6 py-24 md:py-32 relative flex flex-col md:flex-row">
    <div class="flex flex-col md:flex-row gap-12 items-start mb-5">
        <div class="md:w-1/4 ml-4">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">vize projektu</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="md:w-3/4">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Projekt je inspirován filmem <strong class="mark">TENET</strong>, který je o tajném agentovi, který má zabránit konci světa, ale háček je v tom, že v tomhle světě <strong class="mark">jde čas i pozpátku</strong>.
            </p>
        </div>
    </div>
    <div class="flex flex-col md:flex-row gap-12 items-start">
        <div class="md:w-1/4 ml-4">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">technologie</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="md:w-3/4">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Videa jsou točená na <strong class="mark">Xiaomi 15</strong>. O postprodukci se pak postaralo kombo <strong class="mark">GIMP (na fotky)</strong> a <strong class="mark">Audacity (na audio)</strong>. Všechno jsme to nakonec sestříhali v <strong class="mark">DaVinci Resolve</strong>.
            </p>
        </div>
    </div>
</section>
<section class="max-w-8xl mx-auto px-6 pt-0 pb-24 md:pb-32 relative flex flex-col md:flex-row">
    <div class="flex flex-col md:flex-row gap-12 items-start mb-5">
        <div class="md:w-1/4 ml-4">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">informace o videu</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="md:w-3/4">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Video je exportováno ve formátu .mov v rozlišení 1920x1080 30FPS s velikostí 370MB
            </p>
        </div>
    </div>
    <div class="flex flex-col md:flex-row gap-12 items-start mb-5">
        <div class="md:w-1/4 ml-4">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">prezentace</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="md:w-3/4">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Prezentace formou webové stránky je hostovaná na serveru <strong class="mark">zwa.toad</strong>, který slouží k odevzdávání semestrálních projektů na předmět <strong class="mark">ZWA</strong>. Stránka je tvořena pomocí frontendové knihovny <strong class="mark">tailwind</strong a pomocí custom animací <strong class="mark">glitche textu</strong>.
            </p>
        </div>
    </div>
</section> -->

<section class="max-w-8xl mx-auto px-6 py-24 md:py-32 relative grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

    <div class="flex flex-col items-start">
        <div class="w-full mb-2">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">vize projektu</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="w-full">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Projekt je inspirován filmem <strong class="mark">TENET</strong>, který je o tajném agentovi, který má zabránit konci světa, ale háček je v tom, že v tomhle světě <strong class="mark">jde čas i pozpátku</strong>.
            </p>
        </div>
    </div>

    <div class="flex flex-col items-start">
        <div class="w-full mb-2">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">technologie</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="w-full">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Videa jsou točená na <strong class="mark">Xiaomi 15</strong>. O postprodukci se pak postaralo kombo <strong class="mark">GIMP (na fotky)</strong> a <strong class="mark">Audacity (na audio)</strong>. Všechno jsme to nakonec sestříhali v <strong class="mark">DaVinci Resolve</strong>.
            </p>
        </div>
    </div>

</section>

<section class="max-w-8xl mx-auto px-6 pt-0 pb-24 md:pb-32 relative grid grid-cols-1 md:grid-cols-2 gap-12 items-start">

    <div class="flex flex-col items-start">
        <div class="w-full mb-2">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">informace o videu</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="w-full">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Video je exportováno ve formátu .mov v rozlišení 1920x1080 30FPS s velikostí 370MB.
            </p>
        </div>
    </div>

    <div class="flex flex-col items-start">
        <div class="w-full mb-2">
            <h2 class="text-xs text-red-500 tracking-[0.2em] font-bold mb-4 uppercase">prezentace</h2>
            <div class="w-12 h-1 bg-white mb-6"></div>
        </div>
        <div class="w-full">
            <p class="text-xl md:text-2xl leading-relaxed text-gray-300 reveal mb-8">
                Prezentace formou webové stránky je hostovaná na serveru <strong class="mark">zwa.toad</strong>, který slouží k odevzdávání semestrálních projektů na předmět <strong class="mark">ZWA</strong>. Stránka je tvořena pomocí frontendové knihovny <strong class="mark">tailwind</strong> a pomocí custom animací <strong class="mark">glitche textu</strong>.
            </p>
        </div>
    </div>

</section>

<section class="w-full bg-white text-black py-20 overflow-hidden relative group">
    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition-opacity duration-500 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-end mb-8">
            <h2 class="text-4xl md:text-6xl font-bold tracking-tighter uppercase">Naše veledílo</h2>
        </div>

        <div class="relative w-full aspect-video bg-black shadow-[20px_20px_0px_0px_rgba(255,0,76,1)] transition-transform duration-500 hover:translate-x-[-5px] hover:translate-y-[-5px]">
            <iframe
                    class="w-full h-full object-cover opacity-90 hover:opacity-100 transition-opacity duration-300"
                    src="https://www.youtube.com/embed/k6j9g7JJBR4"
                    title="INVERZE"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>

            <div class="absolute top-4 left-4 border border-white/20 px-2 py-1 text-[10px] text-white/50 font-mono tracking-widest pointer-events-none">
                ISO 800 // F 2.8
            </div>
            <div class="absolute bottom-4 right-4 text-white text-2xl animate-pulse pointer-events-none">
                <i class="fas fa-play"></i>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-32">
    <div class="flex flex-col items-center mb-16">
        <h2 class="text-3xl font-bold tracking-[0.5em] text-center mb-2 uppercase">PROCES TVORBY</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="relative group reveal magnetic-element">
            <div class="overflow-hidden bg-gray-900 aspect-[4/5] mb-4 relative border border-gray-800">
                <img src="1000003541.jpg" alt="Process 1" class="w-full h-full object-cover duality-img">
                <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
            </div>
            <h3 class="text-lg font-bold uppercase tracking-wide">Natáčení</h3>
            <p class="text-gray-500 text-sm mt-1">Večer natáčení</p>
        </div>

        <div class="relative group reveal magnetic-element" style="transition-delay: 100ms;">
            <div class="overflow-hidden bg-gray-900 aspect-[4/5] mb-4 relative border border-gray-800">
                <img src="1000003557.jpg" alt="Process 2" class="w-full h-full object-cover duality-img">
                <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
            </div>
            <h3 class="text-lg font-bold uppercase tracking-wide">Střih</h3>
            <p class="text-gray-500 text-sm mt-1">Ukázka střihu</p>
        </div>

        <div class="relative group reveal magnetic-element" style="transition-delay: 200ms;">
            <div class="overflow-hidden bg-gray-900 aspect-[4/5] mb-4 relative border border-gray-800">
                <img src="1000003559.jpg" alt="Process 3" class="w-full h-full object-cover duality-img">
                <div class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-black to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
            </div>
            <h3 class="text-lg font-bold uppercase tracking-wide">Proces střihu</h3>
            <p class="text-gray-500 text-sm mt-1">Pauza při kompletování projektu</p>
        </div>

        <div class="relative group reveal magnetic-element" style="transition-delay: 200ms;">
            <div class="overflow-hidden bg-gray-900 aspect-[4/5] mb-4 relative border border-gray-800">
                <img src="DaVinci Resolve - ZMT v1 03.12.2025 21_30_29.png" alt="Process 3" class="w-full h-full object-cover duality-img">
            </div>
            <h3 class="text-lg font-bold uppercase tracking-wide">Davinci</h3>
        </div>

        <div class="relative group reveal magnetic-element" style="transition-delay: 200ms;">
            <div class="overflow-hidden bg-gray-900 aspect-[4/5] mb-4 relative border border-gray-800">
                <img src="audacity zmt.png" alt="Process 3" class="w-full h-full object-cover duality-img">
            </div>
            <h3 class="text-lg font-bold uppercase tracking-wide">Audacity</h3>
        </div>

        <div class="relative group reveal magnetic-element" style="transition-delay: 200ms;">
            <div class="overflow-hidden bg-gray-900 aspect-[4/5] mb-4 relative border border-gray-800">
                <img src="gimp%20zmt.png" alt="Process 3" class="w-full h-full object-cover duality-img">
            </div>
            <h3 class="text-lg font-bold uppercase tracking-wide">Gimp</h3>
        </div>

    </div>
</section>
<section class="bg-zinc-900 border-t border-zinc-800 py-24 relative overflow-hidden">
    <div class="max-w-6xl mx-auto px-6 relative z-10">
        <h2 class="text-5xl font-bold mb-16 text-center tracking-tighter">TVŮRCI</h2>

        <div class="flex flex-col md:flex-row justify-center gap-16">
            <div class="text-center group reveal">
                <div class="relative w-48 h-48 mx-auto mb-6 rounded-full p-1 border border-zinc-700 group-hover:border-red-500 transition-colors duration-500">
                    <div class="w-full h-full rounded-full overflow-hidden relative">
                        <img src="1000003560.jpg" alt="MH2" class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white">Michal Hoda</h3>
                <p class="text-red-500 text-sm tracking-[0.2em] uppercase mt-2">Režie / Hlavní role / Mistr střihu</p>
            </div>
            <div class="text-center group reveal" style="transition-delay: 200ms;">
                <div class="relative w-48 h-48 mx-auto mb-6 rounded-full p-1 border border-zinc-700 group-hover:border-cyan-400 transition-colors duration-500">
                    <div class="w-full h-full rounded-full overflow-hidden relative">
                        <img src="1000003561.jpg" alt="MH" class="w-full h-full object-cover filter grayscale group-hover:grayscale-0 transition-all duration-500">
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white">Michal Hrouda</h3>
                <p class="text-cyan-400 text-sm tracking-[0.2em] uppercase mt-2">Technické zázemí / Webová prezentace / Kamera</p>
            </div>
        </div>
    </div>
</section>
<footer class="bg-black py-12 border-t border-zinc-900 text-center">
    <div class="flex flex-col items-center justify-center gap-4">
        <p class="text-zinc-600 text-xs tracking-widest uppercase">
            &copy; 2025 Projekt na ZMP Michal Hoda a Michal Hrouda
        </p>
    </div>
</footer>

<script>
    const cursorDot = document.getElementById("cursor-dot");
    const cursorOutline = document.getElementById("cursor-outline");

    document.querySelectorAll('a, button, .duality-img').forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursorOutline.style.transform = 'translate(-50%, -50%) scale(1.5)';
            cursorOutline.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
            cursorOutline.style.borderColor = 'transparent';
        });
        el.addEventListener('mouseleave', () => {
            cursorOutline.style.transform = 'translate(-50%, -50%) scale(1)';
            cursorOutline.style.backgroundColor = 'transparent';
            cursorOutline.style.borderColor = 'rgba(255, 255, 255, 0.5)';
        });
    });

    // --- SCROLL REVEAL LOGIC ---
    const revealElements = document.querySelectorAll(".reveal");

    const revealOnScroll = () => {
        const windowHeight = window.innerHeight;
        const elementVisible = 150;

        revealElements.forEach((reveal) => {
            const elementTop = reveal.getBoundingClientRect().top;
            if (elementTop < windowHeight - elementVisible) {
                reveal.classList.add("active");
            } else {
                // Optional: remove class to replay animation when scrolling back up
                // reveal.classList.remove("active");
            }
        });
    };

    window.addEventListener("scroll", revealOnScroll);
    // Trigger once on load
    revealOnScroll();

    // --- MAGNETIC BUTTONS (Micro-interaction) ---
    // Subtle movement of elements towards the cursor
    const magnets = document.querySelectorAll('.magnetic-element');

    /*
    magnets.forEach((magnet) => {
        magnet.addEventListener('mousemove', (e) => {
            const position = magnet.getBoundingClientRect();
            const x = e.clientX - position.left - position.width / 2;
            const y = e.clientY - position.top - position.height / 2;

            magnet.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
        });

        magnet.addEventListener('mouseleave', () => {
            magnet.style.transform = 'translate(0px, 0px)';
        });
    });
*/
    // --- REVERSE SCROLL BUTTON ---
    document.getElementById('reverse-btn').addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // --- GLITCH TEXT RANDOMIZER ---
    // Occasionally changes the clip-path randomly for extra chaos
    const glitchHeader = document.querySelector('.glitch-text');
    setInterval(() => {
        if(Math.random() > 0.95) {
            glitchHeader.style.transform = `translateX(${Math.random() * 4 - 2}px)`;
            setTimeout(() => {
                glitchHeader.style.transform = 'translateX(0)';
            }, 100);
        }
    }, 2000);

</script>
</body>
</html>