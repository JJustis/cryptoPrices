<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Price Widget - Interactive Demo</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #4f46e5;
            --success-color: #22c55e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .section h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.8rem;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .demo-container {
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .demo-container h3 {
            margin-bottom: 15px;
            color: #444;
        }

        .code-block {
            background: #1e1e1e;
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            position: relative;
            margin: 20px 0;
            overflow-x: auto;
        }

        .code-block pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .copy-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .theme-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .theme-button {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #ddd;
            font-weight: 500;
        }

        .theme-button.active {
            outline: 2px solid var(--primary-color);
        }

        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-color);
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            display: none;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .section {
                padding: 20px;
            }
            .demo-grid {
                grid-template-columns: 1fr;
            }
            .hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="container">
            <h1>Cryptocurrency Price Widget</h1>
            <p>A lightweight, customizable widget to display real-time cryptocurrency prices on your website</p>
        </div>
    </div>

    <div class="container">
        <section class="section">
            <h2>Live Demos</h2>
            <div class="theme-selector">
                <button class="theme-button active" data-theme="light" onclick="changeTheme('light', this)">Light Theme</button>
                <button class="theme-button" data-theme="dark" onclick="changeTheme('dark', this)">Dark Theme</button>
                <button class="theme-button" data-theme="ocean" onclick="changeTheme('ocean', this)">Ocean Theme</button>
                <button class="theme-button" data-theme="forest" onclick="changeTheme('forest', this)">Forest Theme</button>
            </div>
            <div class="demo-grid">
                <div class="demo-container">
                    <h3>Small Widget</h3>
                    <div id="smallWidget"></div>
                </div>
                <div class="demo-container">
                    <h3>Medium Widget</h3>
                    <div id="mediumWidget"></div>
                </div>
                <div class="demo-container">
                    <h3>Large Widget</h3>
                    <div id="largeWidget"></div>
                </div>
            </div>
        </section>

        <section class="section">
            <h2>Installation</h2>
            <div class="code-block">
                <button class="copy-button" onclick="copyCode(this)">Copy</button>
                <pre><code id="embedCode"></code></pre>
            </div>
        </section>
    </div>

    <div id="successMessage" class="success-message">Copied to clipboard!</div>

    <script src="widget.js"></script>
    <script>
        let currentTheme = 'light';
        const domain = window.location.origin;

        function generateEmbedCode(theme, size = 'medium') {
            return `<div id="cryptoWidget"></div>
<script src="${domain}/widgets/cryptoprices/widget.js"><\/script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new CryptoWidget('cryptoWidget', '${size}', {
            theme: '${theme}',
            updateInterval: 60000
        });
    });
<\/script>`;
        }

        function updateEmbedCode() {
            const codeElement = document.getElementById('embedCode');
            codeElement.textContent = generateEmbedCode(currentTheme);
        }

        function changeTheme(theme, button) {
            // Update active button state
            document.querySelectorAll('.theme-button').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');

            // Update current theme
            currentTheme = theme;

            // Reinitialize widgets
            initializeWidgets(theme);

            // Update embed code
            updateEmbedCode();
        }

        function initializeWidgets(theme) {
            if (window.widgets) {
                window.widgets.forEach(widget => {
                    if (widget.container) {
                        widget.container.innerHTML = '';
                    }
                });
            }

            window.widgets = [
                new CryptoWidget('smallWidget', 'small', { theme }),
                new CryptoWidget('mediumWidget', 'medium', { theme }),
                new CryptoWidget('largeWidget', 'large', { theme })
            ];
        }

        function copyCode(button) {
            const codeBlock = button.nextElementSibling.textContent;
            navigator.clipboard.writeText(codeBlock).then(() => {
                const message = document.getElementById('successMessage');
                message.style.display = 'block';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 2000);
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeWidgets('light');
            updateEmbedCode();
        });
    </script>
</body>
</html>