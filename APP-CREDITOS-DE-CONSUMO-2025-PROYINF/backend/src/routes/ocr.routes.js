import express from 'express';
import multer from 'multer';
import vision from '@google-cloud/vision';

const router = express.Router();
const upload = multer({ storage: multer.memoryStorage() });

// Inicializa cliente Vision (usa GOOGLE_APPLICATION_CREDENTIALS del .env)
const client = new vision.ImageAnnotatorClient();

// ---------------- Helpers: RUT y Nombre ----------------
function isValidRut(body, dv) {
  let sum = 0, mul = 2;
  for (let i = body.length - 1; i >= 0; i--) {
    sum += Number(body[i]) * mul;
    mul = mul === 7 ? 2 : mul + 1;
  }
  const res = 11 - (sum % 11);
  const dvCalc = res === 11 ? '0' : res === 10 ? 'K' : String(res);
  return dvCalc === dv.toUpperCase();
}

function findRutInfo(rawText = '') {
  // 1) normaliza texto global
  const norm = String(rawText)
    .replace(/\u00A0/g, ' ')     // NBSP -> espacio
    .replace(/[–—−]/g, '-')      // guiones unicode -> ASCII
    .replace(/\s+/g, ' ')        // colapsa espacios
    .toUpperCase();

  // 2) líneas (sin tocar: nos sirve para ubicar el índice de ventana)
  const lines = String(rawText).split(/\r?\n+/).map(s => s.trim()).filter(Boolean);
  const linesUp = lines.map(l => l.toUpperCase());

  // ---- helpers
  const isValidRut = (body, dv) => {
    let sum = 0, mul = 2;
    for (let i = body.length - 1; i >= 0; i--) {
      sum += Number(body[i]) * mul;
      mul = mul === 7 ? 2 : mul + 1;
    }
    const res = 11 - (sum % 11);
    const dvCalc = res === 11 ? '0' : res === 10 ? 'K' : String(res);
    return dvCalc === dv.toUpperCase();
  };

  const formatWithDots = (digits) =>
    digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

  const locateLineIdx = (needle) => {
    const nd = needle.replace(/\./g, '');
    for (let i = 0; i < lines.length; i++) {
      if (lines[i].replace(/\./g, '').includes(nd)) return i;
    }
    return -1;
  };

  const GOOD = new Set(['RUN', 'RUT']);
  const BAD  = new Set(['DOCUMENTO', 'DOC.', 'N°', 'NO.', 'NUMERO', 'NÚMERO']);
  const ctxScore = (idx, radius = 2) => {
    if (idx < 0) return 0;
    let sc = 0;
    const from = Math.max(0, idx - radius);
    const to   = Math.min(linesUp.length - 1, idx + radius);
    for (let i = from; i <= to; i++) {
      for (const g of GOOD) if (linesUp[i].includes(g)) sc += 3;
      for (const b of BAD)  if (linesUp[i].includes(b)) sc -= 3;
    }
    return sc;
  };

  // 3) PRIMER PASO: regex global (cuerpo con posibles puntos/espacios + DV opcional)
  //    - captura cuerpo: 1-2 + 3 + 3 (con punto o espacio) o 7-8 corridos
  //    - DV opcional, si falta lo calculamos
  const reGlobal =
    /\b(?:RUN|RUT|RUN:)?\s*([0-9]{1,2}(?:[.\s]?[0-9]{3}){2}|[0-9]{7,8})\s*-?\s*([0-9K])?\b/g;

  const candidates = [];
  for (const m of norm.matchAll(reGlobal)) {
    const bodyDigits = m[1].replace(/\D/g, '');
    let dv = (m[2] || '').toUpperCase();
    if (bodyDigits.length < 7 || bodyDigits.length > 8) continue;

    if (!dv) {
      // calcula DV si no vino
      let sum = 0, mul = 2;
      for (let i = bodyDigits.length - 1; i >= 0; i--) {
        sum += Number(bodyDigits[i]) * mul;
        mul = mul === 7 ? 2 : mul + 1;
      }
      const res = 11 - (sum % 11);
      dv = res === 11 ? '0' : res === 10 ? 'K' : String(res);
    }
    if (!isValidRut(bodyDigits, dv)) continue;

    const pretty = `${formatWithDots(bodyDigits)}-${dv}`;
    const idx = locateLineIdx(`${bodyDigits}-${dv}`);
    const score = (bodyDigits.length === 8 ? 1 : 0) + ctxScore(idx, 2);

    candidates.push({ pretty, idx, score });
  }

  // 4) SEGUNDO PASO (fallback): “RUN … <cuerpo>” y DV en el SIGUIENTE token o línea
  //    Cubre casos donde el OCR separa el DV.
  if (!candidates.length) {
    for (let i = 0; i < linesUp.length; i++) {
      const L = linesUp[i]
        .replace(/\u00A0/g, ' ')
        .replace(/[–—−]/g, '-')
        .replace(/\s+/g, ' ');

      if (!/RUN|RUT/.test(L)) continue;

      // busca cuerpo en esta línea o la siguiente
      const bodyHere = L.match(/([0-9]{1,2}(?:[.\s]?[0-9]{3}){2}|[0-9]{7,8})/);
      const bodyNext = i + 1 < linesUp.length
        ? linesUp[i + 1].match(/([0-9]{1,2}(?:[.\s]?[0-9]{3}){2}|[0-9]{7,8})/) : null;

      const bodyRaw = (bodyHere || bodyNext)?.[1];
      if (!bodyRaw) continue;

      const bodyDigits = bodyRaw.replace(/\D/g, '');
      if (bodyDigits.length < 7 || bodyDigits.length > 8) continue;

      // DV en esta/siguiente línea (token suelto)
      const dvHere = L.match(/-?\s*([0-9K])\b(?!.*[0-9])/);
      const dvNext = i + 1 < linesUp.length ? linesUp[i + 1].match(/^\s*-?\s*([0-9K])\b/) : null;

      let dv = (dvHere || dvNext)?.[1] || '';
      if (!dv) {
        // calcula DV si no aparece
        let sum = 0, mul = 2;
        for (let k = bodyDigits.length - 1; k >= 0; k--) {
          sum += Number(bodyDigits[k]) * mul;
          mul = mul === 7 ? 2 : mul + 1;
        }
        const res = 11 - (sum % 11);
        dv = res === 11 ? '0' : res === 10 ? 'K' : String(res);
      }
      if (!isValidRut(bodyDigits, dv)) continue;

      const pretty = `${formatWithDots(bodyDigits)}-${dv}`;
      const idx = i;
      const score = 5 + (bodyDigits.length === 8 ? 1 : 0) + ctxScore(idx, 2);
      candidates.push({ pretty, idx, score });
    }
  }

  if (!candidates.length) return { rut: '', index: -1, lines };

  candidates.sort((a, b) => b.score - a.score);
  const best = candidates[0];
  return { rut: best.pretty, index: best.idx, lines };
}



function guessFullName(rawText = '', hintIndex = -1, linesArg) {
  // 1) normaliza
  const lines = (linesArg ?? rawText.split(/\r?\n+/))
    .map(s => s.trim().replace(/\s+/g, ' '))
    .filter(Boolean)
    .map(s => s.toUpperCase());

  const STOP = new Set([
    'CHILE','CÉDULA','DE','IDENTIDAD','REPÚBLICA','SERVICIO','REGISTRO','CIVIL','E','IDENTIFICACIÓN',
    'FIRMA','DEL','TITULAR','SEXO','NACIONALIDAD','FECHA','NACIMIENTO','VÁLIDA','HASTA','EMISIÓN',
    'RUN','RUT','Nº','N°','NUMERO','NÚMERO','DOCUMENTO','CARNET','CI','CEDULA','IDENTIFICACION','PAIS','PAÍS'
  ]);
  const LABELS = ['APELLIDOS','APELLIDO','NOMBRES','NOMBRE'];
  const NOISE_WORDS = /(CH?I?LE|HILE|CHECHILE|CLILE)/;

  const cleanTokens = (txt) =>
    txt.split(' ')
      .filter(w => w && !STOP.has(w) && !LABELS.includes(w) && !NOISE_WORDS.test(w))
      .join(' ')
      .trim();

  const isNameLike = (l) => {
    if (!l || /\d/.test(l) || l.length < 2) return false;
    const kept = cleanTokens(l);
    return kept.length >= 2; // al menos 2 letras tras limpieza
  };

  const getAfter = (label) => {
    const idx = lines.findIndex(l => l.replace(/\s+/g,' ') === label);
    if (idx === -1) return '';
    const collected = [];
    for (let i = idx + 1; i < Math.min(lines.length, idx + 3); i++) {
      if (isNameLike(lines[i])) collected.push(cleanTokens(lines[i]));
    }
    return collected.join(' ').trim();
  };

  // 2) estrategia principal: usar rótulos
  const apellidos = getAfter('APELLIDOS') || getAfter('APELLIDO');
  const nombres   = getAfter('NOMBRES')   || getAfter('NOMBRE');

  if (nombres && apellidos) {
    let out = `${nombres} ${apellidos}`.replace(/\b(APELLIDOS?|NOMBRES?)\b/g, '').replace(/\s+/g,' ').trim();
    return out;
  }
  if (nombres) {
    return nombres.replace(/\b(APELLIDOS?|NOMBRES?)\b/g, '').replace(/\s+/g,' ').trim();
  }
  if (apellidos) {
    return apellidos.replace(/\b(APELLIDOS?|NOMBRES?)\b/g, '').replace(/\s+/g,' ').trim();
  }

  // 3) fallback: ventana alrededor del RUT
  if (typeof hintIndex === 'number' && hintIndex >= 0) {
    const from = Math.max(0, hintIndex - 4);
    const to   = Math.min(lines.length - 1, hintIndex + 4);
    const cands = [];
    for (let i = from; i <= to; i++) {
      if (isNameLike(lines[i])) cands.push({ idx: i, txt: cleanTokens(lines[i]) });
    }
    if (cands.length) {
      cands.sort((a,b) => a.idx - b.idx);
      // une contiguas
      let merged = cands[0].txt;
      for (let i = 1; i < cands.length; i++) {
        if (cands[i].idx === cands[i-1].idx + 1) merged += ' ' + cands[i].txt;
      }
      return merged.replace(/\b(APELLIDOS?|NOMBRES?)\b/g, '').replace(/\s+/g,' ').trim();
    }
  }

  // 4) último recurso: mejores candidatas globales
  const all = lines.map(l => (isNameLike(l) ? cleanTokens(l) : '')).filter(Boolean);
  return all.join(' ').replace(/\b(APELLIDOS?|NOMBRES?)\b/g, '').replace(/\s+/g,' ').trim();
}




// ---------------- Endpoint: /api/ocr ----------------
router.post('/ocr', upload.single('file'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ error: 'No se subió ningún archivo' });
    }

    // Google Vision OCR
    const [result] = await client.textDetection({ image: { content: req.file.buffer } });
    const rawText = result?.fullTextAnnotation?.text || '';

    // Post-procesamiento
    const { rut: identification, index: rutIndex, lines } = findRutInfo(rawText);
    const fullName = guessFullName(rawText, rutIndex, lines);

    let linesDbg = [];
    if (typeof rutIndex === 'number' && rutIndex >= 0 && Array.isArray(lines)) {
      const from = Math.max(0, rutIndex - 3);
      const to = Math.min(lines.length - 1, rutIndex + 3);
      linesDbg = lines.slice(from, to + 1);
    }
    console.log('[OCR window]', { rutIndex, linesDbg });

    return res.json({ identification, fullName, rawText, linesDbg });
  } catch (err) {
    console.error('OCR error:', err);
    return res.status(500).json({ error: 'Error procesando la imagen' });
  }
});

export default router;
