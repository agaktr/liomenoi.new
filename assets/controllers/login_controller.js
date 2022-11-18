import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

    }

    login(event) {
        event.preventDefault();

        const form = new FormData(event.currentTarget.closest('form'));
        const data = Object.fromEntries(form.entries());
        const url = event.currentTarget.closest('[data-url]').dataset.url;

        fetch(url,{
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Apto-Api': 1,
            },
            body: JSON.stringify(data)
        })
            .then(res => res.json())
            .then(response => {

                console.log(response)
        })

    }

}
