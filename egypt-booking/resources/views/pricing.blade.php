{{-- resources/views/pricing.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>نظام تسعير رحلات العمرة v6</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
<style>
:root{
  --g1:#0f4428; --g2:#1a6b41; --g3:#2d9e62; --gl:#e6f4ed; --gp:#f0f9f4;
  --gold:#c9a84c; --bd:#d4e3da; --bg:#f4f7f5;
  --tx:#1a2e22; --muted:#5a7a65; --wh:#fff;
  --r:12px; --rs:8px; --sh:0 2px 12px rgba(26,107,65,.08);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Cairo',sans-serif;background:var(--bg);color:var(--tx);direction:rtl;min-height:100vh}
.topbar{background:linear-gradient(135deg,var(--g1),var(--g2) 55%,var(--g3));padding:0 28px;height:66px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 16px rgba(10,50,30,.25);position:sticky;top:0;z-index:100}
.tb-brand{display:flex;align-items:center;gap:10px;color:#fff}
.tb-ico{width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px}
.tb-brand h1{font-size:16px;font-weight:700}
.tb-brand p{font-size:11px;opacity:.75}
.wrap{max-width:1180px;margin:0 auto;padding:24px 16px 60px}

/* steps */
.steps{display:flex;gap:4px;background:var(--wh);border:1px solid var(--bd);border-radius:var(--r);padding:6px;margin-bottom:22px;overflow-x:auto;box-shadow:var(--sh)}
.sb{flex:1;min-width:90px;padding:9px 6px;border:none;background:transparent;border-radius:var(--rs);cursor:pointer;font-family:'Cairo',sans-serif;font-size:11.5px;font-weight:600;color:var(--muted);display:flex;flex-direction:column;align-items:center;gap:4px;transition:.2s}
.sb:hover:not(.active){background:var(--gp);color:var(--g2)}
.sb.active{background:var(--g2);color:#fff}
.sb.done{background:var(--gl);color:var(--g1)}
.sc{width:24px;height:24px;border-radius:50%;background:rgba(0,0,0,.07);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700}
.sb.active .sc{background:rgba(255,255,255,.25)}
.sb.done .sc{background:var(--g2);color:#fff}

/* panel */
.panel{background:var(--wh);border:1px solid var(--bd);border-radius:var(--r);padding:26px;box-shadow:var(--sh);animation:fadeIn .22s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}
.pt{font-size:15px;font-weight:700;color:var(--g1);margin-bottom:18px;padding-bottom:12px;border-bottom:1.5px solid var(--gl);display:flex;align-items:center;gap:8px}
.sl{font-size:11.5px;font-weight:700;color:var(--g2);text-transform:uppercase;letter-spacing:.4px;background:var(--gl);padding:4px 12px;border-radius:20px;display:inline-block;margin:16px 0 12px}
.g2c{display:grid;grid-template-columns:1fr 1fr;gap:13px}
.g3c{display:grid;grid-template-columns:1fr 1fr 1fr;gap:11px}
.g4c{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:10px}
@media(max-width:680px){.g3c,.g4c{grid-template-columns:1fr 1fr}}
@media(max-width:430px){.g2c,.g3c,.g4c{grid-template-columns:1fr}}
.f{display:flex;flex-direction:column;gap:6px}
.f label{font-size:12px;font-weight:600;color:var(--muted)}
.f small{font-size:10.5px;color:var(--muted)}
.f input,.f select{width:100%;padding:9px 11px;border:1.5px solid var(--bd);border-radius:var(--rs);font-family:'Cairo',sans-serif;font-size:13.5px;font-weight:500;color:var(--tx);background:var(--wh);direction:rtl;transition:.18s}
.f input:focus,.f select:focus{outline:none;border-color:var(--g3);box-shadow:0 0 0 3px rgba(45,158,98,.1)}
.f input.hi{border-color:var(--gold);background:#fffdf5}
.f input.read{background:var(--gl);color:var(--g2);font-weight:700;cursor:default}
.nav{display:flex;justify-content:space-between;margin-top:26px;padding-top:18px;border-top:1px solid var(--gl);gap:10px}
.btn{padding:9px 22px;border-radius:var(--rs);border:none;cursor:pointer;font-family:'Cairo',sans-serif;font-size:13px;font-weight:700;transition:.18s}
.bp{background:var(--g2);color:#fff;box-shadow:0 2px 8px rgba(26,107,65,.22)}
.bp:hover{background:var(--g1);transform:translateY(-1px)}
.bs{background:var(--wh);color:var(--muted);border:1.5px solid var(--bd)}
.bs:hover{background:var(--gp);color:var(--g2);border-color:var(--g2)}
.bg{background:var(--gold);color:#fff;box-shadow:0 2px 8px rgba(201,168,76,.22)}
.bg:hover{background:#b8943c}

/* hotel table */
.htable{width:100%;border-collapse:collapse;font-size:12.5px;margin-top:8px}
.htable th{background:var(--g2);color:#fff;padding:8px 10px;font-weight:600;text-align:center;white-space:nowrap}
.htable th:first-child{text-align:right}
.htable td{padding:7px 8px;border-bottom:1px solid var(--gl);text-align:center;vertical-align:middle}
.htable tr:nth-child(even) td{background:var(--gp)}
.htable td:first-child{font-weight:700;color:var(--g1);text-align:right}
.htable td.calc{color:var(--g2);font-weight:600}
.htable td.inp{padding:4px 6px}
.htable td input{width:100%;padding:5px 8px;border:1.5px solid var(--bd);border-radius:6px;font-family:'Cairo',sans-serif;font-size:13px;text-align:center;background:var(--wh)}
.htable td input:focus{outline:none;border-color:var(--g3)}

/* formula */
.formula{background:linear-gradient(135deg,#fffdf5,#f7fbf8);border:1.5px dashed var(--gold);border-radius:var(--r);padding:12px 14px;margin-top:12px;font-size:12px;color:var(--g1)}
.formula strong{color:var(--g2)}
.formula .line{margin:3px 0}

/* s1s2 card */
.calc-card{background:linear-gradient(135deg,var(--g1) 0%,var(--g2) 100%);color:#fff;border-radius:var(--r);padding:20px;margin-top:18px}
.calc-card h3{font-size:13px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:6px;opacity:.9}
.cc-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.12);font-size:12.5px;gap:12px}
.cc-row:last-child{border-bottom:none}
.cc-row .lbl{opacity:.8}
.cc-row .val{font-weight:700;font-size:13px}
.cc-div{background:rgba(255,255,255,.1);border-radius:8px;padding:12px;margin:10px 0}
.cc-total{display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding:10px 0 0;border-top:1.5px solid rgba(255,255,255,.3);gap:12px}
.cc-total .lbl{font-weight:700;font-size:13.5px}
.cc-total .val{font-weight:900;font-size:18px;color:#a8e6c4}
.cc-sub{font-size:11px;opacity:.65}

/* summary */
.sum-bar{background:var(--gp);border:1px solid var(--bd);border-radius:var(--rs);padding:10px 16px;display:flex;flex-wrap:wrap;gap:14px;font-size:12px;color:var(--muted);margin-bottom:18px}
.sum-bar strong{color:var(--g1)}
.res-table{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:20px}
.res-table th{background:var(--g2);color:#fff;padding:10px 12px;font-weight:600}
.res-table td{padding:9px 12px;border-bottom:1px solid var(--gl)}
.res-table tr:nth-child(even) td{background:var(--gp)}
.res-table tr.sec td{background:var(--gl);font-weight:700;color:var(--g1);font-size:11.5px}
.res-table tr.tot td{background:var(--gl)!important;font-weight:700;color:var(--g1)}
.badge-room{display:inline-block;font-size:10.5px;padding:2px 8px;border-radius:12px;background:var(--gl);color:var(--g1);font-weight:700;margin-right:4px}

/* result cards */
.rcards{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:20px}
@media(max-width:1050px){.rcards{grid-template-columns:1fr 1fr 1fr}}
@media(max-width:600px){.rcards{grid-template-columns:1fr 1fr}}
.rcard{border:1.5px solid var(--bd);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh)}
.rcard-h{background:var(--g2);color:#fff;padding:9px 12px;font-size:12.5px;font-weight:700;text-align:center}
.rcard-b{padding:12px}
.rcard-row{display:flex;justify-content:space-between;padding:4px 0;border-bottom:.5px solid var(--gl);font-size:12px}
.rcard-row:last-child{border:none}
.rcard-row .l{color:var(--muted)}
.rcard-row .v{font-weight:700;color:var(--g1);font-size:13px}
.rcard-row .vc{color:var(--muted);font-size:12px}

.note{font-size:11.5px;color:var(--muted);background:var(--gl);padding:8px 12px;border-radius:var(--rs);margin-top:10px}

/* PDF / print */
@page{size:A4 landscape;margin:8mm}
@media print{
  .topbar,.steps,.nav,.btn{display:none!important}
  body{background:#fff!important;font-size:10px!important}
  .wrap{padding:0!important;max-width:none!important;width:100%!important}
  .panel{box-shadow:none!important;border:none!important;padding:0!important}
  .pt{font-size:14px!important;margin-bottom:8px!important;padding-bottom:6px!important}
  .sl{margin:8px 0 6px!important;font-size:10px!important}
  .sum-bar{padding:6px!important;gap:8px!important;font-size:9px!important;margin-bottom:8px!important}
  .rcards{grid-template-columns:repeat(6,1fr)!important;gap:5px!important;margin-bottom:8px!important}
  .rcard{box-shadow:none!important;border:1px solid #ccc!important;break-inside:avoid}
  .rcard-h{padding:5px!important;font-size:9px!important}
  .rcard-b{padding:5px!important}
  .rcard-row{font-size:8.5px!important;padding:2px 0!important}
  .res-table,.htable{font-size:7.5px!important;page-break-inside:auto!important;width:100%!important;table-layout:fixed!important}
  .res-table th,.htable th{padding:3px!important;white-space:normal!important;word-break:break-word!important}
  .res-table td,.htable td{padding:2px 3px!important;white-space:normal!important;word-break:break-word!important;vertical-align:top!important}
  .calc-card{background:#fff!important;color:#111!important;border:1px solid #bbb!important;padding:8px!important;break-inside:avoid}
  .cc-div{background:#f7f7f7!important;color:#111!important}
  .cc-row{border-bottom:1px solid #ddd!important;color:#111!important}
  .cc-total{border-top:1px solid #777!important;color:#111!important}
  .cc-total .val{color:#111!important}
  *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
}
</style>
</head>
<body>

<div class="topbar">
  <div class="tb-brand">
    <div class="tb-ico">🕌</div>
    <div>
      <h1>نظام تسعير رحلات العمرة</h1>
      <p>يشمل السداسي والخماسي + نقل ومشرف مرن + معادلة السعر النهائي</p>
    </div>
  </div>
  <button class="btn bg" onclick="window.print()" style="font-size:12px;padding:7px 14px">🖨️ PDF / طباعة</button>
  {{-- زر رجوع للمشروع --}}
    <a href="{{ route('trips.index') }}"
       style="position:fixed;bottom:20px;left:20px;
              background:#0f4428;color:white;
              padding:10px 18px;border-radius:8px;
              text-decoration:none;font-family:Cairo,sans-serif;
              z-index:9999;box-shadow:0 2px 8px rgba(0,0,0,.3);">
        ← رجوع للنظام
    </a>
</div>

<div class="wrap">
  <nav class="steps" id="snav"></nav>
  <div id="pc"></div>
</div>

<script>
// ═══════════════════════════ STATE ═══════════════════════════
const S = {
  gen:  { name:'', pax:45, sar:13.12, usd:49.3 },
  cost: { visa:7250, brc:2200, fa:16000, fc:8000, fi:3000 },
  trn:  {
    cycle_sar:2000,
    cycle_egp:2000 * 13.12,
    cycles:1,
    pp_egp:0,
    lastEdited:'cycle_sar'
  },
  mec:  { name:'', nights:7, sextuple:0, quint:0, quad:0, triple:0, double:0, single:0 },
  mad:  { name:'', nights:3, sextuple:0, quint:0, quad:0, triple:0, double:0, single:0 },
  sup:  {
    count:1,
    non_loaded:1,
    daily_sar:100,
    days:14,
    total_sar:100 * 14 * 1,
    total_egp:100 * 14 * 13.12,
    pp_egp:0,
    lastEdited:'daily_sar',
    mec_room:'double',
    mad_room:'double'
  },
  ext:  { misc:0, risk:0, gift:0, profit:1000 }
};

const STEPS = [
  {l:'بيانات عامة',      e:'⚙️'},
  {l:'تكاليف + نقل',     e:'💸'},
  {l:'فندق مكة',          e:'🏨'},
  {l:'فندق المدينة',       e:'🕌'},
  {l:'إشراف + مصاريف',   e:'👤'},
  {l:'النتائج',            e:'📊'}
];

const ROOMS = {
  sextuple:{l:'سداسي',n:6},
  quint:{l:'خماسي',n:5},
  quad:{l:'رباعي',n:4},
  triple:{l:'ثلاثي',n:3},
  double:{l:'ثنائي',n:2},
  single:{l:'فردي',n:1}
};

let cur = 0;

// ═══════════════════════════ CALC ═══════════════════════════
const N = k => parseFloat(k)||0;
const div = () => Math.max(1, N(S.gen.pax) - N(S.sup.non_loaded));
const sar = () => Math.max(.0001, N(S.gen.sar));

function syncTransportFrom(source) {
  const d = div();
  const cycles = Math.max(1, N(S.trn.cycles));

  if (source === 'cycle_sar') {
    S.trn.lastEdited = 'cycle_sar';
    S.trn.cycle_egp = N(S.trn.cycle_sar) * sar();
    S.trn.pp_egp = (N(S.trn.cycle_egp) * cycles) / d;
  }

  if (source === 'cycle_egp') {
    S.trn.lastEdited = 'cycle_egp';
    S.trn.cycle_sar = N(S.trn.cycle_egp) / sar();
    S.trn.pp_egp = (N(S.trn.cycle_egp) * cycles) / d;
  }

  if (source === 'pp_egp') {
    S.trn.lastEdited = 'pp_egp';
    S.trn.cycle_egp = (N(S.trn.pp_egp) * d) / cycles;
    S.trn.cycle_sar = N(S.trn.cycle_egp) / sar();
  }

  if (source === 'cycles' || source === 'div' || source === 'rate') {
    if (S.trn.lastEdited === 'pp_egp') {
      S.trn.cycle_egp = (N(S.trn.pp_egp) * d) / cycles;
      S.trn.cycle_sar = N(S.trn.cycle_egp) / sar();
    } else if (S.trn.lastEdited === 'cycle_egp') {
      S.trn.cycle_sar = N(S.trn.cycle_egp) / sar();
      S.trn.pp_egp = (N(S.trn.cycle_egp) * cycles) / d;
    } else {
      S.trn.cycle_egp = N(S.trn.cycle_sar) * sar();
      S.trn.pp_egp = (N(S.trn.cycle_egp) * cycles) / d;
    }
  }
}

const trTotalEgp = () => N(S.trn.cycle_egp) * Math.max(1, N(S.trn.cycles));
const trPP = () => N(S.trn.pp_egp);
const hPP = (h, rt) => (N(h[rt]) * N(h.nights) / ROOMS[rt].n) * sar();
const supMec = () => hPP(S.mec, S.sup.mec_room);
const supMad = () => hPP(S.mad, S.sup.mad_room);

function syncSupervisorFrom(source) {
  const d = div();
  const days = Math.max(1, N(S.sup.days));
  const count = Math.max(1, N(S.sup.count));
  const dayCount = days * count;

  if (source === 'daily_sar') {
    S.sup.lastEdited = 'daily_sar';
    S.sup.total_sar = N(S.sup.daily_sar) * dayCount;
    S.sup.total_egp = N(S.sup.total_sar) * sar();
    S.sup.pp_egp = N(S.sup.total_egp) / d;
  }

  if (source === 'total_sar') {
    S.sup.lastEdited = 'total_sar';
    S.sup.total_egp = N(S.sup.total_sar) * sar();
    S.sup.pp_egp = N(S.sup.total_egp) / d;
    S.sup.daily_sar = N(S.sup.total_sar) / dayCount;
  }

  if (source === 'total_egp') {
    S.sup.lastEdited = 'total_egp';
    S.sup.total_sar = N(S.sup.total_egp) / sar();
    S.sup.pp_egp = N(S.sup.total_egp) / d;
    S.sup.daily_sar = N(S.sup.total_sar) / dayCount;
  }

  if (source === 'pp_egp') {
    S.sup.lastEdited = 'pp_egp';
    S.sup.total_egp = N(S.sup.pp_egp) * d;
    S.sup.total_sar = N(S.sup.total_egp) / sar();
    S.sup.daily_sar = N(S.sup.total_sar) / dayCount;
  }

  if (source === 'days' || source === 'count' || source === 'div' || source === 'rate') {
    if (S.sup.lastEdited === 'pp_egp') {
      S.sup.total_egp = N(S.sup.pp_egp) * d;
      S.sup.total_sar = N(S.sup.total_egp) / sar();
      S.sup.daily_sar = N(S.sup.total_sar) / dayCount;
    } else if (S.sup.lastEdited === 'total_egp') {
      S.sup.total_sar = N(S.sup.total_egp) / sar();
      S.sup.pp_egp = N(S.sup.total_egp) / d;
      S.sup.daily_sar = N(S.sup.total_sar) / dayCount;
    } else if (S.sup.lastEdited === 'total_sar') {
      S.sup.total_egp = N(S.sup.total_sar) * sar();
      S.sup.pp_egp = N(S.sup.total_egp) / d;
      S.sup.daily_sar = N(S.sup.total_sar) / dayCount;
    } else {
      S.sup.total_sar = N(S.sup.daily_sar) * dayCount;
      S.sup.total_egp = N(S.sup.total_sar) * sar();
      S.sup.pp_egp = N(S.sup.total_egp) / d;
    }
  }
}

const supTotalEgp = () => N(S.sup.total_egp);
const supTotalSar = () => N(S.sup.total_sar);
const s1 = () => (N(S.cost.fa) + N(S.cost.visa) + N(S.cost.brc) + trPP() + supMec() + supMad()) / div();
const s2 = () => N(S.sup.pp_egp);
const supCost = () => s1() + s2();

const finalA = rt => N(S.cost.fa) + N(S.cost.visa) + N(S.cost.brc) + trPP() + hPP(S.mec,rt) + hPP(S.mad,rt) + supCost() + N(S.ext.misc) + N(S.ext.risk) + N(S.ext.gift) + N(S.ext.profit);
const finalC = rt => N(S.cost.fc) + N(S.cost.visa) + N(S.cost.brc) + trPP() + hPP(S.mec,rt) + hPP(S.mad,rt) + supCost() + N(S.ext.misc) + N(S.ext.risk) + N(S.ext.gift) + N(S.ext.profit);
const finalI = () => N(S.cost.fi);

function finalParts(rt, kind) {
  const flight = kind === 'adult' ? N(S.cost.fa) : N(S.cost.fc);
  const parts = {
    flight,
    visa: N(S.cost.visa),
    brc: N(S.cost.brc),
    transport: trPP(),
    mecca: hPP(S.mec, rt),
    madina: hPP(S.mad, rt),
    supervisor: supCost(),
    extras: N(S.ext.misc) + N(S.ext.risk) + N(S.ext.gift),
    profit: N(S.ext.profit)
  };
  parts.total =
    parts.flight +
    parts.visa +
    parts.brc +
    parts.transport +
    parts.mecca +
    parts.madina +
    parts.supervisor +
    parts.extras +
    parts.profit;

  return parts;
}

function finalEquationText(parts) {
  return `${EGP(parts.flight)} + ${EGP(parts.visa)} + ${EGP(parts.brc)} + ${EGP(parts.transport)} + ${EGP(parts.mecca)} + ${EGP(parts.madina)} + ${EGP(parts.supervisor)} + ${EGP(parts.extras)} + ${EGP(parts.profit)} = ${EGP(parts.total)} ج.م`;
}

function finalEquationRows(activeRooms) {
  const rows = [];

  activeRooms.forEach(([rt, room]) => {
    const adult = finalParts(rt, 'adult');
    const child = finalParts(rt, 'child');

    rows.push(`
      <tr>
        <td>${room.l}</td>
        <td>كبير</td>
        <td>${EGP(adult.flight)}</td>
        <td>${EGP(adult.visa)}</td>
        <td>${EGP(adult.brc)}</td>
        <td>${EGP(adult.transport)}</td>
        <td>${EGP(adult.mecca)}</td>
        <td>${EGP(adult.madina)}</td>
        <td>${EGP(adult.supervisor)}</td>
        <td>${EGP(adult.extras)}</td>
        <td>${EGP(adult.profit)}</td>
        <td style="font-weight:800;color:var(--g2)">${EGP(adult.total)}</td>
        <td style="font-size:11px;color:var(--muted);text-align:right">${finalEquationText(adult)}</td>
      </tr>
    `);

    rows.push(`
      <tr>
        <td>${room.l}</td>
        <td>طفل</td>
        <td>${EGP(child.flight)}</td>
        <td>${EGP(child.visa)}</td>
        <td>${EGP(child.brc)}</td>
        <td>${EGP(child.transport)}</td>
        <td>${EGP(child.mecca)}</td>
        <td>${EGP(child.madina)}</td>
        <td>${EGP(child.supervisor)}</td>
        <td>${EGP(child.extras)}</td>
        <td>${EGP(child.profit)}</td>
        <td style="font-weight:800;color:var(--g2)">${EGP(child.total)}</td>
        <td style="font-size:11px;color:var(--muted);text-align:right">${finalEquationText(child)}</td>
      </tr>
    `);
  });

  rows.push(`
    <tr class="tot">
      <td>—</td>
      <td>رضيع</td>
      <td>${EGP(finalI())}</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>0</td>
      <td>${EGP(finalI())}</td>
      <td style="font-size:11px;text-align:right">${EGP(finalI())} = ${EGP(finalI())} ج.م</td>
    </tr>
  `);

  return rows.join('');
}

const EGP = n => Math.round(N(n)).toLocaleString('ar-EG');
const SAR = n => N(n).toLocaleString('ar-EG', {maximumFractionDigits:2});

// ═══════════════════════════ NAV ═══════════════════════════
function nav() {
  document.getElementById('snav').innerHTML = STEPS.map((s,i)=>`
    <button class="sb ${i===cur?'active':i<cur?'done':''}" onclick="go(${i})">
      <span style="font-size:15px">${s.e}</span>
      <div class="sc">${i<cur?'✓':i+1}</div>
      <span>${s.l}</span>
    </button>`).join('');
}

function go(i){ cur=i; syncTransportFrom('div'); syncSupervisorFrom('div'); PAGES[i](); }
function set(pc){ document.getElementById('pc').innerHTML=pc; nav(); bind(); }

// ═══════════════════════════ TRANSPORT UI ═══════════════════════════
function transportFormula() {
  const d = div();
  const cycles = Math.max(1, N(S.trn.cycles));
  return `
    <div class="formula" id="transport_formula">
      <div class="line"><strong>إجمالي الدورة بالجنيه</strong> = سعر الدورة بالريال ${SAR(S.trn.cycle_sar)} × معامل الريال ${SAR(S.gen.sar)} = <strong>${EGP(S.trn.cycle_egp)} ج.م</strong></div>
      <div class="line"><strong>إجمالي النقل لكل الدورات</strong> = ${EGP(S.trn.cycle_egp)} × عدد الدورات ${cycles} = <strong>${EGP(trTotalEgp())} ج.م</strong></div>
      <div class="line"><strong>النقل للفرد</strong> = إجمالي النقل ${EGP(trTotalEgp())} ÷ القاسم ${d} = <strong>${EGP(trPP())} ج.م</strong></div>
      <div class="line"><strong>لو كتبت النقل للفرد</strong>: إجمالي سعر الدورة بالجنيه = النقل للفرد × القاسم ÷ عدد الدورات = ${EGP(S.trn.pp_egp)} × ${d} ÷ ${cycles} = <strong>${EGP(S.trn.cycle_egp)} ج.م</strong></div>
      <div class="line"><strong>إجمالي سعر الدورة بالريال</strong> = ${EGP(S.trn.cycle_egp)} ÷ ${SAR(S.gen.sar)} = <strong>${SAR(S.trn.cycle_sar)} ر.س</strong></div>
    </div>
  `;
}

function supervisorFormula() {
  const d = div();
  const days = Math.max(1, N(S.sup.days));
  const count = Math.max(1, N(S.sup.count));
  return `
    <div class="formula" id="supervisor_formula">
      <div class="line"><strong>إجمالي تكلفة المشرف بالريال</strong> = اليومية ${SAR(S.sup.daily_sar)} × عدد الأيام ${days} × عدد المشرفين ${count} = <strong>${SAR(supTotalSar())} ر.س</strong></div>
      <div class="line"><strong>إجمالي تكلفة المشرف بالجنيه</strong> = ${SAR(supTotalSar())} ر.س × معامل الريال ${SAR(S.gen.sar)} = <strong>${EGP(supTotalEgp())} ج.م</strong></div>
      <div class="line"><strong>قيمة المشرف لكل فرد / س2</strong> = إجمالي المشرف ${EGP(supTotalEgp())} ÷ القاسم ${d} = <strong>${EGP(s2())} ج.م</strong></div>
      <div class="line"><strong>لو كتبت قيمة المشرف لكل فرد</strong>: إجمالي تكلفة المشرف بالجنيه = ${EGP(S.sup.pp_egp)} × ${d} = <strong>${EGP(supTotalEgp())} ج.م</strong></div>
      <div class="line"><strong>إجمالي تكلفة المشرف بالريال</strong> = ${EGP(supTotalEgp())} ÷ ${SAR(S.gen.sar)} = <strong>${SAR(supTotalSar())} ر.س</strong></div>
      <div class="line"><strong>اليومية المحسوبة</strong> = ${SAR(supTotalSar())} ÷ (${days} يوم × ${count} مشرف) = <strong>${SAR(S.sup.daily_sar)} ر.س</strong></div>
    </div>
  `;
}

function updateSupervisorInputs() {
  const daily = document.getElementById('s_day');
  const totalSar = document.getElementById('s_total_sar');
  const totalEgp = document.getElementById('s_total_egp');
  const pp = document.getElementById('s_pp');
  const sf = document.getElementById('supervisor_formula');

  if (daily && document.activeElement !== daily) daily.value = Number(N(S.sup.daily_sar).toFixed(2));
  if (totalSar && document.activeElement !== totalSar) totalSar.value = Number(N(S.sup.total_sar).toFixed(2));
  if (totalEgp && document.activeElement !== totalEgp) totalEgp.value = Math.round(N(S.sup.total_egp));
  if (pp && document.activeElement !== pp) pp.value = Math.round(N(S.sup.pp_egp));

  if (sf) sf.outerHTML = supervisorFormula();
}

function updateTransportInputs() {
  const cs = document.getElementById('t_csar');
  const ce = document.getElementById('t_cegp');
  const pp = document.getElementById('t_pp');
  const tf = document.getElementById('transport_formula');
  const note = document.getElementById('transport_note');

  if (cs && document.activeElement !== cs) cs.value = Number(N(S.trn.cycle_sar).toFixed(2));
  if (ce && document.activeElement !== ce) ce.value = Math.round(N(S.trn.cycle_egp));
  if (pp && document.activeElement !== pp) pp.value = Math.round(N(S.trn.pp_egp));

  if (tf) tf.outerHTML = transportFormula();
  if (note) {
    note.textContent = `القاسم المستخدم الآن = ${div()} فرد (عدد الأفراد ${S.gen.pax} − غير المحملين ${S.sup.non_loaded}). إذا غيرت القاسم سيُعاد الحساب تلقائياً.`;
  }
}

// ═══════════════════════════ HOTEL TABLE ═══════════════════════════
function hotelTable(id, hotel) {
  const rows = Object.entries(ROOMS).map(([rt,{l,n}])=>{
    const ppSar = N(hotel[rt]) * N(hotel.nights) / n;
    const ppEgp = ppSar * sar();
    const totRoom = N(hotel[rt]) * N(hotel.nights);
    return `<tr>
      <td>${l} (${n} أسرة)</td>
      <td class="inp"><input type="number" id="${id}_${rt}" value="${hotel[rt]||''}" placeholder="0" min="0" step="0.01" inputmode="decimal"></td>
      <td class="calc"><span id="${id}_${rt}_night">${EGP(N(hotel[rt]))}</span> ر.س</td>
      <td class="calc"><span id="${id}_${rt}_total">${EGP(totRoom)}</span> ر.س</td>
      <td class="calc"><span id="${id}_${rt}_ppsar">${EGP(ppSar)}</span> ر.س</td>
      <td class="calc" style="color:var(--g1);font-weight:700"><span id="${id}_${rt}_ppegp">${EGP(ppEgp)}</span> ج.م</td>
    </tr>`;
  }).join('');
  return `<div style="overflow-x:auto">
  <table class="htable">
    <thead><tr>
      <th>نوع الغرفة</th>
      <th>سعر الغرفة/ليلة (ر.س)</th>
      <th>سعر الليلة</th>
      <th>إجمالي الفترة/غرفة</th>
      <th>نصيب الفرد (ر.س)</th>
      <th>نصيب الفرد (ج.م)</th>
    </tr></thead>
    <tbody id="${id}_tbody">${rows}</tbody>
  </table></div>`;
}

function updateHotelRow(id, hotel, rt) {
  const n = ROOMS[rt].n;
  const ppSar = N(hotel[rt]) * N(hotel.nights) / n;
  const ppEgp = ppSar * sar();
  const totRoom = N(hotel[rt]) * N(hotel.nights);

  const night = document.getElementById(`${id}_${rt}_night`);
  const total = document.getElementById(`${id}_${rt}_total`);
  const ppsar = document.getElementById(`${id}_${rt}_ppsar`);
  const ppegp = document.getElementById(`${id}_${rt}_ppegp`);

  if(night) night.textContent = EGP(N(hotel[rt]));
  if(total) total.textContent = EGP(totRoom);
  if(ppsar) ppsar.textContent = EGP(ppSar);
  if(ppegp) ppegp.textContent = EGP(ppEgp);
}

function updateAllHotelRows(id, hotel) {
  Object.keys(ROOMS).forEach(rt => updateHotelRow(id, hotel, rt));
}

function bindHotel(id, hotel) {
  Object.keys(ROOMS).forEach(rt=>{
    const el = document.getElementById(`${id}_${rt}`);
    if(el) el.addEventListener('input',()=>{
      hotel[rt] = el.value;
      updateHotelRow(id, hotel, rt);
      if(cur===4) refreshS1S2();
    });
  });
}

// ═══════════════════════════ S1 S2 CARD ═══════════════════════════
function s1s2Card() {
  const d = div();
  const tp = trPP();
  const sm = supMec();
  const sd = supMad();
  const sum = N(S.cost.fa) + N(S.cost.visa) + N(S.cost.brc) + tp + sm + sd;
  const _s1 = sum / d;
  const _s2 = s2();
  const total = _s1 + _s2;
  const supMecRoom = ROOMS[S.sup.mec_room];
  const supMadRoom = ROOMS[S.sup.mad_room];

  return `<div class="calc-card">
    <h3>📐 تفاصيل حساب تكلفة الإشراف</h3>

    <div style="background:rgba(255,255,255,.08);border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:12px">
      <span style="opacity:.8">القاسم = عدد الأفراد الكلي </span>
      <strong>(${N(S.gen.pax)})</strong>
      <span style="opacity:.8"> − غير المحملين </span>
      <strong>(${N(S.sup.non_loaded)})</strong>
      <span style="opacity:.8"> = </span>
      <strong style="font-size:15px;color:#a8e6c4">${d} فرد</strong>
    </div>

    <div class="cc-div">
      <div style="font-size:12px;font-weight:700;margin-bottom:8px;opacity:.9">ـ س1 : تكلفة المشرف الأساسية ÷ القاسم</div>
      <div class="cc-row"><span class="lbl">✈️ الطيران (كبار)</span><span class="val">${EGP(N(S.cost.fa))} ج.م</span></div>
      <div class="cc-row"><span class="lbl">🪪 التأشيرة</span><span class="val">${EGP(N(S.cost.visa))} ج.م</span></div>
      <div class="cc-row"><span class="lbl">📱 الباركود</span><span class="val">${EGP(N(S.cost.brc))} ج.م</span></div>
      <div class="cc-row">
        <span class="lbl">🚌 النقل/للفرد<br><span class="cc-sub">${SAR(S.trn.cycle_sar)} ر.س × ${N(S.trn.cycles)} دورة × ${SAR(sar())} ج.م ÷ ${d} فرد</span></span>
        <span class="val">${EGP(tp)} ج.م</span>
      </div>
      <div class="cc-row">
        <span class="lbl">🏨 فندق مكة/للفرد (${supMecRoom.l})<br>
          <span class="cc-sub">${SAR(N(S.mec[S.sup.mec_room]))} ر.س × ${N(S.mec.nights)} ليالي ÷ ${supMecRoom.n} × ${SAR(sar())}</span></span>
        <span class="val">${EGP(sm)} ج.م</span>
      </div>
      <div class="cc-row">
        <span class="lbl">🕌 فندق المدينة/للفرد (${supMadRoom.l})<br>
          <span class="cc-sub">${SAR(N(S.mad[S.sup.mad_room]))} ر.س × ${N(S.mad.nights)} ليالي ÷ ${supMadRoom.n} × ${SAR(sar())}</span></span>
        <span class="val">${EGP(sd)} ج.م</span>
      </div>
      <div style="border-top:1px solid rgba(255,255,255,.25);margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:12px;opacity:.8">المجموع = ${EGP(sum)} ج.م ÷ ${d}</span>
        <span style="font-size:15px;font-weight:900;color:#a8e6c4">س1 = ${EGP(_s1)} ج.م</span>
      </div>
    </div>

    <div class="cc-div">
      <div style="font-size:12px;font-weight:700;margin-bottom:8px;opacity:.9">ـ س2 : تكلفة المشرف المرنة ÷ القاسم</div>
      <div class="cc-row"><span class="lbl">اليومية</span><span class="val">${SAR(N(S.sup.daily_sar))} ر.س</span></div>
      <div class="cc-row"><span class="lbl">إجمالي المشرف بالريال</span><span class="val">${SAR(supTotalSar())} ر.س</span></div>
      <div class="cc-row"><span class="lbl">إجمالي المشرف بالجنيه</span><span class="val">${EGP(supTotalEgp())} ج.م</span></div>
      <div class="cc-row"><span class="lbl">القاسم</span><span class="val">${d} فرد</span></div>
      <div style="border-top:1px solid rgba(255,255,255,.25);margin-top:8px;padding-top:8px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:12px;opacity:.8">${EGP(supTotalEgp())} ÷ ${d}</span>
        <span style="font-size:15px;font-weight:900;color:#a8e6c4">س2 = ${EGP(_s2)} ج.م</span>
      </div>
    </div>

    <div class="cc-total">
      <span class="lbl">فلوس الإشراف/للفرد = س1 ${EGP(_s1)} + س2 ${EGP(_s2)}</span>
      <span class="val">${EGP(total)} ج.م</span>
    </div>
  </div>`;
}

function refreshS1S2() {
  syncTransportFrom('div');
  const el = document.getElementById('s1s2box');
  if(el) el.innerHTML = s1s2Card();
}

// ═══════════════════════════ PAGES ═══════════════════════════

// 1. GENERAL
function pgGen() {
  set(`<div class="panel">
    <div class="pt"><span>⚙️</span> بيانات الرحلة العامة</div>
    <div class="g2c">
      <div class="f"><label>اسم البرنامج / الرحلة</label><input id="g_name" value="${S.gen.name}" placeholder="مثال: رحلة رمضان ٢٠٢٦"></div>
      <div class="f"><label>إجمالي عدد الأفراد (شامل المشرف)</label><input id="g_pax" type="number" min="1" value="${S.gen.pax}"></div>
    </div>
    <div class="sl">💱 أسعار الصرف</div>
    <div class="g2c">
      <div class="f"><label>سعر الريال السعودي ( جنيه )</label><input class="hi" id="g_sar" type="number" step="0.01" value="${S.gen.sar}"></div>
      <div class="f"><label>سعر الدولار الأمريكي ( جنيه )</label><input id="g_usd" type="number" step="0.01" value="${S.gen.usd}"></div>
    </div>
    <div class="note">تمت إضافة الغرفة السداسية والخماسية في كل الفنادق والنتائج، وتستطيع استخدامهما كذلك لغرفة المشرف.</div>
    <div class="nav"><div></div><button class="btn bp" onclick="go(1)">التالي &larr;</button></div>
  </div>`);
}

// 2. COSTS + TRANSPORT
function pgCosts() {
  syncTransportFrom('div');
  set(`<div class="panel">
    <div class="pt"><span>💸</span> التكاليف الأساسية والنقل</div>
    <div class="sl">🪪 التأشيرة والرسوم الحكومية</div>
    <div class="g2c">
      <div class="f"><label>التأشيرة ( جنيه )</label><input id="c_visa" type="number" value="${S.cost.visa}"></div>
      <div class="f"><label>باركود / غرفة سياحة ( جنيه )</label><input id="c_brc" type="number" value="${S.cost.brc}"></div>
    </div>
    <div class="sl">✈️ تذاكر الطيران</div>
    <div class="g3c">
      <div class="f"><label>كبار ( جنيه )</label><input class="hi" id="c_fa" type="number" value="${S.cost.fa}"></div>
      <div class="f"><label>أطفال ( جنيه )</label><input id="c_fc" type="number" value="${S.cost.fc}"></div>
      <div class="f"><label>رضع ( جنيه )</label><input id="c_fi" type="number" value="${S.cost.fi}"></div>
    </div>

    <div class="sl">🚌 النقل — اكتب أي قيمة والباقي يتحسب تلقائياً</div>
    <div class="g4c">
      <div class="f">
        <label>سعر دورة النقل ( ريال سعودي )</label>
        <input class="hi" id="t_csar" type="number" step="0.01" value="${SAR(S.trn.cycle_sar)}">
        <small>لو عدلتها: يحسب الدورة بالجنيه والنقل للفرد</small>
      </div>
      <div class="f">
        <label>سعر دورة النقل ( جنيه )</label>
        <input class="hi" id="t_cegp" type="number" step="1" value="${Math.round(S.trn.cycle_egp)}">
        <small>لو عدلتها: يحسب الريال والنقل للفرد</small>
      </div>
      <div class="f">
        <label>عدد الدورات</label>
        <input id="t_cyc" type="number" min="1" value="${S.trn.cycles}">
      </div>
      <div class="f">
        <label>النقل / للفرد ( جنيه )</label>
        <input class="hi" id="t_pp" type="number" step="1" value="${Math.round(S.trn.pp_egp)}">
        <small>لو عدلتها: يحسب إجمالي سعر الدورة</small>
      </div>
    </div>

    ${transportFormula()}
    <div class="note" id="transport_note">القاسم المستخدم الآن = ${div()} فرد (عدد الأفراد ${S.gen.pax} − غير المحملين ${S.sup.non_loaded}). يمكن تعديل القاسم في خطوة الإشراف.</div>

    <div class="nav">
      <button class="btn bs" onclick="go(0)">&rarr; السابق</button>
      <button class="btn bp" onclick="go(2)">التالي &larr;</button>
    </div>
  </div>`);
}

// 3. MECCA
function pgMecca() {
  set(`<div class="panel">
    <div class="pt"><span>🏨</span> فندق مكة المكرمة</div>
    <div class="g2c" style="margin-bottom:14px">
      <div class="f"><label>اسم الفندق</label><input id="m_name" value="${S.mec.name}" placeholder="اختياري"></div>
      <div class="f"><label>عدد الليالي بمكة</label><input class="hi" id="m_nights" type="number" min="1" value="${S.mec.nights}"></div>
    </div>
    <div class="sl">💰 أسعار الغرف / الليلة — أدخل بالريال السعودي</div>
    ${hotelTable('m', S.mec)}
    <div class="note">تمت إضافة السداسي والخماسي، وتم إصلاح مشكلة الكتابة في الخانة: لن يتم إعادة رسم الصف أثناء الكتابة، لذلك يمكنك كتابة 100 أو أي رقم طبيعي بدون أن يتقطع الإدخال.</div>
    <div class="nav">
      <button class="btn bs" onclick="go(1)">&rarr; السابق</button>
      <button class="btn bp" onclick="go(3)">التالي &larr;</button>
    </div>
  </div>`);
  bindHotel('m', S.mec);
  document.getElementById('m_name').addEventListener('input',e=>S.mec.name=e.target.value);
  document.getElementById('m_nights').addEventListener('input',e=>{S.mec.nights=e.target.value; updateAllHotelRows('m',S.mec);});
}

// 4. MADINA
function pgMadina() {
  set(`<div class="panel">
    <div class="pt"><span>🕌</span> فندق المدينة المنورة</div>
    <div class="g2c" style="margin-bottom:14px">
      <div class="f"><label>اسم الفندق</label><input id="d_name" value="${S.mad.name}" placeholder="اختياري"></div>
      <div class="f"><label>عدد الليالي بالمدينة</label><input class="hi" id="d_nights" type="number" min="1" value="${S.mad.nights}"></div>
    </div>
    <div class="sl">💰 أسعار الغرف / الليلة — أدخل بالريال السعودي</div>
    ${hotelTable('d', S.mad)}
    <div class="note">تمت إضافة السداسي والخماسي، وتم إصلاح مشكلة الكتابة في الخانة: لن يتم إعادة رسم الصف أثناء الكتابة، لذلك يمكنك كتابة 100 أو أي رقم طبيعي بدون أن يتقطع الإدخال.</div>
    <div class="nav">
      <button class="btn bs" onclick="go(2)">&rarr; السابق</button>
      <button class="btn bp" onclick="go(4)">التالي &larr;</button>
    </div>
  </div>`);
  bindHotel('d', S.mad);
  document.getElementById('d_name').addEventListener('input',e=>S.mad.name=e.target.value);
  document.getElementById('d_nights').addEventListener('input',e=>{S.mad.nights=e.target.value; updateAllHotelRows('d',S.mad);});
}

// 5. SUPERVISOR + EXTRAS
function pgSup() {
  const sel = (rt) => Object.entries(ROOMS).map(([k,{l,n}])=>`<option value="${k}" ${k===rt?'selected':''}>${l} (${n} أسرة)</option>`).join('');

  set(`<div class="panel">
    <div class="pt"><span>👤</span> الإشراف والمصاريف الإضافية</div>

    <div class="sl">🔧 إعدادات المشرف — اكتب أي قيمة والباقي يتحسب تلقائياً</div>
    <div class="g4c">
      <div class="f"><label>عدد المشرفين</label><input id="s_cnt" type="number" min="1" value="${S.sup.count}"></div>
      <div class="f"><label>غير المحملين (يُطرحون من القاسم)</label><input id="s_nl" type="number" min="0" value="${S.sup.non_loaded}"></div>
      <div class="f"><label>عدد أيام الرحلة</label><input id="s_days" type="number" min="1" value="${S.sup.days}"></div>
      <div class="f"><label>اليومية ( ريال سعودي )</label><input class="hi" id="s_day" type="number" step="0.01" value="${SAR(S.sup.daily_sar)}"><small>لو عدلتها: يحسب الإجمالي وقيمة الفرد</small></div>
      <div class="f"><label>إجمالي تكلفة المشرف ( ريال )</label><input class="hi" id="s_total_sar" type="number" step="0.01" value="${SAR(S.sup.total_sar)}"><small>لو عدلتها: يحسب اليومية وقيمة الفرد</small></div>
      <div class="f"><label>إجمالي تكلفة المشرف ( جنيه )</label><input class="hi" id="s_total_egp" type="number" step="1" value="${Math.round(S.sup.total_egp)}"><small>لو عدلتها: يحسب الريال وقيمة الفرد</small></div>
      <div class="f"><label>قيمة المشرف لكل فرد / س2 ( جنيه )</label><input class="hi" id="s_pp" type="number" step="1" value="${Math.round(S.sup.pp_egp)}"><small>لو عدلتها: يحسب إجمالي المشرف بالريال والجنيه</small></div>
    </div>
    ${supervisorFormula()}
    <div class="sl">🏨 نوع غرفة المشرف (تؤثر على س1)</div>
    <div class="g2c">
      <div class="f"><label>غرفة المشرف في مكة</label>
        <select id="s_mr">${sel(S.sup.mec_room)}</select>
      </div>
      <div class="f"><label>غرفة المشرف في المدينة</label>
        <select id="s_md">${sel(S.sup.mad_room)}</select>
      </div>
    </div>

    <div id="s1s2box">${s1s2Card()}</div>

    <div class="sl">💰 مصاريف إضافية وربحية</div>
    <div class="g4c">
      <div class="f"><label>نثريات / للفرد ( جنيه )</label><input id="e_misc" type="number" value="${S.ext.misc}"></div>
      <div class="f"><label>مخاطر / للفرد ( جنيه )</label><input id="e_risk" type="number" value="${S.ext.risk}"></div>
      <div class="f"><label>هدايا / للفرد ( جنيه )</label><input id="e_gift" type="number" value="${S.ext.gift}"></div>
      <div class="f"><label>ربح / للفرد ( جنيه )</label><input class="hi" id="e_prf" type="number" value="${S.ext.profit}"></div>
    </div>

    <div class="nav">
      <button class="btn bs" onclick="go(3)">&rarr; السابق</button>
      <button class="btn bp" onclick="go(5)">📊 النتائج النهائية</button>
    </div>
  </div>`);
}

// 6. RESULTS
function pgRes() {
  const activeRooms = Object.entries(ROOMS).filter(([rt])=>
    N(S.mec[rt])>0 || N(S.mad[rt])>0
  );
  if(activeRooms.length===0) activeRooms.push(...Object.entries(ROOMS));

  const _s1=s1(), _s2=s2(), _sc=supCost(), _tp=trPP();
  const d=div();
  const pax=N(S.gen.pax);
  const pax_nl = N(S.sup.non_loaded);

  const cards = activeRooms.map(([rt,{l,n}])=>`
    <div class="rcard">
      <div class="rcard-h">${l} (${n} أسرة)</div>
      <div class="rcard-b">
        <div class="rcard-row"><span class="l">كبار</span><span class="v">${EGP(finalA(rt))}</span></div>
        <div class="rcard-row"><span class="l">أطفال</span><span class="vc">${EGP(finalC(rt))}</span></div>
        <div class="rcard-row"><span class="l">رضع</span><span class="vc">${EGP(finalI())}</span></div>
      </div>
    </div>`).join('');

  const breakdown = [
    {lbl:'🪪 التأشيرة', val: N(S.cost.visa)},
    {lbl:'📱 الباركود / غرفة سياحة', val: N(S.cost.brc)},
    {lbl:'✈️ الطيران — كبار', val: N(S.cost.fa)},
    {lbl:'✈️ الطيران — أطفال', val: N(S.cost.fc)},
    {lbl:'✈️ الطيران — رضع', val: N(S.cost.fi)},
    {lbl:`🚌 النقل/للفرد (${SAR(N(S.trn.cycle_sar))} ر.س = ${EGP(N(S.trn.cycle_egp))} ج للدورة × ${N(S.trn.cycles)} دورة ÷ ${d} فرد)`, val: _tp},
    ...activeRooms.map(([rt,{l}])=>({lbl:`🏨 مكة — ${l} (${N(S.mec.nights)} ليالي)`, val: hPP(S.mec,rt)})),
    ...activeRooms.map(([rt,{l}])=>({lbl:`🕌 المدينة — ${l} (${N(S.mad.nights)} ليالي)`, val: hPP(S.mad,rt)})),
    {lbl:'', val:0, sec:true, label:'حساب الإشراف'},
    {lbl:`فلوس الإشراف — القاسم ${d} (${pax} − ${pax_nl} غير محملين)`, val:0, header:true},
    {lbl:`  ↳ س1 (طيران+تأشيرة+باركود+نقل+فندق مكة ${ROOMS[S.sup.mec_room].l}+المدينة ${ROOMS[S.sup.mad_room].l}) ÷ ${d}`, val: _s1},
    {lbl:`  ↳ س2 (إجمالي المشرف ${SAR(supTotalSar())} ر.س = ${EGP(supTotalEgp())} ج.م ÷ ${d})`, val: _s2},
    {lbl:'💚 إجمالي الإشراف/للفرد', val: _sc, total:true},
    {lbl:'', val:0, sec:true, label:'مصاريف إضافية'},
    {lbl:'نثريات', val: N(S.ext.misc)},
    {lbl:'مخاطر وخدمات إضافية', val: N(S.ext.risk)},
    {lbl:'هدايا', val: N(S.ext.gift)},
    {lbl:'💰 الربح/للفرد', val: N(S.ext.profit), total:true},
  ];

  const brows = breakdown.map(r=>{
    if(r.sec) return `<tr class="sec"><td colspan="2">${r.label}</td></tr>`;
    if(r.header) return `<tr><td colspan="2" style="font-size:12px;color:var(--g2);font-weight:600;background:var(--gp);padding:7px 12px">${r.lbl}</td></tr>`;
    if(r.lbl==='') return '';
    return `<tr class="${r.total?'tot':''}"><td style="font-size:12.5px">${r.lbl}</td><td style="font-weight:${r.total?700:600};color:var(--g2)">${EGP(r.val)} ج.م</td></tr>`;
  }).join('');

  set(`<div class="panel" id="printArea">
    <div class="pt"><span>📊</span> النتائج النهائية${S.gen.name?' — '+S.gen.name:''}</div>

    <div class="sum-bar">
      <span>الريال: <strong>${SAR(S.gen.sar)} ج.م</strong></span>
      <span>الأفراد: <strong>${pax}</strong></span>
      <span>القاسم: <strong>${d}</strong></span>
      <span>مكة: <strong>${S.mec.nights} ليالي</strong></span>
      <span>المدينة: <strong>${S.mad.nights} ليالي</strong></span>
      <span>النقل/فرد: <strong>${EGP(_tp)} ج.م</strong></span>
      <span>إشراف/فرد: <strong>${EGP(_sc)} ج.م</strong></span>
    </div>

    <div class="rcards">${cards}</div>

    <div class="sl">🧮 المعادلة الكبيرة النهائية لكل فرد</div>
    <div class="note">المعادلة = الطيران + التأشيرة + الباركود + النقل للفرد + فندق مكة + فندق المدينة + الإشراف + الإضافات + الربح.</div>
    <div style="overflow-x:auto">
      <table class="res-table">
        <thead>
          <tr>
            <th>الغرفة</th>
            <th>الفئة</th>
            <th>طيران</th>
            <th>تأشيرة</th>
            <th>باركود</th>
            <th>نقل</th>
            <th>مكة</th>
            <th>المدينة</th>
            <th>إشراف</th>
            <th>إضافات</th>
            <th>ربح</th>
            <th>النهائي</th>
            <th>المعادلة بالأرقام</th>
          </tr>
        </thead>
        <tbody>${finalEquationRows(activeRooms)}</tbody>
      </table>
    </div>

    <div class="sl">📂 تفاصيل مكونات السعر</div>
    <div style="overflow-x:auto">
      <table class="res-table">
        <thead><tr><th>البند</th><th style="width:150px;text-align:center">القيمة (جنيه)</th></tr></thead>
        <tbody>${brows}</tbody>
      </table>
    </div>

    <div class="nav">
      <button class="btn bs" onclick="go(4)">&rarr; تعديل</button>
      <div style="display:flex;gap:8px">
        <button class="btn bg" onclick="window.print()">🖨️ PDF / طباعة</button>
        <button class="btn bp" onclick="reset()">🔄 رحلة جديدة</button>
      </div>
    </div>
  </div>`);
}

const PAGES = [pgGen, pgCosts, pgMecca, pgMadina, pgSup, pgRes];

// ═══════════════════════════ BIND ═══════════════════════════
function bind() {
  const M = {
    g_name:['gen','name'],g_pax:['gen','pax'],g_sar:['gen','sar'],g_usd:['gen','usd'],
    c_visa:['cost','visa'],c_brc:['cost','brc'],c_fa:['cost','fa'],c_fc:['cost','fc'],c_fi:['cost','fi'],
    s_cnt:['sup','count'],s_nl:['sup','non_loaded'],s_days:['sup','days'],
    e_misc:['ext','misc'],e_risk:['ext','risk'],e_gift:['ext','gift'],e_prf:['ext','profit']
  };
  Object.entries(M).forEach(([id,[sec,fld]])=>{
    const el=document.getElementById(id);
    if(el) el.addEventListener('input',()=>{
      S[sec][fld]=el.value;
      if(id === 'g_sar') {
        syncTransportFrom('rate');
        syncSupervisorFrom('rate');
      }
      if(id === 'g_pax' || id === 's_nl') {
        syncTransportFrom('div');
        syncSupervisorFrom('div');
      }
      if(id === 's_cnt') syncSupervisorFrom('count');
      if(id === 's_days') syncSupervisorFrom('days');
      if(cur===1) updateTransportInputs();
      if(cur===4) {
        updateSupervisorInputs();
        refreshS1S2();
      }
    });
  });

  const tCsar = document.getElementById('t_csar');
  const tCegp = document.getElementById('t_cegp');
  const tCyc  = document.getElementById('t_cyc');
  const tPp   = document.getElementById('t_pp');

  if(tCsar) tCsar.addEventListener('input',()=>{
    S.trn.cycle_sar = tCsar.value;
    syncTransportFrom('cycle_sar');
    updateTransportInputs();
  });

  if(tCegp) tCegp.addEventListener('input',()=>{
    S.trn.cycle_egp = tCegp.value;
    syncTransportFrom('cycle_egp');
    updateTransportInputs();
  });

  if(tCyc) tCyc.addEventListener('input',()=>{
    S.trn.cycles = Math.max(1, N(tCyc.value));
    syncTransportFrom('cycles');
    updateTransportInputs();
  });

  if(tPp) tPp.addEventListener('input',()=>{
    S.trn.pp_egp = tPp.value;
    syncTransportFrom('pp_egp');
    updateTransportInputs();
  });

  const sDay = document.getElementById('s_day');
  const sTotalSar = document.getElementById('s_total_sar');
  const sTotalEgp = document.getElementById('s_total_egp');
  const sPp = document.getElementById('s_pp');

  if(sDay) sDay.addEventListener('input',()=>{
    S.sup.daily_sar = sDay.value;
    syncSupervisorFrom('daily_sar');
    updateSupervisorInputs();
    refreshS1S2();
  });

  if(sTotalSar) sTotalSar.addEventListener('input',()=>{
    S.sup.total_sar = sTotalSar.value;
    syncSupervisorFrom('total_sar');
    updateSupervisorInputs();
    refreshS1S2();
  });

  if(sTotalEgp) sTotalEgp.addEventListener('input',()=>{
    S.sup.total_egp = sTotalEgp.value;
    syncSupervisorFrom('total_egp');
    updateSupervisorInputs();
    refreshS1S2();
  });

  if(sPp) sPp.addEventListener('input',()=>{
    S.sup.pp_egp = sPp.value;
    syncSupervisorFrom('pp_egp');
    updateSupervisorInputs();
    refreshS1S2();
  });

  const smr=document.getElementById('s_mr');
  const smd=document.getElementById('s_md');
  if(smr) smr.addEventListener('change',()=>{S.sup.mec_room=smr.value; refreshS1S2();});
  if(smd) smd.addEventListener('change',()=>{S.sup.mad_room=smd.value; refreshS1S2();});
}

function reset() {
  if(!confirm('بدء رحلة جديدة؟ سيتم مسح جميع البيانات.')) return;
  Object.assign(S.gen,{name:'',pax:45,sar:13.12,usd:49.3});
  Object.assign(S.cost,{visa:7250,brc:2200,fa:16000,fc:8000,fi:3000});
  Object.assign(S.trn,{cycle_sar:2000,cycle_egp:2000*13.12,cycles:1,pp_egp:0,lastEdited:'cycle_sar'});
  Object.assign(S.mec,{name:'',nights:7,sextuple:0,quint:0,quad:0,triple:0,double:0,single:0});
  Object.assign(S.mad,{name:'',nights:3,sextuple:0,quint:0,quad:0,triple:0,double:0,single:0});
  Object.assign(S.sup,{count:1,non_loaded:1,daily_sar:100,days:14,total_sar:1400,total_egp:1400*13.12,pp_egp:0,lastEdited:'daily_sar',mec_room:'double',mad_room:'double'});
  Object.assign(S.ext,{misc:0,risk:0,gift:0,profit:1000});
  syncTransportFrom('cycle_sar');
  syncSupervisorFrom('daily_sar');
  go(0);
}

syncTransportFrom('cycle_sar');
syncSupervisorFrom('daily_sar');
go(0);
</script>
</body>
</html>