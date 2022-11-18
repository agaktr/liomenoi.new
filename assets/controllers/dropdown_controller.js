import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

        // Close the dropdown menu if the user clicks outside of it
        window.onclick = function(event) {

            if (
                !event.target.matches('.dropdown-toggle') &&
                null === event.target.closest('.dropdown-toggle')
            ) {
                let dropdowns = document.getElementsByClassName("dropdown");
                let i;
                for (i = 0; i < dropdowns.length; i++) {
                    let openDropdown = dropdowns[i];

                    if (
                        dropdowns[i].dataset.close === 'outside' &&
                        null !== event.target.closest('.dropdown-menu')
                    ){

                        continue
                    }

                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    }

    sanitize(){
        let dropdowns = document.getElementsByClassName("dropdown");
        let i;
        for (i = 0; i < dropdowns.length; i++) {
            let openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('show')) {
                openDropdown.classList.remove('show');
            }
        }
    }

    click(event) {

        //close all dropdowns
        this.sanitize();

        event.currentTarget.closest('.dropdown').classList.toggle("show");
    }

}
