<!-- FILE: resources/views/tenants/military_gear/errors/404.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARGET NOT FOUND | Military Gear</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap');
        
        body {
            font-family: 'Orbitron', monospace;
            background: linear-gradient(135deg, #1a2a32 0%, #0d1a26 100%);
            color: #c8d6e5;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(46, 204, 113, 0.1) 0%, transparent 55%),
                radial-gradient(circle at 75% 75%, rgba(41, 128, 185, 0.1) 0%, transparent 55%);
        }
        
        .military-gradient {
            background: linear-gradient(90deg, #2ecc71 0%, #27ae60 100%);
        }
        
        .camo-pattern {
            background-color: #2c3e50;
            background-image: 
                linear-gradient(45deg, #34495e 25%, transparent 25%),
                linear-gradient(-45deg, #34495e 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, #34495e 75%),
                linear-gradient(-45deg, transparent 75%, #34495e 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        
        .glitch-text {
            position: relative;
            animation: glitch 3s infinite;
        }
        
        @keyframes glitch {
            0% { transform: translate(0); }
            2% { transform: translate(-2px, 2px); }
            4% { transform: translate(-2px, -2px); }
            6% { transform: translate(2px, 2px); }
            8% { transform: translate(2px, -2px); }
            10% { transform: translate(0); }
            100% { transform: translate(0); }
        }
        
        .radar-scan {
            position: relative;
            overflow: hidden;
        }
        
        .radar-scan::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(transparent, rgba(46, 204, 113, 0.2), transparent);
            animation: radar 4s linear infinite;
        }
        
        @keyframes radar {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }
        
        .terminal-text {
            background: #1a1a1a;
            border: 1px solid #2ecc71;
            font-family: 'Courier New', monospace;
        }
        
        .blink {
            animation: blink 1s step-end infinite;
        }
        
        @keyframes blink {
            50% { opacity: 0; }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-4xl w-full">
        <div class="camo-pattern border-2 border-gray-700 rounded-xl overflow-hidden shadow-2xl">
            <!-- Header -->
            <div class="border-b border-gray-700 p-4 flex items-center justify-between bg-gray-900/50">
                <div class="flex items-center space-x-3">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
                <div class="text-sm font-bold tracking-widest text-gray-400">
                    MILITARY GEAR COMMAND v2.1.4
                </div>
                <div class="text-xs text-gray-500">
                    STATUS: <span class="text-red-400 font-bold">ALERT</span>
                </div>
            </div>
            
            <div class="p-8 md:p-12">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <!-- Left: Error Display -->
                    <div class="text-center">
                        <div class="relative inline-block mb-6">
                            <div class="radar-scan w-64 h-64 rounded-full border-4 border-green-500/30 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="glitch-text text-8xl font-black text-green-400 mb-2">404</div>
                                    <div class="text-xl font-bold text-gray-300 tracking-widest">TARGET LOST</div>
                                </div>
                            </div>
                            
                            <!-- Radar Dots -->
                            <div class="absolute top-4 left-4 w-4 h-4 bg-green-400 rounded-full animate-pulse"></div>
                            <div class="absolute top-4 right-4 w-4 h-4 bg-green-400 rounded-full animate-pulse delay-300"></div>
                            <div class="absolute bottom-4 left-4 w-4 h-4 bg-green-400 rounded-full animate-pulse delay-700"></div>
                            <div class="absolute bottom-4 right-4 w-4 h-4 bg-green-400 rounded-full animate-pulse delay-1000"></div>
                        </div>
                        
                        <div class="mt-6">
                            <div class="inline-flex items-center px-4 py-2 bg-gray-900/50 rounded-lg border border-gray-700">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                <span class="text-sm text-gray-300">SECTOR: UNKNOWN</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right: Terminal Interface -->
                    <div>
                        <div class="terminal-text rounded-lg p-6 mb-6">
                            <div class="font-mono text-sm space-y-2">
                                <div class="flex">
                                    <span class="text-green-400 mr-2">$</span>
                                    <span class="text-gray-300">search_target "{{ request()->path() }}"</span>
                                </div>
                                <div class="text-red-400">> ERROR: TARGET NOT FOUND IN DATABASE</div>
                                <div class="flex">
                                    <span class="text-green-400 mr-2">$</span>
                                    <span class="text-gray-300">check_coordinates --force</span>
                                </div>
                                <div class="text-yellow-400">> WARNING: COORDINATES INVALID OR OUTDATED</span></div>
                                <div class="flex">
                                    <span class="text-green-400 mr-2">$</span>
                                    <span class="text-gray-300 blink">▋</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <h3 class="text-xl font-bold text-gray-300 border-b border-gray-700 pb-2">
                                <span class="text-green-400">></span> RECOMMENDED ACTIONS
                            </h3>
                            
                            <div class="grid grid-cols-1 gap-3">
                                <a href="/" 
                                   class="military-gradient text-white font-bold py-3 px-6 rounded-lg hover:opacity-90 transition flex items-center justify-center group">
                                    <span class="mr-2">⌂</span>
                                    RETURN TO BASE COMMAND
                                </a>
                                
                                <a href="{{ route('shop.products') }}"
                                   class="border-2 border-green-500/30 text-green-400 font-bold py-3 px-6 rounded-lg hover:bg-green-500/10 transition flex items-center justify-center group">
                                    <span class="mr-2">⚙</span>
                                    ACCESS ARMORY CATALOG
                                </a>
                                
                                <a href="{{ route('shop.products', ['preset' => 'new']) }}"
                                   class="border-2 border-blue-500/30 text-blue-400 font-bold py-3 px-6 rounded-lg hover:bg-blue-500/10 transition flex items-center justify-center group">
                                    <span class="mr-2">🆕</span>
                                    NEW DEPLOYMENTS
                                </a>
                                
                                <a href="{{ route('shop.products', ['preset' => 'discount']) }}"
                                   class="border-2 border-red-500/30 text-red-400 font-bold py-3 px-6 rounded-lg hover:bg-red-500/10 transition flex items-center justify-center group">
                                    <span class="mr-2">🔥</span>
                                    TACTICAL DISCOUNTS
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-700">
                            <div class="text-sm text-gray-400">
                                <div class="flex items-center justify-between mb-2">
                                    <span>OPERATION STATUS:</span>
                                    <span class="text-green-400 font-bold">ACTIVE</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>COMMUNICATION:</span>
                                    <a href="{{ route('contact.index') }}" class="text-blue-400 hover:text-blue-300 hover:underline">
                                        ESTABLISH LINK
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="border-t border-gray-700 p-4 bg-gray-900/50">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-xs text-gray-500 mb-2 md:mb-0">
                        <span class="text-green-400">MIL-GEAR-404</span> | SECURITY CLEARANCE: LEVEL 1 | TIMESTAMP: {{ now()->format('Ymd-His') }}
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-xs text-gray-400">
                            FREQUENCY: <span class="text-green-400">SECURE</span>
                        </div>
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-gray-600 rounded-full"></div>
                            <div class="w-2 h-2 bg-gray-600 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom Navigation -->
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('cart.index') }}" class="text-gray-500 hover:text-green-400 transition text-sm flex items-center">
                <span class="mr-1">🎯</span> CART
            </a>
            <a href="{{ route('client.profile') }}" class="text-gray-500 hover:text-green-400 transition text-sm flex items-center">
                <span class="mr-1">🪪</span> IDENTITY
            </a>
            <a href="{{ route('contact.index') }}" class="text-gray-500 hover:text-green-400 transition text-sm flex items-center">
                <span class="mr-1">📡</span> COMMS
            </a>
        </div>
    </div>
    
    <!-- Military Sound Effects (optional) -->
    <audio id="radarSound" preload="auto">
        <source src="https://assets.mixkit.co/sfx/preview/mixkit-sci-fi-radar-sonar-scan-836.mp3" type="audio/mpeg">
    </audio>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Play radar sound on hover (optional)
            const radar = document.querySelector('.radar-scan');
            if (radar) {
                radar.addEventListener('mouseenter', () => {
                    const audio = document.getElementById('radarSound');
                    if (audio) {
                        audio.volume = 0.3;
                        audio.play().catch(() => {});
                    }
                });
            }
            
            // Terminal typing effect
            const terminal = document.querySelector('.terminal-text');
            if (terminal) {
                const cursor = terminal.querySelector('.blink');
                if (cursor) {
                    let isVisible = true;
                    setInterval(() => {
                        cursor.style.opacity = isVisible ? '0' : '1';
                        isVisible = !isVisible;
                    }, 500);
                }
            }
            
            // Military code effect
            const codeLines = [
                "> INITIATING SECTOR SCAN...",
                "> SCANNING FOR HOSTILES...",
                "> NO THREATS DETECTED",
                "> SWITCHING TO RECON MODE"
            ];
            
            let currentLine = 0;
            const terminalOutput = terminal?.querySelector('.text-red-400');
            
            if (terminalOutput) {
                setInterval(() => {
                    terminalOutput.textContent = codeLines[currentLine];
                    currentLine = (currentLine + 1) % codeLines.length;
                }, 2000);
            }
        });
    </script>
</body>
</html>