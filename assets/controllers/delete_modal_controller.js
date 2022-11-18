import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {

    connect () {

    }

    submit(event) {
        event.preventDefault();

        let form = event.currentTarget

        let waitConfirm = new Promise((resolve, reject) => {

            document.getElementById("approveDelete").addEventListener( "click", function () {

                resolve(true);
            });

            document.getElementById("rejectDelete").addEventListener( "click", function () {

                reject(false);
            });
        })

        waitConfirm.then((resolve) => {

            form.submit()
        },(reject) => {

            return reject;
        })
    }

}
