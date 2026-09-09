@extends('frontend.layouts.main')

@section('content')
    @include('frontend.sections.donation')

    @foreach (\App\Models\Page::where('slug', 'home')->first()->sections as $section)
        @include('frontend.sections.' . $section)
    @endforeach

    @include('frontend.sections.blog')
    @include('frontend.sections.donation-category')

    @include('frontend.sections.projects')

    @include('frontend.sections.gallery')
    
    
    <!-chat bot sytem design --->
        
    
        <div class="fixed bottom-6 right-6 flex items-center gap-3">
        <div id="chatLabel" class="hidden md:block bg-white shadow-lg border border-gray-100 px-4 py-2 rounded-2xl text-slate-700 text-sm font-medium">
            May I help you?👋
        </div>
        <button id="mainChatBtn" onclick="toggleChat()" class="pulse-effect bg-[#25D366] hover:bg-[#128C7E] text-white p-4 rounded-full shadow-2xl transition-all active:scale-90 flex items-center justify-center relative">
            <svg id="openIcon" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <svg id="closeIcon" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </div>

    <div id="chatWindow" class="chat-container fixed bottom-24 right-6 w-[360px] md:w-[400px] h-[550px] bg-white rounded-[2rem] overflow-hidden z-[998] border border-gray-200 shadow-2xl flex flex-col">
        <header class="bg-[#075E54] p-3 text-white flex justify-between items-center flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-[#075E54] font-bold text-lg shadow-md">S</div>
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-sm leading-tight text-white">Sarathi</h3>
                    <p class="text-[10px] text-green-100 opacity-80">Online | AI Assistant</p>
                </div>
            </div>
            <button onclick="toggleChat()" class="hover:bg-white/10 p-2 rounded-full"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
        </header>

        <main id="messageArea" class="flex-1 p-4 overflow-y-auto flex flex-col gap-4 z-10">
            <div class="bg-white p-3.5 rounded-2xl rounded-tl-none shadow-sm max-w-[85%] text-[13px] text-slate-800 border border-slate-100">
                Nomoskar! Ami(SARATHI) SDF Foundation-er AI assistant. Kivabe shahajjo korte pari?
            </div>
            <div id="quickQuestions" class="flex flex-wrap gap-2">
                <button onclick="quickAsk('SDF Foundation ki?')" class="bg-white/80 backdrop-blur-sm border border-[#075E54] text-[#075E54] text-[11px] px-3 py-1.5 rounded-full shadow-sm font-medium">SDF Foundation ki?</button>
                <button onclick="quickAsk('Office kothay?')" class="bg-white/80 backdrop-blur-sm border border-[#075E54] text-[#075E54] text-[11px] px-3 py-1.5 rounded-full shadow-sm font-medium">Office kothay?</button>
            </div>
        </main>

        <footer class="p-4 bg-white flex-shrink-0">
            <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-full px-4 focus-within:ring-2 focus-within:ring-[#075E54]">
                <input type="text" id="userInput" placeholder="Type message..." class="flex-1 bg-transparent border-none py-2 text-sm outline-none text-slate-700" onkeypress="handleKey(event)">
                <button onclick="sendMessage()" class="bg-[#075E54] text-white p-2.5 rounded-full shadow-md active:scale-90 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                </button>
            </div>
        </footer>
    </div>
        
        
    
    
    

@endsection


@push('styles')

<link rel="stylesheet" href="{{ asset('assets/frontend/css/motion.css') }}">

<style>
    .sub-title { background: linear-gradient(90deg, #ffffff, #80ffaa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; text-transform: uppercase; font-size: 1rem; }
    .modern-title { background: linear-gradient(to right, #ffffff, #e0e0e0); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }


    /* popup area styles */

    :root { --brand-green: #075E54; --whatsapp-bg: #e5ddd5; }
        body { font-family: 'Inter', 'Hind Siliguri', sans-serif; overflow-x: hidden; margin: 0; padding: 0; }

        .pulse-effect::after {
            content: '';
            position: absolute;
            width: 100%; height: 100%; top: 0; left: 0;
            background-color: #25D366;
            border-radius: 50%;
            z-index: -1;
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.8); opacity: 0; }
        }

        .chat-container {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform: translateY(30px) scale(0.95);
            opacity: 0;
            pointer-events: none;
            max-height: 80vh;
        }

        .chat-container.active {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        #messageArea {
            background-color: var(--whatsapp-bg);
            background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
            scrollbar-width: thin;
        }

        @media (max-width: 640px) {
            .chat-container { width: 95% !important; right: 2.5% !important; left: 2.5% !important; height: 70vh !important; bottom: 90px !important; }
        }

        .typing-dot { width: 4px; height: 4px; background: #9ca3af; border-radius: 50%; animation: typing 1.4s infinite ease-in-out; }
        @keyframes typing { 0%, 80%, 100% { transform: translateY(0); } 40% { transform: translateY(-4px); } }




</style>
@endpush



@push('scripts')

<script src="{{ asset('assets/frontend/js/motion.js') }}"></script>

<script>
    // এটি যোগ করুন যেন আপনার বুটস্ট্র্যাপ ডিজাইন নষ্ট না হয়
    tailwind.config = {
        corePlugins: {
            preflight: false,
        }
    }
</script>

<script>

    // --- CONFIGURATION ---
    let alertInterval;
    let audioCtx = null;

    function playNotifySound() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.05, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
            osc.connect(gain); gain.connect(audioCtx.destination);
            osc.start(); osc.stop(audioCtx.currentTime + 0.3);
        } catch(e) { console.log("Audio block by browser"); }
    }

    function startAlertSystem() {
        const starter = () => {
            if(!alertInterval) {
                alertInterval = setInterval(() => {
                    const win = document.getElementById('chatWindow');
                    if (win && !win.classList.contains('active')) {
                        playNotifySound();
                    }
                }, 3000);
            }
            document.removeEventListener('click', starter);
            document.removeEventListener('touchstart', starter);
        };
        document.addEventListener('click', starter);
        document.addEventListener('touchstart', starter);
    }

    function toggleChat() {
        const win = document.getElementById('chatWindow');
        win.classList.toggle('active');
        document.getElementById('openIcon').classList.toggle('hidden');
        document.getElementById('closeIcon').classList.toggle('hidden');
        if(win.classList.contains('active')) {
            document.getElementById('chatLabel').style.display = 'none';
        }
    }

    window.onload = startAlertSystem;
    function handleKey(e) { if (e.key === 'Enter') sendMessage(); }
    function quickAsk(q) { document.getElementById('userInput').value = q; sendMessage(); }

    // --- ржПржЗ ржЕржВрж╢ржЯрж┐ ржкрзНрж░ржзрж╛ржи ржкрж░рж┐ржмрж░рзНрждржи ---
    async function sendMessage() {
        const inputField = document.getElementById('userInput');
        const messageArea = document.getElementById('messageArea');
        const userText = inputField.value.trim();
        if (!userText) return;

        // User Message UI
        messageArea.innerHTML += `<div class="self-end bg-[#dcf8c6] p-3 rounded-2xl rounded-tr-none shadow-sm max-w-[85%] text-[13px] text-slate-800 mb-2">${userText}</div>`;
        inputField.value = '';
        messageArea.scrollTop = messageArea.scrollHeight;

        // Loading Indicator
        const loadingId = "loading-" + Date.now();
        messageArea.innerHTML += `<div id="${loadingId}" class="bg-white p-2 rounded-xl shadow-sm w-12 flex justify-center gap-1 mb-2 border border-slate-100"><span class="typing-dot"></span><span class="typing-dot" style="animation-delay:0.2s"></span><span class="typing-dot" style="animation-delay:0.4s"></span></div>`;
        messageArea.scrollTop = messageArea.scrollHeight;

        try {
            // Laravel-ржПрж░ ржЕржнрзНржпржирзНрждрж░рзАржг Route-ржП рж░рж┐ржХрзЛрзЯрзЗрж╕рзНржЯ ржкрж╛ржарж╛ржирзЛ рж╣ржЪрзНржЫрзЗ
            const response = await fetch("{{ route('frontend.chat.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // рж╕рж┐ржХрж┐ржЙрж░рж┐ржЯрж┐рж░ ржЬржирзНржп ржЧрзБрж░рзБрждрзНржмржкрзВрж░рзНржг
                },
                body: JSON.stringify({ message: userText })
            });

            const data = await response.json();

            // рж▓рзЛржбрж┐ржВ ржПржирж┐ржорзЗрж╢ржи рж░рж┐ржорзБржн ржХрж░рж╛
            const loadingElement = document.getElementById(loadingId);
            if (loadingElement) loadingElement.remove();

            if (data.reply) {
                // AI-ржПрж░ ржЙрждрзНрждрж░ ржжрзЗржЦрж╛ржирзЛ
                messageArea.innerHTML += `<div class="bg-white p-3.5 rounded-2xl rounded-tl-none shadow-sm max-w-[85%] text-[13px] text-slate-800 border border-slate-100 mb-2 leading-relaxed">${data.reply}</div>`;
                playNotifySound();
            } else {
                messageArea.innerHTML += `<div class="bg-red-50 text-red-500 p-2 text-[11px] rounded-lg self-center">Error: ${data.error || 'Something went wrong'}</div>`;
            }
        } catch (e) {
            if (document.getElementById(loadingId)) document.getElementById(loadingId).remove();
            messageArea.innerHTML += `<div class="bg-red-50 text-red-500 p-2 text-[11px] rounded-lg self-center">Connection Error</div>`;
            console.error(e);
        }
        messageArea.scrollTop = messageArea.scrollHeight;
    }
    
    
    
    
    
    
    
    
</script> 

@endpush
