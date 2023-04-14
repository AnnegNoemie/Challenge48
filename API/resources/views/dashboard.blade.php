@php $prevFloor = null @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-amber-400 overflow-hidden shadow-sm sm:rounded-lg px-4 py-4">
                <p class="text-xl text-center"> Etage 0</p>
            </div>
        </div>
    </div>

    @foreach ($rooms as $room)
        @if ($prevFloor != $room->floor ?? 0)
            @php $prevFloor = $room->floor ?? 0  @endphp
            <div class="py-2">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-amber-400 overflow-hidden shadow-sm sm:rounded-lg px-4 py-4">
                        <p class="text-xl text-center"> Etage {{ $prevFloor }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="py-2">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center">
                            <p>{{ $room->name }}</p>
                            <p id="display-calendar-{{ $room->number }}"
                                onclick="displayCalendar('{{ $room->number }}')">
                                Réserver
                            </p>
                            <p class="hidden" id="hide-calendar-{{ $room->number }}"
                                onclick="hideCalendar('{{ $room->number }}')">
                                Masquer
                            </p>
                        </div>
                        <div id="calendar-{{ $room->number }}" class="calendar mt-5 hidden"></div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function displayCalendar(number) {
            var calendar = document.getElementById('calendar-' + number);
            // set the calendar visible
            calendar.classList.remove('hidden');
            // set the button to hide the calendar
            document.getElementById('display-calendar-' + number).classList.add('hidden');
            document.getElementById('hide-calendar-' + number).classList.remove('hidden');

            // hide automatically all calendars except the one we want to display
            var calendars = document.querySelectorAll('.calendar');
            calendars.forEach(function(calendar) {
                if (calendar.id !== 'calendar-' + number) {
                    hideCalendar(calendar.id.split('-')[1]);
                }
            });

            var calendarEl = calendar
            var calendar = new window.Calendar(calendarEl, {
                plugins: [window.interaction, window.dayGridPlugin, window.timeGridPlugin, window
                    .listPlugin
                ],
                initialView: 'timeGridWeek',
                slotDuration: '01:00:00',
                slotLabelInterval: '01:00:00',
                businessHours: {
                    daysOfWeek: [1, 2, 3, 4, 5], // du lundi au vendredi
                    startTime: '09:00', // heure d'ouverture
                    endTime: '18:00' // heure de fermeture
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('/room/' + number + '/not-available', {
                            method: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                        })
                        .then(response => response.json())
                        .then(data => successCallback(data))
                        .catch(error => failureCallback(error));
                },
                eventClick: function(info) {
                    if (info.event.title === 'Complet') {
                        return; // Do nothing if event name is 'complete'
                    }

                    if (confirm("Êtes-vous sûr de vouloir supprimer cet événement ?")) {
                        var start = info.event.start;
                        var end = info.event.end;

                        // envoyer une requête HTTP à l'API pour réserver la plage horaire
                        fetch('/room/' + number + '/unbook', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')
                                        .getAttribute('content')
                                },
                                body: JSON.stringify({
                                    start: start,
                                    end: end
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                calendar.refetchEvents();
                            })
                            .catch(error => {
                                console.error('Erreur lors de la suppression du créneau.',
                                    error);
                                info.revert();
                            });
                    }
                },
                eventAdd: function(info) {

                },
                selectable: true,
                select: function(info) {
                    var start = info.start;
                    var end = info.end;
                    if (start.toDateString() !== end.toDateString()) {
                        alert('La plage horaire sélectionnée doit être sur le même jour.');
                        return;
                    }

                    // Vérifier que la plage horaire sélectionnée est disponible
                    var events = calendar.getEvents();
                    var overlap = events.some(function(event) {
                        return info.start < event.end && info.end > event.start;
                    });
                    if (overlap) {
                        alert(
                            'La plage horaire sélectionnée n\'est pas disponible. Veuillez sélectionner une autre plage horaire.'
                        );
                        return;
                    }

                    // envoyer une requête HTTP à l'API pour réserver la plage horaire
                    fetch('/room/' + number + '/book', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                start: start,
                                end: end
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Réservation effectuée avec succès.');
                            calendar.refetchEvents();
                        })
                        .catch(error => {
                            console.error('Erreur lors de la réservation du créneau.', error);
                            info.revert();
                        });
                    alert('La plage horaire sélectionnée a été réservée avec succès !');
                }
            });
            calendar.render();

            setInterval(function() {
                calendar.refetchEvents();
            }, 60000); // 60000ms = 1 minute

            var parent = calendarEl.parentElement.parentElement.parentElement;
            parent.scrollIntoView({
                behavior: 'smooth'
            });
        }

        function hideCalendar(number) {
            var calendar = document.getElementById('calendar-' + number);
            calendar.classList.add('hidden');
            document.getElementById('display-calendar-' + number).classList.remove('hidden');
            document.getElementById('hide-calendar-' + number).classList.add('hidden');
            calendar.innerHTML = '';
        }
    </script>
</x-app-layout>
