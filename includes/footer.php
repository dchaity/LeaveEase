</div>
    </main>

    <footer style="background: white; border-top: 1px solid var(--gray-200); padding: 24px 0; margin-top: 48px;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <span style="font-weight: 600; color: var(--text-primary);">LeaveEase</span>
                </div>
                <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">
                    &copy; <?= date('Y') ?> LeaveEase. All rights reserved.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" style="color: var(--text-muted); font-size: 0.85rem;">Privacy</a>
                    <a href="#" style="color: var(--text-muted); font-size: 0.85rem;">Terms</a>
                    <a href="#" style="color: var(--text-muted); font-size: 0.85rem;">Help</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- AI Chat Assistant -->
    <button class="ai-chat-toggle" id="aiChatToggle">
        <i class="fas fa-robot"></i>
    </button>

    <div class="ai-chat-container" id="aiChatContainer">
        <div class="ai-chat-header">
            <h4><i class="fas fa-robot"></i> AI Assistant</h4>
            <button style="background: none; border: none; color: white; cursor: pointer; font-size: 1.2rem;" onclick="document.getElementById('aiChatContainer').classList.remove('active')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="ai-chat-messages" id="aiChatMessages">
            <div class="chat-message ai">
                👋 Hello! I'm your AI Assistant. How can I help you today?
                <br><br>
                You can ask me about:
                <br>• Leave balance
                <br>• Applying for leave
                <br>• Request status
                <br>• Upcoming holidays
            </div>
        </div>
        <div class="ai-chat-input">
            <input type="text" id="aiChatInput" placeholder="Type your message...">
            <button id="aiChatSend">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
    <script src="<?= BASE_URL ?>assets/js/ai-assistant.js"></script>
    <?php if (!empty($is_admin_area)): ?>
    <script src="<?= BASE_URL ?>assets/js/admin.js"></script>
    <?php endif; ?>

    <script>
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
        const dateStr = now.toLocaleDateString('en-US', {
            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
        });
        const clockEl = document.getElementById('liveClock');
        const dateEl = document.getElementById('liveDate');
        if (clockEl) clockEl.textContent = timeStr;
        if (dateEl) dateEl.textContent = dateStr;
    }
    updateClock();
    setInterval(updateClock, 1000);

    setTimeout(() => {
        document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
    </script>
</body>
</html>