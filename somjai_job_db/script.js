/* ═══════════════════════════════════════════
   script.js — Somjai 2559 Job Application
   (address cascade handled by address.js)
═══════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {

    // ── DATE IN HEADER ──────────────────────────────────────────
    const headerDate = document.getElementById('headerDate');
    if (headerDate) {
        const now = new Date();
        headerDate.textContent = now.toLocaleDateString('th-TH', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // ── PROFILE UPLOAD ──────────────────────────────────────────
    const profileInput = document.getElementById('profileInput');
    const profilePreview = document.getElementById('profilePreview');
    const profilePlaceholder = document.getElementById('profilePlaceholder');
    const profileRemoveBtn = document.getElementById('profileRemoveBtn');
    const profileBox = document.getElementById('profileBox');

    function showProfile(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            profilePreview.src = e.target.result;
            profilePreview.classList.remove('hidden');
            profilePlaceholder.style.display = 'none';
            profileRemoveBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    if (profileInput) {
        profileInput.addEventListener('change', function() {
            if (profileInput.files[0]) showProfile(profileInput.files[0]);
        });
    }

    if (profileRemoveBtn) {
        profileRemoveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            profilePreview.src = '';
            profilePreview.classList.add('hidden');
            profilePlaceholder.style.display = '';
            profileRemoveBtn.classList.add('hidden');
            profileInput.value = '';
        });
    }

    if (profileBox) {
        profileBox.addEventListener('dragover', e => { e.preventDefault();
            profileBox.classList.add('drag-over'); });
        profileBox.addEventListener('dragleave', () => profileBox.classList.remove('drag-over'));
        profileBox.addEventListener('drop', e => {
            e.preventDefault();
            profileBox.classList.remove('drag-over');
            const f = e.dataTransfer.files[0];
            if (f && f.type.startsWith('image/')) showProfile(f);
        });
    }

    // ── AUTO AGE ────────────────────────────────────────────────
    var bdEl = document.getElementById('birthDate');
    if (bdEl) {
        bdEl.addEventListener('input', function() {
            var val = this.value.trim();
            var b;
            // Support dd/mm/yyyy or dd-mm-yyyy
            var dmyMatch = val.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
            if (dmyMatch) {
                b = new Date(dmyMatch[3], dmyMatch[2] - 1, dmyMatch[1]);
            } else {
                b = new Date(val);
            }
            if (isNaN(b.getTime())) return;
            var today = new Date();
            var age = today.getFullYear() - b.getFullYear();
            if (today.getMonth() < b.getMonth() || (today.getMonth() === b.getMonth() && today.getDate() < b.getDate())) age--;
            var ageField = document.getElementById('ageField');
            if (ageField) ageField.value = age >= 0 ? age : '';
        });
    }

    // ── CONDITIONAL BLOCKS ──────────────────────────────────────
    [
        { name: 'hasAcquaintance', val: 'มี', id: 'acquaintanceBlock' },
        { name: 'hasOwnBusiness', val: 'มี', id: 'businessBlock' },
        { name: 'canDriveCar', val: 'ได้', id: 'carGearBlock' },
        { name: 'isPregnant', val: 'ใช่', id: 'pregnantBlock' },
        { name: 'hasDisease', val: 'มี', id: 'diseaseBlock' },
        { name: 'legalStatus', val: 'ยังดำเนินคดีอยู่', id: 'legalBlock' },
    ].forEach(({ name, val, id }) => {
        const block = document.getElementById(id);
        if (!block) return;
        document.querySelectorAll(`[name="${name}"]`).forEach(r => {
            r.addEventListener('change', () => {
                block.classList.toggle('hidden', !(r.checked && r.value === val));
            });
        });
    });

    // ── RATING BUTTONS ──────────────────────────────────────────
    document.querySelectorAll('.rating-track').forEach(track => {
        const fieldName = track.dataset.field;
        const hidden = document.getElementById(fieldName);
        track.querySelectorAll('.r-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                track.querySelectorAll('.r-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                if (hidden) hidden.value = btn.dataset.v;
            });
        });
    });

    // ── WORK HISTORY TABS ───────────────────────────────────────
    buildWorkPanels();
    document.querySelectorAll('.w-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.w-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.work-panel').forEach(p => p.classList.remove('active'));
            var wp = document.querySelector('.work-panel[data-panel="' + tab.dataset.job + '"]');
            if (wp) wp.classList.add('active');
        });
    });

    // ── FORM SUBMIT ─────────────────────────────────────────────
    var appForm = document.getElementById('jobApplicationForm');
    if (appForm) {
        appForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            let valid = true;

            document.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));

            document.querySelectorAll('[required]').forEach(el => {
                const isHidden = el.closest('.cond-block.hidden') || el.closest('#regAddressFields.hidden');
                if (isHidden) return;
                if (!el.value.trim()) {
                    el.classList.add('invalid');
                    valid = false;
                    el.addEventListener('input', () => el.classList.remove('invalid'), { once: true });
                    el.addEventListener('change', () => el.classList.remove('invalid'), { once: true });
                }
            });

            if (!valid) {
                var firstInvalid = document.querySelector('.invalid');
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // ── Send to API ──────────────────────────────────────────
            const form = document.getElementById('jobApplicationForm');
            const submitBtn = form.querySelector('.submit-btn');
            const formData = new FormData(form);

            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.7';
            submitBtn.childNodes[0].textContent = 'กำลังส่ง... ';

            try {
                const res = await fetch('/SomjaiJobDB/backend/submit_application.php', {
                    method: 'POST',
                    body: formData,
                });

                // Read raw text first — avoids SyntaxError if PHP returns non-JSON
                const text = await res.text();
                console.log('Server response:', text);

                let json;
                try {
                    json = JSON.parse(text);
                } catch (_) {
                    console.error('Non-JSON response:', text);
                    alert('❌ Server error:\n' + text.substring(0, 300));
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.childNodes[0].textContent = 'ส่งใบสมัคร ';
                    return;
                }

                if (json.success) {
                    var overlay = document.getElementById('successOverlay');
                    if (overlay) overlay.classList.remove('hidden');
                } else {
                    const msg = json.errors ?
                        json.errors.join('\n') :
                        (json.error || 'เกิดข้อผิดพลาด กรุณาลองใหม่');
                    console.error('API error:', json);
                    alert('❌ ' + msg);
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.childNodes[0].textContent = 'ส่งใบสมัคร ';
                }
            } catch (err) {
                alert('❌ ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้');
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.childNodes[0].textContent = 'ส่งใบสมัคร ';
            }
        });
    }

});

/* ── WORK PANELS ───────────────────────────────────────────────── */
function buildWorkPanels() {
    const container = document.getElementById('workPanels');
    if (!container) return;
    for (let i = 1; i <= 5; i++) {
        const div = document.createElement('div');
        div.className = 'work-panel' + (i === 1 ? ' active' : '');
        div.dataset.panel = i;
        div.innerHTML = `
      <div class="work-panel-inner">
        <div class="field-grid col-2">
          <div class="field-group">
            <label class="field-label">ชื่อบริษัท</label>
            <input type="text" name="job${i}_company" class="field-input" placeholder="ชื่อบริษัท/องค์กร"/>
          </div>
          <div class="field-group">
            <label class="field-label">ประเภทธุรกิจ</label>
            <input type="text" name="job${i}_bizType" class="field-input" placeholder="เช่น ค้าปลีก, การผลิต"/>
          </div>
        </div>
        <div class="field-grid col-3">
          <div class="field-group">
            <label class="field-label">อำเภอ/เขต</label>
            <input type="text" name="job${i}_district" class="field-input" placeholder="อำเภอ/เขต"/>
          </div>
          <div class="field-group">
            <label class="field-label">จังหวัด</label>
            <input type="text" name="job${i}_province" class="field-input" placeholder="จังหวัด"/>
          </div>
          <div class="field-group">
            <label class="field-label">ตำแหน่ง</label>
            <input type="text" name="job${i}_position" class="field-input" placeholder="ตำแหน่งงาน"/>
          </div>
        </div>
        <div class="field-grid col-2">
          <div class="field-group">
            <label class="field-label">วันที่เริ่มทำงาน</label>
            <input type="text" name="job${i}_start" class="field-input" placeholder="วว/ดด/ปปปป"/>
          </div>
          <div class="field-group">
            <label class="field-label">วันที่ลาออก</label>
            <input type="text" name="job${i}_end" class="field-input" placeholder="วว/ดด/ปปปป"/>
          </div>
        </div>
        <div class="field-group">
          <label class="field-label">ลักษณะงานที่ทำ 1</label>
          <input type="text" name="job${i}_duty1" class="field-input" placeholder="ลักษณะงานหลัก"/>
        </div>
        <div class="field-group">
          <label class="field-label">ลักษณะงานที่ทำ 2</label>
          <input type="text" name="job${i}_duty2" class="field-input" placeholder="ลักษณะงานเพิ่มเติม"/>
        </div>
        <div class="field-group">
          <label class="field-label">ลักษณะงานที่ทำ 3</label>
          <input type="text" name="job${i}_duty3" class="field-input" placeholder="ลักษณะงานเพิ่มเติม"/>
        </div>
        <div class="field-group">
          <label class="field-label">สาเหตุที่ลาออก</label>
          <input type="text" name="job${i}_reason" class="field-input" placeholder="สาเหตุที่ลาออก"/>
        </div>
      </div>`;
        container.appendChild(div);
    }
}