<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Campus Assistant | NAVIC ERA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --navy: #0f172a;
            --navy-light: #1e293b;
            --primary: #3b82f6;
            --secondary: #2563eb;
            --accent: #60a5fa;
            --text: #e2e8f0;
            --card-bg: rgba(30, 41, 59, 0.4);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--navy);
            color: var(--text);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #particles {
            position: fixed;
            inset: 0;
            z-index: 1;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        }
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 1.5rem 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            background: rgba(15,23,42,0.95);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(96,165,250,0.2);
        }
        .nav-logo {
            font-size: 2.3rem;
            font-weight: 900;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
            font-weight: 600;
        }
        .user-info button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 28px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: .3s;
        }
        .user-info button:hover {
            background: #2563eb;
            transform: scale(1.05);
        }
        .main-container {
            flex: 1;
            position: relative;
            z-index: 2;
            max-width: 1200px;
            width: 100%;
            margin: 100px auto 40px;
            padding: 0 30px;
            display: flex;
            flex-direction: column;
        }
        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }
        .header-section h1 {
            font-size: 4rem;
            background: linear-gradient(90deg, #60a5fa, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            font-weight: 800;
        }
        .header-section p {
            font-size: 1.3rem;
            color: #cbd5e1;
            opacity: 0.9;
        }
        .chat-container {
            background: rgba(30,41,59,0.5);
            backdrop-filter: blur(20px);
            border-radius: 32px;
            border: 1px solid rgba(96,165,250,0.3);
            box-shadow: 0 30px 80px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 300px);
            min-height: 500px;
            max-height: 700px;
        }
        .chat-header {
            padding: 25px 35px;
            border-bottom: 1px solid rgba(96,165,250,0.2);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .ai-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(59,130,246,0.5); }
            50% { box-shadow: 0 0 35px rgba(59,130,246,0.8); }
        }
        .chat-title {
            flex: 1;
        }
        .chat-title h2 {
            font-size: 1.5rem;
            color: #60a5fa;
            margin-bottom: 5px;
        }
        .chat-title p {
            font-size: 0.9rem;
            color: #94a3b8;
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .messages-container::-webkit-scrollbar {
            width: 8px;
        }
        .messages-container::-webkit-scrollbar-track {
            background: rgba(15,23,42,0.5);
            border-radius: 10px;
        }
        .messages-container::-webkit-scrollbar-thumb {
            background: rgba(96,165,250,0.3);
            border-radius: 10px;
        }
        .messages-container::-webkit-scrollbar-thumb:hover {
            background: rgba(96,165,250,0.5);
        }
        .message {
            display: flex;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message.user {
            flex-direction: row-reverse;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .message.ai .message-avatar {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
        }
        .message.user .message-avatar {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        }
        .message-content {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 20px;
            line-height: 1.6;
        }
        .message.ai .message-content {
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(96,165,250,0.2);
        }
        .message.user .message-content {
            background: rgba(139,92,246,0.15);
            border: 1px solid rgba(167,139,250,0.2);
        }
        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 15px;
        }
        .typing-indicator span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #60a5fa;
            animation: typing 1.4s infinite;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        .quick-actions {
            padding: 15px 30px;
            border-top: 1px solid rgba(96,165,250,0.2);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .quick-btn {
            padding: 8px 16px;
            background: rgba(59,130,246,0.15);
            border: 1px solid rgba(96,165,250,0.3);
            border-radius: 20px;
            color: #60a5fa;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .quick-btn:hover {
            background: rgba(59,130,246,0.25);
            transform: translateY(-2px);
        }
        .input-container {
            padding: 25px 30px;
            border-top: 1px solid rgba(96,165,250,0.2);
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .input-wrapper {
            flex: 1;
            position: relative;
        }
        #userInput {
            width: 100%;
            padding: 15px 50px 15px 20px;
            background: rgba(15,23,42,0.6);
            border: 1px solid rgba(96,165,250,0.4);
            border-radius: 25px;
            color: white;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: .3s;
        }
        #userInput:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 25px rgba(96,165,250,0.3);
        }
        .voice-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #60a5fa;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            transition: .3s;
        }
        .voice-btn:hover {
            color: #93c5fd;
            transform: translateY(-50%) scale(1.1);
        }
        .voice-btn.recording {
            color: #ef4444;
            animation: recording-pulse 1s infinite;
        }
        @keyframes recording-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        #sendBtn {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: .4s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        #sendBtn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(59,130,246,0.4);
        }
        #sendBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .footer {
            text-align: center;
            padding: 30px;
            background: rgba(15,23,42,0.9);
            color: #94a3b8;
            font-size: 1rem;
            border-top: 1px solid rgba(96,165,250,0.2);
        }
        @media(max-width: 768px) {
            .header-section h1 { font-size: 2.5rem; }
            .message-content { max-width: 85%; }
            .chat-container { height: calc(100vh - 250px); }
        }
    </style>
</head>
<body>
    <div id="particles"></div>

    <div class="navbar">
        <div class="nav-logo">NAVIC ERA</div>
        <div class="user-info">
            <span>Welcome, <span id="username">Student</span> 👋</span>
            <button onclick="location.href='dashboard.php'">🏠 Home</button>
        </div>
    </div>

    <div class="main-container">
        <div class="header-section">
            <h1>🤖 AI Campus Assistant</h1>
            <p>Ask me anything about GEHU campus navigation</p>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <div class="ai-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="chat-title">
                    <h2>NAVIC AI</h2>
                    <p>Your intelligent campus guide</p>
                </div>
            </div>

            <div class="messages-container" id="messagesContainer">
                <div class="message ai">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        Hello! I'm your NAVIC AI assistant. I can help you navigate the GEHU campus. 
                        Try asking me things like:
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li>"I'm in CR 401, go to CR 101"</li>
                            <li>"Where is the library?"</li>
                            <li>"From CR 302 to Computer Lab 5"</li>
                            <li>"Find washroom on third floor"</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <button class="quick-btn" onclick="sendQuick('Where is the library?')">📚 Find Library</button>
                <button class="quick-btn" onclick="sendQuick('From CR 401 to CR 101')">🗺️ Popular Route</button>
                <button class="quick-btn" onclick="sendQuick('Find washroom on ground floor')">🚻 Washroom</button>
                <button class="quick-btn" onclick="sendQuick('Where is Computer Lab 1?')">💻 Computer Lab</button>
            </div>

            <div class="input-container">
                <div class="input-wrapper">
                    <input type="text" id="userInput" placeholder="Ask me about campus navigation..." 
                           onkeypress="if(event.key==='Enter') sendMessage()">
                    <button class="voice-btn" id="voiceBtn" onclick="toggleVoice()" title="Voice input">
                        <i class="fas fa-microphone"></i>
                    </button>
                </div>
                <button id="sendBtn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                    Send
                </button>
            </div>
        </div>
    </div>

    <div class="footer">
        © 2025 Team 'CodeVerse' | NAVIC ERA - GEHU Campus Navigation System | BCA'26
    </div>

    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        // Particles background
        particlesJS('particles', {
            particles: {
                number: { value: 85 },
                color: { value: ['#3b82f6', '#60a5fa', '#93c5fd'] },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#60a5fa', opacity: 0.2 },
                move: { enable: true, speed: 1.8 }
            },
            interactivity: {
                events: { onhover: { enable: true, mode: 'repulse' } }
            }
        });

        // Username from localStorage
        const storedUsername = localStorage.getItem('username');
        if (storedUsername) {
            document.getElementById('username').textContent = storedUsername;
        }

        // Chat functionality
        const messagesContainer = document.getElementById('messagesContainer');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');

        function addMessage(content, isUser = false) {
            const message = document.createElement('div');
            message.className = `message ${isUser ? 'user' : 'ai'}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = `<i class="fas fa-${isUser ? 'user' : 'robot'}"></i>`;
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            messageContent.innerHTML = content;
            
            message.appendChild(avatar);
            message.appendChild(messageContent);
            messagesContainer.appendChild(message);
            
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function showTyping() {
            const typing = document.createElement('div');
            typing.className = 'message ai';
            typing.id = 'typingIndicator';
            typing.innerHTML = `
                <div class="message-avatar"><i class="fas fa-robot"></i></div>
                <div class="message-content">
                    <div class="typing-indicator">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            `;
            messagesContainer.appendChild(typing);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function removeTyping() {
            const typing = document.getElementById('typingIndicator');
            if (typing) typing.remove();
        }

        async function sendMessage() {
            const question = userInput.value.trim();
            if (!question) return;

            addMessage(question, true);
            userInput.value = '';
            sendBtn.disabled = true;

            showTyping();

            try {
                const formData = new FormData();
                formData.append('question', question);

                const response = await fetch('campus_ai.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                removeTyping();
                
                if (data.ok && data.answer) {
                    addMessage(data.answer, false);
                } else {
                    addMessage('❌ Sorry, I encountered an error. Please try again.', false);
                }
            } catch (error) {
                removeTyping();
                addMessage('❌ Connection error. Please check your internet and try again.', false);
                console.error('Error:', error);
            } finally {
                sendBtn.disabled = false;
                userInput.focus();
            }
        }

        function sendQuick(text) {
            userInput.value = text;
            sendMessage();
        }

        // Voice recognition
        let recognition = null;
        let isRecording = false;

        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            recognition = new SpeechRecognition();
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.lang = 'en-IN';

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                userInput.value = transcript;
                stopRecording();
            };

            recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                stopRecording();
                addMessage('❌ Voice recognition error. Please try typing instead.', false);
            };

            recognition.onend = () => {
                stopRecording();
            };
        }

        function toggleVoice() {
            if (!recognition) {
                alert('Voice recognition is not supported in your browser. Please use Chrome or Edge.');
                return;
            }

            if (isRecording) {
                recognition.stop();
            } else {
                recognition.start();
                startRecording();
            }
        }

        function startRecording() {
            isRecording = true;
            const voiceBtn = document.getElementById('voiceBtn');
            voiceBtn.classList.add('recording');
            voiceBtn.innerHTML = '<i class="fas fa-stop"></i>';
        }

        function stopRecording() {
            isRecording = false;
            const voiceBtn = document.getElementById('voiceBtn');
            voiceBtn.classList.remove('recording');
            voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>';
        }

        // Focus input on load
        userInput.focus();
    </script>
</body>
</html>