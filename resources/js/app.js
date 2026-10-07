import './bootstrap';
import './alert';
import './modal';
import Alpine from 'alpinejs';
import { registerCourseAccessPicker } from './course-access-picker';

window.Alpine = Alpine;
registerCourseAccessPicker(Alpine);
Alpine.start();
