import { Controller } from '@hotwired/stimulus';
import {setCookie} from "../js/cookies";

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

    }

    seen(event) {
        event.preventDefault();

        const url = event.currentTarget.closest('[data-url]').dataset.url;
        const notiId = event.currentTarget.dataset.notiId;

        const notiBadge = event.currentTarget.querySelector('.noti-badge')

        if (null === notiBadge){
            return
        }

        notiBadge.remove()
        document.querySelector('#noti-badge-unseen').innerHTML = parseInt(document.querySelector('#noti-badge-unseen').innerHTML) - 1;

        //send locale to server as POST parameter
        fetch(url,{
            method: 'POST',
            async: true,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({notiId})
        })
            .then(data => {

            })
            .catch(error => {
                console.log('Error:',error);
            });
    }


}
