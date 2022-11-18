import { Controller } from '@hotwired/stimulus';
import { getCookie,setCookie } from "../js/cookies";

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

        //get locale from cookie
        const locale = getCookie('locale');
        //get local from html tag
        const htmlLocale = document.querySelector('html').getAttribute('lang');

        //if cookie locale is not empty and cookie locale is not equal to html lang
        if (locale !== '' && locale !== htmlLocale) {
            //fire click event on element with data-locale attribute equal to cookie locale
            document.querySelector(`[data-locale="${locale}"]`).click();
        }
    }

    changeLocale(event) {
        event.preventDefault();

        //get data-local from clicked element
        const locale = event.currentTarget.dataset.locale;
        const url = event.currentTarget.closest('[data-url]').dataset.url;

        //send locale to server as POST parameter
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({locale: locale})
        })
        .then(data => {

            //set cookie with locale
            setCookie('locale', locale, 365);

            //reload page
            location.reload();
        })
        .catch(error => {
            console.log('Error:', error);
        });

    }

}
