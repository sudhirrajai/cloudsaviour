<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CloudSaviour — Something is coming</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg:  #020508;
  --a1:  #3b82f6;
  --a2:  #8b5cf6;
  --a3:  #10b981;
  --tx:  #e2eeff;
  --t2:  #4a6080;
  --t3:  #1a2840;
  --H:   'Bricolage Grotesque', sans-serif;
  --M:   'JetBrains Mono', monospace;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html {
  height: 100%;
  font-size: 16px;
}

body {
  background: var(--bg);
  color: var(--tx);
  font-family: var(--M);
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  overflow-x: hidden;
  overflow-y: auto;
  cursor: none;
  position: relative;
}

/* ── CANVAS ── */
canvas {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
}

/* ── GRAIN ── */
.grain {
  position: fixed; inset: 0; z-index: 1; pointer-events: none;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.04'/%3E%3C/svg%3E");
}

/* ── VIGNETTE ── */
.vignette {
  position: fixed; inset: 0; z-index: 2; pointer-events: none;
  background: radial-gradient(ellipse at center, transparent 30%, rgba(2,5,8,.9) 100%);
}

/* ── FLOATING TAGS ── */
.ftags { position: fixed; inset: 0; z-index: 3; pointer-events: none; overflow: hidden; }
.ftag {
  position: absolute; bottom: -30px;
  font-family: var(--M); font-size: .6rem;
  color: var(--t3); letter-spacing: .05em; white-space: nowrap;
  animation: floatUp linear infinite; opacity: 0;
}
@keyframes floatUp {
  0%   { opacity: 0; transform: translateY(0); }
  8%   { opacity: 1; }
  88%  { opacity: .35; }
  100% { opacity: 0; transform: translateY(-105vh); }
}

/* ── CURSOR ── */
#cur  { position: fixed; width: 9px; height: 9px; background: var(--a1); border-radius: 50%; pointer-events: none; z-index: 9999; transform: translate(-50%,-50%); mix-blend-mode: screen; transition: width .25s, height .25s, background .25s; }
#cring { position: fixed; width: 34px; height: 34px; border: 1px solid rgba(59,130,246,.35); border-radius: 50%; pointer-events: none; z-index: 9998; transform: translate(-50%,-50%); transition: width .3s, height .3s, border-color .3s; }

/* ── LAYOUT ── */
.page {
  position: relative; z-index: 10;
  width: 100%; max-width: 600px;
  padding: 0 24px;
  display: flex; flex-direction: column; align-items: center;
  text-align: center;
  /* vertical rhythm handled by justify-content on body */
}

/* ── TOP BAR ── */
.topbar {
  width: 100%; max-width: 600px;
  padding: 20px 24px 0;
  position: relative; z-index: 10;
  display: flex; align-items: center; justify-content: space-between;
  flex-shrink: 0;
  opacity: 0; animation: fIn .7s ease .1s both;
}
.logo {
  display: flex; align-items: center; gap: 8px;
  text-decoration: none; color: var(--tx);
}
.lm { width: 26px; height: 26px; background: linear-gradient(135deg, var(--a1), var(--a2)); border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.lm svg { width: 14px; height: 14px; fill: none; stroke: #fff; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.logo-name { font-family: var(--H); font-weight: 700; font-size: .95rem; letter-spacing: -.025em; }
.logo-by { font-size: .58rem; color: var(--t2); margin-left: 2px; }
.vmcore-link { font-size: .65rem; color: var(--t2); text-decoration: none; letter-spacing: .06em; transition: color .2s; }
.vmcore-link:hover { color: var(--a1); }

/* ── MAIN CONTENT ── */
.main {
  flex: 1;
  display: flex; flex-direction: column; align-items: center;
  justify-content: center;
  padding: 20px 0;
  gap: clamp(12px, 2.5vh, 28px);
}

/* STATUS PILL */
.pill {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 4px 12px;
  border: 1px solid rgba(59,130,246,.2); background: rgba(59,130,246,.05);
  border-radius: 100px; font-size: .65rem; color: var(--a1); letter-spacing: .08em;
  opacity: 0; animation: fIn .7s ease .3s both;
}
.pdot { width: 5px; height: 5px; border-radius: 50%; background: var(--a3); box-shadow: 0 0 5px var(--a3); animation: blink 2s ease-in-out infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

/* HEADLINE */
.hl {
  font-family: var(--H);
  font-size: clamp(2rem, 5.5vw, 4.2rem);
  font-weight: 800; line-height: 1.06;
  letter-spacing: -.04em;
  margin: 0;
}
.hl-line { display: block; overflow: hidden; }
.hl-inner { display: block; transform: translateY(105%); animation: slideUp .85s cubic-bezier(.16,1,.3,1) both; }
.hl-line:nth-child(1) .hl-inner { animation-delay: .4s; }
.hl-line:nth-child(2) .hl-inner { animation-delay: .52s; }
.hl-line:nth-child(3) .hl-inner { animation-delay: .64s; }
@keyframes slideUp { to { transform: translateY(0); } }

.grd {
  background: linear-gradient(110deg, var(--a1) 0%, var(--a2) 55%, var(--a3) 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text; background-size: 200%;
  animation: gs 5s ease-in-out infinite;
}
@keyframes gs { 0%,100%{background-position:0%} 50%{background-position:100%} }

/* SUBTEXT */
.sub {
  font-size: clamp(.75rem, 1.5vw, .875rem);
  color: var(--t2); line-height: 1.85; font-weight: 300; letter-spacing: .01em;
  opacity: 0; animation: fIn .7s ease .85s both;
}
.sub em { color: var(--tx); font-style: normal; }

/* REDACTED */
.redacted {
  display: flex; flex-direction: column; gap: 8px; align-items: center;
  opacity: 0; animation: fIn .7s ease .95s both;
}
.rrow { display: flex; align-items: center; gap: 9px; font-size: .65rem; color: var(--t2); letter-spacing: .04em; }
.rbar { height: 10px; border-radius: 2px; background: var(--t3); animation: rshimmer 2.8s ease-in-out infinite; }
@keyframes rshimmer { 0%,100%{opacity:.5} 50%{opacity:1;background:rgba(59,130,246,.14)} }
.rrow:nth-child(1) .rbar { width: 130px; animation-delay: 0s; }
.rrow:nth-child(2) .rbar { width: 95px;  animation-delay: .5s; }
.rrow:nth-child(3) .rbar { width: 160px; animation-delay: 1s; }

/* FORM */
.form-wrap { width: 100%; max-width: 400px; opacity: 0; animation: fIn .7s ease 1.05s both; }
.frow {
  display: flex; gap: 0;
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(148,163,184,.12);
  border-radius: 9px; overflow: hidden;
  transition: border-color .3s, box-shadow .3s;
}
.frow:focus-within { border-color: rgba(59,130,246,.4); box-shadow: 0 0 0 3px rgba(59,130,246,.08); }
.frow input {
  flex: 1; background: transparent; border: none; outline: none;
  padding: 12px 16px; font-family: var(--M); font-size: .78rem;
  color: var(--tx); letter-spacing: .02em; min-width: 0;
}
.frow input::placeholder { color: var(--t2); }
.frow button {
  padding: 10px 16px; background: var(--a1); border: none; cursor: none;
  font-family: var(--H); font-size: .82rem; font-weight: 700;
  color: #fff; letter-spacing: -.01em; white-space: nowrap;
  transition: background .2s;
}
.frow button:hover { background: #60a5fa; }
.fnote { margin-top: 9px; font-size: .62rem; color: var(--t2); letter-spacing: .04em; }
.fnote em { color: var(--a3); font-style: normal; }

/* SUCCESS */
.succ { display: none; flex-direction: column; align-items: center; gap: 6px; padding: 12px; }
.succ-ico { width: 36px; height: 36px; background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; animation: popIn .5s cubic-bezier(.16,1,.3,1); }
@keyframes popIn { from{transform:scale(0)} to{transform:scale(1)} }
.succ-tx { font-size: .75rem; color: var(--a3); letter-spacing: .06em; }
.succ-pos { font-size: .62rem; color: var(--t2); }

/* SOCIAL PROOF ROW */
.proof {
  display: flex; align-items: center; gap: 10px;
  font-size: .65rem; color: var(--t2); letter-spacing: .04em;
  opacity: 0; animation: fIn .7s ease 1.15s both;
}
.avs { display: flex; }
.av {
  width: 20px; height: 20px; border-radius: 50%;
  border: 2px solid var(--bg);
  display: flex; align-items: center; justify-content: center;
  font-size: .55rem; font-weight: 700; font-family: var(--H);
  margin-left: -5px;
}
.av:first-child { margin-left: 0; }
.proof-num { font-family: var(--H); font-size: 1rem; font-weight: 800; color: var(--tx); letter-spacing: -.03em; }

/* COUNTDOWN */
.cd {
  display: flex; align-items: center; gap: clamp(10px, 2vw, 20px);
  opacity: 0; animation: fIn .7s ease 1.25s both;
}
.cdi { text-align: center; }
.cdn { font-family: var(--H); font-size: clamp(1.4rem, 3.5vw, 2rem); font-weight: 800; color: var(--tx); letter-spacing: -.04em; display: block; line-height: 1; }
.cdl { font-size: .55rem; color: var(--t2); letter-spacing: .1em; text-transform: uppercase; margin-top: 3px; display: block; }
.cds { font-family: var(--H); font-size: clamp(1.2rem, 3vw, 1.7rem); color: var(--t3); padding-bottom: 8px; animation: blink 1s ease-in-out infinite; }

/* BOTTOM BAR */
.botbar {
  width: 100%; max-width: 600px;
  padding: 0 24px 18px;
  position: relative; z-index: 10;
  display: flex; align-items: center; justify-content: center;
  gap: 16px; flex-shrink: 0;
  opacity: 0; animation: fIn .7s ease 1.4s both;
}
.blink { font-size: .62rem; color: var(--t2); text-decoration: none; letter-spacing: .05em; transition: color .2s; }
.blink:hover { color: var(--a1); }
.bsep { width: 3px; height: 3px; border-radius: 50%; background: var(--t3); }

@keyframes fIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }

/* ── RESPONSIVE TWEAKS ── */
@media (max-height: 700px) {
  .main { gap: 10px; }
  .topbar { padding-top: 14px; }
  .botbar { padding-bottom: 12px; }
}
@media (max-width: 480px) {
  .frow button { padding: 10px 12px; font-size: .76rem; }
  .logo-by { display: none; }
  .cd { gap: 10px; }
}
@media (max-width: 360px) {
  .frow { flex-direction: column; border-radius: 9px; }
  .frow button { padding: 10px; border-radius: 0 0 9px 9px; }
}
</style>
</head>
<body>

<div id="cur"></div>
<div id="cring"></div>
<canvas id="c"></canvas>
<div class="grain"></div>
<div class="vignette"></div>
<div class="ftags" id="ftags"></div>

<!-- TOP BAR -->
<div class="topbar">
  <a href="/" class="logo">
    <div class="lm">
      <svg viewBox="0 0 16 16"><rect x="2" y="3" width="12" height="8" rx="1.5"/><path d="M5 11v2M11 11v2M3 13h10"/></svg>
    </div>
    <span class="logo-name">CloudSaviour <span class="logo-by">by VMCore</span></span>
  </a>
  <a href="https://vmcore.in" target="_blank" class="vmcore-link">vmcore.in ↗</a>
</div>

<!-- MAIN -->
<div class="page">
  <div class="main">

    <!-- STATUS -->
    <div class="pill"><span class="pdot"></span>BUILDING IN STEALTH</div>

    <!-- HEADLINE -->
    <h1 class="hl">
      <span class="hl-line"><span class="hl-inner">Your AWS bill</span></span>
      <span class="hl-line"><span class="hl-inner">has a <span class="grd">secret.</span></span></span>
      <span class="hl-line"><span class="hl-inner">We found it.</span></span>
    </h1>

    <!-- SUBTEXT -->
    <p class="sub">
      Something every developer pays for.<br>
      Almost nobody notices.<br>
      We built the fix. <em>You'll see it soon.</em>
    </p>

    <!-- REDACTED -->
    <div class="redacted">
      <div class="rrow"><span>///</span><div class="rbar"></div><span>detected</span></div>
      <div class="rrow"><span>///</span><div class="rbar"></div><span>eliminated</span></div>
      <div class="rrow"><span>///</span><div class="rbar"></div><span>saved</span></div>
    </div>

    <!-- FORM -->
    <div class="form-wrap">
      <div class="frow" id="frow">
        <input type="email" id="email" placeholder="your@email.com" autocomplete="off" />
        <button onclick="join()">Get early access →</button>
      </div>
      <div class="succ" id="succ">
        <div class="succ-ico">✓</div>
        <div class="succ-tx">YOU'RE IN THE LIST</div>
        <div class="succ-pos">You are #<span id="posn">{{ $totalCount }}</span> in line</div>
      </div>
      <p class="fnote">No spam. No pitch decks. Just <em>early access</em> when we launch.</p>
    </div>

    <!-- SOCIAL PROOF -->
    <div class="proof">
      <div class="avs">
        <div class="av" style="background:rgba(59,130,246,.2);color:var(--a1)">R</div>
        <div class="av" style="background:rgba(139,92,246,.2);color:var(--a2)">S</div>
        <div class="av" style="background:rgba(16,185,129,.2);color:var(--a3)">A</div>
        <div class="av" style="background:rgba(245,158,11,.2);color:#f59e0b">K</div>
        <div class="av" style="background:rgba(239,68,68,.2);color:#ef4444">+</div>
      </div>
      <span><span class="proof-num" id="wnum">{{ $totalCount }}</span> developers already waiting</span>
    </div>

    <!-- COUNTDOWN -->
    <div class="cd">
      <div class="cdi"><span class="cdn" id="dd">23</span><span class="cdl">days</span></div>
      <span class="cds">:</span>
      <div class="cdi"><span class="cdn" id="dh">00</span><span class="cdl">hours</span></div>
      <span class="cds">:</span>
      <div class="cdi"><span class="cdn" id="dm">00</span><span class="cdl">mins</span></div>
      <span class="cds">:</span>
      <div class="cdi"><span class="cdn" id="ds">00</span><span class="cdl">secs</span></div>
    </div>

  </div>
</div>

<!-- BOTTOM BAR -->
<div class="botbar">
  <a href="/" class="blink">← Back to home</a>
  <div class="bsep"></div>
  <a href="https://vmcore.in" target="_blank" class="blink">VMCore</a>
  <div class="bsep"></div>
  <a href="#" class="blink">Twitter / X</a>
  <div class="bsep"></div>
  <span style="font-size:.6rem;color:var(--t3);letter-spacing:.06em">© 2025 VMCORE</span>
</div>

<script>
/* CURSOR */
const cur = document.getElementById('cur');
const cring = document.getElementById('cring');
let mx=0,my=0,rx=0,ry=0;
document.addEventListener('mousemove', e => {
  mx=e.clientX; my=e.clientY;
  cur.style.left=mx+'px'; cur.style.top=my+'px';
});
(function raf(){
  rx+=(mx-rx)*.12; ry+=(my-ry)*.12;
  cring.style.left=rx+'px'; cring.style.top=ry+'px';
  requestAnimationFrame(raf);
})();
document.querySelectorAll('button,a,input').forEach(el=>{
  el.addEventListener('mouseenter',()=>{ cur.style.width='18px';cur.style.height='18px';cring.style.width='48px';cring.style.height='48px';cring.style.borderColor='rgba(59,130,246,.6)'; });
  el.addEventListener('mouseleave',()=>{ cur.style.width='9px';cur.style.height='9px';cring.style.width='34px';cring.style.height='34px';cring.style.borderColor='rgba(59,130,246,.35)'; });
});

/* PARTICLE CANVAS */
const canvas=document.getElementById('c');
const ctx=canvas.getContext('2d');
let W,H;
function resize(){ W=canvas.width=window.innerWidth; H=canvas.height=window.innerHeight; }
resize(); window.addEventListener('resize',resize);

class P {
  constructor(){ this.reset(true); }
  reset(init){
    this.x=Math.random()*W; this.y=Math.random()*H;
    this.r=Math.random()*.9+.3;
    this.vx=(Math.random()-.5)*.12; this.vy=(Math.random()-.5)*.12;
    this.life=init?Math.floor(Math.random()*300):0;
    this.max=Math.random()*280+180;
    const c=['rgba(59,130,246,','rgba(139,92,246,','rgba(16,185,129,'];
    this.col=c[Math.floor(Math.random()*c.length)];
  }
  tick(){
    this.life++;
    const a=this.life<60?this.life/60:this.life>this.max-60?(this.max-this.life)/60:1;
    ctx.beginPath(); ctx.arc(this.x,this.y,this.r,0,Math.PI*2);
    ctx.fillStyle=this.col+(a*.65)+')'; ctx.fill();
    this.x+=this.vx+(mx/W-.5)*.04;
    this.y+=this.vy+(my/H-.5)*.04;
    if(this.life>=this.max) this.reset();
  }
}
const pts=[]; for(let i=0;i<100;i++) pts.push(new P());

function drawLines(){
  for(let i=0;i<pts.length;i++){
    for(let j=i+1;j<pts.length;j++){
      const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y;
      const d=Math.sqrt(dx*dx+dy*dy);
      if(d<90){ ctx.beginPath(); ctx.moveTo(pts[i].x,pts[i].y); ctx.lineTo(pts[j].x,pts[j].y); ctx.strokeStyle=`rgba(59,130,246,${(1-d/90)*.055})`; ctx.lineWidth=.4; ctx.stroke(); }
    }
  }
}
(function loop(){ ctx.clearRect(0,0,W,H); drawLines(); pts.forEach(p=>p.tick()); requestAnimationFrame(loop); })();

/* FLOATING TAGS */
const tags=['ec2:StopInstances','aws:costExplorer','idle_detected','nat_gateway','$0.045/hr','ebs:DetachVolume','snapshot_cleanup','rds:StopDBInstance','elasticIP:release','cloudwatch:metrics','auto_shutdown','cost_reduced','unused_volume','savings: $47/mo','ai_recommendation','schedule:22:00','workspace:prod'];
const ft=document.getElementById('ftags');
tags.forEach(t=>{
  const el=document.createElement('div');
  el.className='ftag'; el.textContent=t;
  el.style.left=(3+Math.random()*92)+'%';
  el.style.animationDuration=(16+Math.random()*18)+'s';
  el.style.animationDelay=(Math.random()*14)+'s';
  ft.appendChild(el);
});

/* COUNTDOWN */
const launch = new Date("{{ $launchDate }}");
launch.setHours(0,0,0,0);

function tick(){
  let now = new Date();
  let diff = launch - now;
  let isPast = diff < 0;
  let absDiff = Math.abs(diff);

  const d = Math.floor(absDiff/86400000);
  let rem = absDiff % 86400000;
  const h = Math.floor(rem/3600000); rem %= 3600000;
  const m = Math.floor(rem/60000); rem %= 60000;
  const s = Math.floor(rem/1000);

  document.getElementById('dd').textContent = (isPast ? '-' : '') + String(d).padStart(2,'0');
  document.getElementById('dh').textContent = String(h).padStart(2,'0');
  document.getElementById('dm').textContent = String(m).padStart(2,'0');
  document.getElementById('ds').textContent = String(s).padStart(2,'0');
}
tick(); setInterval(tick,1000);

/* COUNT ANIMATION */
let wc={{ $totalCount }};
setTimeout(()=>{
  let s=null; const el=document.getElementById('wnum');
  const run=ts=>{ if(!s)s=ts; const p=Math.min((ts-s)/2000,1); const e=1-Math.pow(1-p,3); el.textContent=Math.floor(e*wc); if(p<1)requestAnimationFrame(run); };
  requestAnimationFrame(run);
},1600);

/* RANDOM TICK */
setInterval(()=>{
  if(Math.random()>.55){
    // Optional: we can disable the random tick since we are pulling from database
    // Or we leave it as an ephemeral visual effect
  }
},8000);

/* FORM */
async function join(){
  const emailInput = document.getElementById('email');
  const email = emailInput.value.trim();
  if(!email || !email.includes('@')){
    emailInput.style.color='#ef4444'; emailInput.placeholder='enter a valid email ↑';
    setTimeout(()=>{ emailInput.style.color=''; emailInput.placeholder='your@email.com'; },2200);
    return;
  }

  try {
    const res = await fetch('{{ route('waitlist.store') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify({ email })
    });
    
    const data = await res.json();
    
    if (res.ok) {
      wc = data.position;
      document.getElementById('posn').textContent=wc;
      const fr=document.getElementById('frow');
      fr.style.transition='all .3s ease'; fr.style.opacity='0'; fr.style.transform='translateY(-6px)';
      setTimeout(()=>{ 
         fr.style.display='none'; 
         document.getElementById('succ').style.display='flex'; 
         document.getElementById('wnum').textContent=wc; 
      }, 300);
    } else {
        emailInput.style.color='#ef4444'; 
        emailInput.value=''; 
        emailInput.placeholder = data.errors?.email?.[0] || 'Something went wrong';
        setTimeout(()=>{ emailInput.style.color=''; emailInput.placeholder='your@email.com'; },2200);
    }
  } catch (err) {
      emailInput.style.color='#ef4444'; 
      emailInput.value=''; 
      emailInput.placeholder = 'Network error';
      setTimeout(()=>{ emailInput.style.color=''; emailInput.placeholder='your@email.com'; },2200);
  }
}
document.getElementById('email').addEventListener('keydown',e=>{ if(e.key==='Enter') join(); });
</script>
</body>
</html>
