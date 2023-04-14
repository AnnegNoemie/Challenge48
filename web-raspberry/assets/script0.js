function updateData() {
  fetch('http://192.168.137.23/api/floor/0')
  .then(response => response.json())
  .then(data => {

    displayData(data)

  })
  .catch(error => {
    console.error('Error:', error)
  })
}

function displayData(data){
  const roomTable = document.getElementById('room-table')
  const tbody = roomTable.getElementsByTagName('tbody')[0]
  tbody.innerHTML = ""

  data.forEach(room => {
    const tr = document.createElement('tr')
    const idTd = document.createElement('td')
    const statusTd = document.createElement('td')
    const freePlacesTd = document.createElement('td')

    idTd.textContent = room.number
    const freePlaces = parseInt(room.capacity) - parseInt(room.booked)
    freePlacesTd.textContent = freePlaces + " / " + room.capacity

    let status = "disponible"
    if (room.capacity === room.booked) {
      status = "complet"
      statusTd.classList.add("red")
    } else {
      statusTd.classList.add("green")
    }
    statusTd.textContent = status

    tr.appendChild(idTd)
    tr.appendChild(statusTd)
    tr.appendChild(freePlacesTd)
    tbody.appendChild(tr)
   
})
}

updateData()
setInterval(updateData, 60000)
