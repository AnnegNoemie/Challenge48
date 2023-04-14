import './bootstrap';

import Alpine from 'alpinejs';

import { Calendar } from '@fullcalendar/core';
window.Calendar = Calendar;

import interaction from '@fullcalendar/interaction';
window.interaction = interaction;

import dayGridPlugin from '@fullcalendar/daygrid';
window.dayGridPlugin = dayGridPlugin;

import timeGridPlugin from '@fullcalendar/timegrid';
window.timeGridPlugin = timeGridPlugin;

import listPlugin from '@fullcalendar/list';
window.listPlugin = listPlugin;



window.Alpine = Alpine;

Alpine.start();
