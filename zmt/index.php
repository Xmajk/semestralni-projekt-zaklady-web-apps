<?php
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Výstava</title>

    <!-- Pixelové písmo (retro) -->
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

    <style>
        :root{
            --bg:#0e0e0e;
            --fg:#eaeaea;
            --muted:#9a9a9a;
            --accent:#37ff8b;      /* jemná moderní zelená */
            --accent-dim:#1b9c56;
            --card:#151515;
            --border:#222;
        }

        *{box-sizing:border-box}
        html,body{height:100%}

        body{
            margin:0;
            background:radial-gradient(1200px 600px at 50% -10%, #1a1a1a 0%, var(--bg) 60%);
            color:var(--fg);
            font-family:"Press Start 2P", system-ui, sans-serif;
            letter-spacing:0.5px;
        }

        header{
            text-align:center;
            padding:64px 16px 24px;
            font-size:clamp(18px,3vw,28px);
            color:var(--fg);
            text-shadow:0 0 8px rgba(55,255,139,0.15);
        }

        .sub{
            text-align:center;
            color:var(--muted);
            font-size:10px;
            opacity:.9;
            margin-bottom:28px;
        }

        .gallery{
            display:flex;
            flex-direction:column;
            gap:100px;
            width:min(980px, 92%);
            margin:0 auto 120px;
        }

        /* Položky výstavy */
        .item{
            display:flex;
            justify-content:center;
            opacity:0;
            transform:translateY(18px);
            transition:opacity .8s ease, transform .8s ease;
        }
        .item.visible{ opacity:1; transform:translateY(0); }

        .frame{
            width:100%;
            max-width:1100px;
            background:linear-gradient(0deg,#141414,#171717);
            border:2px solid var(--border);
            border-radius:10px;
            padding:10px;
            box-shadow:
                    0 0 0 1px #0b0b0b inset,
                    0 8px 30px rgba(0,0,0,.4);
        }

        .frame img,
        .frame iframe{
            display:block;
            width:100%;
            height:auto;
            border-radius:6px;
            border:1px solid #1f1f1f;
            cursor:pointer;
            transition:transform .5s ease;
            will-change:transform;
        }
        .frame img:hover,
        .frame iframe:hover{ transform:scale(1.015); }

        /* ── Lightbox ─────────────────────────────────────────── */
        #lightbox{
            position:fixed; inset:0;
            background:rgba(0,0,0,.92);
            display:none; /* flex when open */
            align-items:center; justify-content:center;
            z-index:999;
        }

        .lb-content{
            position:relative;
            max-width:92vw; max-height:92vh;
            display:flex; align-items:center; justify-content:center;
        }

        #lb-img{
            max-width:100%; max-height:100%;
            border-radius:8px;
            image-rendering:auto;
            box-shadow:0 0 0 2px #1f1f1f, 0 20px 60px rgba(0,0,0,.7);
        }

        /* Šipky (pixel/retro, ale čisté) */
        .lb-nav{
            position:absolute; inset:0;
            pointer-events:none; /* jen tlačítka chytají click */
        }
        .lb-btn{
            pointer-events:auto;
            position:absolute; top:50%;
            translate:0 -50%;
            width:52px; height:52px;
            border:2px solid var(--accent);
            border-radius:6px;
            background:rgba(20,20,20,.6);
            display:grid; place-items:center;
            cursor:pointer;
            user-select:none;
            transition:transform .15s ease, background .15s ease, box-shadow .15s ease;
            box-shadow:0 0 0 0 rgba(55,255,139,0);
        }
        .lb-btn:hover{
            background:rgba(20,20,20,.85);
            transform:scale(1.05);
            box-shadow:0 0 12px 2px rgba(55,255,139,.25);
        }
        .lb-prev{ left:-68px; }
        .lb-next{ right:-68px; }

        .lb-icon{
            font-size:18px; line-height:1;
            color:var(--accent);
        }

        .lb-close{
            position:absolute; top:-58px; right:0;
            width:46px; height:46px;
            border:2px solid var(--accent);
            border-radius:6px;
            background:rgba(20,20,20,.6);
            display:grid; place-items:center;
            cursor:pointer;
        }
        .lb-close:hover{ background:rgba(20,20,20,.85); }
        .lb-close .lb-icon{ font-size:14px; }

        /* Název / čítač dole */
        .lb-caption{
            position:absolute; bottom:-46px; left:0; right:0;
            text-align:center; font-size:10px; color:var(--muted);
        }

        /* Responsivní doladění */
        @media (max-width: 860px){
            .lb-prev{ left:-56px; }
            .lb-next{ right:-56px; }
        }
        @media (max-width: 640px){
            .gallery{ gap:60px; }
            .lb-prev{ left:-52px; }
            .lb-next{ right:-52px; }
            .lb-btn{ width:48px; height:48px; }
        }

        /* Fokus pro klávesy (a11y) */
        .lb-btn:focus-visible,
        .lb-close:focus-visible{
            outline:2px dashed var(--accent);
            outline-offset:3px;
        }
    </style>
</head>
<body>

<header>VÝSTAVA</header>
<div class="sub">klid • prostor • pixel vibe</div>

<main class="gallery" id="gallery">
    <!-- FOTO -->
    <section class="item">
        <figure class="frame">
            <img src="https://picsum.photos/id/1011/1200/800" alt="Exponát 1" data-caption="Exponát 1 – Mlčení města">
        </figure>
    </section>

    <section class="item">
        <figure class="frame">
            <img src="https://picsum.photos/id/1022/1200/800" alt="Exponát 2" data-caption="Exponát 2 – Zrno a světlo">
        </figure>
    </section>

    <section class="item">
        <figure class="frame">
            <img src="https://picsum.photos/id/1039/1200/800" alt="Exponát 3" data-caption="Exponát 3 – Mezi stíny">
        </figure>
    </section>

    <!-- VIDEO (zůstává, ale navigace šipkami je pro fotografie) -->
    <section class="item">
        <figure class="frame">
            <iframe
                src="https://www.youtube.com/embed/dQw4w9WgXcQ"
                title="Video 1"
                allowfullscreen
                loading="lazy">
            </iframe>
        </figure>
    </section>

    <!-- FOTO -->
    <section class="item">
        <figure class="frame">
            <img src="https://picsum.photos/id/1041/1200/800" alt="Exponát 4" data-caption="Exponát 4 – Poslední ozvěna">
        </figure>
    </section>
</main>

<!-- Lightbox -->
<div id="lightbox" aria-hidden="true">
    <div class="lb-content">
        <img id="lb-img" alt="">
        <div class="lb-nav" aria-hidden="false">
            <button class="lb-btn lb-prev" id="lb-prev" aria-label="Předchozí (šipka vlevo)">
                <span class="lb-icon">◀</span>
            </button>
            <button class="lb-btn lb-next" id="lb-next" aria-label="Další (šipka vpravo)">
                <span class="lb-icon">▶</span>
            </button>
            <button class="lb-close" id="lb-close" aria-label="Zavřít (Esc)">
                <span class="lb-icon">✕</span>
            </button>
            <div class="lb-caption" id="lb-cap"></div>
        </div>
    </div>
</div>

<script>
    /* Fade-in při scrollu */
    const observer = new IntersectionObserver((entries)=> {
        entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    },{ threshold: 0.12 });
    document.querySelectorAll('.item').forEach(i => observer.observe(i));

    /* Sbíráme všechny obrázky z galerie (pořadí = navigace) */
    const photoNodes = Array.from(document.querySelectorAll('.frame img'));
    const photos = photoNodes.map((img, idx) => ({
        src: img.currentSrc || img.src,
        alt: img.alt || ('Exponát ' + (idx+1)),
        cap: img.dataset.caption || img.alt || ''
    }));

    /* Lightbox elementy */
    const lb = document.getElementById('lightbox');
    const lbImg = document.getElementById('lb-img');
    const lbCap = document.getElementById('lb-cap');
    const btnPrev = document.getElementById('lb-prev');
    const btnNext = document.getElementById('lb-next');
    const btnClose = document.getElementById('lb-close');

    let currentIndex = 0;
    let isOpen = false;

    /* Otevření s indexem */
    function openLightbox(index){
        if (!photos.length) return;
        currentIndex = (index + photos.length) % photos.length;
        const {src, alt, cap} = photos[currentIndex];
        lbImg.src = src;
        lbImg.alt = alt;
        lbCap.textContent = `${cap || alt}  —  ${currentIndex+1}/${photos.length}`;
        lb.style.display = 'flex';
        lb.setAttribute('aria-hidden','false');
        isOpen = true;
        // fokus na Next pro a11y
        btnNext.focus();
    }

    /* Zavření */
    function closeLightbox(){
        lb.style.display = 'none';
        lb.setAttribute('aria-hidden','true');
        lbImg.src = '';
        isOpen = false;
    }

    /* Další / Předchozí */
    function showNext(step=1){
        openLightbox(currentIndex + step);
    }

    /* Click na obrázek v galerii -> otevři dle indexu */
    photoNodes.forEach((img, i) => {
        img.addEventListener('click', () => openLightbox(i));
    });

    /* Click mimo obsah (pozadí) zavře */
    lb.addEventListener('click', (e)=>{
        if (e.target === lb) closeLightbox();
    });

    /* Tlačítka */
    btnPrev.addEventListener('click', (e)=>{ e.stopPropagation(); showNext(-1); });
    btnNext.addEventListener('click', (e)=>{ e.stopPropagation(); showNext(+1); });
    btnClose.addEventListener('click', (e)=>{ e.stopPropagation(); closeLightbox(); });

    /* Klávesy: Esc, ←, → */
    window.addEventListener('keydown', (e)=>{
        if(!isOpen) return;
        if(e.key === 'Escape') closeLightbox();
        else if(e.key === 'ArrowRight') showNext(+1);
        else if(e.key === 'ArrowLeft') showNext(-1);
    });
</script>

</body>
</html>

