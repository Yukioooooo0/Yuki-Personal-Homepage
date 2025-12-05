document.addEventListener('DOMContentLoaded', function () {
  // Highlight if available
  if (window.hljs && typeof hljs.highlightAll === 'function') {
    try { hljs.highlightAll(); } catch (e) { /* ignore */ }
  }

  const codeBlocks = Array.from(document.querySelectorAll('pre > code'));
  if (!codeBlocks.length) return;

  codeBlocks.forEach(code => {
    const pre = code.parentElement;
    // ensure pre is positioned (styles.css sets position:relative)

    // create toolbar
    const toolbar = document.createElement('div');
    toolbar.className = 'code-toolbar';

    // language label
    const langMatch = (code.className || '').match(/language-([a-zA-Z0-9_+-]+)/);
    if (langMatch) {
      const lang = document.createElement('span');
      lang.className = 'code-lang';
      lang.textContent = langMatch[1];
      toolbar.appendChild(lang);
    }

    // copy button
    const btn = document.createElement('button');
    btn.className = 'copy-btn';
    btn.type = 'button';
    btn.setAttribute('aria-label', '复制代码');
    btn.textContent = '复制';
    toolbar.appendChild(btn);

    pre.appendChild(toolbar);

    btn.addEventListener('click', async function () {
      const text = code.innerText;
      try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(text);
        } else {
          // fallback
          const ta = document.createElement('textarea');
          ta.value = text;
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          ta.remove();
        }

        btn.textContent = '已复制';
        btn.disabled = true;
        setTimeout(() => {
          btn.textContent = '复制';
          btn.disabled = false;
        }, 1400);
      } catch (err) {
        btn.textContent = '复制失败';
        setTimeout(() => { btn.textContent = '复制'; }, 1400);
      }
    });
  });
});
