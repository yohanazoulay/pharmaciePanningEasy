function addSegment(day, data={}) {
    const container = document.querySelector(`.segments[data-day="${day}"]`);
    if (!container) return;
    const index = container.querySelectorAll('.segment').length;
    const seg = document.createElement('div');
    seg.className = 'segment';
    seg.innerHTML = `
        <input type="time" name="schedule[${day}][${index}][start]" value="${data.start||''}">
        <input type="time" name="schedule[${day}][${index}][end]" value="${data.end||''}">
        <select name="schedule[${day}][${index}][ph1]">
            <option value="A" ${data.ph1==='B'?'':'selected'}>A S1</option>
            <option value="B" ${data.ph1==='B'?'selected':''}>B S1</option>
        </select>
        <select name="schedule[${day}][${index}][ph2]">
            <option value="A" ${data.ph2==='B'?'':'selected'}>A S2</option>
            <option value="B" ${data.ph2==='B'?'selected':''}>B S2</option>
        </select>
        <button type="button" class="remove-segment">&times;</button>
    `;
    container.appendChild(seg);
    seg.querySelectorAll('input, select').forEach(el => el.addEventListener('change', calculateHours));
    seg.querySelector('.remove-segment').addEventListener('click', () => {
        seg.remove();
        renumberSegments(day);
        calculateHours();
    });
    calculateHours();
}

function renumberSegments(day){
    const container = document.querySelector(`.segments[data-day="${day}"]`);
    container.querySelectorAll('.segment').forEach((seg,i)=>{
        seg.querySelectorAll('input, select').forEach(el=>{
            if(el.name.includes('[start]')) el.name = `schedule[${day}][${i}][start]`;
            if(el.name.includes('[end]')) el.name = `schedule[${day}][${i}][end]`;
            if(el.name.includes('[ph1]')) el.name = `schedule[${day}][${i}][ph1]`;
            if(el.name.includes('[ph2]')) el.name = `schedule[${day}][${i}][ph2]`;
        });
    });
}

function calculateHours(){
    const totals = {A:{w1:0,w2:0}, B:{w1:0,w2:0}};
    let openHours = 0;
    document.querySelectorAll('.segments').forEach(dayContainer=>{
        const dayIndex = parseInt(dayContainer.dataset.day,10);
        dayContainer.querySelectorAll('.segment').forEach(seg=>{
            const start = seg.querySelector('input[name$="[start]"]').value;
            const end = seg.querySelector('input[name$="[end]"]').value;
            if(start && end){
                const diff = (new Date('1970-01-01T'+end) - new Date('1970-01-01T'+start))/3600000;
                const ph1 = seg.querySelector('select[name$="[ph1]"]').value;
                const ph2 = seg.querySelector('select[name$="[ph2]"]').value;
                totals[ph1].w1 += diff;
                totals[ph2].w2 += diff;
                if(dayIndex < 6) openHours += diff;
            }
        });
    });
    document.getElementById('w1A').textContent = totals.A.w1;
    document.getElementById('w1B').textContent = totals.B.w1;
    document.getElementById('w2A').textContent = totals.A.w2;
    document.getElementById('w2B').textContent = totals.B.w2;
    document.getElementById('totA').textContent = totals.A.w1 + totals.A.w2;
    document.getElementById('totB').textContent = totals.B.w1 + totals.B.w2;
    document.getElementById('openHours').textContent = openHours;
    const saveBtn=document.getElementById('saveBtn');
    if(totals.A.w1 + totals.A.w2 > 70 || totals.B.w1 + totals.B.w2 > 70){
        saveBtn.disabled=true;
        saveBtn.title='Un pharmacien dépasse 70h sur deux semaines';
    } else {
        saveBtn.disabled=false;
        saveBtn.title='';
    }
}

if(document.getElementById('scheduleForm')){
    document.querySelectorAll('.add-segment').forEach(btn=>{
        btn.addEventListener('click',()=>addSegment(btn.dataset.day));
    });
    document.querySelectorAll('.segment').forEach(seg=>{
        seg.querySelectorAll('input, select').forEach(el=>el.addEventListener('change', calculateHours));
        seg.querySelector('.remove-segment').addEventListener('click',()=>{
            const day = seg.closest('.segments').dataset.day;
            seg.remove();
            renumberSegments(day);
            calculateHours();
        });
    });
    calculateHours();
}
