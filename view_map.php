<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$serverUser = isset($_SESSION['user_name']) && $_SESSION['user_name'] !== '' ? htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>View Campus Map | NAVIC ERA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --navy: #0f172a;
            --primary: #3b82f6;
            --accent: #60a5fa;
            --text: #e2e8f0;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--navy);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }
        #particles {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        }
        .navbar {
            position: fixed;
            top: 0; width: 100%; padding: 1.5rem 6%;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000;
            background: rgba(15,23,42,0.95);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(96,165,250,0.2);
        }
        .nav-logo {
            font-size: 2.3rem; font-weight: 900;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .user-info {
            display: flex; align-items: center; gap: 20px; color: white; font-weight: 600;
        }
        .user-info button {
            background: #3b82f6; color: white; border: none;
            padding: 10px 28px; border-radius: 50px; cursor: pointer;
            font-weight: 600; transition: .3s;
        }
        .user-info button:hover { background: #2563eb; transform: scale(1.05); }

        .main {
            flex: 1; text-align: center; padding: 120px 30px 60px; position: relative; z-index: 2;
        }
        h1 {
            font-size: 4.5rem; background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 40px; font-weight: 800;
        }
        .search-container {
            display: flex; justify-content: center; align-items: center; gap: 12px; margin: 30px auto; max-width: 600px;
        }
        .search-container input {
            flex: 1; padding: 18px 24px; background: rgba(15,23,42,0.8);
            border: 1px solid rgba(96,165,250,0.4); border-radius: 50px; color: white;
            font-size: 1.1rem; transition: .3s;
        }
        .search-container input:focus {
            outline: none; border-color: #60a5fa; box-shadow: 0 0 25px rgba(96,165,250,0.5);
        }
        .search-container button {
            background: linear-gradient(135deg, #3b82f6, #60a5fa); color: white;
            border: none; padding: 18px 32px; border-radius: 50px; cursor: pointer;
            font-weight: 700; font-size: 1.1rem; transition: .4s;
        }
        .search-container button:hover {
            transform: translateY(-4px); box-shadow: 0 15px 30px rgba(59,130,246,0.4);
        }
        .floor-buttons {
            display: flex; justify-content: center; flex-wrap: wrap; gap: 18px; margin: 40px 0;
        }
        .floor-buttons button {
            background: rgba(15,23,42,0.8); color: #60a5fa;
            border: 2px solid rgba(96,165,250,0.4); padding: 16px 32px;
            border-radius: 50px; font-size: 1.1rem; font-weight: 700;
            cursor: pointer; transition: .4s; min-width: 160px;
        }
        .floor-buttons button:hover {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white; border-color: transparent;
            transform: translateY(-6px); box-shadow: 0 15px 35px rgba(59,130,246,0.4);
        }
        .map-container {
            background: rgba(15,23,42,0.6); border-radius: 28px;
            width: 90%; height: 75vh; margin: 40px auto; overflow: auto;
            position: relative; display: flex; justify-content: center; align-items: center;
            border: 1px solid rgba(96,165,250,0.2); box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            transition: opacity .4s ease;
        }
        .map-container img {
            width: 90%; height: 100%; object-fit: contain; display: block; margin: auto;
            opacity: 0; transform: scale(.95); filter: blur(3px);
            transition: opacity .8s ease, transform .8s ease, filter .8s ease;
        }
        .map-container img.loaded { opacity: 1; transform: scale(1); filter: blur(0); }

        .footer {
            text-align: center; padding: 40px; background: rgba(15,23,42,0.9);
            color: #94a3b8; font-size: 1.1rem; border-top: 1px solid rgba(96,165,250,0.2);
        }
        .ai-fab {
            position: fixed; bottom: 40px; right: 40px; width: 72px; height: 72px;
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border: none; border-radius: 50%; font-size: 34px; color: white;
            cursor: pointer; box-shadow: 0 12px 45px rgba(59,130,246,0.6);
            z-index: 999; display: flex; align-items: center; justify-content: center;
            animation: fabPulse 3s infinite;
        }
        .ai-fab:hover { transform: scale(1.12); }
        @keyframes fabPulse { 0%,100% { box-shadow: 0 12px 45px rgba(59,130,246,0.6); } 50% { box-shadow: 0 12px 65px rgba(59,130,246,0.9); } }
        @media(max-width:700px) {
            .map-container { height: 60vh; }
            .floor-buttons { gap: 12px; }
            h1 { font-size: 3.5rem; }
        }
    </style>
</head>
<body>

<div id="particles"></div>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-logo">NAVIC ERA</div>
    <div class="user-info">
        <span>Welcome, <b id="username"><?php echo $serverUser ?: 'Student'; ?></b> 👋</span>
        <div class="nav-buttons">
            <button onclick="location.href='dashboard.php'">🏠 Home</button>
            <button onclick="location.href='logout.php'">🚪 Logout</button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main" role="main">
    <h1>View Campus Map</h1>

    <div class="search-container" role="search" aria-label="Search rooms">
        <input id="searchInput" type="text" placeholder="Search class or lab (e.g., CR 302)" aria-label="Search input">
        <button onclick="searchRoom()" aria-label="Search button">Search</button>
    </div>

    <div class="floor-buttons" role="menu" aria-label="Floor selection">
        <button onclick="showFloor('Basement')">Basement</button>
        <button onclick="showFloor('Ground')">Ground Floor</button>
        <button onclick="showFloor('First')">First Floor</button>
        <button onclick="showFloor('Second')">Second Floor</button>
        <button onclick="showFloor('Third')">Third Floor</button>
        <button onclick="showFloor('Fourth')">Fourth Floor</button>
        <button onclick="showFloor('Fifth')">Fifth Floor</button>
    </div>

    <div id="mapContainer" class="map-container" aria-live="polite">
        Select a floor to view its map.
    </div>
</div>

<div class="footer">© 2025 Team 'CodeVerse' | NAVIC ERA - GEHU Campus Navigation System | BCA'26</div>

<a href="campus_ai_ui.php" class="ai-fab"><i class="fas fa-robot"></i></a>

<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
// === YOUR ORIGINAL SCRIPT 100% UNTOUCHED BELOW ===
(function(){
    const serverUser = <?php echo json_encode($serverUser); ?>;
    if (!serverUser) {
        document.getElementById('username').textContent = localStorage.getItem('username') || 'Student';
    }
    const floorImages = {
        "Basement": "BASE.png",
        "Ground": "gif.png",
        "First": "1.png",
        "Second": "2.png",
        "Third": "3.png",
        "Fourth": "4.png",
        "Fifth": "nh.png"
    };
    window.showFloor = function(floor) {
        const container = document.getElementById('mapContainer');
        container.style.opacity = 0;
        setTimeout(() => {
            container.innerHTML = '';
            if (floorImages[floor]) {
                const img = document.createElement('img');
                img.src = floorImages[floor];
                img.alt = floor + ' Map';
                img.onload = () => {
                    img.classList.add('loaded');
                    container.appendChild(img);
                    container.style.opacity = 1;
                };
                img.onerror = () => {
                    container.textContent = floor + ' Map image not found.';
                    container.style.opacity = 1;
                };
            } else {
                container.textContent = floor + ' Map not available.';
                container.style.opacity = 1;
            }
        }, 250);
    };

    const floorMap = {
        "BASEMENT CR A": "Basement","BASEMENT CR B": "Basement","BASEMENT RR A-1": "Basement",
        "DIGITAL LOGIC MICROPROCESSOR LAB": "Basement","CIVIL AND MECHANICAL ENGINEERING LAB": "Basement",
        "CCTV ROOM": "Basement","PHD CELL": "Basement","REGISTRAR OFFICE": "Basement",
        "FEE CELL": "Basement","DEGREE & MARKSHEET": "Basement","EXAM CELL": "Basement",
        "MALE LIFT": "Basement","FEMALE LIFT": "Basement","STAFF LIFT": "Basement",
        "PARKING": "Basement","DEPARTMENT OF CIVIL & MECHANICAL ENGINEERING": "Basement",
        "CR 101":"Ground","CR 102":"Ground","CR 103":"Ground","CR 104":"Ground","CR 105":"Ground",
        "COMPUTER LAB 1":"Ground","COMPUTER LAB 10":"Ground","UBUNTU LAB 1":"Ground","UBUNTU LAB 2":"Ground",
        "PHARMACEUTICS - III LAB":"Ground","LIBRARY":"Ground","SEMINAR HALL":"Ground","MEETING HALL":"Ground",
        "RECEPTION":"Ground","MI ROOM":"Ground","TRANSPORT ENQUIRY":"Ground","HOSTEL ENQUIRY":"Ground",
        "RESEARCH & DEVELOPMENT CELL":"Ground","ADMISSION BACK OFFICE":"Ground","ADMISSION ENQUIRY":"Ground",
        "CORPORATE RESOURCE CENTER (PLACEMENT / ALUMNI)":"Ground","OPEN AUDITORIUM":"Ground",
        "CR 201":"First","CR 202":"First","CR 203":"First","CR 204":"First","CR 205":"First",
        "CR 206":"First","CR 207":"First","COMPUTER LAB 2":"First","COMPUTER LAB 9":"First",
        "THIN-CLIENT LAB 1":"First","THIN-CLIENT LAB 2":"First","VICE CHANCELLOR OFFICE":"First",
        "PRESIDENT OFFICE":"First","CENTRAL LIBRARY":"First","SCHOOL OF PHARMACY":"First","SCHOOL OF ENGINEERING":"First",
        "CR 301":"Second","CR 302":"Second","CR 303":"Second","CR 304":"Second","CR 305":"Second",
        "COMPUTER LAB 3":"Second","COMPUTER LAB 8":"Second","ANIMATION AND GAMING LAB":"Second",
        "PHARMA LAB - I":"Second","PHARMA LAB - II":"Second","PHARMACEUTICAL ANALYSIS":"Second","DEPARTMENT OF VISUAL ART":"Second",
        "CR 401":"Third","CR 402":"Third","CR 403":"Third","CR 405":"Third","CR 406":"Third",
        "COMPUTER LAB 4":"Third","COMPUTER LAB 7":"Third","COMPUTER LAB 11":"Third","COMPUTER LAB 12":"Third",
        "LOGIC DESIGN (MICROPROCESSOR LAB)":"Third","BASIC ELECTRONIC ENGINEERING LAB":"Third","MAC LAB":"Third",
        "SCHOOL OF COMPUTING":"Third","COMPUTER SCIENCE & ENGINEERING STAFF ROOM":"Third","HOD PDP":"Third",
        "CR 501":"Fourth","CR 502":"Fourth","CR 503":"Fourth","CR 504":"Fourth",
        "COMPUTER LAB 5":"Fourth","PHYSICS LAB":"Fourth","IAPT LAB":"Fourth","CHEMISTRY LAB":"Fourth",
        "G.C. LAB I":"Fourth","G.C. LAB II":"Fourth","DEPARTMENT OF PHYSICS":"Fourth","DEPARTMENT OF FASHION AND DESIGN":"Fourth",
        "HOD PHYSICS":"Fourth","HOD FASHION DESIGN":"Fourth",
        "CR 601":"Fifth","CR 602":"Fifth","CR 603":"Fifth","CR 604":"Fifth","CR 605":"Fifth","CR 606":"Fifth",
        "LT 601":"Fifth","LT 602":"Fifth","COMPUTER LAB 6":"Fifth","LAW LIBRARY":"Fifth","PDP STAFF ROOM":"Fifth",
        "LAW STAFF ROOM":"Fifth","SCHOOL OF LAW":"Fifth","KP NAUTIYAL AUDITORIUM":"Fifth","NEW AUDITORIUM":"Fifth",
        "MOOT COURT":"Fifth","NCC ROOM":"Fifth"
    };

    window.searchRoom = function() {
        const raw = document.getElementById('searchInput').value.trim();
        if (!raw) { alert('Please enter a classroom or lab name to search.'); return; }
        const input = raw.toUpperCase();
        const found = floorMap[input];
        if (found) {
            showFloor(found);
            alert(input + ' is located on the ' + found + ' Floor.');
        } else {
            const normalized = input.replace(/\s+/g, ' ').replace(/LT\s*/,'LT ').replace(/CR\s*/,'CR ');
            const found2 = floorMap[normalized];
            if (found2) {
                showFloor(found2);
                alert(normalized + ' is located on the ' + found2 + ' Floor.');
            } else {
                alert('Room not found! Please check the name and try again.');
            }
        }
    };

    window.showFloor = window.showFloor || function(floor) {
        const container = document.getElementById('mapContainer');
        container.style.opacity = 0;
        setTimeout(() => {
            container.innerHTML = '';
            if (floorImages[floor]) {
                const img = document.createElement('img');
                img.src = floorImages[floor];
                img.alt = floor + ' Map';
                img.onload = () => { img.classList.add('loaded'); container.appendChild(img); container.style.opacity = 1; };
                img.onerror = () => { container.textContent = floor + ' Map image not found.'; container.style.opacity = 1; };
            } else {
                container.textContent = floor + ' Map not available.';
                container.style.opacity = 1;
            }
        }, 250);
    };
    window.floorMap = floorMap;
})();

// Particles Background
particlesJS('particles', {
    particles: { number: { value: 90 }, color: { value: ['#3b82f6','#60a5fa','#93c5fd'] },
        opacity: { value: 0.5, random: true }, size: { value: 3, random: true },
        line_linked: { enable: true, distance: 150, color: '#60a5fa', opacity: 0.2 },
        move: { enable: true, speed: 1.8 }
    },
    interactivity: { events: { onhover: { enable: true, mode: 'repulse' } } }
});
</script>
</body>
</html>