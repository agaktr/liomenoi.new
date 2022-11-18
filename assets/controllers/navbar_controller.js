import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

        if (window.isMobile){
            this.toggle(new Event('click'))
        }
    }

    toggle(event) {
        event.preventDefault();

        document.body.classList.toggle('sidenav-active');
    }

}
