// UniVerse tool runtime: turns a declarative form into a live calculator.
import { TOOLS } from './tools.generated.js';

const numberFormatter = (decimals) =>
  new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: decimals,
  });

const h = {
  n(value) {
    if (typeof value === 'number') {
      if (!Number.isFinite(value)) throw new Error('Please enter a valid number.');
      return value;
    }
    if (value === null || value === undefined || String(value).trim() === '') {
      throw new Error('Please fill in every required field.');
    }
    const parsed = Number(String(value).replace(/,/g, '').trim());
    if (!Number.isFinite(parsed)) throw new Error(`"${value}" is not a valid number.`);
    return parsed;
  },
  round(value, decimals = 2) {
    const factor = 10 ** decimals;
    const sign = value < 0 ? -1 : 1;
    return (sign * Math.round(Math.abs(value) * factor)) / factor;
  },
  clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  },
  pct(part, whole) {
    if (whole === 0) throw new Error('The total cannot be zero.');
    return (part / whole) * 100;
  },
  splitLines(text) {
    return String(text ?? '')
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter(Boolean);
  },
  words(text) {
    return String(text ?? '')
      .trim()
      .split(/\s+/)
      .filter(Boolean);
  },
  escapeHtml(text) {
    return String(text ?? '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
    }[c]));
  },
};

function readValue(field) {
  if (field.type === 'checkbox') return field.checked;
  return field.value;
}

function collectValues(form) {
  const values = {};
  for (const field of form.elements) {
    if (!field.name) continue;
    values[field.name] = readValue(field);
  }
  return values;
}

function formatNumber(value) {
  if (!Number.isFinite(value)) return '—';
  const abs = Math.abs(value);
  if (abs !== 0 && (abs >= 1e15 || abs < 1e-6)) {
    return value.toExponential(6).replace(/\.?0+e/, 'e');
  }
  return numberFormatter(6).format(value);
}

function displayValue(result) {
  const raw = result.value;
  if (typeof raw === 'number') {
    const decimals = Number.isInteger(result.decimals) ? result.decimals : 6;
    const rounded = h.round(raw, decimals);
    return { text: decimals === 0 ? numberFormatter(0).format(rounded) : formatNumber(rounded), unit: result.unit || '' };
  }
  return { text: String(raw ?? '—'), unit: result.unit || '' };
}

function applyVisibility(form) {
  for (const wrapper of form.querySelectorAll('[data-input]')) {
    const rules = wrapper.dataset.showIf;
    if (!rules) continue;
    let visible = true;
    for (const [name, allowed] of Object.entries(JSON.parse(rules))) {
      const field = form.elements[name];
      const value = field ? readValue(field) : '';
      const options = Array.isArray(allowed) ? allowed : [allowed];
      if (!options.map(String).includes(String(value))) visible = false;
    }
    wrapper.hidden = !visible;
  }
}

function renderResults(container, results) {
  container.replaceChildren();
  container.classList.add('is-filled');

  for (const result of results) {
    const card = document.createElement('div');
    card.className = 'result-card';

    const label = document.createElement('div');
    label.className = 'result-card__label';
    label.textContent = result.label || 'Result';

    const value = document.createElement('div');
    value.className = 'result-card__value';
    const { text, unit } = displayValue(result);
    value.textContent = text;
    if (unit) {
      const unitEl = document.createElement('span');
      unitEl.className = 'result-card__unit';
      unitEl.textContent = unit;
      value.append(' ', unitEl);
    }

    card.append(label, value);

    if (result.hint) {
      const hint = document.createElement('p');
      hint.className = 'result-card__hint';
      hint.textContent = result.hint;
      card.append(hint);
    }

    container.append(card);
  }

  const actions = document.createElement('div');
  actions.className = 'result-actions';

  const copy = document.createElement('button');
  copy.type = 'button';
  copy.className = 'btn btn--ghost btn--sm';
  copy.textContent = 'Copy results';
  copy.addEventListener('click', async () => {
    const lines = results.map((r) => {
      const { text, unit } = displayValue(r);
      return `${r.label || 'Result'}: ${text}${unit ? ' ' + unit : ''}`;
    });
    try {
      await navigator.clipboard.writeText(lines.join('\n'));
      copy.textContent = 'Copied';
      setTimeout(() => (copy.textContent = 'Copy results'), 1600);
    } catch {
      copy.textContent = 'Copy failed';
    }
  });

  actions.append(copy);
  container.append(actions);
}

function renderError(container, message) {
  container.replaceChildren();
  container.classList.add('is-filled');
  const box = document.createElement('div');
  box.className = 'result-error';
  box.setAttribute('role', 'alert');
  box.textContent = message;
  container.append(box);
}

function setUrlState(form) {
  const values = collectValues(form);
  const params = new URLSearchParams();
  for (const [key, value] of Object.entries(values)) {
    if (value === '' || value === false) continue;
    params.set(key, String(value));
  }
  const url = new URL(window.location.href);
  url.search = params.toString();
  window.history.replaceState({}, '', url);
}

function trackVisit(form) {
  if (form.dataset.tracked === 'true') return;
  form.dataset.tracked = 'true';

  const url = form.dataset.trackUrl;
  if (!url) return;

  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    keepalive: true,
    headers: {
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
    },
  }).catch(() => {
    // Usage tracking is best-effort; the calculator must keep working offline.
  });
}

function prefillFromUrl(form) {
  const params = new URLSearchParams(window.location.search);
  let touched = false;
  for (const field of form.elements) {
    if (!field.name || !params.has(field.name)) continue;
    const value = params.get(field.name);
    if (field.type === 'checkbox') {
      field.checked = value === '1' || value === 'true' || value === 'on';
    } else {
      field.value = value;
    }
    touched = true;
  }
  return touched;
}

export function initToolForms() {
  for (const form of document.querySelectorAll('form[data-tool]')) {
    const slug = form.dataset.tool;
    const container = form.querySelector('[data-tool-results]') || document.querySelector('[data-tool-results]');
    const compute = TOOLS[slug];
    if (!compute || !container) continue;

    applyVisibility(form);
    form.addEventListener('change', () => applyVisibility(form));

    let timer = null;
    const run = async () => {
      const values = collectValues(form);
      try {
        const output = await compute(values, h);
        const results = Array.isArray(output) ? output : [output];
        if (!results.length || results.some((r) => !r || typeof r !== 'object')) {
          throw new Error('This tool did not return a result.');
        }
        renderResults(container, results);
        setUrlState(form);
      } catch (error) {
        renderError(container, error instanceof Error ? error.message : 'Something went wrong.');
      }
    };

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      trackVisit(form);
      run();
    });

    form.addEventListener('input', () => {
      trackVisit(form);
      clearTimeout(timer);
      timer = setTimeout(run, 220);
    });

    const prefilled = prefillFromUrl(form);
    if (prefilled) applyVisibility(form);
    if (prefilled || form.dataset.autorun === 'true') run();
  }
}

document.addEventListener('DOMContentLoaded', initToolForms);
