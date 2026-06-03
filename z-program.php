<?php
/**
 * PHP MASTERCLASS EPISODE 1 - VISUAL STORYBOARD
 * Based on: z-program-prompt.md
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Masterclass - Episode 1 | Visual Storyboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Space+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #050508;
            --card-bg: rgba(20, 20, 35, 0.7);
            --neon-blue: #00f2ff;
            --neon-magenta: #ff00e5;
            --neon-green: #39ff14;
            --neon-yellow: #fff200;
            --text-main: #e0e0e0;
            --text-muted: #a0a0b0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3, .glitch-text {
            font-family: 'Space Grotesk', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* --- BACKGROUND EFFECTS --- */
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 0, 229, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 0, 229, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            mask-image: radial-gradient(circle at center, black, transparent 80%);
        }

        .glow-sphere {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(162, 0, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: -1;
            filter: blur(80px);
        }

        /* --- LAYOUT --- */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        /* --- COMPONENT: GLITCH --- */
        .glitch-wrapper {
            position: relative;
        }

        .glitch {
            font-size: 5rem;
            font-weight: 800;
            position: relative;
            color: white;
            text-shadow: 0.05em 0 0 rgba(255, 0, 0, 0.75),
                        -0.025em -0.05em 0 rgba(0, 255, 0, 0.75),
                        0.025em 0.05em 0 rgba(0, 0, 255, 0.75);
            animation: glitch 1s infinite;
        }

        .glitch span {
            position: absolute;
            top: 0;
            left: 0;
        }

        @keyframes glitch {
            0% { text-shadow: 0.05em 0 0 rgba(255, 0, 0, 0.75),-0.025em -0.05em 0 rgba(0, 255, 0, 0.75),0.025em 0.05em 0 rgba(0, 0, 255, 0.75); }
            14% { text-shadow: 0.05em 0 0 rgba(255, 0, 0, 0.75),-0.025em -0.05em 0 rgba(0, 255, 0, 0.75),0.025em 0.05em 0 rgba(0, 0, 255, 0.75); }
            15% { text-shadow: -0.05em -0.025em 0 rgba(255, 0, 0, 0.75),0.025em 0.025em 0 rgba(0, 255, 0, 0.75),-0.05em -0.05em 0 rgba(0, 0, 255, 0.75); }
            49% { text-shadow: -0.05em -0.025em 0 rgba(255, 0, 0, 0.75),0.025em 0.025em 0 rgba(0, 255, 0, 0.75),-0.05em -0.05em 0 rgba(0, 0, 255, 0.75); }
            50% { text-shadow: 0.025em 0.05em 0 rgba(255, 0, 0, 0.75),0.05em 0 0 rgba(0, 255, 0, 0.75),0 -0.05em 0 rgba(0, 0, 255, 0.75); }
            99% { text-shadow: 0.025em 0.05em 0 rgba(255, 0, 0, 0.75),0.05em 0 0 rgba(0, 255, 0, 0.75),0 -0.05em 0 rgba(0, 0, 255, 0.75); }
            100% { text-shadow: -0.025em 0 0 rgba(255, 0, 0, 0.75),-0.025em -0.025em 0 rgba(0, 255, 0, 0.75),-0.025em -0.05em 0 rgba(0, 0, 255, 0.75); }
        }

        .subtitle {
            font-size: 1.5rem;
            color: var(--neon-blue);
            margin-top: 10px;
            font-weight: 300;
        }

        /* --- SECTION STYLING --- */
        section {
            padding: 100px 0;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }

        section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .section-tag {
            display: inline-block;
            background: linear-gradient(90deg, var(--neon-magenta), var(--neon-blue));
            color: white;
            padding: 5px 15px;
            font-size: 0.8rem;
            font-weight: bold;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 2.5rem;
            margin-bottom: 40px;
            background: linear-gradient(90deg, #fff, #888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* --- CARDS --- */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .glass-card:hover {
            border-color: var(--neon-blue);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 242, 255, 0.1);
        }

        .glass-card h3 {
            color: var(--neon-blue);
            margin-bottom: 15px;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--neon-magenta);
        }

        .teaser-section {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(20, 20, 35, 0.9));
            border: 2px solid var(--neon-blue);
            border-radius: 20px;
            padding: 80px 40px;
            text-align: center;
            margin-top: 100px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 50px rgba(0, 242, 255, 0.2);
        }

        .lock-container {
            font-size: 5rem;
            color: var(--neon-blue);
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
            animation: lock-glow 2s infinite alternate;
        }

        @keyframes lock-glow {
            0% { transform: scale(1); text-shadow: 0 0 10px var(--neon-blue); }
            100% { transform: scale(1.05); text-shadow: 0 0 30px var(--neon-blue), 0 0 50px var(--neon-blue); }
        }

        .lock-shackle {
            position: absolute;
            top: -20px;
            left: 50%;
            transform: translateX(-50%);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .teaser-section:hover .lock-shackle {
            transform: translateX(-50%) translateY(-10px);
            color: var(--neon-magenta);
        }

        .teaser-subtitle {
            font-size: 1.2rem;
            letter-spacing: 5px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .teaser-title {
            font-size: 3rem;
            color: white;
            font-weight: 800;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }
        .error-screen {
            background: rgba(255, 0, 0, 0.1);
            border: 2px solid #ff0055;
            padding: 40px;
            border-radius: 10px;
            margin: 40px 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 30px rgba(255, 0, 85, 0.2);
            text-align: center;
        }

        .error-screen::before {
            content: 'CRITICAL ERROR';
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 0.6rem;
            color: #ff0055;
            letter-spacing: 2px;
            opacity: 0.5;
        }

        .error-title {
            color: #ff0055;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(255, 0, 85, 0.5);
        }

        .error-subtitle {
            color: white;
            font-size: 1.2rem;
            font-family: 'Courier New', monospace;
        }

        .error-glitch-layer {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: repeating-linear-gradient(0deg, rgba(255,0,85,0.05) 0px, transparent 2px);
            pointer-events: none;
        }
        .scene-block {
            margin-bottom: 60px;
            border-left: 2px dashed rgba(255, 255, 255, 0.1);
            padding-left: 40px;
            position: relative;
        }

        .scene-block::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            background: var(--neon-blue);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--neon-blue);
        }

        .scene-num {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .visual-cue {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            font-style: italic;
            color: #ccc;
        }

        .visual-cue i {
            margin-right: 10px;
            color: var(--neon-yellow);
        }

        .code-window {
            background: #1e1e1e;
            border-radius: 10px;
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            border: 1px solid #333;
            margin: 20px 0;
            color: #d4d4d4;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .code-window .keyword { color: #569cd6; }
        .code-window .string { color: #ce9178; }
        .code-window .comment { color: #6a9955; }

        .big-output {
            text-align: center;
            padding: 40px;
            font-size: 3rem;
            font-weight: 800;
            color: var(--neon-green);
            text-shadow: 0 0 20px rgba(57, 255, 20, 0.3);
        }

        /* --- FOOTER --- */
        footer {
            padding: 60px 0;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted);
        }

        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            animation: bounce 2s infinite;
            color: var(--text-muted);
            font-size: 1.5rem;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }

        @media (max-width: 768px) {
            .glitch { font-size: 3rem; }
            .section-title { font-size: 2rem; }
            .scene-block { padding-left: 20px; }
        }

        /* --- SCENE 1: COLD OPEN --- */
        #cold-open {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Space Grotesk', sans-serif;
            overflow: hidden;
        }

        .loader-container {
            text-align: center;
            position: relative;
            padding: 40px;
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid var(--neon-blue);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.2), inset 0 0 20px rgba(0, 242, 255, 0.1);
            backdrop-filter: blur(5px);
        }

        .loader-container::before {
            content: '';
            position: absolute;
            top: -5px; left: -5px; right: -5px; bottom: -5px;
            border: 1px solid var(--neon-magenta);
            opacity: 0.3;
            pointer-events: none;
            clip-path: polygon(0 0, 10% 0, 10% 100%, 0 100%);
            animation: border-glitch 2s infinite linear;
        }

        @keyframes border-glitch {
            0% { clip-path: polygon(0 0, 10% 0, 10% 100%, 0 100%); }
            25% { clip-path: polygon(0 0, 100% 0, 100% 10%, 0 10%); }
            50% { clip-path: polygon(90% 0, 100% 0, 100% 100%, 90% 100%); }
            75% { clip-path: polygon(0 90%, 100% 90%, 100% 100%, 0 100%); }
            100% { clip-path: polygon(0 0, 10% 0, 10% 100%, 0 100%); }
        }

        .boot-text {
            color: var(--neon-blue);
            font-size: 1.8rem;
            letter-spacing: 5px;
            margin-bottom: 30px;
            text-shadow: 0 0 15px var(--neon-blue);
            font-weight: 800;
        }

        .progress-wrapper-outer {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .progress-percent {
            font-family: 'Courier New', monospace;
            color: var(--neon-magenta);
            font-size: 0.9rem;
            width: 40px;
        }

        .progress-bar-wrapper {
            width: 300px;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
        }

        .progress-fill {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--neon-blue), var(--neon-magenta));
            box-shadow: 0 0 15px var(--neon-blue);
            transition: width 2s cubic-bezier(0.1, 0, 0.5, 1);
        }

        .access-granted {
            display: none;
            color: var(--neon-green);
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 0 0 20px var(--neon-green);
            animation: glitch-flash 0.5s infinite;
        }

        @keyframes glitch-flash {
            0% { opacity: 1; transform: skew(0deg); }
            10% { opacity: 0.8; transform: skew(10deg); color: #fff; }
            20% { opacity: 1; transform: skew(-10deg); }
            30% { opacity: 0.9; transform: skew(0deg); }
            100% { opacity: 1; }
        }

        .scanline {
            width: 100%;
            height: 100px;
            z-index: 10001;
            background: linear-gradient(0deg, rgba(0, 0, 0, 0) 0%, rgba(255, 255, 255, 0.05) 50%, rgba(0, 0, 0, 0) 100%);
            opacity: 0.1;
            position: absolute;
            bottom: 100%;
            animation: scanline 4s linear infinite;
        }

        @keyframes scanline {
            0% { bottom: 100%; }
            100% { bottom: -100px; }
        }

        .noise {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://upload.wikimedia.org/wikipedia/commons/7/76/White_noise_400x300.png');
            opacity: 0.05;
            pointer-events: none;
            z-index: 10002;
        }

        /* --- ADVANCED GLITCH EFFECTS --- */
        .glitch-text {
            position: relative;
            display: inline-block;
        }

        .glitch-text::before,
        .glitch-text::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.8;
        }

        .glitch-text::before {
            color: #0ff;
            z-index: -1;
            animation: glitch-anim 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) both infinite;
        }

        .glitch-text::after {
            color: #f0f;
            z-index: -2;
            animation: glitch-anim 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94) reverse both infinite;
        }

        @keyframes glitch-anim {
            0% { transform: translate(0); }
            20% { transform: translate(-3px, 3px); }
            40% { transform: translate(-3px, -3px); }
            60% { transform: translate(3px, 3px); }
            80% { transform: translate(3px, -3px); }
            100% { transform: translate(0); }
        }

        /* Digital Distortion Blocks */
        .distortion-layer {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 10005;
            background: transparent;
            overflow: hidden;
            display: none;
        }

        .distort-block {
            position: absolute;
            background: rgba(0, 242, 255, 0.2);
            width: 100%;
            height: 2px;
            animation: distort-h 0.1s infinite;
        }

        @keyframes distort-h {
            0% { top: 0%; left: -100%; }
            100% { top: 100%; left: 100%; }
        }

        .terminal-logs {
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            color: var(--neon-blue);
            opacity: 0.5;
            margin-top: 30px;
            text-align: left;
            width: 300px;
            height: 60px;
            overflow: hidden;
        }

        .log-line {
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .access-granted {
            display: none;
            color: var(--neon-green);
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 0 0 30px var(--neon-green), 0 0 60px var(--neon-green);
            animation: ultimate-flash 0.2s infinite;
            letter-spacing: 10px;
        }

        @keyframes ultimate-flash {
            0% { opacity: 1; transform: scale(1) skew(0deg); filter: hue-rotate(0deg); }
            50% { opacity: 0.5; transform: scale(1.1) skew(20deg); filter: hue-rotate(90deg); }
            100% { opacity: 1; transform: scale(1) skew(-20deg); filter: hue-rotate(180deg); }
        }

        .glitch-reveal {
            animation: slideOut 1s forwards cubic-bezier(0.7, 0, 0.3, 1);
            animation-delay: 1.5s;
        }

        @keyframes slideOut {
            0% { transform: translateY(0); }
            100% { transform: translateY(-100%); display: none; }
        }

        .outro-container {
            text-align: center;
            padding: 100px 0;
            background: radial-gradient(circle at center, rgba(255, 0, 229, 0.1) 0%, transparent 70%);
        }

        .chipmunks-waving {
            width: 500px;
            max-width: 90%;
            border-radius: 30px;
            box-shadow: 0 0 50px rgba(255, 0, 229, 0.3);
            border: 2px solid var(--neon-magenta);
            margin-bottom: 50px;
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .chipmunks-waving:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .subscribe-btn {
            background: var(--neon-magenta);
            color: white;
            padding: 20px 60px;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 5px;
            border: none;
            cursor: pointer;
            position: relative;
            box-shadow: 0 0 30px var(--neon-magenta);
            transition: all 0.3s;
            font-family: 'Space Grotesk', sans-serif;
        }

        .subscribe-btn:hover {
            background: white;
            color: var(--neon-magenta);
            box-shadow: 0 0 50px white;
            transform: translateY(-5px);
        }

        .subscribe-btn::after {
            content: 'CLICK IT!!';
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8rem;
            color: var(--neon-yellow);
            opacity: 0;
            transition: 0.3s;
        }

        .subscribe-btn:hover::after {
            opacity: 1;
            bottom: -40px;
        }

        /* --- TITLE CARD STYLE --- */
        .full-screen-title {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #000;
            position: relative;
            z-index: 50;
            overflow: hidden;
            text-align: center;
            border-top: 2px solid var(--neon-blue);
            border-bottom: 2px solid var(--neon-magenta);
        }

        .title-box {
            position: relative;
            padding: 60px;
            background: rgba(0,0,0,0.8);
            border: 2px solid #fff;
            box-shadow: 0 0 30px rgba(0, 242, 255, 0.3);
            animation: zoomInTitle 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes zoomInTitle {
            from { transform: scale(0.5); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .title-box .clapper {
            font-size: 4rem;
            margin-bottom: 20px;
            display: block;
            animation: clapAnim 1s infinite alternate;
        }

        @keyframes clapAnim {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-15deg); }
        }

        .title-box h2 {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 20px;
            color: #fff;
            text-shadow: 4px 4px 0 var(--neon-magenta);
        }

        .title-box .typing-sub {
            font-family: 'Courier New', monospace;
            color: var(--neon-blue);
            font-size: 1.2rem;
            letter-spacing: 3px;
            border-right: 2px solid var(--neon-blue);
            animation: typingEffect 2s steps(25), blink-caret 0.5s infinite;
            white-space: nowrap;
            overflow: hidden;
            display: inline-block;
        }

        @keyframes typingEffect {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent }
            50% { border-color: var(--neon-blue); }
        }

        .tools-badges {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .tool-badge {
            background: var(--neon-magenta);
            padding: 10px 20px;
            font-weight: 800;
            font-size: 0.9rem;
            border-radius: 5px;
            animation: bounceIn 0.5s forwards;
            opacity: 0;
        }

        .tool-badge.alt { background: var(--neon-yellow); color: #000; }

        @keyframes bounceIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div id="cold-open">
        <div class="noise"></div>
        <div class="scanline"></div>
        <div class="distortion-layer" id="distort-box">
            <div class="distort-block"></div>
        </div>
        <div class="loader-container" id="booting-screen">
            <div class="boot-text glitch-text" id="status-text" data-text="BOOTING PHP WORLD...">BOOTING PHP WORLD...</div>
            <div class="progress-wrapper-outer">
                <div class="progress-bar-wrapper">
                    <div class="progress-fill" id="progress"></div>
                </div>
                <div class="progress-percent" id="percent">0%</div>
            </div>
            <div class="terminal-logs" id="logs">
                <!-- Loaded by JS -->
            </div>
        </div>
        <div class="access-granted" id="access-granted-text" data-text="ACCESS GRANTED">
            ACCESS GRANTED 🔥
        </div>
    </div>

    <div class="grid-bg"></div>
    <div class="glow-sphere" style="top: -200px; left: -200px; background: radial-gradient(circle, rgba(0, 242, 255, 0.1) 0%, transparent 70%);"></div>
    <div class="glow-sphere" style="bottom: -200px; right: -200px; background: radial-gradient(circle, rgba(255, 0, 229, 0.1) 0%, transparent 70%);"></div>

    <header class="container">
        <div class="glitch-wrapper">
            <h1 class="glitch">PHP MASTERCLASS</h1>
            <p class="subtitle">EPISODE 1: WHAT IS PHP + SETUP + FIRST CODE 🔥</p>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </header>

    <section class="full-screen-title" id="title-card-env">
        <div class="title-box">
            <span class="clapper">🎬</span>
            <h2>SETTING UP<br>DEVELOPER ENVIRONMENT...</h2>
            <div style="height: 1.5rem;"><span class="typing-sub">INSTALLING TOOLS 🔥</span></div>
            <div class="tools-badges">
                <div class="tool-badge" style="animation-delay: 2s;">🛠️ TOOLS YOU NEED</div>
                <div class="tool-badge alt" style="animation-delay: 2.3s;">ALVIN APPROVED! ✅</div>
            </div>
        </div>
        <div class="scanline"></div>
    </section>

    <main class="container">
        <!-- SETUP SECTION -->
        <section id="setup">
            <span class="section-tag">Phase 01</span>
            <h2 class="section-title">Environment Setup</h2>
            <div class="grid-3">
                <div class="glass-card">
                    <div class="card-icon"><i class="fas fa-server"></i></div>
                    <h3>1. XAMPP</h3>
                    <p>Local Server (Apache + MySQL)</p>
                </div>
                <div class="glass-card">
                    <div class="card-icon"><i class="fas fa-code"></i></div>
                    <h3>2. VS CODE</h3>
                    <p>Code Editor</p>
                </div>
                <div class="glass-card">
                    <div class="card-icon"><i class="fab fa-chrome"></i></div>
                    <h3>3. BROWSER</h3>
                    <p>Output Viewer</p>
                </div>
            </div>
            
            <div class="glass-card" style="margin-top: 30px;">
                <div class="card-icon"><i class="fas fa-folder-open"></i></div>
                <h3>4. Project Folder Setup</h3>
                <p>C:\xampp\htdocs\php_project</p>
                <div class="visual-cue">
                    <i class="fas fa-info-circle"></i> <b>htdocs folder</b> = ROOT SERVER FOLDER
                </div>
            </div>
        </section>

        <!-- WHAT IS PHP SECTION -->
        <section id="basics">
            <span class="section-tag">Phase 02</span>
            <h2 class="section-title">What is PHP?</h2>
            
            <div class="scene-block">
                <h3>Translation: Brain of the Website</h3>
            </div>

            <div class="scene-block">
                <h3>How the System Works</h3>
                <ul style="list-style: none; margin-top: 20px;">
                    <li><i class="fas fa-arrow-right" style="color: var(--neon-blue); margin-right: 10px;"></i> Kung mo open ka ug website (REQUEST)</li>
                    <li><i class="fas fa-arrow-right" style="color: var(--neon-blue); margin-right: 10px;"></i> PHP mo process (COMPUTE)</li>
                    <li><i class="fas fa-arrow-right" style="color: var(--neon-blue); margin-right: 10px;"></i> Mo balik result (RESPONSE)</li>
                </ul>
            </div>
        </section>

        <!-- FIRST CODE SECTION -->
        <section id="code">
            <span class="section-tag">Phase 03</span>
            <h2 class="section-title">Your First Code</h2>
            
            <div class="scene-block">
                <h3>Hello Bossing!</h3>
                
                <div class="code-window">
                    <span class="keyword">&lt;?php</span><br>
                    &nbsp;&nbsp;<span class="keyword">echo</span> <span class="string">"Hello Bossing!"</span>;<br>
                    <span class="keyword">?&gt;</span>
                </div>

                </div>

                <div class="big-output">
                    Hello Bossing! 🔥
                </div>
            </div>
        </section>

        <!-- ERROR MOMENT SECTION -->
        <section id="error-moment">
            <span class="section-tag">Fun Moment</span>
            <h2 class="section-title">Oops! Syntax Error</h2>
            <div class="error-screen">
                <div class="error-glitch-layer"></div>
                <div class="error-title glitch-text" data-text="SYNTAX ERROR">SYNTAX ERROR</div>
                <div class="error-subtitle">NEWBIE MODE 😆</div>
                <div style="margin-top: 20px; color: var(--text-muted); font-size: 0.8rem; font-family: 'Courier New', monospace;">
                    [line 3] Parse error: unexpected '.', expecting end of file
                </div>
            </div>
        </section>

        <!-- NEXT EPISODE TEASER -->
        <section id="teaser">
            <div class="teaser-section">
                <div class="error-glitch-layer"></div>
                <div class="lock-container">
                    <i class="fas fa-lock-open lock-shackle"></i>
                    <i class="fas fa-database"></i>
                </div>
                <div class="teaser-subtitle glitch-text" data-text="NEXT EPISODE:">NEXT EPISODE:</div>
                <div class="teaser-title">VARIABLES SYSTEM 💾🔥</div>
                
                <div style="margin-top: 50px; opacity: 0.5; font-size: 0.8rem; letter-spacing: 3px;">
                    ENCRYPTION: ACTIVE | MEMORY ALLOCATION: PENDING
                </div>
            </div>
        </section>

        <!-- OUTRO SCENE -->
        <section id="final-outro">
            <div class="outro-container">
                <img src="./cyberpunk_chipmunks_waving_1779427883865.png" alt="Chipmunks Waving" class="chipmunks-waving">
                <div>
                    <button class="subscribe-btn glitch-text" data-text="SUBSCRIBE">SUBSCRIBE</button>
                </div>
                <div style="margin-top: 40px; color: var(--text-muted); letter-spacing: 5px; font-weight: 300;">
                    THANKS FOR WATCHING BOSSING! 🔥
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2026 PHP Masterclass Episodes | Stacey Halu Halu Design System</p>
        </div>
    </footer>

    <script>
        // Simple Intersection Observer for scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('section').forEach(section => {
            observer.observe(section);
        });

        // --- SCENE 1: COLD OPEN LOGIC ---
        window.addEventListener('load', () => {
            const progressBar = document.getElementById('progress');
            const bootingScreen = document.getElementById('booting-screen');
            const accessGrantedText = document.getElementById('access-granted-text');
            const coldOpen = document.getElementById('cold-open');
            const statusText = document.getElementById('status-text');
            const logsContainer = document.getElementById('logs');
            const distortBox = document.getElementById('distort-box');

            const logMessages = [
                "> INITIALIZING KERNEL...",
                "> LOADING PHP_MODULES...",
                "> CONNECTING TO DATABASE...",
                "> BYPASSING FIREWALL...",
                "> ENCRYPTING ASSETS...",
                "> ACCESSING ROOT...",
                "> STARTING ENGINE..."
            ];

            let logIndex = 0;
            function addLog() {
                if (logIndex < logMessages.length) {
                    const line = document.createElement('div');
                    line.className = 'log-line';
                    line.innerText = logMessages[logIndex++];
                    logsContainer.appendChild(line);
                    setTimeout(addLog, 300);
                }
            }
            addLog();

            // Scramble Text Effect
            const originalText = statusText.innerText;
            const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*";
            let iteration = 0;
            
            const scrambleInterval = setInterval(() => {
                statusText.innerText = originalText.split("")
                    .map((letter, index) => {
                        if(index < iteration) return originalText[index];
                        return letters[Math.floor(Math.random() * 44)]
                    })
                    .join("");
                
                if(iteration >= originalText.length) clearInterval(scrambleInterval);
                iteration += 1 / 3;
            }, 30);

            // Start Loading
            setTimeout(() => {
                if(progressBar) progressBar.style.width = '100%';
                
                // Percentage Counter
                const percentEl = document.getElementById('percent');
                let count = 0;
                const countInterval = setInterval(() => {
                    count += Math.floor(Math.random() * 3) + 1;
                    if(count >= 100) {
                        count = 100;
                        clearInterval(countInterval);
                    }
                    if(percentEl) percentEl.innerText = count + '%';
                }, 80);
            }, 100);

            // Random distortions
            setInterval(() => {
                if (distortBox) {
                    distortBox.style.display = 'block';
                    setTimeout(() => distortBox.style.display = 'none', 50);
                }
            }, 1500);

            // Finish Loading
            setTimeout(() => {
                if(bootingScreen) bootingScreen.style.display = 'none';
                if(accessGrantedText) accessGrantedText.style.display = 'block';
                
                // Intense Flash
                document.body.style.filter = 'invert(1)';
                setTimeout(() => document.body.style.filter = 'none', 100);

                if(coldOpen) coldOpen.style.background = '#0a0a0a';
                
                setTimeout(() => {
                    if(coldOpen) coldOpen.classList.add('glitch-reveal');
                }, 1200);
            }, 3500);
        });

        // Add some random glitches to elements on hover
        document.querySelectorAll('.glass-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.borderColor = 'var(--neon-magenta)';
                setTimeout(() => {
                    card.style.borderColor = 'var(--neon-blue)';
                }, 100);
            });
        });
    </script>
</body>
</html>
