import './bootstrap';
import Alpine from 'alpinejs';
import dataTable from './data-table';

window.Alpine = Alpine;

// Register reusable Alpine components
Alpine.data('dataTable', dataTable);

Alpine.start();
