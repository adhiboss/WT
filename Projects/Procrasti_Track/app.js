// ─── STATE MANAGEMENT ───
const defaultState = {
  xp: 1240,
  level: 7,
  streak: 5,
  hearts: 5,
  tasks: []
};

function getState() {
  const saved = localStorage.getItem('procrastitrack_state');
  return saved ? JSON.parse(saved) : defaultState;
}

function saveState(state) {
  localStorage.setItem('procrastitrack_state', JSON.stringify(state));
}

// ─── ANIMATIONS & UI HELPERS ───
function floatXP(amount, x, y) {
  const el = document.createElement('div');
  el.className = 'float-xp';
  el.style.left = x + 'px';
  el.style.top = y + 'px';
  el.textContent = `+${amount} XP`;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 1000);
}

function confettiBurst(x, y) {
  // Simple emoji burst
  const emojis = ['🎉', '✨', '⭐', '🔥', '🦉'];
  for (let i = 0; i < 15; i++) {
    const el = document.createElement('div');
    el.style.position = 'fixed';
    el.style.left = x + 'px';
    el.style.top = y + 'px';
    el.style.fontSize = '1.2rem';
    el.textContent = emojis[Math.floor(Math.random() * emojis.length)];
    el.style.pointerEvents = 'none';
    el.style.zIndex = '6000';
    document.body.appendChild(el);
    
    const angle = Math.random() * Math.PI * 2;
    const dist = 50 + Math.random() * 100;
    const tx = Math.cos(angle) * dist;
    const ty = Math.sin(angle) * dist;

    el.animate([
      { transform: 'translate(0,0) scale(1)', opacity: 1 },
      { transform: `translate(${tx}px, ${ty}px) scale(0)`, opacity: 0 }
    ], { duration: 800 + Math.random() * 400, easing: 'cubic-bezier(0,0,0.2,1)' }).onfinish = () => el.remove();
  }
}

// ─── MEME / TOAST SYSTEM ───
const MEMES = [
  { t: "Duo is watching...", s: "You have 3 overdue tasks. Don't make me come to your house.", e: "🦉" },
  { t: "Nice work!", s: "You're actually being productive. I'm impressed (for now).", e: "🎯" },
  { t: "Streak at risk!", s: "One missed task and your 5-day streak is GONE. 🔥", e: "🚨" },
  { t: "Level Up Soon", s: "Only 260 XP until Level 8. Stop scrolling and start clicking.", e: "🚀" }
];

function showMeme(taskTitle = "") {
  const m = MEMES[Math.floor(Math.random() * MEMES.length)];
  const t = document.getElementById('memeToast');
  if (!t) return;
  document.getElementById('mEmo').textContent = m.e;
  document.getElementById('mTitle').textContent = m.t;
  document.getElementById('mSub').textContent = m.s;
  document.getElementById('mTask').textContent = taskTitle ? `Re: ${taskTitle}` : "";
  t.style.display = 'block';
  t.classList.add('fade-up');
}

function closeMeme() {
  const t = document.getElementById('memeToast');
  if (t) t.style.display = 'none';
}

function showCelebration(title, sub, icon) {
  const el = document.createElement('div');
  el.className = 'celebration';
  el.innerHTML = `
    <div class="celebration-card">
      <div style="font-size:5rem;margin-bottom:1rem">${icon || '🏆'}</div>
      <h1 style="font-size:2.5rem;font-weight:900;margin-bottom:.5rem">${title}</h1>
      <p style="font-size:1.1rem;font-weight:600;color:var(--text2);margin-bottom:2rem">${sub}</p>
      <button class="btn btn-primary" onclick="this.closest('.celebration').remove()">AWESOME!</button>
    </div>
  `;
  document.body.appendChild(el);
}

function injectNavStats() {
  const s = getState();
  const nav = document.querySelector('nav');
  if (!nav) return;
  document.querySelectorAll('.nav-streak,.nav-hearts').forEach(e => e.remove());
  const streak = document.createElement('div');
  streak.className = 'nav-streak';
  streak.innerHTML = `🔥 ${s.streak}`;
  const hearts = document.createElement('div');
  hearts.className = 'nav-hearts';
  hearts.innerHTML = `❤️ ${s.hearts}`;
  const cta = nav.querySelector('.nav-cta')?.closest('li');
  if (cta) {
    nav.insertBefore(hearts, cta);
    nav.insertBefore(streak, hearts);
  } else {
    nav.appendChild(streak);
    nav.appendChild(hearts);
  }
}

document.addEventListener('DOMContentLoaded', () => {
    injectNavStats();
});
