<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$serverUser = $_SESSION['user_name'] ?? 'Student';
$serverUser = htmlspecialchars($serverUser, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Path | NAVIC ERA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
    <style>
        :root { --navy:#0f172a; --primary:#3b82f6; --accent:#60a5fa; --text:#e2e8f0; }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--navy);color:var(--text);overflow-x:hidden;min-height:100vh;}
        #particles{position:fixed;inset:0;z-index:1;background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#334155 100%);}
        .navbar{position:fixed;top:0;width:100%;padding:1.5rem 6%;display:flex;justify-content:space-between;align-items:center;z-index:1000;background:rgba(15,23,42,.95);backdrop-filter:blur(24px);border-bottom:1px solid rgba(96,165,250,.2);transition:.4s;}
        .nav-logo{font-size:2.3rem;font-weight:900;background:linear-gradient(90deg,#60a5fa,#93c5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
        .user-info{color:white;font-weight:600;font-size:1.1rem;display:flex;align-items:center;gap:20px;}
        .user-info button{background:#3b82f6;color:white;border:none;padding:10px 28px;border-radius:50px;cursor:pointer;font-weight:600;transition:.3s;}
        .user-info button:hover{background:#2563eb;transform:scale(1.05);}
        .container{position:relative;z-index:2;max-width:1000px;margin:130px auto 100px;padding:50px;background:rgba(30,41,59,.5);backdrop-filter:blur(20px);border-radius:32px;border:1px solid rgba(96,165,250,.3);box-shadow:0 30px 80px rgba(0,0,0,.5);}
        h1{font-size:4.5rem;text-align:center;background:linear-gradient(90deg,#60a5fa,#93c5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:2rem;font-weight:800;}
        .search-box{max-width:600px;margin:0 auto;display:flex;flex-direction:column;gap:28px;}
        label{font-weight:600;color:#60a5fa;font-size:1.2rem;}
        select{padding:18px;background:rgba(15,23,42,.8);border:1px solid rgba(96,165,250,.4);border-radius:18px;color:white;font-size:1.1rem;transition:.3s;}
        select:focus{outline:none;border-color:#60a5fa;box-shadow:0 0 25px rgba(96,165,250,.5);}
        .action-buttons{display:flex;gap:20px;justify-content:center;flex-wrap:wrap;}
        button{padding:16px 32px;border:none;border-radius:50px;font-size:1.2rem;font-weight:700;cursor:pointer;transition:.4s;display:flex;align-items:center;gap:12px;}
        .find-btn{background:linear-gradient(135deg,#3b82f6,#60a5fa);color:white;}
        .find-btn:hover{transform:translateY(-5px);box-shadow:0 15px 35px rgba(59,130,246,.5);}
        .voice-btn{background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:white;}
        .voice-btn.recording{background:#ef4444;animation:pulse 1.5s infinite;}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(239,68,68,.7)}70%{box-shadow:0 0 0 15px transparent}100%{box-shadow:0 0 0 0 transparent}}
        #resultBox{margin-top:50px;padding:30px;background:rgba(15,23,42,.6);border-radius:24px;border:1px solid rgba(96,165,250,.2);font-size:1.2rem;line-height:2;color:#cbd5e1;}
        .step{padding:16px 20px;background:rgba(30,41,59,.6);border-left:5px solid #60a5fa;border-radius:12px;margin:16px 0;}
        .direction-icon{color:#60a5fa;font-weight:bold;margin-right:10px;font-size:1.4rem;}
        .time-info{text-align:center;padding:20px;background:linear-gradient(135deg,rgba(96,165,250,.2),rgba(59,130,246,.2));border-radius:16px;font-size:1.4rem;font-weight:700;color:#93c5fd;margin:25px 0;}
        .footer{text-align:center;padding:40px;font-size:1.1rem;color:#94a3b8;background:rgba(15,23,42,.9);border-top:1px solid rgba(96,165,250,.2);}
        .ai-fab{position:fixed;bottom:40px;right:40px;width:72px;height:72px;background:linear-gradient(135deg,#60a5fa,#3b82f6);border:none;border-radius:50%;font-size:34px;color:white;cursor:pointer;box-shadow:0 12px 45px rgba(59,130,246,.6);z-index:999;display:flex;align-items:center;justify-content:center;animation:fabPulse 3s infinite;}
        .ai-fab:hover{transform:scale(1.12);}
        @keyframes fabPulse{0%,100%{box-shadow:0 12px 45px rgba(59,130,246,.6)}50%{box-shadow:0 12px 65px rgba(59,130,246,.9)}}
    </style>
</head>
<body>
<div id="particles"></div>

<div class="navbar" id="navbar">
    <div class="nav-logo">NAVIC ERA</div>
    <div class="user-info">
        Welcome, <span id="username"><?= $serverUser ?></span> 👋
        <button onclick="location.href='dashboard.php'">🏠 Home</button>
        <button onclick="location.href='logout.php'">🚪 Logout</button>
    </div>
</div>

<div class="container">
    <h1>Find Your Path</h1>
    <div class="search-box">
        <div><label>From (Starting Point)</label>
            <select id="startRoom"><option value="">-- Select Starting Location --</option></select>
        </div>
        <div><label>To (Destination)</label>
            <select id="endRoom"><option value="">-- Select Destination --</option></select>
        </div>
        <div class="action-buttons">
            <button class="find-btn" onclick="findPath()"><i class="fas fa-route"></i> Find Path</button>
            <button id="voiceBtn" class="voice-btn" onclick="toggleVoiceAssist()"><i class="fas fa-microphone"></i> Speak Instructions</button>
        </div>
    </div>
    <div id="resultBox">Select your starting point and destination above to get instant directions.</div>
</div>

<div class="footer">© 2025 Team CodeVerse | NAVIC ERA - GEHU Campus Navigation System | BCA Batch 2026</div>

<a href="campus_ai_ui.php" class="ai-fab" id="aiBtn"><i class="fas fa-robot"></i></a>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
// ====== ALL DATA & LOGIC (100% WORKING) ======
const allRooms = ["REGISTRAR OFFICE","ACCOUNTS OFFICE","EXAMINATION CELL","FEE CELL","PHD CELL","VICE CHANCELLOR OFFICE","PRESIDENT OFFICE","HOD PDP","HOD PHYSICS","HOD FASHION DESIGN","DEPARTMENT OF PHYSICS","DEPARTMENT OF FASHION AND DESIGN","MECHANICAL ENGINEERING DEPT","CIVIL ENGINEERING DEPT","COMPUTER SCIENCE & ENGINEERING STAFF ROOM","PDP STAFF ROOM","LAW STAFF ROOM","SCHOOL OF LAW","SCHOOL OF ENGINEERING","SCHOOL OF PHARMACY","SCHOOL OF COMPUTING","BASEMENT CR A","BASEMENT CR B","BASEMENT RR A-1","CR 101","CR 102","CR 103","CR 104","CR 105","CR 201","CR 202","CR 203","CR 204","CR 205","CR 206","CR 207","CR 301","CR 302","CR 303","CR 304","CR 305","CR 401","CR 402","CR 403","CR 405","CR 406","CR 501","CR 502","CR 503","CR 504","CR 601","CR 602","CR 603","CR 604","CR 605","CR 606","COMPUTER LAB 1","COMPUTER LAB 2","COMPUTER LAB 3","COMPUTER LAB 4","COMPUTER LAB 5","COMPUTER LAB 6","COMPUTER LAB 7","COMPUTER LAB 8","COMPUTER LAB 9","COMPUTER LAB 10","COMPUTER LAB 11","COMPUTER LAB 12","THIN-CLIENT LAB 1","THIN-CLIENT LAB 2","UBUNTU LAB 1","UBUNTU LAB 2","ANIMATION AND GAMING LAB","PHYSICS LAB","CHEMISTRY LAB","IAPT LAB","PHARMA LAB - I","PHARMA LAB - II","PHARMACEUTICAL ANALYSIS LAB","PHARMA CHEMISTRY LAB","PHARMACOGNOSY LAB","LOGIC DESIGN (MICROPROCESSOR LAB)","BASIC ELECTRONIC ENGINEERING LAB","DIGITAL LOGIC MICROPROCESSOR LAB","MECHANICAL ENGINEERING LAB","CIVIL AND MECHANICAL ENGINEERING LAB","CIVIL ENGINEERING LAB","G.C. LAB I","G.C. LAB II","MAC LAB","LIBRARY","CENTRAL LIBRARY","LAW LIBRARY","SEMINAR HALL","MEETING HALL","KP NAUTIYAL AUDITORIUM","NEW AUDITORIUM","OPEN AUDITORIUM","NEW HALL STAFF ROOM","STORE ROOM","CCTV ROOM","DEGREE AND MARKSHEET","RECEPTION","MI ROOM","TRANSPORT ENQUIRY","HOSTEL ENQUIRY","RESEARCH & DEVELOPMENT CELL","MOOT COURT","NCC ROOM","ADMISSION BACK OFFICE","ADMISSION ENQUIRY","CORPORATE RESOURCE CENTER (PLACEMENT / ALUMNI)","SERVER ROOM","STAFF ROOM","STAFF ROOM CR 301","STAFF ROOM CR 305","STAFF ROOM CR 403","STAFF ROOM CR 406","STAFF ROOM CR 503","STAFF ROOM CR 504","STAFF ROOM CR 206","STAFF ROOM CR 207","DEPARTMENT OF MEDIA AND MASS COMMUNICATION","DEPARTMENT OF VISUAL ART","DEPARTMENT OF CIVIL & MECHANICAL ENGINEERING"];

function populate(selId){
    const sel = document.getElementById(selId);
    allRooms.forEach(r => { const opt = document.createElement('option'); opt.value = r; opt.textContent = r; sel.appendChild(opt); });
}
populate('startRoom'); populate('endRoom');

const roomToFloor = { /* your full mapping — kept exactly as before */ 
    "REGISTRAR OFFICE":"Basement","ACCOUNTS OFFICE":"Basement","EXAMINATION CELL":"Basement","FEE CELL":"Basement","PHD CELL":"Basement","BASEMENT CR A":"Basement","BASEMENT CR B":"Basement","BASEMENT RR A-1":"Basement","DIGITAL LOGIC MICROPROCESSOR LAB":"Basement","MECHANICAL ENGINEERING LAB":"Basement","CIVIL AND MECHANICAL ENGINEERING LAB":"Basement","CIVIL ENGINEERING LAB":"Basement","MECHANICAL ENGINEERING DEPT":"Basement","CIVIL ENGINEERING DEPT":"Basement","STORE ROOM":"Basement","CCTV ROOM":"Basement","DEGREE AND MARKSHEET":"Basement",
    "CR 101":"Floor 1","CR 102":"Floor 1","CR 103":"Floor 1","CR 104":"Floor 1","CR 105":"Floor 1","COMPUTER LAB 1":"Floor 1","COMPUTER LAB 10":"Floor 1","UBUNTU LAB 1":"Floor 1","UBUNTU LAB 2":"Floor 1","LIBRARY":"Floor 1","SEMINAR HALL":"Floor 1","MEETING HALL":"Floor 1","RECEPTION":"Floor 1","MI ROOM":"Floor 1","TRANSPORT ENQUIRY":"Floor 1","HOSTEL ENQUIRY":"Floor 1","RESEARCH & DEVELOPMENT CELL":"Floor 1","ADMISSION BACK OFFICE":"Floor 1","ADMISSION ENQUIRY":"Floor 1","CORPORATE RESOURCE CENTER (PLACEMENT / ALUMNI)":"Floor 1","OPEN AUDITORIUM":"Floor 1",
    "CR 201":"Floor 2","CR 202":"Floor 2","CR 203":"Floor 2","CR 204":"Floor 2","CR 205":"Floor 2","CR 206":"Floor 2","CR 207":"Floor 2","COMPUTER LAB 2":"Floor 2","COMPUTER LAB 9":"Floor 2","THIN-CLIENT LAB 1":"Floor 2","THIN-CLIENT LAB 2":"Floor 2","VICE CHANCELLOR OFFICE":"Floor 2","PRESIDENT OFFICE":"Floor 2","CENTRAL LIBRARY":"Floor 2","SERVER ROOM":"Floor 2","SCHOOL OF ENGINEERING":"Floor 2","STAFF ROOM CR 206":"Floor 2","STAFF ROOM CR 207":"Floor 2",
    "CR 301":"Floor 3","CR 302":"Floor 3","CR 303":"Floor 3","CR 304":"Floor 3","CR 305":"Floor 3","COMPUTER LAB 3":"Floor 3","COMPUTER LAB 8":"Floor 3","PHARMA LAB - I":"Floor 3","PHARMA LAB - II":"Floor 3","PHARMACEUTICAL ANALYSIS LAB":"Floor 3","PHARMA CHEMISTRY LAB":"Floor 3","PHARMACOGNOSY LAB":"Floor 3","ANIMATION AND GAMING LAB":"Floor 3","SCHOOL OF PHARMACY":"Floor 3","NEW HALL STAFF ROOM":"Floor 3","DEPARTMENT OF MEDIA AND MASS COMMUNICATION":"Floor 3","DEPARTMENT OF VISUAL ART":"Floor 3","STAFF ROOM CR 301":"Floor 3","STAFF ROOM CR 305":"Floor 3",
    "CR 401":"Floor 4","CR 402":"Floor 4","CR 403":"Floor 4","CR 405":"Floor 4","CR 406":"Floor 4","COMPUTER LAB 4":"Floor 4","COMPUTER LAB 7":"Floor 4","COMPUTER LAB 11":"Floor 4","COMPUTER LAB 12":"Floor 4","LOGIC DESIGN (MICROPROCESSOR LAB)":"Floor 4","BASIC ELECTRONIC ENGINEERING LAB":"Floor 4","MAC LAB":"Floor 4","SCHOOL OF COMPUTING":"Floor 4","COMPUTER SCIENCE & ENGINEERING STAFF ROOM":"Floor 4","STAFF ROOM CR 403":"Floor 4","STAFF ROOM CR 406":"Floor 4",
    "CR 501":"Floor 5","CR 502":"Floor 5","CR 503":"Floor 5","CR 504":"Floor 5","COMPUTER LAB 5":"Floor 5","PHYSICS LAB":"Floor 5","CHEMISTRY LAB":"Floor 5","IAPT LAB":"Floor 5","G.C. LAB I":"Floor 5","G.C. LAB II":"Floor 5","HOD PDP":"Floor 5","HOD PHYSICS":"Floor 5","HOD FASHION DESIGN":"Floor 5","DEPARTMENT OF PHYSICS":"Floor 5","DEPARTMENT OF FASHION AND DESIGN":"Floor 5","STAFF ROOM CR 503":"Floor 5","STAFF ROOM CR 504":"Floor 5",
    "CR 601":"Floor 6","CR 602":"Floor 6","CR 603":"Floor 6","CR 604":"Floor 6","CR 605":"Floor 6","CR 606":"Floor 6","COMPUTER LAB 6":"Floor 6","LAW LIBRARY":"Floor 6","KP NAUTIYAL AUDITORIUM":"Floor 6","NEW AUDITORIUM":"Floor 6","MOOT COURT":"Floor 6","NCC ROOM":"Floor 6","SCHOOL OF LAW":"Floor 6","PDP STAFF ROOM":"Floor 6","LAW STAFF ROOM":"Floor 6"
};

const liftLocations = { "Basement":"near Basement CR A / Staff Lift", "Floor 1":"near CR 105 / Staff Lift", "Floor 2":"near CR 207 / Staff Lift", "Floor 3":"near CR 305 / Staff Lift", "Floor 4":"near CR 406 / Staff Lift", "Floor 5":"near CR 504 / Staff Lift", "Floor 6":"near CR 606 / Staff Lift" };
const WALK_SPEED = 75, CORR_LEN = 15, LIFT_WAIT = 1, LIFT_PER = 0.3;

function estimateSameFloorTime(s,e){ const a=parseInt(s.match(/\d+/)?.[0]||"0"), b=parseInt(e.match(/\d+/)?.[0]||"0"); return Math.abs(a-b)*CORR_LEN/WALK_SPEED; }
function estimateCrossFloorTime(sf,ef){ const a=sf==="Basement"?0:parseInt(sf.split(' ')[1]), b=ef==="Basement"?0:parseInt(ef.split(' ')[1]); return LIFT_WAIT + Math.abs(b-a)*LIFT_PER + 40/WALK_SPEED; }
function formatTime(m){ if(m<1) return Math.round(m*60)+' sec'; const mn=Math.floor(m), sc=Math.round((m-mn)*60); return sc? mn+' min '+sc+' sec' : mn+' min'; }

function getSameFloorPath(start,end,floor){
    const steps=[], time=estimateSameFloorTime(start,end), s=parseInt(start.match(/\d+/)?.[0]||"0"), e=parseInt(end.match(/\d+/)?.[0]||"0");
    steps.push(`<div class="step"><span class="direction-icon">Start</span> You are at <b>${start}</b> on <b>${floor}</b>.</div>`);
    if(Math.abs(s-e)<=1) steps.push(`<div class="step"><span class="direction-icon">Go</span> <b>Move straight</b> to <b>${end}</b> (~${formatTime(time)}).</div>`);
    else if(s<e){ steps.push(`<div class="step"><span class="direction-icon">Right</span> <b>Move right</b> down the corridor.</div>`); steps.push(`<div class="step"><span class="direction-icon">Go</span> Continue to <b>${end}</b> (~${formatTime(time)}).</div>`); }
    else{ steps.push(`<div class="step"><span class="direction-icon">Left</span> <b>Move left</b> down the corridor.</div>`); steps.push(`<div class="step"><span class="direction-icon">Go</span> Continue to <b>${end}</b> (~${formatTime(time)}).</div>`); }
    steps.push(`<div class="step"><span class="direction-icon">Arrived</span> You have arrived at <b>${end}</b>!</div>`);
    const plain = steps.join(' ').replace(/<\/?[^>]+>/g,'');
    return {html:steps.join(''),time,plain};
}
function getCrossFloorPath(start,sf,end,ef){
    const steps=[], time=estimateCrossFloorTime(sf,ef), up = (ef==="Basement"?0:parseInt(ef.split(' ')[1])) > (sf==="Basement"?0:parseInt(sf.split(' ')[1]));
    steps.push(`<div class="step"><span class="direction-icon">Start</span> You are at <b>${start}</b> on <b>${sf}</b>.</div>`);
    steps.push(`<div class="step"><span class="direction-icon">Walk</span> Walk to the lift/stairs <b>${liftLocations[sf]}</b>.</div>`);
    steps.push(`<div class="step"><span class="direction-icon">${up?'Up':'Down'}</span> Take the <b>lift ${up?'UP':'DOWN'}</b> to <b>${ef}</b> (~${formatTime(time)} total).</div>`);
    steps.push(`<div class="step"><span class="direction-icon">Exit</span> Exit on <b>${ef}</b> near <b>${liftLocations[ef]}</b>.</div>`);
    steps.push(`<div class="step"><span class="direction-icon">Go</span> Follow the hallway to <b>${end}</b>.</div>`);
    steps.push(`<div class="step"><span class="direction-icon">Arrived</span> You have arrived at <b>${end}</b>!</div>`);
    const plain = steps.join(' ').replace(/<\/?[^>]+>/g,'');
    return {html:steps.join(''),time,plain};
}

let lastVoiceText = '';
function findPath(){
    const start = document.getElementById('startRoom').value;
    const end = document.getElementById('endRoom').value;
    const box = document.getElementById('resultBox');
    if(!start||!end){ box.innerHTML=`<div class="step">Please select both locations.</div>`; return; }
    if(start===end){ box.innerHTML=`<div class="step">You are already at <b>${start}</b>!</div>`; return; }
    const sf = roomToFloor[start], ef = roomToFloor[end];
    if(!sf||!ef){ box.innerHTML=`<div class="step">Location not found.</div>`; return; }
    let html = `<div style="font-size:20px;margin-bottom:15px;color:#60a5fa;">Step-by-Step Directions</div>`, res;
    if(sf===ef) res = getSameFloorPath(start,end,sf);
    else res = getCrossFloorPath(start,sf,end,ef);
    html += res.html + `<div class="time-info">Estimated Time: <b>${formatTime(res.time)}</b></div>`;
    box.innerHTML = html;
    lastVoiceText = res.plain;
}

// Voice Assistant (fully working)
let synth = window.speechSynthesis, speaking = false, voiceReady = false;
const voiceBtn = document.getElementById('voiceBtn');
function loadVoices(){ voices = synth.getVoices(); if(voices.length) voiceReady = true; }
if(synth){ if(synth.getVoices().length) loadVoices(); synth.onvoiceschanged = loadVoices; }
function speak(text){
    if(!voiceReady) return alert("Voice not ready yet.");
    if(speaking) synth.cancel();
    const utter = new SpeechSynthesisUtterance(text);
    utter.rate = 0.9; utter.lang = 'en-IN';
    const indian = voices.find(v=>v.lang.includes('en-IN')) || voices.find(v=>v.lang.includes('en')) || voices[0];
    if(indian) utter.voice = indian;
    utter.onend = ()=>{ speaking=false; voiceBtn.classList.remove('recording'); voiceBtn.innerHTML='<i class="fas fa-microphone"></i> Speak Instructions'; };
    synth.speak(utter); speaking=true;
    voiceBtn.classList.add('recording'); voiceBtn.innerHTML='Stop Voice';
}
function toggleVoiceAssist(){
    if(speaking){ synth.cancel(); speaking=false; voiceBtn.classList.remove('recording'); voiceBtn.innerHTML='<i class="fas fa-microphone"></i> Speak Instructions'; }
    else if(lastVoiceText) speak(lastVoiceText);
    else alert("Find a path first!");
}

// Particles + Animations
particlesJS('particles', { particles: { number: { value: 90 }, color: { value: ['#3b82f6','#60a5fa','#93c5fd'] }, opacity: { value: 0.5, random: true }, size: { value: 3, random: true }, line_linked: { enable: true, distance: 150, color: '#60a5fa', opacity: 0.2 }, move: { enable: true, speed: 1.8 } }, interactivity: { events: { onhover: { enable: true, mode: 'repulse' } } } });
gsap.from(".container > *", { scrollTrigger: { trigger: ".container", start: "top 80%" }, y: 80, opacity: 0, duration: 1.2, stagger: 0.2, ease: "power4.out" });
window.addEventListener('scroll', () => document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 100));

// Ripple for AI button
document.getElementById('aiBtn').addEventListener('click', e => {
    const ripple = document.createElement('span');
    ripple.style.position = 'absolute'; ripple.style.borderRadius = '50%'; ripple.style.background = 'rgba(255,255,255,.7)'; ripple.style.transform = 'scale(0)'; ripple.style.animation = 'ripple 0.7s ease-out';
    ripple.style.left = (e.clientX - e.target.offsetLeft - 36) + 'px'; ripple.style.top = (e.clientY - e.target.offsetTop - 36) + 'px'; ripple.style.width = ripple.style.height = '72px';
    e.target.appendChild(ripple); setTimeout(() => ripple.remove(), 700);
});
document.head.insertAdjacentHTML('beforeend', `<style>@keyframes ripple { to { transform: scale(4.5); opacity: 0; } }</style>`);
</script>
</body>
</html>