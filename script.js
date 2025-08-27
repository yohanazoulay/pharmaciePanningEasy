const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
let schedule = initialSchedule.length ? initialSchedule : Array.from({length:14}, () => ({
    shifts: [
        {start:'', end:'', pharm:''},
        {start:'', end:'', pharm:''}
    ]
}));

function buildTable() {
    const container = document.getElementById('schedule');
    const table = document.createElement('table');
    const thead = document.createElement('thead');
    thead.innerHTML = '<tr><th>Jour</th><th>Matin</th><th>Pharmacien</th><th>Après-midi</th><th>Pharmacien</th></tr>';
    table.appendChild(thead);
    const tbody = document.createElement('tbody');
    schedule.forEach((day, index) => {
        const tr = document.createElement('tr');
        const dayName = days[index % 7] + ' S' + (Math.floor(index/7)+1);
        tr.innerHTML = `<td>${dayName}</td>`;
        day.shifts.forEach((shift, sIndex) => {
            const tdTime = document.createElement('td');
            tdTime.innerHTML = `<input type="time" value="${shift.start}" class="start"> - <input type="time" value="${shift.end}" class="end">`;
            const tdPharm = document.createElement('td');
            tdPharm.innerHTML = `<select class="pharm"><option value=""></option><option value="A">A</option><option value="B">B</option></select>`;
            tr.appendChild(tdTime);
            tr.appendChild(tdPharm);
            tdPharm.querySelector('select').value = shift.pharm;
            tdTime.querySelector('.start').addEventListener('change', e => {
                schedule[index].shifts[sIndex].start = e.target.value;
                updateTotals();
            });
            tdTime.querySelector('.end').addEventListener('change', e => {
                schedule[index].shifts[sIndex].end = e.target.value;
                updateTotals();
            });
            tdPharm.querySelector('select').addEventListener('change', e => {
                schedule[index].shifts[sIndex].pharm = e.target.value;
                updateTotals();
            });
        });
        tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    const totals = document.createElement('div');
    totals.id = 'totals';
    totals.innerHTML = 'Pharmacien A: <span id="hoursA">0</span>h / 70h max<br>Pharmacien B: <span id="hoursB">0</span>h / 70h max';
    container.innerHTML = '';
    container.appendChild(table);
    container.appendChild(totals);
    updateTotals();
}

function updateTotals() {
    let hours = {A:0, B:0};
    schedule.forEach(day => {
        day.shifts.forEach(shift => {
            if (shift.start && shift.end && shift.pharm) {
                const start = parseTime(shift.start);
                const end = parseTime(shift.end);
                if (end > start) {
                    const diff = (end - start) / 3600000;
                    hours[shift.pharm] += diff;
                }
            }
        });
    });
    document.getElementById('hoursA').textContent = hours.A.toFixed(1);
    document.getElementById('hoursB').textContent = hours.B.toFixed(1);
    document.getElementById('saveBtn').disabled = (hours.A > 70 || hours.B > 70);
}

function parseTime(t) {
    const [h,m] = t.split(':').map(Number);
    return new Date(0,0,0,h,m).getTime();
}

function save() {
    fetch('save_project.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'code=' + encodeURIComponent(projectCode) + '&schedule=' + encodeURIComponent(JSON.stringify(schedule))
    }).then(r => r.text()).then(t => {
        alert(t === 'ok' ? 'Sauvegarde réussie' : 'Erreur de sauvegarde');
    });
}

document.getElementById('saveBtn').addEventListener('click', save);
buildTable();

