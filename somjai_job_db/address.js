/* ═══════════════════════════════════════════
   address.js — same-address checkbox copy
═══════════════════════════════════════════ */

/* ── same-address checkbox ──────────────────────────────────── */
function setupSameAddress() {
    const checkbox = document.getElementById('sameAddress');
    if (!checkbox) return;

    checkbox.addEventListener('change', () => {
        if (!checkbox.checked) return;

        const fields = [
            ['cur_houseNo',    'reg_houseNo'],
            ['cur_village',    'reg_village'],
            ['cur_road',       'reg_road'],
            ['cur_soi',        'reg_soi'],
        ];
        fields.forEach(([src, dst]) => {
            const s = document.querySelector(`[name="${src}"]`);
            const d = document.querySelector(`[name="${dst}"]`);
            if (s && d) d.value = s.value;
        });

        const idFields = [
            ['cur_province',    'reg_province'],
            ['cur_district',    'reg_district'],
            ['cur_subdistrict', 'reg_subdistrict'],
            ['cur_postal',      'reg_postal'],
        ];
        idFields.forEach(([srcId, dstId]) => {
            const s = document.getElementById(srcId);
            const d = document.getElementById(dstId);
            if (s && d) d.value = s.value;
        });
    });
}

/* ── INIT ───────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    setupSameAddress();
});
