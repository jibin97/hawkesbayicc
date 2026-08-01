// header scroll state
const header = document.getElementById("siteHeader");
window.addEventListener("scroll", () => {
  header.classList.toggle("scrolled", window.scrollY > 40);
});

// build ghungroo bells
const bellsGroup = document.getElementById("bells");
const count = 30;
for (let i = 0; i < count; i++) {
  const x = 20 + (1160 / (count - 1)) * i;
  const y = 8 + Math.sin(i / 2) * 6;
  const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
  g.setAttribute("class", "bell");
  g.setAttribute("transform", `translate(${x},${y})`);
  g.innerHTML = `<line x1="0" y1="0" x2="0" y2="14" stroke="#C9A24A" stroke-width="1"/>
      <circle cx="0" cy="20" r="5" fill="#EA9216" stroke="#C9A24A" stroke-width="1"/>
      <circle cx="0" cy="22.5" r="1.2" fill="#1B1330"/>`;
  bellsGroup.appendChild(g);
}

// scroll reveal
const revealEls = document.querySelectorAll(".reveal");
const io = new IntersectionObserver(
  (entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add("in");
        io.unobserve(e.target);
      }
    });
  },
  { threshold: 0.15 },
);
revealEls.forEach((el) => io.observe(el));
