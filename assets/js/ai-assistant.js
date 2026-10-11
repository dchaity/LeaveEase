// ============================================
// LEAVEEASE - AI ASSISTANT CHATBOT
// ============================================

class AIAssistant {
    constructor() {
        this.isOpen = false;
        this.container = document.getElementById('aiChatContainer');
        this.toggleBtn = document.getElementById('aiChatToggle');
        this.messagesContainer = document.getElementById('aiChatMessages');
        this.inputField = document.getElementById('aiChatInput');
        this.sendBtn = document.getElementById('aiChatSend');
        this.isTyping = false;

        this.init();
    }

    init() {
        if (!this.container || !this.toggleBtn) {
            console.warn('⚠️ AI Chat elements not found on page');
            return;
        }
        this.toggleBtn.addEventListener('click', () => this.toggleChat());
        if (this.sendBtn) this.sendBtn.addEventListener('click', () => this.sendMessage());
        if (this.inputField) {
            this.inputField.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.sendMessage();
            });
        }
        console.log('✅ AI Assistant initialized');
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        this.container.classList.toggle('active');
        if (this.isOpen) {
            this.toggleBtn.innerHTML = '<i class="fas fa-times"></i>';
            this.inputField.focus();
        } else {
            this.toggleBtn.innerHTML = '<i class="fas fa-robot"></i>';
        }
    }

    addMessage(type, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${type}`;
        messageDiv.innerHTML = content;
        this.messagesContainer.appendChild(messageDiv);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    showTyping() {
        if (this.isTyping) return;
        this.isTyping = true;
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message ai';
        typingDiv.id = 'typingIndicator';
        typingDiv.innerHTML = '<i class="fas fa-circle" style="font-size: 6px; animation: pulse 1s infinite;"></i> <i class="fas fa-circle" style="font-size: 6px; animation: pulse 1s infinite 0.2s;"></i> <i class="fas fa-circle" style="font-size: 6px; animation: pulse 1s infinite 0.4s;"></i>';
        this.messagesContainer.appendChild(typingDiv);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    removeTyping() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
        this.isTyping = false;
    }

    async sendMessage() {
        const message = this.inputField.value.trim();
        if (!message) return;

        this.addMessage('user', message);
        this.inputField.value = '';
        this.inputField.disabled = true;
        this.showTyping();

        try {
            // Build API URL robustly
            let apiUrl;
            if (typeof BASE_URL !== 'undefined' && BASE_URL) {
                apiUrl = BASE_URL + 'ai-api.php';
            } else {
                // Fallback: detect based on current path
                const pathParts = window.location.pathname.split('/');
                const baseIndex = pathParts.findIndex(p => p === 'pages' || p === 'admin');
                const basePath = baseIndex > -1 ? pathParts.slice(0, baseIndex).join('/') : '';
                apiUrl = window.location.origin + basePath + '/ai-api.php';
            }

            console.log('📡 AI API URL:', apiUrl);

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message }),
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const data = await response.json();
            this.removeTyping();

            if (data.success) {
                this.addMessage('ai', data.response);
            } else {
                this.addMessage('ai', '❌ ' + (data.message || 'Sorry, I encountered an error.'));
            }
        } catch (error) {
            console.error('AI Error:', error);
            this.removeTyping();
            this.addMessage('ai', '❌ Sorry, I\'m having trouble connecting. Please try again.<br><small style="color: #999;">Error: ' + error.message + '</small>');
        }

        this.inputField.disabled = false;
        this.inputField.focus();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    new AIAssistant();
});