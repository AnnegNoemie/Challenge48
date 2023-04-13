fetch('https://rawcdn.githack.com/akabab/superhero-api/0.2.0/api/all.json')
  .then((response) => response.json())
  .then(data => {
     loadData(data, 20, 24)
})

function loadData(data, start, end) {
  const tableBody = document.querySelector('#room-table tbody')
  for (let i = start; i < end; i++) {
    const room = data[i]
    const row = document.createElement('tr')
    const idCell = document.createElement('td')
    idCell.textContent = room.id
    const statusCell = document.createElement('td')
    statusCell.textContent = room.name
    const nbCell = document.createElement('td')
    nbCell.textContent = room.appearance.race
    row.appendChild(idCell)
    row.appendChild(statusCell)
    row.appendChild(nbCell)
    tableBody.appendChild(row)
  }
}
