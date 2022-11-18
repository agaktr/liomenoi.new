import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

        let collapsibles = document.getElementsByClassName("collapsible");
        let i;
        for (i = 0; i < collapsibles.length; i++) {

            let collapse = collapsibles[i].querySelector('.collapse')
            if (collapse.classList.contains('show')) {
                collapse.style.height = collapse.scrollHeight + 'px';
            }
        }
    }

    click(event) {

        let collapse = event.currentTarget.closest('.collapsible').querySelector('.collapse')
        collapse.classList.toggle("show");

        if (collapse.classList.contains('show')) {
            collapse.style.height = collapse.scrollHeight + 'px';
        }else{
            collapse.style.height = '0';
        }
    }

}
