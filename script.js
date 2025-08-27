function getPharmacistInfo(){
    return {
        A:{
            name: document.querySelector('input[name="pharmacists[A][name]"]').value || 'A',
            color: document.querySelector('input[name="pharmacists[A][color]"]').value || '#ff6666'
        },
        B:{
            name: document.querySelector('input[name="pharmacists[B][name]"]').value || 'B',
            color: document.querySelector('input[name="pharmacists[B][color]"]').value || '#6666ff'
        }
    };
}

function showToast(message, isError=false){
    const toast = document.createElement('div');
    toast.className = 'toast' + (isError ? ' error' : '');
    toast.textContent = message;
    document.body.appendChild(toast);
    requestAnimationFrame(()=>toast.classList.add('show'));
    setTimeout(()=>{
        toast.classList.remove('show');
        setTimeout(()=>toast.remove(),500);
    },3000);
}

function applySelectStyles(){
    const info = getPharmacistInfo();
    document.querySelectorAll('.ph-select').forEach(sel=>{
        const ph = sel.value;
        sel.style.backgroundColor = info[ph].color;
        sel.style.color = '#fff';
        sel.options[0].textContent = `${info.A.name} ${sel.dataset.slot}`;
        sel.options[0].style.backgroundColor = info.A.color;
        sel.options[0].style.color = '#fff';
        sel.options[1].textContent = `${info.B.name} ${sel.dataset.slot}`;
        sel.options[1].style.backgroundColor = info.B.color;
        sel.options[1].style.color = '#fff';
    });
    const labelA = document.getElementById('labelA');
    const labelB = document.getElementById('labelB');
    if(labelA){ labelA.textContent = info.A.name; labelA.style.color = info.A.color; }
    if(labelB){ labelB.textContent = info.B.name; labelB.style.color = info.B.color; }
}

function addOpenSegment(day, data={}){
    const container = document.querySelector(`.segments-open[data-day="${day}"]`);
    if(!container) return;
    const index = container.querySelectorAll('.segment').length;
    const seg = document.createElement('div');
    seg.className = 'segment';
    seg.dataset.index = index;
    seg.innerHTML = `
        <input type="time" name="schedule[${day}][${index}][start]" value="${data.start||''}">
        <input type="time" name="schedule[${day}][${index}][end]" value="${data.end||''}">
        <button type="button" class="remove-segment">&times;</button>
    `;
    container.appendChild(seg);
    seg.querySelectorAll('input').forEach(el=>el.addEventListener('change', calculateHours));
    seg.querySelector('.remove-segment').addEventListener('click', ()=>{
        seg.remove();
        renumberOpenSegments(day);
        calculateHours();
    });
    calculateHours();
}

function renumberOpenSegments(day){
    const container = document.querySelector(`.segments-open[data-day="${day}"]`);
    container.querySelectorAll('.segment').forEach((seg,i)=>{
        seg.dataset.index = i;
        seg.querySelectorAll('input').forEach(el=>{
            if(el.name.includes('[start]')) el.name = `schedule[${day}][${i}][start]`;
            if(el.name.includes('[end]')) el.name = `schedule[${day}][${i}][end]`;
        });
    });
}

function addPharmSegment(day, data={}){
    const container = document.querySelector(`.segments-pharm[data-day="${day}"]`);
    if(!container) return;
    const index = container.querySelectorAll('.segment').length;
    const info = getPharmacistInfo();
    const seg = document.createElement('div');
    seg.className = 'segment';
    seg.dataset.index = index;
    seg.innerHTML = `
        <input type="time" name="pharm_sched[${day}][${index}][start]" value="${data.start||''}">
        <input type="time" name="pharm_sched[${day}][${index}][end]" value="${data.end||''}">
        <select name="pharm_sched[${day}][${index}][ph1]" class="ph-select" data-slot="S1">
            <option value="A" ${data.ph1==='B'?'':'selected'}>${info.A.name} S1</option>
            <option value="B" ${data.ph1==='B'?'selected':''}>${info.B.name} S1</option>
        </select>
        <select name="pharm_sched[${day}][${index}][ph2]" class="ph-select" data-slot="S2">
            <option value="A" ${data.ph2==='B'?'':'selected'}>${info.A.name} S2</option>
            <option value="B" ${data.ph2==='B'?'selected':''}>${info.B.name} S2</option>
        </select>
        <button type="button" class="remove-pharm">&times;</button>
    `;
    const addBtn = container.querySelector('.add-pharm');
    container.insertBefore(seg, addBtn);
    seg.querySelectorAll('input').forEach(el=>el.addEventListener('change', calculateHours));
    seg.querySelectorAll('select').forEach(el=>el.addEventListener('change', ()=>{applySelectStyles();calculateHours();}));
    seg.querySelector('.remove-pharm').addEventListener('click', ()=>{
        seg.remove();
        renumberPharmSegments(day);
        calculateHours();
    });
    applySelectStyles();
    calculateHours();
}

const toastEl = document.getElementById('toast');
if(toastEl){
    if(toastEl.dataset.message) showToast(toastEl.dataset.message);
    if(toastEl.dataset.error) showToast(toastEl.dataset.error, true);
}

function renumberPharmSegments(day){
    const container = document.querySelector(`.segments-pharm[data-day="${day}"]`);
    container.querySelectorAll('.segment').forEach((seg,i)=>{
        seg.dataset.index = i;
        seg.querySelectorAll('input,select').forEach(el=>{
            if(el.name.includes('[start]')) el.name = `pharm_sched[${day}][${i}][start]`;
            if(el.name.includes('[end]')) el.name = `pharm_sched[${day}][${i}][end]`;
            if(el.name.includes('[ph1]')) el.name = `pharm_sched[${day}][${i}][ph1]`;
            if(el.name.includes('[ph2]')) el.name = `pharm_sched[${day}][${i}][ph2]`;
        });
    });
}

function calculateHours(){
    const totals = {A:{w1:0,w2:0}, B:{w1:0,w2:0}};
    let openHours = 0;
    for(let day=0; day<7; day++){
        const openContainer = document.querySelector(`.segments-open[data-day="${day}"]`);
        if(openContainer){
            openContainer.querySelectorAll('.segment').forEach(seg=>{
                const start = seg.querySelector('input[name$="[start]"]').value;
                const end = seg.querySelector('input[name$="[end]"]').value;
                if(start && end){
                    const diff = (new Date('1970-01-01T'+end) - new Date('1970-01-01T'+start))/3600000;
                    if(day < 6) openHours += diff;
                }
            });
        }
        const pharmContainer = document.querySelector(`.segments-pharm[data-day="${day}"]`);
        if(pharmContainer){
            pharmContainer.querySelectorAll('.segment').forEach(seg=>{
                const start = seg.querySelector('input[name$="[start]"]').value;
                const end = seg.querySelector('input[name$="[end]"]').value;
                const ph1 = seg.querySelector('select[name$="[ph1]"]').value;
                const ph2 = seg.querySelector('select[name$="[ph2]"]').value;
                if(start && end){
                    const diff = (new Date('1970-01-01T'+end) - new Date('1970-01-01T'+start))/3600000;
                    totals[ph1].w1 += diff;
                    totals[ph2].w2 += diff;
                }
            });
        }
    }
    document.getElementById('w1A').textContent = totals.A.w1;
    document.getElementById('w1B').textContent = totals.B.w1;
    document.getElementById('w2A').textContent = totals.A.w2;
    document.getElementById('w2B').textContent = totals.B.w2;
    document.getElementById('totA').textContent = totals.A.w1 + totals.A.w2;
    document.getElementById('totB').textContent = totals.B.w1 + totals.B.w2;
    const openHoursEl = document.getElementById('openHours');
    if(openHoursEl) openHoursEl.textContent = openHours;
    const saveBtn = document.getElementById('saveBtn');
    if(totals.A.w1 + totals.A.w2 > 70 || totals.B.w1 + totals.B.w2 > 70){
        saveBtn.disabled = true;
        saveBtn.title = 'Un pharmacien dépasse 70h sur deux semaines';
    } else {
        saveBtn.disabled = false;
        saveBtn.title = '';
    }
}

if(document.getElementById('scheduleForm')){
    document.querySelectorAll('.add-segment').forEach(btn=>{
        btn.addEventListener('click',()=>addOpenSegment(btn.dataset.day));
    });
    document.querySelectorAll('.segments-open').forEach(dayContainer=>{
        const day = dayContainer.dataset.day;
        dayContainer.querySelectorAll('.segment').forEach(seg=>{
            seg.querySelectorAll('input').forEach(el=>el.addEventListener('change', calculateHours));
            seg.querySelector('.remove-segment').addEventListener('click',()=>{
                seg.remove();
                renumberOpenSegments(day);
                calculateHours();
            });
        });
    });

    document.querySelectorAll('.add-pharm').forEach(btn=>{
        btn.addEventListener('click',()=>addPharmSegment(btn.dataset.day));
    });
    document.querySelectorAll('.segments-pharm').forEach(dayContainer=>{
        const day = dayContainer.dataset.day;
        dayContainer.querySelectorAll('.segment').forEach(seg=>{
            seg.querySelectorAll('input').forEach(el=>el.addEventListener('change', calculateHours));
            seg.querySelectorAll('select').forEach(el=>el.addEventListener('change', ()=>{applySelectStyles();calculateHours();}));
            const rem = seg.querySelector('.remove-pharm');
            if(rem) rem.addEventListener('click',()=>{
                seg.remove();
                renumberPharmSegments(day);
                calculateHours();
            });
        });
    });

    document.querySelectorAll('input[name^="pharmacists"]').forEach(inp=>inp.addEventListener('input', applySelectStyles));
    applySelectStyles();
    calculateHours();
}

