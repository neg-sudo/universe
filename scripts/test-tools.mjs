#!/usr/bin/env node
/**
 * Runs every declared tool test case against the compiled engine.
 * Usage: node scripts/test-tools.mjs
 */
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { TOOLS } from '../public/assets/js/tools.generated.js';

const root = dirname(dirname(fileURLToPath(import.meta.url)));
const catalog = JSON.parse(readFileSync(join(root, 'public/assets/data/tools.json'), 'utf8'));

const h = {
  n(value) {
    if (typeof value === 'number') {
      if (!Number.isFinite(value)) throw new Error('invalid number');
      return value;
    }
    if (value === null || value === undefined || String(value).trim() === '') throw new Error('missing value');
    const parsed = Number(String(value).replace(/,/g, '').trim());
    if (!Number.isFinite(parsed)) throw new Error(`invalid number: ${value}`);
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
    if (whole === 0) throw new Error('zero total');
    return (part / whole) * 100;
  },
  splitLines: (text) => String(text ?? '').split(/\r?\n/).map((l) => l.trim()).filter(Boolean),
  words: (text) => String(text ?? '').trim().split(/\s+/).filter(Boolean),
  escapeHtml: (text) => String(text ?? ''),
};

let passed = 0;
const failures = [];
const seen = new Set();

for (const tool of catalog) {
  const fn = TOOLS[tool.slug];
  seen.add(tool.slug);
  if (typeof fn !== 'function') {
    failures.push(`${tool.slug}: no compiled function`);
    continue;
  }

  const defaults = {};
  for (const input of tool.inputs) {
    if (input.default !== undefined) defaults[input.name] = input.default;
  }

  for (const [index, test] of tool.tests.entries()) {
    const values = { ...defaults, ...test.input };
    let output;
    try {
      output = await fn(values, h);
    } catch (error) {
      failures.push(`${tool.slug} test ${index + 1}: threw "${error.message}"`);
      continue;
    }

    const results = Array.isArray(output) ? output : [output];
    const byLabel = new Map(results.map((r) => [String(r.label), r.value]));

    for (const [label, expected] of Object.entries(test.expect)) {
      if (!byLabel.has(label)) {
        failures.push(`${tool.slug} test ${index + 1}: missing result "${label}" (got: ${[...byLabel.keys()].join(', ')})`);
        continue;
      }
      const actual = byLabel.get(label);
      if (typeof expected === 'number') {
        const tolerance = Math.max(1e-6, Math.abs(expected) * 1e-6);
        if (typeof actual !== 'number' || !Number.isFinite(actual) || Math.abs(actual - expected) > tolerance) {
          failures.push(`${tool.slug} test ${index + 1}: ${label} expected ${expected}, got ${actual}`);
          continue;
        }
      } else if (String(actual) !== String(expected)) {
        failures.push(`${tool.slug} test ${index + 1}: ${label} expected "${expected}", got "${actual}"`);
        continue;
      }
      passed++;
    }
  }
}

for (const slug of Object.keys(TOOLS)) {
  if (!seen.has(slug)) failures.push(`${slug}: compiled but not present in catalog`);
}

console.log(`Tool tests: ${passed} assertions passed across ${catalog.length} tools.`);
if (failures.length) {
  console.error(`\n${failures.length} failure(s):`);
  for (const failure of failures) console.error(` - ${failure}`);
  process.exit(1);
}
console.log('All tool tests passed.');
