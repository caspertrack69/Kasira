import './bootstrap';
import '@phosphor-icons/web/regular';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

Alpine.start();
