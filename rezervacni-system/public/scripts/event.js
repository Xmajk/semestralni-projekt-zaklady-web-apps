document.addEventListener("DOMContentLoaded", function() {
    // === COUNTDOWN ===
    const container = document.getElementById("countdown-container");
    const labelEl = document.getElementById("countdown-label");
    const timerEl = document.getElementById("countdown-timer");
    const deadlineTime = new Date(container.dataset.deadline).getTime();
    const startTime = new Date(container.dataset.start).getTime();

    function updateTimer() {
        const now = new Date().getTime();
        let targetTime;
        if (now < deadlineTime) {
            targetTime = deadlineTime; labelEl.textContent = "Konec registrace za:";
            labelEl.style.color = "#666"; timerEl.style.color = "var(--primary-kacubo)";
        } else if (now < startTime) {
            targetTime = startTime; labelEl.textContent = "Začátek události za:";
            labelEl.style.color = "#0f5132"; timerEl.style.color = "#333";
        } else {
            labelEl.textContent = "Stav události:"; timerEl.textContent = "Akce proběhla";
            timerEl.style.fontSize = "1.2rem"; return;
        }
        const dist = targetTime - now;
        const d = Math.floor(dist / (86400000));
        const h = Math.floor((dist % (86400000)) / (3600000)).toString().padStart(2,'0');
        const m = Math.floor((dist % (3600000)) / (60000)).toString().padStart(2,'0');
        const s = Math.floor((dist % (60000)) / 1000).toString().padStart(2,'0');
        timerEl.textContent = (d > 0) ? `${d}d ${h}:${m}:${s}` : `${h}:${m}:${s}`;
    }
    updateTimer(); setInterval(updateTimer, 1000);
});