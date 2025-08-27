function updateTimeRange(day, index){
    const openSeg = document.querySelector(`.segments-open[data-day="${day}"] .segment[data-index="${index}"]`);
    const pharmSeg = document.querySelector(`.segments-pharm[data-day="${day}"] .segment[data-index="${index}"]`);
    if(!openSeg || !pharmSeg) return;
    const start = openSeg.querySelector('input[name$="[start]"]').value;
    const end = openSeg.querySelector('input[name$="[end]"]').value;
    pharmSeg.querySelector('.time-range').textContent = `${start} - ${end}`;
}

function addSegment(day, data={}){
    const openContainer = document.querySelector(`.segments-open[data-day="${day}"]`);
    const pharmContainer = document.querySelector(`.segments-pharm[data-day="${day}"]`);
    if(!openContainer || !pharmContainer) return;
    const index = openContainer.querySelectorAll('.segment').length;

    const segOpen = document.createElement('div');
    segOpen.className = 'segment';
    segOpen.dataset.index = index;
    segOpen.innerHTML = `
        <input type="time" name="schedule[${day}][${index}][start]" value="${data.start||''}">
        <input type="time" name="schedule[${day}][${index}][end]" value="${data.end||''}">
        <button type="button" class="remove-segment">&times;</button>
    `;
    openContainer.appendChild(segOpen);

    const segPharm = document.createElement('div');
    segPharm.className = 'segment';
    segPharm.dataset.index = index;
    segPharm.innerHTML = `
        <span class="time-range">${data.start||''} - ${data.end||''}</span>
        <select name="schedule[${day}][${index}][ph1]">
            <option value="A" ${data.ph1==='B'?'':'selected'}>A S1</option>
            <option value="B" ${data.ph1==='B'?'selected':''}>B S1</option>
        </select>
        <select name="schedule[${day}][${index}][ph2]">
            <option value="A" ${data.ph2==='B'?'':'selected'}>A S2</option>
            <option value="B" ${data.ph2==='B'?'selected':''}>B S2</option>
        </select>
    `;
    pharmContainer.appendChild(segPharm);

    segOpen.querySelectorAll('input').forEach(el=>{
        el.addEventListener('change', ()=>{
            updateTimeRange(day, index);
            calculateHours();
        });
    });
    segOpen.querySelector('.remove-segment').addEventListener('click', ()=>{
        segOpen.remove();
        segPharm.remove();
        renumberSegments(day);
        calculateHours();
    });
    segPharm.querySelectorAll('select').forEach(el=>el.addEventListener('change', calculateHours));
    calculateHours();
}

function renumberSegments(day){
    const openContainer = document.querySelector(`.segments-open[data-day="${day}"]`);
    const pharmContainer = document.querySelector(`.segments-pharm[data-day="${day}"]`);
    openContainer.querySelectorAll('.segment').forEach((seg,i)=>{
        seg.dataset.index = i;
        seg.querySelectorAll('input').forEach(el=>{
            if(el.name.includes('[start]')) el.name = `schedule[${day}][${i}][start]`;
            if(el.name.includes('[end]')) el.name = `schedule[${day}][${i}][end]`;
        });
    });
    pharmContainer.querySelectorAll('.segment').forEach((seg,i)=>{
        seg.dataset.index = i;
        seg.querySelectorAll('select').forEach(el=>{
            if(el.name.includes('[ph1]')) el.name = `schedule[${day}][${i}][ph1]`;
            if(el.name.includes('[ph2]')) el.name = `schedule[${day}][${i}][ph2]`;
        });
    });
    pharmContainer.querySelectorAll('.segment').forEach(seg=>updateTimeRange(day, seg.dataset.index));
}

function calculateHours(){
    const totals = {A:{w1:0,w2:0}, B:{w1:0,w2:0}};
    let openHours = 0;
    for(let day=0; day<7; day++){
        const openContainer = document.querySelector(`.segments-open[data-day="${day}"]`);
        const pharmContainer = document.querySelector(`.segments-pharm[data-day="${day}"]`);
        if(!openContainer || !pharmContainer) continue;
        openContainer.querySelectorAll('.segment').forEach(seg=>{
            const index = seg.dataset.index;
            const start = seg.querySelector('input[name$="[start]"]').value;
            const end = seg.querySelector('input[name$="[end]"]').value;
            if(start && end){
                const diff = (new Date('1970-01-01T'+end) - new Date('1970-01-01T'+start))/3600000;
                if(day < 6) openHours += diff;
                const pharmSeg = pharmContainer.querySelector(`.segment[data-index="${index}"]`);
                if(pharmSeg){
                    const ph1 = pharmSeg.querySelector('select[name$="[ph1]"]').value;
                    const ph2 = pharmSeg.querySelector('select[name$="[ph2]"]').value;
                    totals[ph1].w1 += diff;
                    totals[ph2].w2 += diff;
                }
            }
        });
    }
    document.getElementById('w1A').textContent = totals.A.w1;
    document.getElementById('w1B').textContent = totals.B.w1;
    document.getElementById('w2A').textContent = totals.A.w2;
    document.getElementById('w2B').textContent = totals.B.w2;
    document.getElementById('totA').textContent = totals.A.w1 + totals.A.w2;
    document.getElementById('totB').textContent = totals.B.w1 + totals.B.w2;
    const openHoursEl = document.getElementById('openHours');
    if(openHoursEl) openHoursEl.textContent = openHours;
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
    document.querySelectorAll('.segments-open').forEach(dayContainer=>{
        const day = dayContainer.dataset.day;
        dayContainer.querySelectorAll('.segment').forEach(seg=>{
            const index = seg.dataset.index;
            seg.querySelectorAll('input').forEach(el=>{
                el.addEventListener('change',()=>{
                    updateTimeRange(day, index);
                    calculateHours();
                });
            });
            seg.querySelector('.remove-segment').addEventListener('click',()=>{
                const pharmSeg = document.querySelector(`.segments-pharm[data-day="${day}"] .segment[data-index="${index}"]`);
                if(pharmSeg) pharmSeg.remove();
                seg.remove();
                renumberSegments(day);
                calculateHours();
            });
        });
    });
    document.querySelectorAll('.segments-pharm .segment').forEach(seg=>{
        seg.querySelectorAll('select').forEach(el=>el.addEventListener('change', calculateHours));
    });
    calculateHours();
}

