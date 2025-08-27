function calculateHours(){
    let totalA=0, totalB=0;
    document.querySelectorAll('tbody tr').forEach(row=>{
        row.querySelectorAll('td.slot').forEach(slot=>{
            const inputs = slot.querySelectorAll('input');
            const sel = slot.querySelector('select');
            const start = inputs[0].value;
            const end = inputs[1].value;
            if(start && end){
                const diff = (new Date('1970-01-01T'+end) - new Date('1970-01-01T'+start))/3600000;
                if(sel.value==='A') totalA += diff; else totalB += diff;
            }
        });
    });
    document.getElementById('totalA').textContent = totalA;
    document.getElementById('totalB').textContent = totalB;
    const saveBtn=document.getElementById('saveBtn');
    if(totalA>70 || totalB>70){
        saveBtn.disabled=true;
        saveBtn.title='Un pharmacien dépasse 70h sur deux semaines';
    } else {
        saveBtn.disabled=false;
        saveBtn.title='';
    }
}

if(document.getElementById('scheduleForm')){
    calculateHours();
    document.querySelectorAll('input[type="time"], select').forEach(el=>{
        el.addEventListener('change', calculateHours);
    });
}
