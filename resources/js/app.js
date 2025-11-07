import './bootstrap';

import Alpine from 'alpinejs';

// Import Font Awesome
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import Figtree Font
import '@fontsource/figtree/400.css';
import '@fontsource/figtree/500.css';
import '@fontsource/figtree/600.css';

// Import Tom Select
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

// Make TomSelect available globally
window.TomSelect = TomSelect;

window.Alpine = Alpine;

Alpine.start();
