import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    open(event) {

        //open modal
        document.querySelector(event.currentTarget.dataset.modalTarget).classList.toggle('show');
        //disable scroll
        document.querySelector('html').classList.toggle('overflow-hidden');
    }

    dismiss(event) {

        //close modal
        event.currentTarget.closest('.modal').classList.toggle('show');
        //enable scroll
        document.querySelector('html').classList.toggle('overflow-hidden');
    }
}
