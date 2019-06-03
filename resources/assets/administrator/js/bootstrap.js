window.Vue = require('vue');

Vue.filter('truncate', (value, length) => {
    const l = value.length;

    return value.substr(0, length) + ((l > length) ? '...' : '');
});

import MediaManager from './components/MediaManager';
import MediaCarousel from './components/MediaCarousel';
import FoldersList from './components/Folders';
import FilesList from './components/Files';
import FileInfo from './components/FileInfo';
import FileActions from './components/FileActions';
import DropZone from './components/DropZone';
import MakeDirPopup from './components/popups/MkDir';
import MovePopup from './components/popups/Move';
import RenamePopup from './components/popups/Rename';
import ModalFooter from './components/partials/ModalFooter';
import ModalHeader from './components/partials/ModalHeader';

Vue.component('MediaManager', MediaManager);
Vue.component('MediaCarousel', MediaCarousel);

Vue.component('FoldersList', FoldersList);
Vue.component('FilesList', FilesList);
Vue.component('FileInfo', FileInfo);
Vue.component('FileActions', FileActions);
Vue.component('DropZone', DropZone);

// Popups
Vue.component('MakeDirPopup', MakeDirPopup);
Vue.component('MovePopup', MovePopup);
Vue.component('RenamePopup', RenamePopup);

// Partials
Vue.component('ModalFooter', ModalFooter);
Vue.component('ModalHeader', ModalHeader);
