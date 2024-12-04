(function(window) {
    class CryptoWidget {
        constructor(containerId, size = 'medium', options = {}) {
            this.container = document.getElementById(containerId);
            if (!this.container) {
                throw new Error(`Container with ID "${containerId}" not found`);
            }

            // Default configuration
            this.config = {
                updateInterval: options.updateInterval || 60000, // 1 minute
                endpoint: options.endpoint || 'prices.php',
                theme: options.theme || 'light',
                animate: options.animate !== false,
                retryInterval: 5000, // 5 seconds
                maxRetries: 3
            };

            this.size = size;
            this.prices = {};
            this.retryCount = 0;
            this.lastUpdate = null;
            this.styleElement = null;

            // Initialize the widget
            this.init();
        }

        async init() {
            // Add base styles
            this.addStyles();
            
            // Show loading state
            this.showLoading();
            
            // Start price updates
            await this.updatePrices();
            this.startUpdateCycle();
        }

        getThemeStyles() {
            const themes = {
                light: {
                    background: '#ffffff',
                    text: '#333333',
                    border: '#eeeeee',
                    header: '#f8f9fa',
                    hover: '#f8f9fa'
                },
                dark: {
                    background: '#1a1a1a',
                    text: '#ffffff',
                    border: '#333333',
                    header: '#2d2d2d',
                    hover: '#2d2d2d'
                },
                ocean: {
                    background: '#f0f8ff',
                    text: '#00008b',
                    border: '#b0e0e6',
                    header: '#e6f3ff',
                    hover: '#e6f3ff'
                },
                forest: {
                    background: '#f5fff5',
                    text: '#006400',
                    border: '#98fb98',
                    header: '#e8ffe8',
                    hover: '#e8ffe8'
                }
            };

            return themes[this.config.theme] || themes.light;
        }

        addStyles() {
            const theme = this.getThemeStyles();
            const widgetId = this.container.id;
            
            const styles = `
                #${widgetId}.crypto-widget {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    background: ${theme.background};
                    color: ${theme.text};
                }
                #${widgetId}.crypto-widget.small { width: 200px; font-size: 12px; }
                #${widgetId}.crypto-widget.medium { width: 300px; font-size: 14px; }
                #${widgetId}.crypto-widget.large { width: 400px; font-size: 16px; }
                
                #${widgetId} .header {
                    background: ${theme.header};
                    padding: 10px;
                    border-bottom: 1px solid ${theme.border};
                    font-weight: bold;
                    display: flex;
                    justify-content: space-between;
                }
                
                #${widgetId} .crypto-price {
                    padding: 10px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 1px solid ${theme.border};
                    transition: background-color 0.3s ease;
                }
                
                #${widgetId} .crypto-price:last-child {
                    border-bottom: none;
                }
                
                #${widgetId} .crypto-price:hover {
                    background-color: ${theme.hover};
                }
                
                #${widgetId} .price-up { color: #28a745; }
                #${widgetId} .price-down { color: #dc3545; }
                
                #${widgetId} .crypto-name {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                #${widgetId} .loading {
                    padding: 20px;
                    text-align: center;
                    color: ${theme.text};
                }
                
                #${widgetId} .error {
                    padding: 10px;
                    color: #721c24;
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    margin: 10px;
                    border-radius: 4px;
                    font-size: 12px;
                }

                #${widgetId} .update-time {
                    font-size: 10px;
                    color: ${theme.text};
                    opacity: 0.7;
                    text-align: right;
                    padding: 5px 10px;
                }

                @keyframes pulse {
                    0% { opacity: 1; }
                    50% { opacity: 0.5; }
                    100% { opacity: 1; }
                }

                #${widgetId} .loading-pulse {
                    animation: pulse 1.5s infinite;
                }
            `;

            // Remove old styles if they exist
            if (this.styleElement) {
                this.styleElement.remove();
            }

            // Add new styles
            this.styleElement = document.createElement('style');
            this.styleElement.textContent = styles;
            document.head.appendChild(this.styleElement);

            this.container.className = `crypto-widget ${this.size}`;
        }

        showLoading() {
            this.container.innerHTML = '<div class="loading loading-pulse">Loading cryptocurrency prices...</div>';
        }

        showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = message;
            this.container.appendChild(errorDiv);
        }

        async updatePrices() {
            try {
                const response = await fetch(this.config.endpoint);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                if (!data.prices || Object.keys(data.prices).length === 0) {
                    throw new Error('Invalid price data received');
                }

                const oldPrices = {...this.prices};
                this.prices = data.prices;
                this.lastUpdate = new Date();
                this.retryCount = 0;
                this.render(oldPrices);

            } catch (error) {
                console.error('Failed to fetch prices:', error);
                
                if (this.retryCount < this.config.maxRetries) {
                    this.retryCount++;
                    setTimeout(() => this.updatePrices(), this.config.retryInterval);
                } else {
                    this.showError('Unable to fetch cryptocurrency prices. Please try again later.');
                }
            }
        }

        startUpdateCycle() {
            setInterval(() => this.updatePrices(), this.config.updateInterval);
        }

        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(price);
        }

        formatTimeAgo() {
            if (!this.lastUpdate) return '';
            
            const seconds = Math.floor((new Date() - this.lastUpdate) / 1000);
            if (seconds < 60) return 'Updated just now';
            if (seconds < 120) return 'Updated 1 minute ago';
            const minutes = Math.floor(seconds / 60);
            return `Updated ${minutes} minutes ago`;
        }

        render(oldPrices) {
            this.container.innerHTML = `
                <div class="header">
                    <span>Cryptocurrency</span>
                    <span>Price</span>
                </div>
            `;

            for (const [coin, price] of Object.entries(this.prices)) {
                const priceChange = oldPrices[coin] ? price - oldPrices[coin] : 0;
                const priceClass = priceChange > 0 ? 'price-up' : priceChange < 0 ? 'price-down' : '';
                
                const div = document.createElement('div');
                div.className = `crypto-price ${priceClass}`;
                div.innerHTML = `
                    <span class="crypto-name">
                        ${coin.charAt(0).toUpperCase() + coin.slice(1)}
                    </span>
                    <span>${this.formatPrice(price)}</span>
                `;
                this.container.appendChild(div);
            }

            // Add update time
            const timeDiv = document.createElement('div');
            timeDiv.className = 'update-time';
            timeDiv.textContent = this.formatTimeAgo();
            this.container.appendChild(timeDiv);
        }

        updateTheme(theme) {
            this.config.theme = theme;
            this.addStyles();
            this.render(this.prices);
        }
    }

    // Expose CryptoWidget to global scope
    window.CryptoWidget = CryptoWidget;
})(window);
